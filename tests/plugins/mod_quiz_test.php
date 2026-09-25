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
 * Tests for mod_quiz.
 *
 * @package    local_recompletion
 * @copyright  2026 Dan Marsden
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \local_recompletion\plugins\mod_quiz
 */
final class mod_quiz_test extends \advanced_testcase {
    /**
     * Test delete mode removes quiz attempts and grades for the selected user only.
     */
    public function test_reset_delete_cleans_quiz_data_for_target_user_only(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $quizrecord = $DB->get_record('quiz', ['course' => $course->id]);
        $quizid = $quizrecord->id;
        $targetuser = $this->getDataGenerator()->create_user();
        $otheruser = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($targetuser->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($otheruser->id, $course->id, 'student');

        // Clean up any auto-generated quiz attempts.
        $DB->delete_records('quiz_attempts', ['quiz' => $quizid]);
        $DB->delete_records('quiz_grades', ['quiz' => $quizid]);

        $now = time();

        // Create question usages for quiz attempts.
        $cm = get_coursemodule_from_instance('quiz', $quizid);
        $context = \context_module::instance($cm->id);

        $targetusageid = $DB->insert_record('question_usages', (object) [
            'contextid' => $context->id,
            'component' => 'mod_quiz',
            'preferredbehaviour' => 'deferredfeedback',
        ]);

        $otherusageid = $DB->insert_record('question_usages', (object) [
            'contextid' => $context->id,
            'component' => 'mod_quiz',
            'preferredbehaviour' => 'deferredfeedback',
        ]);

        // Create quiz attempts for target user.
        $targetattemptid = $DB->insert_record('quiz_attempts', (object) [
            'quiz' => $quizid,
            'userid' => $targetuser->id,
            'attempt' => 1,
            'uniqueid' => $targetusageid,
            'timecreated' => $now,
            'timemodified' => $now,
            'layout' => '',
            'preview' => 0,
            'state' => 'finished',
            'sumgrades' => 50,
        ]);

        // Create quiz attempts for other user.
        $otherattemptid = $DB->insert_record('quiz_attempts', (object) [
            'quiz' => $quizid,
            'userid' => $otheruser->id,
            'attempt' => 1,
            'uniqueid' => $otherusageid,
            'timecreated' => $now,
            'timemodified' => $now,
            'layout' => '',
            'preview' => 0,
            'state' => 'finished',
            'sumgrades' => 60,
        ]);

        // Create quiz grades for both users.
        $targetgradeid = $DB->insert_record('quiz_grades', (object) [
            'quiz' => $quizid,
            'userid' => $targetuser->id,
            'grade' => 50,
            'timemodified' => $now,
        ]);

        $othergradeid = $DB->insert_record('quiz_grades', (object) [
            'quiz' => $quizid,
            'userid' => $otheruser->id,
            'grade' => 60,
            'timemodified' => $now,
        ]);

        // Verify records exist before reset.
        $this->assertTrue($DB->record_exists('quiz_attempts', ['id' => $targetattemptid]));
        $this->assertTrue($DB->record_exists('quiz_grades', ['id' => $targetgradeid]));
        $this->assertTrue($DB->record_exists('quiz_attempts', ['id' => $otherattemptid]));
        $this->assertTrue($DB->record_exists('quiz_grades', ['id' => $othergradeid]));

        // Reset target user with delete mode.
        mod_quiz::reset($targetuser->id, $course, (object) ['quiz' => LOCAL_RECOMPLETION_DELETE]);

        // Verify target user data is deleted.
        $this->assertFalse($DB->record_exists('quiz_attempts', ['id' => $targetattemptid]));
        $this->assertFalse($DB->record_exists('quiz_grades', ['id' => $targetgradeid]));

        // Verify other user data remains.
        $this->assertTrue($DB->record_exists('quiz_attempts', ['id' => $otherattemptid]));
        $this->assertTrue($DB->record_exists('quiz_grades', ['id' => $othergradeid]));
    }

    /**
     * Test delete mode with archive enabled archives quiz attempts and grades before deletion.
     */
    public function test_reset_delete_with_archive_cleans_and_archives_quiz_data(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $quizrecord = $DB->get_record('quiz', ['course' => $course->id]);
        $quizid = $quizrecord->id;
        $targetuser = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($targetuser->id, $course->id, 'student');

        // Clean up any auto-generated quiz attempts.
        $DB->delete_records('quiz_attempts', ['quiz' => $quizid]);
        $DB->delete_records('quiz_grades', ['quiz' => $quizid]);

        $now = time();

        // Create question usage for quiz attempt.
        $cm = get_coursemodule_from_instance('quiz', $quizid);
        $context = \context_module::instance($cm->id);

        $usageid = $DB->insert_record('question_usages', (object) [
            'contextid' => $context->id,
            'component' => 'mod_quiz',
            'preferredbehaviour' => 'deferredfeedback',
        ]);

        // Create quiz attempt and grade.
        $attemptid = $DB->insert_record('quiz_attempts', (object) [
            'quiz' => $quizid,
            'userid' => $targetuser->id,
            'attempt' => 1,
            'uniqueid' => $usageid,
            'timecreated' => $now,
            'timemodified' => $now,
            'layout' => '',
            'preview' => 0,
            'state' => 'finished',
            'sumgrades' => 50,
        ]);

        $gradeid = $DB->insert_record('quiz_grades', (object) [
            'quiz' => $quizid,
            'userid' => $targetuser->id,
            'grade' => 50,
            'timemodified' => $now,
        ]);

        // Reset with delete and archive mode.
        mod_quiz::reset($targetuser->id, $course, (object) [
            'quiz' => LOCAL_RECOMPLETION_DELETE,
            'archivequiz' => 1,
        ]);

        // Verify records are deleted from main tables.
        $this->assertFalse($DB->record_exists('quiz_attempts', ['id' => $attemptid]));
        $this->assertFalse($DB->record_exists('quiz_grades', ['id' => $gradeid]));

        // Verify records are archived.
        $this->assertTrue($DB->record_exists('local_recompletion_qa', ['quiz' => $quizid, 'userid' => $targetuser->id]));
        $this->assertTrue($DB->record_exists('local_recompletion_qg', ['quiz' => $quizid, 'userid' => $targetuser->id]));
    }

    /**
     * Test extra attempt mode creates or updates quiz override.
     */
    public function test_reset_extra_attempt_creates_override(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', [
            'course' => $course->id,
            'attempts' => 2,
            'timelimit' => 3600,
            'password' => 'testpassword',
        ]);
        $quizrecord = $DB->get_record('quiz', ['course' => $course->id]);
        $quizid = $quizrecord->id;
        $targetuser = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($targetuser->id, $course->id, 'student');

        // Clean up any auto-generated quiz attempts.
        $DB->delete_records('quiz_attempts', ['quiz' => $quizid]);

        $now = time();

        // Create question usage for quiz attempt.
        $cm = get_coursemodule_from_instance('quiz', $quizid);
        $context = \context_module::instance($cm->id);

        $usageid = $DB->insert_record('question_usages', (object) [
            'contextid' => $context->id,
            'component' => 'mod_quiz',
            'preferredbehaviour' => 'deferredfeedback',
        ]);

        // Create an existing quiz attempt to simulate user has attempted the quiz.
        $DB->insert_record('quiz_attempts', (object) [
            'quiz' => $quizid,
            'userid' => $targetuser->id,
            'attempt' => 1,
            'uniqueid' => $usageid,
            'timecreated' => $now,
            'timemodified' => $now,
            'layout' => '',
            'preview' => 0,
            'state' => 'finished',
            'sumgrades' => 50,
        ]);

        // Reset with extra attempt mode.
        mod_quiz::reset($targetuser->id, $course, (object) ['quiz' => LOCAL_RECOMPLETION_EXTRAATTEMPT]);

        // Verify override was created with doubled attempts (1 existing + 2 allowed = 3 total).
        $this->assertTrue($DB->record_exists('quiz_overrides', [
            'quiz' => $quizid,
            'userid' => $targetuser->id,
            'attempts' => 3,
        ]));
    }

    /**
     * Test extra attempt mode updates existing override.
     */
    public function test_reset_extra_attempt_updates_existing_override(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', [
            'course' => $course->id,
            'attempts' => 2,
        ]);
        $quizrecord = $DB->get_record('quiz', ['course' => $course->id]);
        $quizid = $quizrecord->id;
        $targetuser = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($targetuser->id, $course->id, 'student');

        // Clean up any auto-generated quiz attempts and overrides.
        $DB->delete_records('quiz_attempts', ['quiz' => $quizid]);
        $DB->delete_records('quiz_overrides', ['quiz' => $quizid]);

        $now = time();

        // Create question usage for quiz attempt.
        $cm = get_coursemodule_from_instance('quiz', $quizid);
        $context = \context_module::instance($cm->id);

        $usageid = $DB->insert_record('question_usages', (object) [
            'contextid' => $context->id,
            'component' => 'mod_quiz',
            'preferredbehaviour' => 'deferredfeedback',
        ]);

        // Create an existing quiz attempt.
        $DB->insert_record('quiz_attempts', (object) [
            'quiz' => $quizid,
            'userid' => $targetuser->id,
            'attempt' => 1,
            'uniqueid' => $usageid,
            'timecreated' => $now,
            'timemodified' => $now,
            'layout' => '',
            'preview' => 0,
            'state' => 'finished',
            'sumgrades' => 50,
        ]);

        // Create an existing override.
        $overrideid = $DB->insert_record('quiz_overrides', (object) [
            'quiz' => $quizid,
            'userid' => $targetuser->id,
            'attempts' => 1,
        ]);

        // Reset with extra attempt mode.
        mod_quiz::reset($targetuser->id, $course, (object) ['quiz' => LOCAL_RECOMPLETION_EXTRAATTEMPT]);

        // Verify override was updated (1 existing attempt + 2 allowed = 3, should update to 3 if current is 1).
        $override = $DB->get_record('quiz_overrides', ['id' => $overrideid]);
        $this->assertEquals(3, $override->attempts);
    }

    /**
     * Test delete mode with resetquizoverride removes quiz overrides.
     */
    public function test_reset_delete_with_override_reset_removes_overrides(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $quizrecord = $DB->get_record('quiz', ['course' => $course->id]);
        $quizid = $quizrecord->id;
        $targetuser = $this->getDataGenerator()->create_user();
        $otheruser = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($targetuser->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($otheruser->id, $course->id, 'student');

        // Clean up any auto-generated quiz attempts and overrides.
        $DB->delete_records('quiz_attempts', ['quiz' => $quizid]);
        $DB->delete_records('quiz_overrides', ['quiz' => $quizid]);

        // Create question usage for quiz attempt.
        $cm = get_coursemodule_from_instance('quiz', $quizid);
        $context = \context_module::instance($cm->id);

        $usageid = $DB->insert_record('question_usages', (object) [
            'contextid' => $context->id,
            'component' => 'mod_quiz',
            'preferredbehaviour' => 'deferredfeedback',
        ]);

        // Create quiz attempts.
        $DB->insert_record('quiz_attempts', (object) [
            'quiz' => $quizid,
            'userid' => $targetuser->id,
            'attempt' => 1,
            'uniqueid' => $usageid,
            'timecreated' => time(),
            'timemodified' => time(),
            'layout' => '',
            'preview' => 0,
            'state' => 'finished',
            'sumgrades' => 50,
        ]);

        // Create overrides for both users.
        $targetoverideid = $DB->insert_record('quiz_overrides', (object) [
            'quiz' => $quizid,
            'userid' => $targetuser->id,
            'attempts' => 5,
        ]);

        $otheoverideid = $DB->insert_record('quiz_overrides', (object) [
            'quiz' => $quizid,
            'userid' => $otheruser->id,
            'attempts' => 3,
        ]);

        // Reset target user with delete and resetquizoverride enabled.
        mod_quiz::reset($targetuser->id, $course, (object) [
            'quiz' => LOCAL_RECOMPLETION_DELETE,
            'resetquizoverride' => 1,
        ]);

        // Verify target user override is deleted.
        $this->assertFalse($DB->record_exists('quiz_overrides', ['id' => $targetoverideid]));

        // Verify other user override remains.
        $this->assertTrue($DB->record_exists('quiz_overrides', ['id' => $otheoverideid]));
    }

    /**
     * Test that empty quiz config is a no-op.
     */
    public function test_reset_with_empty_quiz_config_is_noop(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $quizrecord = $DB->get_record('quiz', ['course' => $course->id]);
        $quizid = $quizrecord->id;
        $targetuser = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($targetuser->id, $course->id, 'student');

        // Clean up any auto-generated quiz attempts.
        $DB->delete_records('quiz_attempts', ['quiz' => $quizid]);

        $now = time();

        // Create question usage for quiz attempt.
        $cm = get_coursemodule_from_instance('quiz', $quizid);
        $context = \context_module::instance($cm->id);

        $usageid = $DB->insert_record('question_usages', (object) [
            'contextid' => $context->id,
            'component' => 'mod_quiz',
            'preferredbehaviour' => 'deferredfeedback',
        ]);

        // Create quiz attempt and grade.
        $attemptid = $DB->insert_record('quiz_attempts', (object) [
            'quiz' => $quizid,
            'userid' => $targetuser->id,
            'attempt' => 1,
            'uniqueid' => $usageid,
            'timecreated' => $now,
            'timemodified' => $now,
            'layout' => '',
            'preview' => 0,
            'state' => 'finished',
            'sumgrades' => 50,
        ]);

        // Reset with empty config.
        mod_quiz::reset($targetuser->id, $course, (object) ['quiz' => 0]);

        // Verify records still exist.
        $this->assertTrue($DB->record_exists('quiz_attempts', ['id' => $attemptid]));
    }
}
