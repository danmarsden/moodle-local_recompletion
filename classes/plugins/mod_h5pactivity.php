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

namespace local_recompletion\plugins;

use stdClass;
use lang_string;
use admin_setting_configselect;
use admin_setting_configcheckbox;
use admin_settingpage;
use MoodleQuickForm;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/recompletion/locallib.php');

/**
 * H5P handler event.
 *
 * @package    local_recompletion
 * @author     2023 Dmitrii Metelkin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_h5pactivity {

    /**
     * Add params to form.
     *
     * @param MoodleQuickForm $mform
     */
    public static function editingform(MoodleQuickForm $mform): void {
        $config = get_config('local_recompletion');

        $cba = [];
        $cba[] = $mform->createElement('radio', 'h5pactivity', '',
                get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'h5pactivity', '',
                get_string('delete', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'h5pactivity', get_string('h5pattempts', 'local_recompletion'), [' '], false);
        $mform->addHelpButton('h5pactivity', 'h5pattempts', 'local_recompletion');
        $mform->setDefault('h5pactivity', $config->h5pattempts);

        $mform->addElement('checkbox', 'archiveh5pactivity', get_string('archive', 'local_recompletion'));
        $mform->setDefault('archiveh5pactivity', $config->archiveh5p);

        $mform->disabledIf('archiveh5pactivity', 'enable');
        $mform->hideIf('archiveh5pactivity', 'h5pactivity');
        $mform->disabledIf('h5pactivity', 'enable');
    }

    /**
     * Add site level settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings(admin_settingpage $settings): void {
        $choices = [
            LOCAL_RECOMPLETION_NOTHING => get_string('donothing', 'local_recompletion'),
            LOCAL_RECOMPLETION_DELETE => get_string('delete', 'local_recompletion')
        ];

        $settings->add(new admin_setting_configselect('local_recompletion/h5pattempts',
                new lang_string('h5pattempts', 'local_recompletion'),
                new lang_string('h5pattempts_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new admin_setting_configcheckbox('local_recompletion/archiveh5p',
            new lang_string('archiveh5p', 'local_recompletion'), '', 1));
    }

    /**
     * Reset h5pactivity attempt records.
     *
     * @param int $userid - user id
     * @param stdClass $course - course record.
     * @param stdClass $config - recompletion config.
     */
    public static function reset(int $userid, stdClass $course, stdClass $config): void {
        global $DB;

        if (empty($config->h5pactivity)) {
            return;
        }

        if ($config->h5pactivity == LOCAL_RECOMPLETION_DELETE) {
            $params = [
                'userid' => $userid,
                'course' => $course->id
            ];

            $attemptsselectsql = 'userid = :userid AND h5pactivityid IN (SELECT id FROM {h5pactivity} WHERE course = :course)';
            $resultsselectsql = 'attemptid IN (SELECT id FROM {h5pactivity_attempts} WHERE ' . $attemptsselectsql . ')';

            if ($config->archiveh5pactivity) {

                // Archive attempts.
                $attempts = $DB->get_records_select('h5pactivity_attempts', $attemptsselectsql, $params);
                if (!empty($attempts)) {
                    $attemptids = array_keys($attempts);

                    foreach ($attempts as $attempt) {
                        $attempt->course = $course->id;
                        $attempt->originalattemptid = $attempt->id;
                    }

                    $DB->insert_records('local_recompletion_h5p', $attempts);

                    // Archive results.
                    $results = $DB->get_records_select('h5pactivity_attempts_results', $resultsselectsql, $params);
                    if (!empty($results)) {
                        foreach ($results as $result) {
                            $result->course = $course->id;
                        }
                        $DB->insert_records('local_recompletion_h5pr', $results);

                        // Update attemptid for just inserted attempt results with IDs of previously inserted attempts.
                        // We use temp originalattemptid here as it should be unique.
                        if ($DB->get_dbfamily() == 'mysql') {
                            $sql = 'UPDATE {local_recompletion_h5pr} h5pr
                                      JOIN {local_recompletion_h5p} h5p ON h5pr.attemptid = h5p.originalattemptid
                                       SET h5pr.attemptid = h5p.id';
                        } else {
                            $sql = ' UPDATE {local_recompletion_h5pr} h5pr
                                        SET attemptid = h5p.id
                                       FROM {local_recompletion_h5p} h5p
                                      WHERE h5pr.attemptid = h5p.originalattemptid';
                        }

                        $DB->execute($sql);
                    }

                    // Now reset originalattemptid as we don't need it anymore.
                    // As well as to avoid issues with backup and restore when potentially originalattemptid can clash
                    // if restoring a course from another Moodle instance.
                    list($insql, $inparams) = $DB->get_in_or_equal($attemptids, SQL_PARAMS_NAMED);
                    $sql = "UPDATE {local_recompletion_h5p} SET originalattemptid = 0 WHERE originalattemptid $insql";
                    $DB->execute($sql, $inparams);
                }

            }

            // Finally can delete records.
            $DB->delete_records_select('h5pactivity_attempts_results', $resultsselectsql, $params);
            $DB->delete_records_select('h5pactivity_attempts', $attemptsselectsql, $params);
        }
    }
}
