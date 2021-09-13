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
 * SCORM handler event.
 *
 * @package     local_recompletion
 * @author      Dan Marsden
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\activities;

use lang_string;

defined('MOODLE_INTERNAL') || die();

/**
 * SCORM handler event.
 *
 * @package    local_recompletion
 * @author     Dan Marsden
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class scorm {
    /**
     * Add params to form.
     * @param moodleform $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform) : void {
        $config = get_config('local_recompletion');

        $cba = array();
        $cba[] = $mform->createElement('radio', 'scormdata', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'scormdata', '',
            get_string('delete', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'scorm', get_string('scormattempts', 'local_recompletion'), array(' '), false);
        $mform->addHelpButton('scorm', 'scormattempts', 'local_recompletion');
        $mform->setDefault('scormdata', $config->scormattempts);

        $mform->addElement('checkbox', 'archivescormdata',
            get_string('archive', 'local_recompletion'));
        $mform->setDefault('archivescormdata', $config->archivescormdata);

        $mform->disabledIf('archivescormdata', 'enable', 'notchecked');
        $mform->hideIf('archivescormdata', 'scormdata', 'notchecked');
        $mform->disabledIf('scormdata', 'enable', 'notchecked');

    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {
        $choices = array(LOCAL_RECOMPLETION_NOTHING => get_string('donothing', 'local_recompletion'),
            LOCAL_RECOMPLETION_DELETE => get_string('delete', 'local_recompletion'));
        $settings->add(new \admin_setting_configselect('local_recompletion/scormattempts',
            new lang_string('scormattempts', 'local_recompletion'),
            new lang_string('scormattempts_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new \admin_setting_configcheckbox('local_recompletion/archivescormdata',
            new lang_string('archivescormdata', 'local_recompletion'), '', 1));
    }

    /**
     * Reset and archive scorm records.
     * @param \stdclass $userid - user id
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;

        if (empty($config->scormdata)) {
            return;
        } else if ($config->scormdata == LOCAL_RECOMPLETION_DELETE) {
            $params = array('userid' => $userid, 'course' => $course->id);
            $selectsql = 'userid = ? AND scormid IN (SELECT id FROM {scorm} WHERE course = ?)';
            if ($config->archivescormdata) {
                $scormscoestrack = $DB->get_records_select('scorm_scoes_track', $selectsql, $params);
                foreach ($scormscoestrack as $sid => $unused) {
                    // Add courseid to records to help with restore process.
                    $scormscoestrack[$sid]->course = $course->id;
                }
                $DB->insert_records('local_recompletion_sst', $scormscoestrack);
            }
            $DB->delete_records_select('scorm_scoes_track', $selectsql, $params);
            $DB->delete_records_select('scorm_aicc_session', $selectsql, $params);
        }
    }
}