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

        $fs = get_file_storage();
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'assignsubmission_file',
            'filearea' => 'submission_files',
            'itemid' => $targetsubmissionid,
            'filepath' => '/',
            'filename' => 'submission.txt',
        ], 'submission file contents');
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'assignfeedback_file',
            'filearea' => 'feedback_files',
            'itemid' => $targetgradeid,
            'filepath' => '/',
            'filename' => 'feedback.txt',
        ], 'feedback file contents');
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'assignfeedback_editpdf',
            'filearea' => 'download',
            'itemid' => $targetgradeid,
            'filepath' => '/',
            'filename' => 'feedback.pdf',
        ], 'pdf contents');
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'assignfeedback_editpdf',
            'filearea' => 'readonlypages',
            'itemid' => $targetgradeid,
            'filepath' => '/',
            'filename' => 'page1.png',
        ], 'page image contents');

        $this->assertTrue($DB->record_exists('assign_grades', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
        $this->assertTrue($DB->record_exists('assign_user_flags', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
        $this->assertTrue($DB->record_exists('assign_user_mapping', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
        $this->assertTrue($DB->record_exists('assignfeedback_comments', ['assignment' => $assign->id, 'grade' => $targetgradeid]));
        $this->assertTrue($DB->record_exists('comments', [
            'itemid' => $targetsubmissionid,
            'component' => 'assignsubmission_comments',
        ]));

        mod_assign::reset($targetuser->id, $course, (object) [
            'assign' => LOCAL_RECOMPLETION_DELETE,
            'archiveassign' => 1,
        ]);

        $this->assertFalse($DB->record_exists('assign_grades', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
        $this->assertFalse($DB->record_exists('assign_user_flags', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
        $this->assertFalse($DB->record_exists('assign_user_mapping', ['assignment' => $assign->id, 'userid' => $targetuser->id]));
        $this->assertFalse($DB->record_exists('assignfeedback_comments', ['assignment' => $assign->id, 'grade' => $targetgradeid]));
        $this->assertFalse($DB->record_exists('comments', [
            'itemid' => $targetsubmissionid,
            'component' => 'assignsubmission_comments',
        ]));
        $archivedsubmission = $DB->get_record('local_recompletion_as', [
            'assignment' => $assign->id,
            'userid' => $targetuser->id,
        ]);
        $archivedgrade = $DB->get_record('local_recompletion_ag', [
            'assignment' => $assign->id,
            'userid' => $targetuser->id,
        ]);

        if (!$archivedsubmission || !$archivedgrade) {
            throw new \coding_exception('Archived submission and grade records were not created.');
        }
        $this->assertTrue($DB->record_exists('files', [
            'component' => 'local_recompletion',
            'filearea' => 'submission_files',
            'itemid' => $archivedsubmission->id,
            'filename' => 'submission.txt',
        ]));
        $this->assertTrue($DB->record_exists('files', [
            'component' => 'local_recompletion',
            'filearea' => 'feedback_files',
            'itemid' => $archivedgrade->id,
            'filename' => 'feedback.txt',
        ]));
        $this->assertTrue($DB->record_exists('files', [
            'component' => 'local_recompletion',
            'filearea' => 'download',
            'itemid' => $archivedgrade->id,
            'filename' => 'feedback.pdf',
        ]));
        $this->assertTrue($DB->record_exists('files', [
            'component' => 'local_recompletion',
            'filearea' => 'readonlypages',
            'itemid' => $archivedgrade->id,
            'filename' => 'page1.png',
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
     * When assignment archiving is enabled, submission and feedback files should be copied into the
     * recompletion file areas before the original assignment records are removed.
     *
     * This is a focused regression test for the file archiving path used by recompletion.
     */
    public function test_reset_with_archiveassign_copies_submission_and_feedback_files(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        $student = $this->getDataGenerator()->create_user();
        $teacher = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $now = time();

        $submissionid = $DB->insert_record('assign_submission', (object) [
            'assignment' => $assign->id,
            'userid' => $student->id,
            'timecreated' => $now,
            'timemodified' => $now,
            'status' => 'submitted',
            'groupid' => 0,
            'attemptnumber' => 0,
            'latest' => 1,
        ]);

        $gradeid = $DB->insert_record('assign_grades', (object) [
            'assignment' => $assign->id,
            'userid' => $student->id,
            'timecreated' => $now,
            'timemodified' => $now,
            'grader' => $teacher->id,
            'grade' => 90,
            'penalty' => 0,
            'attemptnumber' => 0,
        ]);

        // Bump the archive tables so their next ids are guaranteed to be different from the source ids.
        while ((int) $DB->get_field_sql('SELECT COALESCE(MAX(id), 0) FROM {local_recompletion_as}') <= $submissionid) {
            $DB->insert_record('local_recompletion_as', (object) [
                'assignment' => 0,
                'userid' => 0,
                'timecreated' => $now,
                'timemodified' => $now,
                'status' => 'draft',
                'groupid' => 0,
                'attemptnumber' => 0,
                'latest' => 0,
                'course' => $course->id,
            ]);
        }
        while ((int) $DB->get_field_sql('SELECT COALESCE(MAX(id), 0) FROM {local_recompletion_ag}') <= $gradeid) {
            $DB->insert_record('local_recompletion_ag', (object) [
                'assignment' => 0,
                'userid' => 0,
                'timecreated' => $now,
                'timemodified' => $now,
                'grader' => $teacher->id,
                'grade' => 0,
                'penalty' => 0,
                'attemptnumber' => 0,
                'course' => $course->id,
            ]);
        }

        $fs = get_file_storage();
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'assignsubmission_file',
            'filearea' => 'submission_files',
            'itemid' => $submissionid,
            'filepath' => '/',
            'filename' => 'submission.txt',
        ], 'submission file contents');
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'assignfeedback_file',
            'filearea' => 'feedback_files',
            'itemid' => $gradeid,
            'filepath' => '/',
            'filename' => 'feedback.txt',
        ], 'feedback file contents');

        mod_assign::reset($student->id, $course, (object) [
            'assign' => LOCAL_RECOMPLETION_DELETE,
            'archiveassign' => 1,
        ]);

        $archivedsubmission = $DB->get_record('local_recompletion_as', [
            'assignment' => $assign->id,
            'userid' => $student->id,
        ], '*', MUST_EXIST);
        $archivedgrade = $DB->get_record('local_recompletion_ag', [
            'assignment' => $assign->id,
            'userid' => $student->id,
        ], '*', MUST_EXIST);

        $this->assertNotSame($submissionid, (int) $archivedsubmission->id);
        $this->assertNotSame($gradeid, (int) $archivedgrade->id);

        $this->assertTrue($DB->record_exists('files', [
            'component' => 'local_recompletion',
            'filearea' => 'submission_files',
            'itemid' => $archivedsubmission->id,
            'filename' => 'submission.txt',
        ]));
        $this->assertTrue($DB->record_exists('files', [
            'component' => 'local_recompletion',
            'filearea' => 'feedback_files',
            'itemid' => $archivedgrade->id,
            'filename' => 'feedback.txt',
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
