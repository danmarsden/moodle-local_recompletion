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
 * Quiz handler event.
 *
 * @package     local_recompletion
 * @author      Dan Marsden
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\activities;

use lang_string;

/**
 * Quiz handler event.
 *
 * @package    local_recompletion
 * @author     Dan Marsden
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class quiz {
    /**
     * Add params to form.
     * @param moodleform $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform) : void {
        $config = get_config('local_recompletion');

        $cba = array();
        $cba[] = $mform->createElement('radio', 'quiz', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'quiz', '',
            get_string('delete', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);
        $cba[] = $mform->createElement('radio', 'quiz', '',
            get_string('extraattempt', 'local_recompletion'), LOCAL_RECOMPLETION_EXTRAATTEMPT);

        $mform->addGroup($cba, 'quiz', get_string('quizattempts', 'local_recompletion'), array(' '), false);
        $mform->addHelpButton('quiz', 'quizattempts', 'local_recompletion');
        $mform->setDefault('quiz', $config->quizattempts);

        $mform->addElement('checkbox', 'archivequiz',
            get_string('archive', 'local_recompletion'));
        $mform->setDefault('archivequiz', $config->archivequiz);

        $mform->disabledIf('quiz', 'enable', 'notchecked');
        $mform->disabledIf('archivequiz', 'enable', 'notchecked');
        $mform->hideIf('archivequiz', 'quiz', 'noteq', LOCAL_RECOMPLETION_DELETE);
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {
        $choices = array(LOCAL_RECOMPLETION_NOTHING => new lang_string('donothing', 'local_recompletion'),
                         LOCAL_RECOMPLETION_DELETE => new lang_string('delete', 'local_recompletion'),
                         LOCAL_RECOMPLETION_EXTRAATTEMPT => new lang_string('extraattempt', 'local_recompletion'));

        $settings->add(new \admin_setting_configselect('local_recompletion/quizattempts',
            new lang_string('quizattempts', 'local_recompletion'),
            new lang_string('quizattempts_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new \admin_setting_configcheckbox('local_recompletion/archivequiz',
            new lang_string('archivequiz', 'local_recompletion'), '', 1));
    }

    /**
     * Reset and archive quiz records.
     * @param \int $userid - userid
     * @param \stdclass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;
        if (empty($config->quiz)) {
            return;
        } else if ($config->quiz == LOCAL_RECOMPLETION_DELETE) {
            $params = array('userid' => $userid, 'course' => $course->id);
            $selectsql = 'userid = ? AND quiz IN (SELECT id FROM {quiz} WHERE course = ?)';
            if ($config->archivequiz) {
                $quizattempts = $DB->get_records_select('quiz_attempts', $selectsql, $params);
                foreach ($quizattempts as $qid => $unused) {
                    // Add courseid to records to help with restore process.
                    $quizattempts[$qid]->course = $course->id;
                }
                $DB->insert_records('local_recompletion_qa', $quizattempts);

                $quizgrades = $DB->get_records_select('quiz_grades', $selectsql, $params);
                foreach ($quizgrades as $qid => $unused) {
                    // Add courseid to records to help with restore process.
                    $quizgrades[$qid]->course = $course->id;
                }
                $DB->insert_records('local_recompletion_qg', $quizgrades);
            }
            $DB->delete_records_select('quiz_attempts', $selectsql, $params);
            $DB->delete_records_select('quiz_grades', $selectsql, $params);
        } else if ($config->quiz == LOCAL_RECOMPLETION_EXTRAATTEMPT) {
            // Get all quizzes that do not have unlimited attempts and have existing data for this user.
            $sql = "SELECT DISTINCT q.*
                      FROM {quiz} q
                      JOIN {quiz_attempts} qa ON q.id = qa.quiz
                     WHERE q.attempts > 0 AND q.course = ? AND qa.userid = ?";
            $quizzes = $DB->get_recordset_sql( $sql, array($course->id, $userid));
            foreach ($quizzes as $quiz) {
                // Get number of this users attempts.
                $attempts = \quiz_get_user_attempts($quiz->id, $userid);
                $countattempts = count($attempts);

                // Allow the user to have the same number of attempts at this quiz as they initially did.
                // EG if they can have 2 attempts, and they have 1 attempt already, allow them to have 2 more attempts.
                $nowallowed = $countattempts + $quiz->attempts;

                // Get stuff needed for the events.
                $cm = get_coursemodule_from_instance('quiz', $quiz->id);
                $context = \context_module::instance($cm->id);

                $eventparams = array(
                    'context' => $context,
                    'other' => array(
                        'quizid' => $quiz->id
                    ),
                    'relateduserid' => $userid
                );

                $conditions = array(
                    'quiz' => $quiz->id,
                    'userid' => $userid);
                if ($oldoverride = $DB->get_record('quiz_overrides', $conditions)) {
                    if ($oldoverride->attempts < $nowallowed) {
                        $oldoverride->attempts = $nowallowed;
                        $DB->update_record('quiz_overrides', $oldoverride);
                        $eventparams['objectid'] = $oldoverride->id;
                        $event = \mod_quiz\event\user_override_updated::create($eventparams);
                        $event->trigger();
                    }
                } else {
                    $data = new \stdClass();
                    $data->attempts = $nowallowed;
                    $data->quiz = $quiz->id;
                    $data->userid = $userid;
                    // Merge quiz defaults with data.
                    $keys = array('timeopen', 'timeclose', 'timelimit', 'password');
                    foreach ($keys as $key) {
                        if (!isset($data->{$key})) {
                            $data->{$key} = $quiz->{$key};
                        }
                    }
                    $newid = $DB->insert_record('quiz_overrides', $data);
                    $eventparams['objectid'] = $newid;
                    $event = \mod_quiz\event\user_override_created::create($eventparams);
                    $event->trigger();
                }
            }
        }
    }
}
