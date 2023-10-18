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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/recompletion/locallib.php');

/**
 * Class local_recompletion_observer
 *
 * @package   local_recompletion
 * @copyright 2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Observer function to handle the assessable_uploaded event in mod_assign.
     * @param \mod_assign\event\submission_graded $event
     */
    public static function submission_graded(\mod_assign\event\submission_graded $event) {
        global $DB;
        $assign = $event->get_assign();
        $course = $assign->get_course();
        // Check if recompletion enabled.
        $config = local_recompletion_get_config($course);
        if (!empty($config->recompletiontype) && !empty($config->assignevent)) {
            $params = array(
                'userid'    => $event->relateduserid,
                'course'    => $course->id
            );
            $ccompletion = new \completion_completion($params);
            // Only update course completion date if already flagged complete.
            if ($ccompletion->is_complete()) {
                // If we already have a completion date, clear it first so that mark_complete works.
                $ccompletion->timecompleted = null;
                $ccompletion->mark_complete($event->timecreated);
            }
        }
    }

    /**
     * Observer function to handle user un-enrolment.
     * @param \core\event\user_enrolment_deleted $event
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        global $DB;

        $userid = $event->relateduserid;
        $course = $DB->get_record('course', ['id' => $event->courseid]);

        $config = local_recompletion_get_config($course);
        if (!empty($config->recompletiontype) && !empty($config->recompletionunenrolenable)) {
            try {
                $reset = new \local_recompletion\task\check_recompletion();
                $errors = $reset->reset_user($userid, $course);
            } catch (\Exception $exception) {
                $errors = [$exception->getMessage()];
            }

            if (!empty($errors)) {
                // TODO: implement a new completion_reset_failed event.
                debugging('Completion reset failed for user ' . $userid .
                    ' in course ' . $course->id . ' Errors: ' . implode(',', $errors), DEBUG_DEVELOPER);
            }
        }
    }
}
