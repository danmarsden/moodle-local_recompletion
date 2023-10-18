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

namespace local_recompletion;

/**
 * Observer_test.
 *
 * @package    local_recompletion
 * @copyright  2023 Dmitrii Metelkin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \local_recompletion\observer
 */
class observer_test extends \advanced_testcase {

    /**
     * Set up recompletion for a given course.
     *
     * @param int $courseid Course ID.
     * @param array $config Recompletion config for a given course. If empty default values will be aplied.
     */
    protected function set_up_recompletion(int $courseid, array $config = []): void {
        global $DB;

        $DB->delete_records('local_recompletion_config', ['course' => $courseid]);

        $defaultconfig = [
            'recompletiontype' => 'ondemand',
            'recompletionunenrolenable' => 1,
            'archivecompletiondata' => 0,
            'deletegradedata' => 1,
            'recompletionemailenable' => 0,
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
     * Test completion is reset after a user is un-enrolled based on course settings.
     */
    public function test_recompletion_rest_after_user_is_unenrolled_based_on_settings() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $enrol = enrol_get_plugin('self');

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $enrolinstance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'self'], '*', MUST_EXIST);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);

        // Enrol two users to a course.
        $enrol->enrol_user($enrolinstance, $user1->id, $studentrole->id);
        $enrol->enrol_user($enrolinstance, $user2->id, $studentrole->id);

        // Make sure we trigger reset on un-enrolment.
        $this->set_up_recompletion($course->id, ['recompletionunenrolenable' => 1]);

        $compluser1 = new \completion_completion(['userid' => $user1->id, 'course' => $course->id]);
        $compluser2 = new \completion_completion(['userid' => $user2->id, 'course' => $course->id]);

        // Initially both users are not completed.
        $this->assertFalse($compluser1->is_complete());
        $this->assertFalse($compluser2->is_complete());

        // Complete course for both.
        $compluser1->mark_complete(0);
        $compluser2->mark_complete(0);

        $compluser1 = new \completion_completion(['userid' => $user1->id, 'course' => $course->id]);
        $compluser2 = new \completion_completion(['userid' => $user2->id, 'course' => $course->id]);

        $this->assertTrue($compluser1->is_complete());
        $this->assertTrue($compluser2->is_complete());

        // Unenrol user 1.
        $enrol->unenrol_user($enrolinstance, $user1->id);

        // Confirm that user 1 is not completed anymore as he's unenrolled form a course. User 2 should still be completed.
        $compluser1 = new \completion_completion(['userid' => $user1->id, 'course' => $course->id]);
        $compluser2 = new \completion_completion(['userid' => $user2->id, 'course' => $course->id]);
        $this->assertFalse($compluser1->is_complete());
        $this->assertTrue($compluser2->is_complete());

        // Enrol back user 1 and confirm he's still not completed.
        $enrol->enrol_user($enrolinstance, $user1->id, $studentrole->id);
        $compluser1 = new \completion_completion(['userid' => $user1->id, 'course' => $course->id]);
        $compluser2 = new \completion_completion(['userid' => $user2->id, 'course' => $course->id]);
        $this->assertFalse($compluser1->is_complete());
        $this->assertTrue($compluser2->is_complete());

        // Now disable completion reset on un-enrolment.
        $this->set_up_recompletion($course->id, ['recompletionunenrolenable' => 0]);

        // Unenrol completed user 2.
        $enrol->unenrol_user($enrolinstance, $user2->id);
        $compluser1 = new \completion_completion(['userid' => $user1->id, 'course' => $course->id]);
        $compluser2 = new \completion_completion(['userid' => $user2->id, 'course' => $course->id]);

        // Confirm completion status is still completed for a user 2. User 1 should be without changes.
        $this->assertFalse($compluser1->is_complete());
        $this->assertTrue($compluser2->is_complete());

        // Enrol back user 2.
        $enrol->enrol_user($enrolinstance, $user2->id, $studentrole->id);
        $compluser1 = new \completion_completion(['userid' => $user1->id, 'course' => $course->id]);
        $compluser2 = new \completion_completion(['userid' => $user2->id, 'course' => $course->id]);

        // Completion status should be still completed for a user 2. User 1 should be without changes.
        $this->assertFalse($compluser1->is_complete());
        $this->assertTrue($compluser2->is_complete());
    }
}
