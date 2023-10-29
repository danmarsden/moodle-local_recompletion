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
 * Tests for mod_h5pactivity.
 *
 * @package    local_recompletion
 * @copyright  2023 Dmitrii Metelkin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \local_recompletion\plugins\mod_h5pactivity
 */
class mod_h5pactivity_test extends \advanced_testcase {

    /**
     * Test mod_h5pactivity recompletion.
     */
    public function test_mod_h5pactivity() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $h5p = $this->getDataGenerator()->create_module('h5pactivity', ['course' => $course->id]);
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Check that tables are empty initially.
        $this->assertFalse($DB->record_exists('h5pactivity_attempts', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('h5pactivity_attempts_results', []));
        $this->assertFalse($DB->record_exists('local_recompletion_h5p', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('local_recompletion_h5pr', []));

        // Generate an attempt for user 1.
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_h5pactivity');
        $generator->create_attempt(['h5pactivityid' => $h5p->id, 'userid' => $user1->id]);

        // Reset user 2 without any attempts to make sure that it doesn't explode.
        mod_h5pactivity::reset($user2->id, $course, (object)['h5pactivity' => 1, 'archiveh5pactivity' => 1]);

        // Check that data is created in original tables.
        $this->assertTrue($DB->record_exists('h5pactivity_attempts', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertTrue($DB->record_exists('h5pactivity_attempts_results', []));
        $this->assertFalse($DB->record_exists('local_recompletion_h5p', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('local_recompletion_h5pr', []));

        // Reset data with "do nothing".
        mod_h5pactivity::reset($user1->id, $course, (object)['h5pactivity' => 0, 'archiveh5pactivity' => 0]);

        // Check that nothing happened.
        $this->assertTrue($DB->record_exists('h5pactivity_attempts', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertTrue($DB->record_exists('h5pactivity_attempts_results', []));
        $this->assertFalse($DB->record_exists('local_recompletion_h5p', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('local_recompletion_h5pr', []));

        // Reset data with "do nothing", but archiving enabled.
        mod_h5pactivity::reset($user1->id, $course, (object)['h5pactivity' => 0, 'archiveh5pactivity' => 1]);

        // Check that nothing happened.
        $this->assertTrue($DB->record_exists('h5pactivity_attempts', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertTrue($DB->record_exists('h5pactivity_attempts_results', []));
        $this->assertFalse($DB->record_exists('local_recompletion_h5p', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('local_recompletion_h5pr', []));

        // Reset data for user 2.
        mod_h5pactivity::reset($user2->id, $course, (object)['h5pactivity' => 1, 'archiveh5pactivity' => 0]);

        // Check that nothing happened for user 1.
        $this->assertTrue($DB->record_exists('h5pactivity_attempts', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertTrue($DB->record_exists('h5pactivity_attempts_results', []));
        $this->assertFalse($DB->record_exists('local_recompletion_h5p', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('local_recompletion_h5pr', []));

        // Reset data without archiving.
        mod_h5pactivity::reset($user1->id, $course, (object)['h5pactivity' => 1, 'archiveh5pactivity' => 0]);

        // Check data is gone from original tables.
        $this->assertFalse($DB->record_exists('h5pactivity_attempts', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('h5pactivity_attempts_results', []));
        $this->assertFalse($DB->record_exists('local_recompletion_h5p', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('local_recompletion_h5pr', []));

        // Create a new attempt for user 1.
        $generator->create_attempt(['h5pactivityid' => $h5p->id, 'userid' => $user1->id]);

        // Check that data is created in original tables.
        $this->assertTrue($DB->record_exists('h5pactivity_attempts', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertTrue($DB->record_exists('h5pactivity_attempts_results', []));
        $this->assertFalse($DB->record_exists('local_recompletion_h5p', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('local_recompletion_h5pr', []));

        $originalattempt = $DB->get_record('h5pactivity_attempts', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]);
        $originalresult = $DB->get_record('h5pactivity_attempts_results', ['attemptid' => $originalattempt->id]);

        // Reset with archiving.
        mod_h5pactivity::reset($user1->id, $course, (object)['h5pactivity' => 1, 'archiveh5pactivity' => 1]);

        // Check that data is created in archived tables.
        $this->assertFalse($DB->record_exists('h5pactivity_attempts', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('h5pactivity_attempts_results', []));
        $this->assertTrue($DB->record_exists('local_recompletion_h5p', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertTrue($DB->record_exists('local_recompletion_h5pr', []));

        // Validate data.
        $attempt = $DB->get_record('local_recompletion_h5p', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]);
        $result = $DB->get_record('local_recompletion_h5pr', ['attemptid' => $attempt->id]);

        $this->assertNotEmpty($result);
        $this->assertEquals(0, $attempt->originalattemptid);
        $this->assertEquals($course->id, $attempt->course);
        $this->assertEquals($course->id, $result->course);

        foreach (['id', 'originalattemptid', 'course', 'attemptid'] as $field) {
            unset($originalattempt->$field);
            unset($attempt->$field);
            unset($originalresult->$field);
            unset($result->$field);
        }

        $this->assertEquals($originalattempt, $attempt);
        $this->assertEquals($originalresult, $result);
    }

    /**
     * Test that can re complete mod_h5pactivity several times.
     */
    public function test_can_recomplete_few_times_in_a_row() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $h5p = $this->getDataGenerator()->create_module('h5pactivity', ['course' => $course->id]);
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Check that tables are empty initially.
        $this->assertFalse($DB->record_exists('h5pactivity_attempts', ['h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('h5pactivity_attempts_results', []));
        $this->assertFalse($DB->record_exists('local_recompletion_h5p', ['h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('local_recompletion_h5pr', []));

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_h5pactivity');
        $generator->create_attempt(['h5pactivityid' => $h5p->id, 'userid' => $user1->id]);
        $generator->create_attempt(['h5pactivityid' => $h5p->id, 'userid' => $user2->id]);

        mod_h5pactivity::reset($user1->id, $course, (object)['h5pactivity' => 1, 'archiveh5pactivity' => 1]);
        $this->assertFalse($DB->record_exists('h5pactivity_attempts', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertTrue($DB->record_exists('h5pactivity_attempts', ['userid' => $user2->id, 'h5pactivityid' => $h5p->id]));

        $generator->create_attempt(['h5pactivityid' => $h5p->id, 'userid' => $user1->id]);
        $generator->create_attempt(['h5pactivityid' => $h5p->id, 'userid' => $user2->id]);

        mod_h5pactivity::reset($user1->id, $course, (object)['h5pactivity' => 1, 'archiveh5pactivity' => 1]);
        $this->assertFalse($DB->record_exists('h5pactivity_attempts', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertTrue($DB->record_exists('h5pactivity_attempts', ['userid' => $user2->id, 'h5pactivityid' => $h5p->id]));

        // Validate data.
        $attempts = $DB->get_records('local_recompletion_h5p', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]);
        foreach ($attempts as $attempt) {
            $this->assertTrue($DB->record_exists('local_recompletion_h5pr', ['attemptid' => $attempt->id]));
        }

        mod_h5pactivity::reset($user2->id, $course, (object)['h5pactivity' => 1, 'archiveh5pactivity' => 1]);
        $this->assertFalse($DB->record_exists('h5pactivity_attempts', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]));
        $this->assertFalse($DB->record_exists('h5pactivity_attempts', ['userid' => $user2->id, 'h5pactivityid' => $h5p->id]));

        // Validate data.
        $attempts = $DB->get_records('local_recompletion_h5p', ['userid' => $user1->id, 'h5pactivityid' => $h5p->id]);
        foreach ($attempts as $attempt) {
            $this->assertTrue($DB->record_exists('local_recompletion_h5pr', ['attemptid' => $attempt->id]));
        }
    }
}
