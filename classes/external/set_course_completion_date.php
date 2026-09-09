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

namespace local_recompletion\external;

use completion_info;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use invalid_parameter_exception;
use moodle_exception;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/recompletion/locallib.php');
require_once($CFG->libdir . '/completionlib.php');

/**
 * External function to set a user's course completion date.
 *
 * @package    local_recompletion
 * @copyright  2026 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_course_completion_date extends external_api {
    /**
     * Describes the parameters for execute.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'userid' => new external_value(PARAM_INT, 'user id'),
                'timecompleted' => new external_value(PARAM_INT, 'new completion unix timestamp'),
            ]
        );
    }

    /**
     * Sets course completion date for a single user.
     *
     * @param int $courseid
     * @param int $userid
     * @param int $timecompleted
     * @return array
     */
    public static function execute($courseid, $userid, $timecompleted) {
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'userid' => $userid,
            'timecompleted' => $timecompleted,
        ]);

        if ($params['timecompleted'] <= 0) {
            throw new invalid_parameter_exception('timecompleted must be a valid unix timestamp.');
        }

        $course = get_course($params['courseid']);
        \core_user::get_user($params['userid'], '*', MUST_EXIST);

        $context = \context_course::instance($course->id);
        self::validate_context($context);
        require_capability('local/recompletion:resetcompletion', $context);

        $completion = new completion_info($course);
        if (!$completion->is_enabled()) {
            throw new moodle_exception('completionnotenabled', 'local_recompletion');
        }

        local_recompletion_update_course_completion($course, [$params['userid']], $params['timecompleted']);

        return [
            'status' => true,
            'errors' => '',
        ];
    }

    /**
     * Describes the execute return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'errors' => new external_value(PARAM_TEXT, 'errors'),
            ]
        );
    }
}
