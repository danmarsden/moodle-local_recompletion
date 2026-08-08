<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_recompletion\task;

/**
 * End-to-end tests confirming the restrictgroups restriction is respected by
 * every recompletion trigger type (ondemand, period, schedule), since they
 * all funnel through check_recompletion::reset_user().
 *
 * @package    local_recompletion
 * @copyright Copyright Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_recompletion\task\check_recompletion
 */
final class check_recompletion_test extends \advanced_testcase {
    /**
     * Set up recompletion config for a given course.
     *
     * @param int $courseid Course ID.
     * @param array $config Recompletion config for a given course.
     */
    protected function set_up_recompletion(int $courseid, array $config): void {
        global $DB;

        $DB->delete_records('local_recompletion_config', ['course' => $courseid]);

        // Mirrors what the settings form always writes (including unchecked checkboxes as 0),
        // since reset_user() without an explicit $config reads raw DB rows, not the defaults
        // applied by local_recompletion_get_config().
        $defaultconfig = [
            'recompletionunenrolenable' => 0,
            'archivecompletiondata' => 0,
            'deletegradedata' => 0,
            'recompletionnotify' => '',
        ];
        $config = array_merge($defaultconfig, $config);

        foreach ($config as $name => $value) {
            $DB->insert_record('local_recompletion_config', (object) [
                'course' => $courseid,
                'name' => $name,
                'value' => $value,
            ]);
        }
    }

    /**
     * Create a course with two groups and a user enrolled/marked complete in each.
     *
     * @param int $completiontime Timestamp to use as the completion time.
     * @return array [course, groupa, groupb, usergroupa, usergroupb]
     */
    protected function set_up_course_with_groups(int $completiontime): array {
        $generator = $this->getDataGenerator();

        $course = $generator->create_course(['enablecompletion' => 1]);
        $groupa = $generator->create_group(['courseid' => $course->id]);
        $groupb = $generator->create_group(['courseid' => $course->id]);

        $usergroupa = $generator->create_and_enrol($course);
        $generator->create_group_member(['groupid' => $groupa->id, 'userid' => $usergroupa->id]);

        $usergroupb = $generator->create_and_enrol($course);
        $generator->create_group_member(['groupid' => $groupb->id, 'userid' => $usergroupb->id]);

        foreach ([$usergroupa, $usergroupb] as $user) {
            $completion = new \completion_completion(['userid' => $user->id, 'course' => $course->id]);
            $completion->mark_complete($completiontime);
        }

        return [$course, $groupa, $groupb, $usergroupa, $usergroupb];
    }

    /**
     * Test that an ondemand reset (self-service or admin-triggered single reset) respects restrictgroups.
     */
    public function test_ondemand_respects_group_restriction(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        [$course, $groupa, , $usergroupa, $usergroupb] = $this->set_up_course_with_groups(time());

        $this->set_up_recompletion($course->id, [
            'recompletiontype' => 'ondemand',
            'restrictgroups' => (string) $groupa->id,
        ]);

        $task = new check_recompletion();

        // Group A is allowed -- resets successfully.
        $errors = $task->reset_user($usergroupa->id, $course);
        $this->assertEmpty($errors);
        $completion = new \completion_completion(['userid' => $usergroupa->id, 'course' => $course->id]);
        $this->assertFalse($completion->is_complete());

        // Group B is not in the allow-list -- reset is blocked.
        $errors = $task->reset_user($usergroupb->id, $course);
        $this->assertNotEmpty($errors);
        $completion = new \completion_completion(['userid' => $usergroupb->id, 'course' => $course->id]);
        $this->assertTrue($completion->is_complete());
    }

    /**
     * Test that a period-based reset (via the scheduled task) respects restrictgroups.
     */
    public function test_period_respects_group_restriction(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enablecompletion', COMPLETION_ENABLED);

        // Completed well before the recompletionduration window, so both are due for reset.
        [$course, $groupa, , $usergroupa, $usergroupb] = $this->set_up_course_with_groups(time() - DAYSECS);

        $this->set_up_recompletion($course->id, [
            'recompletiontype' => 'period',
            'recompletionduration' => HOURSECS,
            'restrictgroups' => (string) $groupa->id,
        ]);

        $task = new check_recompletion();
        $task->execute();

        $completiona = new \completion_completion(['userid' => $usergroupa->id, 'course' => $course->id]);
        $completionb = new \completion_completion(['userid' => $usergroupb->id, 'course' => $course->id]);

        // Group A is allowed -- reset happened.
        $this->assertFalse($completiona->is_complete());
        // Group B is restricted -- completion untouched.
        $this->assertTrue($completionb->is_complete());
    }

    /**
     * Test that a schedule-based reset (via the scheduled task) respects restrictgroups.
     */
    public function test_schedule_respects_group_restriction(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enablecompletion', COMPLETION_ENABLED);

        // No nextresettime configured yet, so the first task run treats both as due.
        [$course, $groupa, , $usergroupa, $usergroupb] = $this->set_up_course_with_groups(time());

        $this->set_up_recompletion($course->id, [
            'recompletiontype' => 'schedule',
            'recompletionschedule' => '1 day',
            'restrictgroups' => (string) $groupa->id,
        ]);

        $task = new check_recompletion();
        $task->execute();

        $completiona = new \completion_completion(['userid' => $usergroupa->id, 'course' => $course->id]);
        $completionb = new \completion_completion(['userid' => $usergroupb->id, 'course' => $course->id]);

        // Group A is allowed -- reset happened.
        $this->assertFalse($completiona->is_complete());
        // Group B is restricted -- completion untouched.
        $this->assertTrue($completionb->is_complete());

        // The schedule task still records a nextresettime for the course, restricted or not.
        global $DB;
        $this->assertTrue($DB->record_exists('local_recompletion_config', [
            'course' => $course->id,
            'name' => 'nextresettime',
        ]));
    }
}
