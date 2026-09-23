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
 * Tests for mod_choice.
 *
 * @package    local_recompletion
 * @copyright  2026 Dan Marsden
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \local_recompletion\plugins\mod_choice
 */
final class mod_choice_test extends \advanced_testcase {
    /**
     * Test delete mode removes choice answers for the selected user only.
     */
    public function test_reset_delete_cleans_choice_data_for_target_user_only(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $choice = $this->getDataGenerator()->create_module('choice', ['course' => $course->id]);
        $targetuser = $this->getDataGenerator()->create_user();
        $otheruser = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($targetuser->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($otheruser->id, $course->id, 'student');

        $now = time();

        // Create choice options.
        $option1 = $DB->insert_record('choice_options', (object) [
            'choiceid' => $choice->id,
            'text' => 'Option 1',
            'maxanswers' => 0,
            'timemodified' => $now,
        ]);
        $option2 = $DB->insert_record('choice_options', (object) [
            'choiceid' => $choice->id,
            'text' => 'Option 2',
            'maxanswers' => 0,
            'timemodified' => $now,
        ]);

        // Create answers for target user.
        $DB->insert_record('choice_answers', (object) [
            'choiceid' => $choice->id,
            'userid' => $targetuser->id,
            'optionid' => $option1,
            'timemodified' => $now,
        ]);

        // Create answers for other user.
        $DB->insert_record('choice_answers', (object) [
            'choiceid' => $choice->id,
            'userid' => $otheruser->id,
            'optionid' => $option2,
            'timemodified' => $now,
        ]);

        // Verify answers exist.
        $this->assertTrue($DB->record_exists('choice_answers', ['choiceid' => $choice->id, 'userid' => $targetuser->id]));
        $this->assertTrue($DB->record_exists('choice_answers', ['choiceid' => $choice->id, 'userid' => $otheruser->id]));

        // Reset with delete mode.
        mod_choice::reset($targetuser->id, $course, (object) ['choice' => LOCAL_RECOMPLETION_DELETE]);

        // Verify target user answers are deleted.
        $this->assertFalse($DB->record_exists('choice_answers', ['choiceid' => $choice->id, 'userid' => $targetuser->id]));

        // Verify other user answers remain.
        $this->assertTrue($DB->record_exists('choice_answers', ['choiceid' => $choice->id, 'userid' => $otheruser->id]));
    }

    /**
     * Test delete mode with archive enabled archives choice answers before deletion.
     */
    public function test_reset_delete_with_archive_cleans_and_archives_choice_data(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $choice = $this->getDataGenerator()->create_module('choice', ['course' => $course->id]);
        $targetuser = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($targetuser->id, $course->id, 'student');

        $now = time();

        // Create choice option.
        $option = $DB->insert_record('choice_options', (object) [
            'choiceid' => $choice->id,
            'text' => 'Option 1',
            'maxanswers' => 0,
            'timemodified' => $now,
        ]);

        // Create answer for target user.
        $answerid = $DB->insert_record('choice_answers', (object) [
            'choiceid' => $choice->id,
            'userid' => $targetuser->id,
            'optionid' => $option,
            'timemodified' => $now,
        ]);

        $this->assertTrue($DB->record_exists('choice_answers', ['id' => $answerid]));

        // Reset with delete and archive mode.
        mod_choice::reset($targetuser->id, $course, (object) [
            'choice' => LOCAL_RECOMPLETION_DELETE,
            'archivechoice' => 1,
        ]);

        // Verify answer is deleted from main table.
        $this->assertFalse($DB->record_exists('choice_answers', ['id' => $answerid]));

        // Verify answer is archived.
        $this->assertTrue($DB->record_exists('local_recompletion_cha', [
            'choiceid' => $choice->id,
            'userid' => $targetuser->id,
        ]));
    }

    /**
     * Test that empty choice config is a no-op.
     */
    public function test_reset_with_empty_choice_config_is_noop(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $choice = $this->getDataGenerator()->create_module('choice', ['course' => $course->id]);
        $targetuser = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($targetuser->id, $course->id, 'student');

        $now = time();

        // Create choice option and answer.
        $option = $DB->insert_record('choice_options', (object) [
            'choiceid' => $choice->id,
            'text' => 'Option 1',
            'maxanswers' => 0,
            'timemodified' => $now,
        ]);

        $answerid = $DB->insert_record('choice_answers', (object) [
            'choiceid' => $choice->id,
            'userid' => $targetuser->id,
            'optionid' => $option,
            'timemodified' => $now,
        ]);

        // Reset with empty config.
        mod_choice::reset($targetuser->id, $course, (object) ['choice' => 0]);

        // Verify answer still exists.
        $this->assertTrue($DB->record_exists('choice_answers', ['id' => $answerid]));
    }
}
