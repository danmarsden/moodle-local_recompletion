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
 * Tests for restore_local_recompletion_plugin.
 *
 * Group ids stored in restrictgroups are course-specific and get new ids on
 * restore/duplication, so they must be remapped rather than copied verbatim.
 *
 * @package    local_recompletion
 * @copyright Copyright Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \restore_local_recompletion_plugin
 */
final class restore_test extends \advanced_testcase {
    /**
     * Test that restrictgroups is remapped to the new course's group ids on course duplication.
     */
    public function test_duplicate_course_remaps_restrictgroups(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        require_once(__DIR__ . '/../../../course/externallib.php');

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $groupa = $generator->create_group(['courseid' => $course->id, 'name' => 'Group A']);
        $groupb = $generator->create_group(['courseid' => $course->id, 'name' => 'Group B']);

        $DB->insert_record('local_recompletion_config', (object) [
            'course' => $course->id,
            'name' => 'recompletiontype',
            'value' => 'ondemand',
        ]);
        $DB->insert_record('local_recompletion_config', (object) [
            'course' => $course->id,
            'name' => 'restrictgroups',
            'value' => $groupa->id . ',' . $groupb->id,
        ]);

        $duplicate = \core_course_external::duplicate_course(
            $course->id,
            'Course duplicate',
            'courseduplicate',
            $course->category,
            1,
            []
        );
        $duplicate = \core_external\external_api::clean_returnvalue(\core_course_external::duplicate_course_returns(), $duplicate);
        $newcourseid = $duplicate['id'];

        // The duplicated course has its own groups, named the same but with new ids.
        $newgroups = array_values(groups_get_all_groups($newcourseid));
        $this->assertCount(2, $newgroups);
        $newgroupids = array_map(fn ($group): string => (string) $group->id, $newgroups);
        sort($newgroupids);

        $restrictgroups = $DB->get_field('local_recompletion_config', 'value', [
            'course' => $newcourseid,
            'name' => 'restrictgroups',
        ]);
        $this->assertNotFalse($restrictgroups);
        $restoredids = explode(',', $restrictgroups);
        sort($restoredids);

        // The restored value must point at the new course's groups, not the original ones.
        $this->assertEquals($newgroupids, $restoredids);
        $this->assertNotEquals([(string) $groupa->id, (string) $groupb->id], $restoredids);
    }
}
