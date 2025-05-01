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
 * External local recompletion API.
 *
 * @package    local_recompletion
 * @author     Noémie Ariste <noemie.ariste@catalyst.net.nz>
 * @copyright  2024 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/local/recompletion/locallib.php");
require_once("$CFG->libdir/grade/grade_item.php");
require_once("$CFG->libdir/grade/grade_grade.php");

/**
 * local recompletion functions
 *
 * @author     Noémie Ariste <noemie.ariste@catalyst.net.nz>
 * @copyright  2024 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_recompletion_external extends external_api {

    /**
     * Describes the parameters for reset_course
     *
     * @return external_function_parameters
     */
    public static function reset_course_parameters() {
        return new external_function_parameters(
                [
                        'courseid' => new external_value(PARAM_INT, 'course id'),
                        'userid' => new external_value(PARAM_INT, 'userid'),
                ]
        );
    }

    /**
     * Resets course completion for the requested course id and user id
     *
     * @param int $courseid
     * @param int $userid
     * @return array of errors and status result
     */
    public static function reset_course($courseid, $userid) {
        $params = self::validate_parameters(self::reset_course_parameters(), [
                'courseid' => $courseid,
                'userid' => $userid,
        ]);

        $course = get_course($params['courseid']);

        // Perform security checks.
        $context = context_course::instance($courseid);
        self::validate_context($context);
        require_capability('local/recompletion:manage', $context);

        $reset = new local_recompletion\task\check_recompletion();
        $errors = $reset->reset_user($params['userid'], $course);

        $result = [];
        $result['status'] = empty($errors) ? true : false;
        $result['errors'] = implode(',', $errors);
        return $result;
    }

    /**
     * Describes the reset_course return value.
     *
     * @return external_single_structure
     */
    public static function reset_course_returns() {
        return new external_single_structure(
                [
                        'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                        'errors' => new external_value(PARAM_TEXT, 'errors'),
                ]
        );
    }
}
