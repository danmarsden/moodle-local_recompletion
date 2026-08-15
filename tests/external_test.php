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

use core_external\external_api;
use local_recompletion\external\reset_course;
use local_recompletion\external\set_course_completion_date;

/**
 * Tests for local_recompletion external classes.
 *
 * @package    local_recompletion
 * @copyright  2026 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \local_recompletion\external\reset_course
 * @covers \local_recompletion\external\set_course_completion_date
 */
final class external_test extends \advanced_testcase {
    /**
     * Ensure set_course_completion_date updates the completion timestamp.
     */
    public function test_set_course_completion_date_updates_completion_time(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $starttime = time() - 3600;
        $targettime = time() - 120;

        $ccompletion = new \completion_completion(['userid' => $user->id, 'course' => $course->id]);
        $ccompletion->mark_complete($starttime);

        $criteriaid = $DB->insert_record('course_completion_criteria', (object) [
            'course' => $course->id,
            'criteriatype' => COMPLETION_CRITERIA_TYPE_SELF,
        ]);
        $DB->insert_record('course_completion_crit_compl', (object) [
            'userid' => $user->id,
            'course' => $course->id,
            'criteriaid' => $criteriaid,
            'timecompleted' => $starttime,
        ]);

        $result = set_course_completion_date::execute($course->id, $user->id, $targettime);
        $result = external_api::clean_returnvalue(set_course_completion_date::execute_returns(), $result);

        $this->assertTrue($result['status']);
        $this->assertSame('', $result['errors']);

        $updated = $DB->get_record('course_completions', [
            'course' => $course->id,
            'userid' => $user->id,
        ], '*', MUST_EXIST);
        $this->assertEquals($targettime, (int) $updated->timecompleted);

        $updatedcriteria = $DB->get_record('course_completion_crit_compl', [
            'course' => $course->id,
            'userid' => $user->id,
            'criteriaid' => $criteriaid,
        ], '*', MUST_EXIST);
        $this->assertEquals($targettime, (int) $updatedcriteria->timecompleted);
    }

    /**
     * Ensure set_course_completion_date validates timestamp input.
     */
    public function test_set_course_completion_date_rejects_non_positive_timestamp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $this->expectException(\invalid_parameter_exception::class);
        set_course_completion_date::execute($course->id, $user->id, 0);
    }

    /**
     * Ensure reset_course clears course completion and returns a clean response.
     */
    public function test_reset_course_resets_completion(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        global $DB;
        $DB->insert_record('local_recompletion_config', (object) [
            'course' => $course->id,
            'name' => 'recompletiontype',
            'value' => 'ondemand',
        ]);
        $DB->insert_record('local_recompletion_config', (object) [
            'course' => $course->id,
            'name' => 'archivecompletiondata',
            'value' => 0,
        ]);
        $DB->insert_record('local_recompletion_config', (object) [
            'course' => $course->id,
            'name' => 'deletegradedata',
            'value' => 0,
        ]);

        $ccompletion = new \completion_completion(['userid' => $user->id, 'course' => $course->id]);
        $ccompletion->mark_complete(time() - 500);

        $this->assertTrue($ccompletion->is_complete());

        $result = reset_course::execute($course->id, $user->id);
        $result = external_api::clean_returnvalue(reset_course::execute_returns(), $result);

        $this->assertTrue($result['status']);
        $this->assertSame('', $result['errors']);

        $updated = new \completion_completion(['userid' => $user->id, 'course' => $course->id]);
        $this->assertFalse($updated->is_complete());
    }
}
