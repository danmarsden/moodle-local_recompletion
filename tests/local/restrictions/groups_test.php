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

namespace local_recompletion\local\restrictions;

use stdClass;

/**
 * Tests for groups restriction.
 *
 * @package    local_recompletion
 * @copyright Copyright Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_recompletion\local\restrictions\groups
 */
final class groups_test extends \advanced_testcase {
    /**
     * Test that method doesn't add restrictgroups attribute if not present in data.
     */
    public function test_set_form_data_does_not_add_attributes(): void {
        $data = new stdClass();

        groups::set_form_data($data);
        $this->assertObjectNotHasProperty('restrictgroups', $data);
    }

    /**
     * Data provider for testing set_form_data.
     *
     * @return array
     */
    public static function set_form_data_data_provider(): array {
        return [
            ['', ''],
            [1, 1],
            ['data', 'data'],
            [(object)['data'], (object)['data']],
            [['data'], 'data'],
            [['data 1', 'data 2'], 'data 1,data 2'],
        ];
    }

    /**
     * Test setting form data.
     *
     * @dataProvider set_form_data_data_provider
     *
     * @param mixed $value
     * @param mixed $expected
     */
    public function test_set_form_data($value, $expected): void {
        $data = new stdClass();
        $data->restrictgroups = $value;

        groups::set_form_data($data);
        $this->assertEquals($expected, $data->restrictgroups);
    }

    /**
     * Test logic of should reset method.
     */
    public function test_should_reset(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        $course = $generator->create_course();
        $groupa = $generator->create_group(['courseid' => $course->id]);
        $groupb = $generator->create_group(['courseid' => $course->id]);

        $usergroupa = $generator->create_and_enrol($course);
        $generator->create_group_member(['groupid' => $groupa->id, 'userid' => $usergroupa->id]);

        $usergroupb = $generator->create_and_enrol($course);
        $generator->create_group_member(['groupid' => $groupb->id, 'userid' => $usergroupb->id]);

        $userboth = $generator->create_and_enrol($course);
        $generator->create_group_member(['groupid' => $groupa->id, 'userid' => $userboth->id]);
        $generator->create_group_member(['groupid' => $groupb->id, 'userid' => $userboth->id]);

        $usernogroup = $generator->create_and_enrol($course);

        // No restriction configured -- everyone resets.
        $this->assertTrue(groups::should_reset($usergroupa->id, $course, (object)[]));
        $this->assertTrue(groups::should_reset($usernogroup->id, $course, (object)[]));

        // Unnormalised (array) config -- treated as unrestricted, same as the enrol restriction.
        $this->assertTrue(groups::should_reset($usergroupa->id, $course, (object)['restrictgroups' => [$groupa->id]]));

        // Restricted to group A only.
        $this->assertTrue(groups::should_reset($usergroupa->id, $course, (object)['restrictgroups' => (string) $groupa->id]));
        $this->assertFalse(groups::should_reset($usergroupb->id, $course, (object)['restrictgroups' => (string) $groupa->id]));
        $this->assertFalse(groups::should_reset($usernogroup->id, $course, (object)['restrictgroups' => (string) $groupa->id]));
        $this->assertTrue(groups::should_reset($userboth->id, $course, (object)['restrictgroups' => (string) $groupa->id]));

        // Restricted to either group A or group B -- membership in any selected group is enough.
        $restricttoboth = (object)['restrictgroups' => $groupa->id . ',' . $groupb->id];
        $this->assertTrue(groups::should_reset($usergroupa->id, $course, $restricttoboth));
        $this->assertTrue(groups::should_reset($usergroupb->id, $course, $restricttoboth));
        $this->assertTrue(groups::should_reset($userboth->id, $course, $restricttoboth));
        $this->assertFalse(groups::should_reset($usernogroup->id, $course, $restricttoboth));
    }
}
