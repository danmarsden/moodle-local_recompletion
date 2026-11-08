<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Privacy provider tests for local_recompletion.
 *
 * @package    local_recompletion
 * @category   test
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\privacy;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;

/**
 * Privacy API tests for local_recompletion.
 *
 * @covers \local_recompletion\privacy\provider
 */
final class provider_test extends provider_testcase {
    /**
     * Test that user data is exported for a user in a course.
     */
    public function test_export_user_data(): void {
        global $DB;

        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        $DB->insert_record('local_recompletion_cc', (object) [
            'userid' => $user->id,
            'course' => $course->id,
            'timeenrolled' => 100,
            'timestarted' => 110,
            'timecompleted' => 120,
            'reaggregate' => 0,
        ]);

        $attemptid = $DB->insert_record('local_recompletion_h5p', (object) [
            'originalattemptid' => 0,
            'h5pactivityid' => 1,
            'userid' => $user->id,
            'timecreated' => 100,
            'timemodified' => 110,
            'attempt' => 1,
            'rawscore' => 50,
            'maxscore' => 100,
            'scaled' => 0.5,
            'duration' => 120,
            'completion' => 1,
            'success' => 1,
            'course' => $course->id,
        ]);

        $DB->insert_record('local_recompletion_h5pr', (object) [
            'attemptid' => $attemptid,
            'subcontent' => 'subcontent-id',
            'timecreated' => 120,
            'interactiontype' => 'choice',
            'description' => 'User response description',
            'correctpattern' => null,
            'response' => '{"answer":"A"}',
            'additionals' => null,
            'rawscore' => 50,
            'maxscore' => 100,
            'duration' => 120,
            'completion' => 1,
            'success' => 1,
            'course' => $course->id,
        ]);

        $contextlist = new approved_contextlist($user, 'local_recompletion', [$context->id]);
        provider::export_user_data($contextlist);

        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());

        $data = $writer->get_data([
            get_string('recompletion', 'local_recompletion'),
            'course_completion',
        ]);
        $this->assertNotNull($data);

        $h5pdata = $writer->get_data([
            get_string('recompletion', 'local_recompletion'),
            'recompletion_h5pr',
        ]);
        $this->assertNotNull($h5pdata);
    }

    /**
     * Test that data is deleted for a single user in a course context.
     */
    public function test_delete_data_for_user(): void {
        global $DB;

        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        $DB->insert_record('local_recompletion_cc', (object) [
            'userid' => $user1->id,
            'course' => $course->id,
            'timeenrolled' => 100,
            'timestarted' => 110,
            'timecompleted' => 120,
            'reaggregate' => 0,
        ]);
        $DB->insert_record('local_recompletion_cc', (object) [
            'userid' => $user2->id,
            'course' => $course->id,
            'timeenrolled' => 200,
            'timestarted' => 210,
            'timecompleted' => 220,
            'reaggregate' => 0,
        ]);

        $contextlist = new approved_contextlist($user1, 'local_recompletion', [$context->id]);
        provider::delete_data_for_user($contextlist);

        $this->assertFalse($DB->record_exists('local_recompletion_cc', [
            'userid' => $user1->id,
            'course' => $course->id,
        ]));
        $this->assertTrue($DB->record_exists('local_recompletion_cc', [
            'userid' => $user2->id,
            'course' => $course->id,
        ]));
    }

    /**
     * Test that all user data is deleted for a course context.
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;

        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        $DB->insert_record('local_recompletion_cc', (object) [
            'userid' => $user1->id,
            'course' => $course->id,
            'timeenrolled' => 100,
            'timestarted' => 110,
            'timecompleted' => 120,
            'reaggregate' => 0,
        ]);
        $DB->insert_record('local_recompletion_cc', (object) [
            'userid' => $user2->id,
            'course' => $course->id,
            'timeenrolled' => 200,
            'timestarted' => 210,
            'timecompleted' => 220,
            'reaggregate' => 0,
        ]);

        provider::delete_data_for_all_users_in_context($context);

        $this->assertFalse($DB->record_exists('local_recompletion_cc', ['course' => $course->id]));
    }
}
