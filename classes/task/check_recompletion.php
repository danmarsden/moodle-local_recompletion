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
     * Get courses that are ready to reset.
     */
    public function get_user_courses_to_reset() {
        global $DB;

        $now = time();
        // Period based recompletion users.
        $sql = "SELECT cc.userid, cc.course, null as nextresettime
                  FROM {course_completions} cc
                  JOIN {local_recompletion_config} r2 ON r2.course = cc.course AND r2.name = 'recompletionduration'
                  JOIN {local_recompletion_config} r3 ON r3.course = cc.course
                                                     AND r3.name = 'recompletiontype' AND r3.value = 'period'
                  JOIN {course} c ON c.id = cc.course
                 WHERE c.enablecompletion = ".COMPLETION_ENABLED."
                   AND cc.timecompleted > 0
                   AND (cc.timecompleted + ".$DB->sql_cast_char2int('r2.value').") < ?";
        $users = $DB->get_records_sql($sql, [$now]);

        // Schedule based recompletion.
        $sql = "SELECT cc.userid,
                       cc.course,
                       r4.value as schedule,
                       cc.timecompleted,
                       coalesce(r3.value, '0') as nextresettime
                  FROM {course_completions} cc
                  JOIN {local_recompletion_config} r2 ON r2.course = cc.course
                                                     AND r2.name = 'recompletiontype' AND r2.value = 'schedule'
             LEFT JOIN {local_recompletion_config} r3 ON r3.course = cc.course AND r3.name = 'nextresettime'
                  JOIN {local_recompletion_config} r4 ON r4.course = cc.course AND r4.name = 'recompletionschedule'
                  JOIN {course} c ON c.id = cc.course
                 WHERE c.enablecompletion = ".COMPLETION_ENABLED."
                   AND cc.timecompleted > 0";
        $recompletions = $DB->get_records_sql($sql, [$now]);
        foreach ($recompletions as $record) {
            // If the reset should happen, make it happen, otherwise wait until the next scheduled time.
            if ($now > $record->nextresettime) {
                $users[] = $record;
            }
        }

        return $users ?? [];
    }

    /**
     * Execute task.
     */
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/recompletion/locallib.php');

        if (!\completion_info::is_enabled_for_site()) {
            return;
        }

        $users = $this->get_user_courses_to_reset();
        $courses = [];
        $configs = [];
        $updateresettimes = [];

        foreach ($users as $user) {
            // Only get the course record for this course (at most once).
            if (!isset($courses[$user->course])) {
                $courses[$user->course] = get_course($user->course);
            }
            $course = $courses[$user->course];

            // Get recompletion config for this course (at most once).
            if (!isset($configs[$user->course])) {
                $rc = $DB->get_records_list('local_recompletion_config', 'course', [$course->id], '', 'name, id, value');
                $configs[$user->course] = (object) local_recompletion_get_data($rc);
            }
            $config = $configs[$user->course];

            $this->reset_user($user->userid, $course, $config);

            // If this course hasn't had its nextresettime set, add it to the array for after.
            if (!isset($updateresettimes[$course->id]) && isset($user->schedule)) {
                // Update next reset time.
                $newconfig = new \stdClass();
                if (isset($rc['nextresettime'])) {
                    $newconfig->id = $rc['nextresettime']->id;
                }
                $newconfig->course = $course->id;
                $newconfig->name = 'nextresettime';
                $newconfig->value = local_recompletion_calculate_schedule_time($user->schedule);

                $updateresettimes[$course->id] = $newconfig;
            }

            foreach ($updateresettimes as $newconfig) {
                // Now that all the users are processed, any courses that have been processed, we can update the nextresettime.
                if (empty($newconfig->id)) {
                    $DB->insert_record('local_recompletion_config', $newconfig);
                } else {
                    $DB->update_record('local_recompletion_config', $newconfig);
                }
            }
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
        $params = ['userid' => $userid, 'course' => $course->id];
        if (!empty(get_config('local_recompletion', 'forcearchivecompletiondata')) || $config->archivecompletiondata) {
            $coursecompletions = $DB->get_records('course_completions', $params);
            $DB->insert_records('local_recompletion_cc', $coursecompletions);
            $criteriacompletions = $DB->get_records('course_completion_crit_compl', $params);
            $DB->insert_records('local_recompletion_cc_cc', $criteriacompletions);
        }
        $DB->delete_records('course_completions', $params);
        $DB->delete_records('course_completion_crit_compl', $params);

        // Archive and delete all activity completions.
        $selectsql = 'userid = ? AND coursemoduleid IN (SELECT id FROM {course_modules} WHERE course = ?)';
        if (!empty(get_config('local_recompletion', 'forcearchivecompletiondata')) || $config->archivecompletiondata) {
            $cmc = $DB->get_records_select('course_modules_completion', $selectsql, $params);
            foreach ($cmc as $cid => $unused) {
                // Add courseid to records to help with restore process.
                $cmc[$cid]->course = $course->id;
            }
            $DB->insert_records('local_recompletion_cmc', $cmc);
        }
        $DB->delete_records_select('course_modules_completion', $selectsql, $params);
        // Removal of course_modules_viewed data (#78).

        $selectsql = 'userid = ? AND coursemoduleid IN (SELECT id FROM {course_modules} WHERE course = ?)';
        if (!empty(get_config('local_recompletion', 'forcearchivecompletiondata')) || $config->archivecompletiondata) {
            $cmc = $DB->get_records_select('course_modules_viewed', $selectsql, $params);
            foreach ($cmc as $cid => $unused) {
                // Add courseid to records to help with restore process.
                $cmc[$cid]->course = $course->id;
            }
            $DB->insert_records('local_recompletion_cmv', $cmc);
        }
        $DB->delete_records_select('course_modules_viewed', $selectsql, $params);

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

        $userrecord = $DB->get_record('user', ['id' => $userid]);
        $context = \context_course::instance($course->id);
        $from = get_admin();
        $a = new \stdClass();
        $a->coursename = format_string($course->fullname, true, ['context' => $context]);
        $a->profileurl = "$CFG->wwwroot/user/view.php?id=$userrecord->id&course=$course->id";
        $a->link = course_get_url($course)->out();
        if (trim($config->recompletionemailbody) !== '') {
            $message = $config->recompletionemailbody;
            $key = ['{$a->coursename}', '{$a->profileurl}', '{$a->link}', '{$a->fullname}', '{$a->email}'];
            $value = [$a->coursename, $a->profileurl, $a->link, fullname($userrecord), $userrecord->email];
            $message = str_replace($key, $value, $message);
            // Message body now stored as html - some might be non-html though, so we have to handle both - not clean but it works for now.
            $keyhtml = [
                '{$a-&gt;coursename}',
                '{$a-&gt;profileurl}',
                '{$a-&gt;link}',
                '{$a-&gt;fullname}',
                '{$a-&gt;email}',
            ];
            $message = str_replace($keyhtml, $value, $message);
            $messagehtml = format_text($message, FORMAT_HTML, ['context' => $context,
                'para' => false, 'newlines' => true, 'filter' => true]);
            $messagetext = html_to_text($messagehtml);
        } else {
            $messagetext = get_string('recompletionemaildefaultbody', 'local_recompletion', $a);
            $messagehtml = text_to_html($messagetext, null, false, true);
        }
        if (trim($config->recompletionemailsubject) !== '') {
            $subject = $config->recompletionemailsubject;
            $keysub = ['{$a->coursename}', '{$a->fullname}'];
            $valuesub = [$a->coursename, fullname($userrecord)];
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
    public function reset_user($userid, $course, $config = null) {
        global $CFG, $DB;

        $errors = [];

        if (empty($config)) {
            $config = (object) $DB->get_records_menu('local_recompletion_config',
                                                     ['course' => $course->id], '', 'name, value');
        }
        if (empty($config->recompletiontype)) {
            $errors[] = get_string('recompletionnotenabledincourse', 'local_recompletion', $course->id);
        }

        $restrictions = local_recompletion_get_supported_restrictions();
        foreach ($restrictions as $plugin) {
            $fqn = 'local_recompletion\\local\\restrictions\\' . $plugin;
            if (!$fqn::should_reset($userid, $course, $config)) {
                $errors[] = $fqn::get_restriction_reason();
                return $errors;
            }
        }

        // Archive and delete course completion.
        $this->reset_completions($userid, $course, $config);

        // Delete current grade information.
        if ($config->deletegradedata) {
            if ($items = \grade_item::fetch_all(['courseid' => $course->id])) {
                foreach ($items as $item) {
                    if ($grades = \grade_grade::fetch_all(['userid' => $userid, 'itemid' => $item->id])) {
                        foreach ($grades as $grade) {
                            $grade->delete('local_recompletion');
                        }
                    }
                }
            }
        }

        $plugins = local_recompletion_get_supported_plugins();
        foreach ($plugins as $plugin) {
            $fqn = 'local_recompletion\\plugins\\' . $plugin;
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
            [
                'objectid'      => $course->id,
                'relateduserid' => $userid,
                'courseid' => $course->id,
                'context' => $context,
            ]
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
