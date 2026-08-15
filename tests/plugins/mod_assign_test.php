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

namespace local_recompletion\plugins;

/**
 * Tests for mod_assign.
 *
 * @package    local_recompletion
 * @copyright  2026 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \local_recompletion\plugins\mod_assign
 */
final class mod_assign_test extends \advanced_testcase {
    /**
     * Test delete mode removes assignment-related data for the selected user only.
     */
    public function test_reset_delete_cleans_assignment_data_for_target_user_only(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        $targetuser = $this->getDataGenerator()->create_user();
        $otheruser = $this->getDataGenerator()->create_user();
        $teacher = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($targetuser->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($otheruser->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $now = time();

        $targetsubmissionid = $DB->insert_record('assign_submission', (object) [
            'assignment' => $assign->id,
            'userid' => $targetuser->id,
            'timecreated' => $now,
            'timemodified' => $now,
            'status' => 'submitted',
            'groupid' => 0,
            'attemptnumber' => 0,
            'latest' => 1,
        ]);
        $othersubmissionid = $DB->insert_record('assign_submission', (object) [
            'assignment' => $assign->id,
            'userid' => $otheruser->id,
            'timecreated' => $now,
            'timemodified' => $now,
            'status' => 'submitted',
            'groupid' => 0,
            'attemptnumber' => 0,
            'latest' => 1,
        ]);

        $targetgradeid = $DB->insert_record('assign_grades', (object) [
            'assignment' => $assign->id,
            'userid' => $targetuser->id,
            'timecreated' => $now,
            'timemodified' => $now,
            'grader' => $teacher->id,
            'grade' => 75,
            'penalty' => 0,
            'attemptnumber' => 0,
        ]);
        $othergradeid = $DB->insert_record('assign_grades', (object) [
            'assignment' => $assign->id,
            'userid' => $otheruser->id,
            'timecreated' => $now,
            'timemodified' => $now,
            'grader' => $teacher->id,
            'grade' => 80,
            'penalty' => 0,
            'attemptnumber' => 0,
        ]);

        $DB->insert_record('assignfeedback_comments', (object) [
            'assignment' => $assign->id,
            'grade' => $targetgradeid,
            'commenttext' => 'Target feedback comment',
            'commentformat' => FORMAT_HTML,
        ]);
        $DB->insert_record('assignfeedback_comments', (object) [
            'assignment' => $assign->id,
            'grade' => $othergradeid,
            'commenttext' => 'Other feedback comment',
            'commentformat' => FORMAT_HTML,
        ]);

        $DB->insert_record('assign_user_flags', (object) [
            'assignment' => $assign->id,
            'userid' => $targetuser->id,
            'locked' => 1,
            'mailed' => 0,
            'extensionduedate' => 0,
            'workflowstate' => null,
            'allocatedmarker' => 0,
        ]);
        $DB->insert_record('assign_user_flags', (object) [
            'assignment' => $assign->id,
            'userid' => $otheruser->id,
            'locked' => 1,
            'mailed' => 0,
            'extensionduedate' => 0,
            'workflowstate' => null,
            'allocatedmarker' => 0,
        ]);

        $DB->insert_record('assign_user_mapping', (object) [
            'assignment' => $assign->id,
            'userid' => $targetuser->id,
        ]);
        $DB->insert_record('assign_user_mapping', (object) [
            'assignment' => $assign->id,
            'userid' => $otheruser->id,
        ]);

        $DB->insert_record('comments', (object) [
            'contextid' => $context->id,
            'component' => 'assignsubmission_comments',
            'commentarea' => 'submission_comments',
            'itemid' => $targetsubmissionid,
            'content' => 'Teacher comment on target submission',
            'format' => FORMAT_HTML,
            'userid' => $teacher->id,
            'timecreated' => $now,
        ]);
        $DB->insert_record('comments', (object) [
            'contextid' => $context->id,
            'component' => 'assignsubmission_comments',
            'commentarea' => 'submission_comments',
            'itemid' => $targetsubmissionid,
            'content' => 'Student comment on target submission',
            'format' => FORMAT_HTML,
            'userid' => $targetuser->id,
            'timecreated' => $now,
        ]);
        $DB->insert_record('comments', (object) [
            'contextid' => $context->id,
            'component' => 'assignsubmission_comments',
            'commentarea' => 'submission_comments',
            'itemid' => $othersubmissionid,
            'content' => 'Teacher comment on other submission',
            'format' => FORMAT_HTML,
            'userid' => $teacher->id,
            'timecreated' => $now,
        ]);

        $this->assertTrue($DB->record_exists('assign_grades', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
        $this->assertTrue($DB->record_exists('assign_user_flags', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
        $this->assertTrue($DB->record_exists('assign_user_mapping', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
        $this->assertTrue($DB->record_exists('assignfeedback_comments', ['assignment' => $assign->id, 'grade' => $targetgradeid]));
        $this->assertTrue($DB->record_exists('comments', [
            'itemid' => $targetsubmissionid,
            'component' => 'assignsubmission_comments',
        ]));

        mod_assign::reset($targetuser->id, $course, (object) ['assign' => LOCAL_RECOMPLETION_DELETE]);

        $this->assertFalse($DB->record_exists('assign_grades', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
        $this->assertFalse($DB->record_exists('assign_user_flags', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
        $this->assertFalse($DB->record_exists('assign_user_mapping', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
        $this->assertFalse($DB->record_exists('assignfeedback_comments', ['assignment' => $assign->id, 'grade' => $targetgradeid]));
        $this->assertFalse($DB->record_exists('comments', [
            'itemid' => $targetsubmissionid,
            'component' => 'assignsubmission_comments',
        ]));

        $this->assertTrue($DB->record_exists('assign_grades', ['assignment' => $assign->id, 'userid' => $otheruser->id]));
        $this->assertTrue($DB->record_exists('assign_user_flags', ['assignment' => $assign->id, 'userid' => $otheruser->id]));
        $this->assertTrue($DB->record_exists('assign_user_mapping', ['assignment' => $assign->id, 'userid' => $otheruser->id]));
        $this->assertTrue($DB->record_exists('assignfeedback_comments', ['assignment' => $assign->id, 'grade' => $othergradeid]));
        $this->assertTrue($DB->record_exists('comments', [
            'itemid' => $othersubmissionid,
            'component' => 'assignsubmission_comments',
        ]));
    }

    /**
     * Test that empty assign config is a no-op.
     */
    public function test_reset_with_empty_assign_config_is_noop(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        $targetuser = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($targetuser->id, $course->id, 'student');

        $now = time();
        $DB->insert_record('assign_grades', (object) [
            'assignment' => $assign->id,
            'userid' => $targetuser->id,
            'timecreated' => $now,
            'timemodified' => $now,
            'grader' => 0,
            'grade' => 0,
            'penalty' => 0,
            'attemptnumber' => 0,
        ]);

        mod_assign::reset($targetuser->id, $course, (object) ['assign' => 0]);

        $this->assertTrue($DB->record_exists('assign_grades', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
    }
}
