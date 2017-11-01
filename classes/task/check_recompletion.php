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
 * Used to check for users that need to recomple.
 *
 * @package    local_recompletion
 * @author     Dan Marsden http://danmarsden.com
 * @copyright  2017 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Check for users that need to recomplete.
 *
 * @package    local_recompletion
 * @author     Dan Marsden http://danmarsden.com
 * @copyright  2017 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class check_recompletion extends \core\task\scheduled_task {
    /**
     * Returns the name of this task.
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('recompletiontask', 'local_recompletion');
    }

    /**
     * Execute task.
     */
    public function execute() {
        global $CFG, $DB, $SITE;
        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->libdir . '/completionlib.php');

        if (!\completion_info::is_enabled_for_site()) {
            return;
        }
        $sql = 'SELECT cc.userid, cc.course
                  FROM {course_completions} cc
                  JOIN {local_recompletion} r ON r.course = cc.course AND r.enable = 1
                  JOIN {course} c ON c.id = cc.course
                  WHERE c.enablecompletion = '.COMPLETION_ENABLED.' AND
                  (cc.timecompleted + r.recompletionduration) < ?';
        $users = $DB->get_recordset_sql($sql, array(time()));
        $courses = array();
        $clearcache = false;
        foreach ($users as $user) {
            if (!isset($courses[$user->course])) {
                // Only get the course record for this course once.
                $course = get_course($user->course);
                $courses[$user->course] = $course;
            } else {
                $course = $courses[$user->course];
            }

            // Delete course completion.
            $params = array('userid' => $user->userid, 'course' => $user->course);
            $DB->delete_records('course_completions', $params);
            $DB->delete_records('course_completion_crit_compl', $params);

            // Delete all activity completions.
            $selectsql = 'userid = ? AND coursemoduleid IN (SELECT id FROM {course_modules} WHERE course = ?)';
            $DB->delete_records_select('course_modules_completion', $selectsql, $params);

            // Now notify user.
            $user = $DB->get_record('user', array('id' => $user->userid));
            $a = new \stdClass();
            $a->name = $course->shortname;
            $a->link = course_get_url($course)->out();
            $subject = get_string('recompletionemailsubject', 'local_recompletion');
            $content = get_string('recompletionemailcontent', 'local_recompletion', $a);
            email_to_user($user, $SITE->shortname, $subject, $content);

            $clearcache = true;
        }
        if ($clearcache) {
            // Difficult to find affected users, just purge all completion cache.
            \cache::make('core', 'completion')->purge();
            \cache::make('core', 'coursecompletion')->purge();
        }
    }
}