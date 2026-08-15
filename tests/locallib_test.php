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
 * Tests for locallib functions.
 *
 * @package    local_recompletion
 * @copyright  2026 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers ::local_recompletion_update_course_completion
 */
final class locallib_test extends \advanced_testcase {
    /**
     * Ensure manual completion date edits also update criterion completion timestamps.
     */
    public function test_update_course_completion_updates_criteria_timestamps_for_selected_users_only(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $targetuser = $this->getDataGenerator()->create_user();
        $otheruser = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($targetuser->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($otheruser->id, $course->id, 'student');

        $oldtime = 1700000000;
        $newtime = 1710000000;

        $targetcompletion = new \completion_completion(['userid' => $targetuser->id, 'course' => $course->id]);
        $targetcompletion->mark_complete($oldtime);

        $othercompletion = new \completion_completion(['userid' => $otheruser->id, 'course' => $course->id]);
        $othercompletion->mark_complete($oldtime);

        $criteriaid = $DB->insert_record('course_completion_criteria', (object) [
            'course' => $course->id,
            'criteriatype' => COMPLETION_CRITERIA_TYPE_SELF,
        ]);

        $DB->insert_record('course_completion_crit_compl', (object) [
            'userid' => $targetuser->id,
            'course' => $course->id,
            'criteriaid' => $criteriaid,
            'timecompleted' => $oldtime,
        ]);
        $DB->insert_record('course_completion_crit_compl', (object) [
            'userid' => $otheruser->id,
            'course' => $course->id,
            'criteriaid' => $criteriaid,
            'timecompleted' => $oldtime,
        ]);

        \local_recompletion_update_course_completion($course->id, [$targetuser->id], $newtime);

        $targetcompletion = new \completion_completion(['userid' => $targetuser->id, 'course' => $course->id]);
        $othercompletion = new \completion_completion(['userid' => $otheruser->id, 'course' => $course->id]);

        $targetcriteriacompletion = $DB->get_record('course_completion_crit_compl', [
            'userid' => $targetuser->id,
            'course' => $course->id,
            'criteriaid' => $criteriaid,
        ], '*', MUST_EXIST);
        $othercriteriacompletion = $DB->get_record('course_completion_crit_compl', [
            'userid' => $otheruser->id,
            'course' => $course->id,
            'criteriaid' => $criteriaid,
        ], '*', MUST_EXIST);

        $this->assertEquals($newtime, (int) $targetcompletion->timecompleted);
        $this->assertEquals($oldtime, (int) $othercompletion->timecompleted);
        $this->assertEquals($newtime, (int) $targetcriteriacompletion->timecompleted);
        $this->assertEquals($oldtime, (int) $othercriteriacompletion->timecompleted);
    }
}
