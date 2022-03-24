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
        global $CFG, $DB;
        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->dirroot . '/local/recompletion/locallib.php');
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->libdir.'/gradelib.php');
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        require_once($CFG->dirroot . '/mod/quiz/lib.php');

        if (!\completion_info::is_enabled_for_site()) {
            return;
        }

        $sql = "SELECT cc.userid, cc.course
            FROM {course_completions} cc
            JOIN {local_recompletion_config} r ON r.course = cc.course AND r.name = 'enable' AND r.value = '1'
            JOIN {local_recompletion_config} r2 ON r2.course = cc.course AND r2.name = 'recompletionduration'
            JOIN {course} c ON c.id = cc.course
            WHERE c.enablecompletion = ".COMPLETION_ENABLED." AND cc.timecompleted > 0 AND
            (cc.timecompleted + ".$DB->sql_cast_char2int('r2.value').") < ?";
        $users = $DB->get_recordset_sql($sql, array(time()));
        $courses = array();
        $configs = array();
        $clearcache = false;
        foreach ($users as $user) {
            if (!isset($courses[$user->course])) {
                // Only get the course record for this course once.
                $course = get_course($user->course);
                $courses[$user->course] = $course;
            } else {
                $course = $courses[$user->course];
            }

            // Get recompletion config.
            if (!isset($configs[$user->course])) {
                // Only get the recompletion config record for this course once.
                $config = $DB->get_records_menu('local_recompletion_config', array('course' => $course->id), '', 'name, value');
                $config = (object) $config;
                $configs[$user->course] = $config;
            } else {
                $config = $configs[$user->course];
            }
            $this->reset_user($user->userid, $course, $config);
        }
    }

    /**
     * Reset and archive completion records
     * @param \int $userid - user id
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    protected function reset_completions($userid, $course, $config) {
        global $DB;
        $params = array('userid' => $userid, 'course' => $course->id);
        if ($config->archivecompletiondata) {
            $coursecompletions = $DB->get_records('course_completions', $params);
            $DB->insert_records('local_recompletion_cc', $coursecompletions);
            $criteriacompletions = $DB->get_records('course_completion_crit_compl', $params);
            $DB->insert_records('local_recompletion_cc_cc', $criteriacompletions);
        }
        $DB->delete_records('course_completions', $params);
        $DB->delete_records('course_completion_crit_compl', $params);

        // Archive and delete all activity completions.
        $selectsql = 'userid = ? AND coursemoduleid IN (SELECT id FROM {course_modules} WHERE course = ?)';
        if ($config->archivecompletiondata) {
            $cmc = $DB->get_records_select('course_modules_completion', $selectsql, $params);
            foreach ($cmc as $cid => $unused) {
                // Add courseid to records to help with restore process.
                $cmc[$cid]->course = $course->id;
            }
            $DB->insert_records('local_recompletion_cmc', $cmc);
        }
        $DB->delete_records_select('course_modules_completion', $selectsql, $params);
    }

    /**
     * Notify user of recompletion.
     * @param \int $userid - user id
     * @param \stdclass $course - record from course table.
     * @param \stdClass $config - recompletion config.
     */
    protected function notify_user($userid, $course, $config) {
        global $DB, $CFG;

        if (!$config->recompletionemailenable) {
            return;
        }

        $userrecord = $DB->get_record('user', array('id' => $userid));
        $context = \context_course::instance($course->id);
        $from = get_admin();
        $a = new \stdClass();
        $a->coursename = format_string($course->fullname, true, array('context' => $context));
        $a->profileurl = "$CFG->wwwroot/user/view.php?id=$userrecord->id&course=$course->id";
        $a->link = course_get_url($course)->out();
        if (trim($config->recompletionemailbody) !== '') {
            $message = $config->recompletionemailbody;
            $key = array('{$a->coursename}', '{$a->profileurl}', '{$a->link}', '{$a->fullname}', '{$a->email}');
            $value = array($a->coursename, $a->profileurl, $a->link, fullname($userrecord), $userrecord->email);
            $message = str_replace($key, $value, $message);
            if (strpos($message, '<') === false) {
                // Plain text only.
                $messagetext = $message;
                $messagehtml = text_to_html($messagetext, null, false, true);
            } else {
                // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                $messagehtml = format_text($message, FORMAT_MOODLE, array('context' => $context,
                    'para' => false, 'newlines' => true, 'filter' => true));
                $messagetext = html_to_text($messagehtml);
            }
        } else {
            $messagetext = get_string('recompletionemaildefaultbody', 'local_recompletion', $a);
            $messagehtml = text_to_html($messagetext, null, false, true);
        }
        if (trim($config->recompletionemailsubject) !== '') {
            $subject = $config->recompletionemailsubject;
            $keysub = array('{$a->coursename}', '{$a->fullname}');
            $valuesub = array($a->coursename, fullname($userrecord));
            $subject = str_replace($keysub, $valuesub, $subject);
        } else {
            $subject = get_string('recompletionemaildefaultsubject', 'local_recompletion', $a);
        }
        // Directly emailing recompletion message rather than using messaging.
        email_to_user($userrecord, $from, $subject, $messagetext, $messagehtml);
    }

    /**
     * Reset user completion.
     * @param \int $userid - id of user.
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    public function reset_user($userid, $course, $config) {
        global $CFG;
        // Archive and delete course completion.
        $this->reset_completions($userid, $course, $config);

        // Delete current grade information.
        if ($config->deletegradedata) {
            if ($items = \grade_item::fetch_all(array('courseid' => $course->id))) {
                foreach ($items as $item) {
                    if ($grades = \grade_grade::fetch_all(array('userid' => $userid, 'itemid' => $item->id))) {
                        foreach ($grades as $grade) {
                            $grade->delete('local_recompletion');
                        }
                    }
                }
            }
        }

        $activities = local_recompletion_get_supported_activities();
        $errors = [];
        foreach ($activities as $activity) {
            $fqn = 'local_recompletion\\activities\\' . $activity;
            $error = $fqn::reset($userid, $course, $config);
            if (!empty($errors)) {
                $errors[] = $error;
            }
        }

        // Now notify user.
        $this->notify_user($userid, $course, $config);

        // Trigger completion reset event for this user.
        $context = \context_course::instance($course->id);
        $event = \local_recompletion\event\completion_reset::create(
            array(
                'objectid'      => $course->id,
                'relateduserid' => $userid,
                'courseid' => $course->id,
                'context' => $context,
            )
        );
        $event->trigger();

        $clearcache = true; // We have made some changes, clear completion cache.

        if ($clearcache) {
            // Difficult to find affected users, just purge all completion cache.
            \cache::make('core', 'completion')->purge();
            // Clear coursecompletion cache which was added in Moodle 3.2.
            if ($CFG->version >= 2016120500) {
                \cache::make('core', 'coursecompletion')->purge();
            }
        }
        return $errors;
    }
}
