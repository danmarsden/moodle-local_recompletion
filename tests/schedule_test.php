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

namespace local_recompletion;

/**
 * Class schedule_test.
 *
 * @package    local_recompletion
 * @author     Kevin Pham <kevinpham@catalyst-au.net>
 * @copyright  Catalyst IT, 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class schedule_test extends \advanced_testcase {

    /**
     * Basic test for future time scheduling.
     */
    public function test_local_recompletion() {
        global $CFG;
        require_once($CFG->dirroot.'/local/recompletion/locallib.php');

        $now = time();

        // Ensure the past cannot be set.
        $nextresettime = \local_recompletion_calculate_schedule_time('yesterday');
        $this->assertEquals(0, $nextresettime);

        // Ensure tomorrow is valid.
        $nextresettime = \local_recompletion_calculate_schedule_time('tomorrow');
        $this->assertGreaterThan($now, $nextresettime);

        // Ensure the past (with a year) cannot be set.
        $nextresettime = \local_recompletion_calculate_schedule_time('Dec 31 2020');
        $this->assertEquals(0, $nextresettime);

        // Ensure no year dates, are okay.
        $nextresettime = \local_recompletion_calculate_schedule_time('Jan 1');
        $this->assertGreaterThan($now, $nextresettime);

        // Same as previously, but for any time of the year.
        $nextresettime = \local_recompletion_calculate_schedule_time('Dec 31');
        $this->assertGreaterThan($now, $nextresettime);

        // Ensure the time, not just a date, can also be used.
        $str = 'Dec 31 14:50';
        $format = 'M d H:i';
        $nextresettime = \local_recompletion_calculate_schedule_time($str);
        $formatteddate = date($format, $nextresettime);
        $this->assertEquals($formatteddate, $str);
    }
}
