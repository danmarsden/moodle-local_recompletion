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
        require_once($CFG->libdir.'/gradelib.php');

        if (!\completion_info::is_enabled_for_site()) {
            return;
        }
        $sql = 'SELECT cc.userid, cc.course, r.archivecompletiondata,
            r.deletegradedata,
            r.deletequizdata, r.archivequizdata,
            r.deletescormdata, r.archivescormdata,
            r.recompletionemailenable, r.recompletionemailsubject, r.recompletionemailbody
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

            // Archive and delete course completion.
            $params = array('userid' => $user->userid, 'course' => $user->course);
            if ($user->archivecompletiondata) {
                $coursecompletions = $DB->get_records('course_completions', $params);
                $DB->insert_records('local_recompletion_cc', $coursecompletions);
                $criteriacompletions = $DB->get_records('course_completion_crit_compl', $params);
                $DB->insert_records('local_recompletion_cc_cc', $criteriacompletions);
            }
            $DB->delete_records('course_completions', $params);
            $DB->delete_records('course_completion_crit_compl', $params);

            // Archive and delete all activity completions.
            $selectsql = 'userid = ? AND coursemoduleid IN (SELECT id FROM {course_modules} WHERE course = ?)';
            if ($user->archivecompletiondata) {
                $cmc = $DB->get_records_select('course_modules_completion', $selectsql, $params);
                foreach($cmc as $cid => $unused) {
                    // Add courseid to records to help with restore process.
                    $cmc[$cid]->course = $user->course;
                }
                $DB->insert_records('local_recompletion_cmc', $cmc);
            }
            $DB->delete_records_select('course_modules_completion', $selectsql, $params);

            // Delete current grade information.
            if ($user->deletegradedata) {
                if ($items = \grade_item::fetch_all(array('courseid' => $course->id))) {
                    foreach ($items as $item) {
                        if ($grades = \grade_grade::fetch_all(array('userid' => $user->userid, 'itemid' => $item->id))) {
                            foreach ($grades as $grade) {
                                $grade->delete('local_recompletion');
                            }
                        }
                    }
                }
            }

            // Archive and delete quiz attempts information.
            if ($user->deletequizdata) {
                $selectsql = 'userid = ? AND quiz IN (SELECT id FROM {quiz} WHERE course = ?)';
                if ($user->archivequizdata) {
                    $quizattempts = $DB->get_records_select('quiz_attempts', $selectsql, $params);
                    foreach($quizattempts as $qid => $unused) {
                        // Add courseid to records to help with restore process.
                        $quizattempts[$qid]->course = $user->course;
                    }
                    $DB->insert_records('local_recompletion_qa', $quizattempts);

                    $quizgrades = $DB->get_records_select('quiz_grades', $selectsql, $params);
                    foreach($quizgrades as $qid => $unused) {
                        // Add courseid to records to help with restore process.
                        $quizgrades[$qid]->course = $user->course;
                    }
                    $DB->insert_records('local_recompletion_qg', $quizgrades);
                }
                $DB->delete_records_select('quiz_attempts', $selectsql, $params);
                $DB->delete_records_select('quiz_grades', $selectsql, $params);
            }

            // Archive and delete scorm attempts information.
            if ($user->deletescormdata) {
                $selectsql = 'userid = ? AND scormid IN (SELECT id FROM {scorm} WHERE course = ?)';
                if ($user->archivescormdata) {
                    $scormscoestrack = $DB->get_records_select('scorm_scoes_track', $selectsql, $params);
                    foreach($scormscoestrack as $sid => $unused) {
                        // Add courseid to records to help with restore process.
                        $scormscoestrack[$sid]->course = $user->course;
                    }
                    $DB->insert_records('local_recompletion_sst', $scormscoestrack);
                }
                $DB->delete_records_select('scorm_scoes_track', $selectsql, $params);
                $DB->delete_records_select('scorm_aicc_session', $selectsql, $params);
            }

            // Now notify user.
            if ($user->recompletionemailenable) {
                $userrecord = $DB->get_record('user', array('id' => $user->userid));
                $coursedetails = $DB->get_record('course', array('id' => $user->course), '*', MUST_EXIST);
                $context = \context_course::instance($course->id);
                $from = get_admin();
                $a = new \stdClass();
                $a->coursename = format_string($coursedetails->fullname, true, array('context' => $context));
                $a->profileurl = "$CFG->wwwroot/user/view.php?id=$userrecord->id&course=$coursedetails->id";
                $a->link = course_get_url($course)->out();
                if (trim($user->recompletionemailbody) !== '') {
                    $message = $user->recompletionemailbody;
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
                if (trim($user->recompletionemailsubject) !== '') {
                    $subject = $user->recompletionemailsubject;
                    $keysub = array('{$a->coursename}', '{$a->fullname}');
                    $valuesub = array($a->coursename, fullname($userrecord));
                    $subject = str_replace($keysub, $valuesub, $subject);
                } else {
                    $subject = get_string('recompletionemaildefaultsubject', 'local_recompletion', $a);
                }
                // Directly emailing recompletion message rather than using messaging.
                email_to_user($userrecord, $from, $subject, $messagetext, $messagehtml);
            }

            $clearcache = true;
        }
        if ($clearcache) {
            // Difficult to find affected users, just purge all completion cache.
            \cache::make('core', 'completion')->purge();
            // Clear coursecompletion cache which was added in Moodle 3.2.
            if ($CFG->version >= 2016120500) {
                \cache::make('core', 'coursecompletion')->purge();
            }
        }
    }
}