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
 * Choice handler event.
 *
 * @package     local_recompletion
 * @author      Antonio Duran
 * @copyright   Antonio Duran
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\plugins;

use lang_string;

/**
 * Choice handler event.
 *
 * @package    local_recompletion
 * @author     Antonio Duran
 * @copyright  Antonio Duran
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mod_choice {
    /**
     * Add params to form.
     * @param moodleform $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform) : void {
        $config = get_config('local_recompletion');

        $cba = array();
        $cba[] = $mform->createElement('radio', 'choice', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'choice', '',
            get_string('delete', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'choice', get_string('choiceattempts', 'local_recompletion'), array(' '), false);
        $mform->addHelpButton('choice', 'choiceattempts', 'local_recompletion');
        $mform->setDefault('choice', $config->choiceattempts);

        $mform->addElement('checkbox', 'archivechoice',
            get_string('archive', 'local_recompletion'));
        $mform->setDefault('archivechoice', $config->archivechoice);

        $mform->disabledIf('archivechoice', 'enable', 'notchecked');
        $mform->hideIf('archivechoice', 'choice', 'notchecked');
        $mform->disabledIf('choice', 'enable', 'notchecked');
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {

        $choices = array(LOCAL_RECOMPLETION_NOTHING => new lang_string('donothing', 'local_recompletion'),
                         LOCAL_RECOMPLETION_DELETE => new lang_string('delete', 'local_recompletion'));

        $settings->add(new \admin_setting_configselect('local_recompletion/choiceattempts',
            new lang_string('choiceattempts', 'local_recompletion'),
            new lang_string('choiceattempts_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new \admin_setting_configcheckbox('local_recompletion/archivechoice',
            new lang_string('archivechoice', 'local_recompletion'), '', 1));
    }

    /**
     * Reset and archive choice records.
     * @param \stdclass $userid - user id
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;

        if (empty($config->choice)) {
            return;
        } else if ($config->choice == LOCAL_RECOMPLETION_DELETE) {
            $params = array('userid' => $userid, 'course' => $course->id);
            $selectsql = 'userid = ? AND choiceid IN (SELECT id FROM {choice} WHERE course = ?)';
            if ($config->archivechoice) {
                $choiceanswers = $DB->get_records_select('choice_answers', $selectsql, $params);
                foreach ($choiceanswers as $cid => $unused) {
                    // Add courseid to records to help with restore process.
                    $choiceanswers[$cid]->course = $course->id;
                }
                $DB->insert_records('local_recompletion_cha', $choiceanswers);
            }
            $DB->delete_records_select('choice_answers', $selectsql, $params);
        }
    }
}
