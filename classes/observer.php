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
 * Event observers used in Urkund Plagiarism plugin.
 *
 * @package    local_recompletion
 * @author     Dan Marsden <dan@danmarsden.com>
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class local_recompletion_observer
 *
 * @package   local_recompletion
 * @copyright 2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_recompletion_observer {
    /**
     * Observer function to handle the assessable_uploaded event in mod_assign.
     * @param \mod_assign\event\submission_graded $event
     */
    public static function submission_graded(\mod_assign\event\submission_graded $event) {
        global $DB;
        $assign = $event->get_assign();
        $course = $assign->get_course();
        // Check if recompletion enabled.
        $config = $DB->get_records_menu('local_recompletion_config', array('course' => $course->id), '', 'name, value');
        $config = (object) $config;
        if (!empty($config->enable) && !empty($config->assignevent)) {
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
}
