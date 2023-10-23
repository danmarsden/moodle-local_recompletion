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
 * Tests for enrol restriction.
 *
 * @package    local_recompletion
 * @copyright  2023 Dmitrii Metelkin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_recompletion\local\restrictions\enrol
 */
class enrol_test extends \advanced_testcase {

    /**
     * Test that method doesn't add restrictenrol attribute if not present in data.
     */
    public function test_set_form_data_does_not_add_attributes() {
        $data = new stdClass();

        enrol::set_form_data($data);
        $this->assertObjectNotHasAttribute('restrictenrol', $data);
    }

    /**
     * Data provider for testing set_form_data.
     *
     * @return array
     */
    public function set_form_data_data_provider(): array {
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
    public function test_set_form_data($value, $expected) {
        $data = new stdClass();
        $data->restrictenrol = $value;

        enrol::set_form_data($data);
        $this->assertEquals($expected, $data->restrictenrol);
    }

    /**
     * Test logic of should reset method.
     */
    public function test_should_reset() {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $usermanual = $this->getDataGenerator()->create_and_enrol($course);
        $userself = $this->getDataGenerator()->create_and_enrol($course, 'student', null, 'self');

        $this->assertTrue(enrol::should_reset($usermanual->id, $course, (object)[]));
        $this->assertTrue(enrol::should_reset($userself->id, $course, (object)[]));

        $this->assertTrue(enrol::should_reset($usermanual->id, $course, (object)['restrictenrol' => ['paypal']]));
        $this->assertTrue(enrol::should_reset($userself->id, $course, (object)['restrictenrol' => ['paypal']]));

        $this->assertFalse(enrol::should_reset($usermanual->id, $course, (object)['restrictenrol' => 'paypal']));
        $this->assertFalse(enrol::should_reset($userself->id, $course, (object)['restrictenrol' => 'paypal']));

        $this->assertFalse(enrol::should_reset($usermanual->id, $course, (object)['restrictenrol' => 'self']));
        $this->assertTrue(enrol::should_reset($userself->id, $course, (object)['restrictenrol' => 'self']));

        $this->assertTrue(enrol::should_reset($usermanual->id, $course, (object)['restrictenrol' => 'manual']));
        $this->assertFalse(enrol::should_reset($userself->id, $course, (object)['restrictenrol' => 'manual']));

        $this->assertFalse(enrol::should_reset($usermanual->id, $course, (object)['restrictenrol' => 'paypal,self']));
        $this->assertTrue(enrol::should_reset($userself->id, $course, (object)['restrictenrol' => 'paypal,self']));

        $this->assertTrue(enrol::should_reset($usermanual->id, $course, (object)['restrictenrol' => 'paypal,self,manual']));
        $this->assertTrue(enrol::should_reset($userself->id, $course, (object)['restrictenrol' => 'paypal,self,manual']));
    }
}
