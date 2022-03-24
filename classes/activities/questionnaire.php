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
 * Questionnaire handler event.
 *
 * @package     local_recompletion
 * @author      Dan Marsden
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\activities;

use lang_string;

/**
 * Questionnaire handler event.
 *
 * @package    local_recompletion
 * @author     Dan Marsden
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class questionnaire {
    /**
     * Add params to form.
     * @param moodleform $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform) : void {
        if (!self::installed()) {
            return;
        }
        $config = get_config('local_recompletion');

        $cba = array();
        $cba[] = $mform->createElement('radio', 'questionnaire', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'questionnaire', '',
            get_string('delete', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'questionnaire', get_string('questionnaireattempts', 'local_recompletion'), array(' '), false);
        $mform->addHelpButton('questionnaire', 'questionnaireattempts', 'local_recompletion');
        $mform->setDefault('questionnaire', $config->questionnaireattempts);

        $mform->addElement('checkbox', 'archivequestionnaire',
            get_string('archive', 'local_recompletion'));
        $mform->setDefault('archivequestionnaire', $config->archivequestionnaire);

        $mform->disabledIf('questionnaire', 'enable', 'notchecked');
        $mform->disabledIf('archivequestionnaire', 'enable', 'notchecked');
        $mform->hideIf('archivequestionnaire', 'questionnaire', 'noteq', LOCAL_RECOMPLETION_DELETE);
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {
        if (!self::installed()) {
            return;
        }
        $choices = array(LOCAL_RECOMPLETION_NOTHING => new lang_string('donothing', 'local_recompletion'),
            LOCAL_RECOMPLETION_DELETE => new lang_string('delete', 'local_recompletion'),
            LOCAL_RECOMPLETION_EXTRAATTEMPT => new lang_string('extraattempt', 'local_recompletion'));

        $settings->add(new \admin_setting_configselect('local_recompletion/questionnaireattempts',
            new lang_string('questionnaireattempts', 'local_recompletion'),
            new lang_string('questionnaireattempts_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new \admin_setting_configcheckbox('local_recompletion/archivequestionnaire',
            new lang_string('archivequestionnaire', 'local_recompletion'), '', 1));
    }

    /**
     * Reset and archive questionnaire records.
     * @param \int $userid - userid
     * @param \stdclass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;
        if (!self::installed()) {
            return;
        }
        $extratables = ['local_recompletion_qr_bool'   => 'questionnaire_response_bool' ,
                        'local_recompletion_qr_date'   => 'questionnaire_response_date',
                        'local_recompletion_qr_m'      => 'questionnaire_resp_multiple',
                        'local_recompletion_qr_other'  => 'questionnaire_response_other',
                        'local_recompletion_qr_rank'   => 'questionnaire_response_rank',
                        'local_recompletion_qr_single' => 'questionnaire_resp_single',
                        'local_recompletion_qr_text'   => 'questionnaire_response_text'];

        if (empty($config->questionnaire)) {
            return;
        } else if ($config->questionnaire == LOCAL_RECOMPLETION_DELETE) {
            $params = array('userid' => $userid, 'course' => $course->id);
            $selectsql = 'userid = ? AND questionnaireid IN (SELECT id FROM {questionnaire} WHERE course = ?)';

            $questionnaireattempts = $DB->get_records_select('questionnaire_response', $selectsql, $params);
            foreach ($questionnaireattempts as $qid => $unused) {
                if ($config->archivequestionnaire) {
                    // Add courseid to repsonse records to help with restore process.
                    $questionnaireattempts[$qid]->course = $course->id;
                    $questionnaireattempts[$qid]->originalresponseid = $qid;

                    foreach ($extratables as $newtable => $table) {
                        $extrarows = $DB->get_records($table, ['response_id' => $qid]);
                        if (!empty($extrarows)) {
                            $DB->insert_records($newtable, $extrarows);
                        }
                    }
                }

                // Delete extra table data for this response.
                foreach ($extratables as $table) {
                    $DB->delete_records($table, ['response_id' => $qid]);
                }
            }

            if ($config->archivequestionnaire) {
                // Archive main response table.
                $DB->insert_records('local_recompletion_qr', $questionnaireattempts);
            }

            $DB->delete_records_select('questionnaire_response', $selectsql, $params);
        }
    }

    /**
     * Helper function to check if questionnaire is installed.
     * @return bool
     */
    public static function installed() {
        global $CFG;
        if (!file_exists($CFG->dirroot.'/mod/questionnaire/version.php')) {
            return false;
        }
        return true;
    }
}
