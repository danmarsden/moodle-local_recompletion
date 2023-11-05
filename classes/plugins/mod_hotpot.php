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
 * Hotpot handler event.
 *
 * @package    local_recompletion
 * @author     2023 Dmitrii Metelkin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_hotpot {

    /**
     * Add params to form.
     *
     * @param MoodleQuickForm $mform
     */
    public static function editingform(MoodleQuickForm $mform): void {
        if (!self::installed()) {
            return;
        }

        $config = get_config('local_recompletion');

        $cba = [];
        $cba[] = $mform->createElement('radio', 'hotpot', '',
                get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'hotpot', '',
                get_string('delete', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'hotpot', get_string('hotpotattempts', 'local_recompletion'), [' '], false);
        $mform->addHelpButton('hotpot', 'hotpotattempts', 'local_recompletion');
        $mform->setDefault('hotpot', $config->hotpotattempts);

        $mform->addElement('checkbox', 'archivehotpot', get_string('archive', 'local_recompletion'));
        $mform->setDefault('archivehhotpot', $config->archivehotpot);

        $mform->disabledIf('archivehotpot', 'enable');
        $mform->hideIf('archivehotpot', 'hotpot');
        $mform->disabledIf('hotpot', 'enable');
    }

    /**
     * Add site level settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings(admin_settingpage $settings): void {
        if (!self::installed()) {
            return;
        }

        $choices = [
            LOCAL_RECOMPLETION_NOTHING => get_string('donothing', 'local_recompletion'),
            LOCAL_RECOMPLETION_DELETE => get_string('delete', 'local_recompletion')
        ];

        $settings->add(new admin_setting_configselect('local_recompletion/hotpotattempts',
                new lang_string('hotpotattempts', 'local_recompletion'),
                new lang_string('hotpotattempts_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new admin_setting_configcheckbox('local_recompletion/archivehotpot',
            new lang_string('archivehotpot', 'local_recompletion'), '', 1));
    }

    /**
     * Reset hotpot records.
     *
     * @param int $userid - user id
     * @param stdClass $course - course record.
     * @param stdClass $config - recompletion config.
     */
    public static function reset(int $userid, stdClass $course, stdClass $config): void {
        global $DB;

        if (!self::installed()) {
            return;
        }

        if (empty($config->hotpot)) {
            return;
        }

        if ($config->hotpot == LOCAL_RECOMPLETION_DELETE) {
            $params = [
                'userid' => $userid,
                'course' => $course->id
            ];

            $attemptsselectsql = 'userid = :userid AND hotpotid IN (SELECT id FROM {hotpot} WHERE course = :course)';
            $extrasql = 'attemptid IN (SELECT id FROM {hotpot_attempts} WHERE ' . $attemptsselectsql . ')';

            if ($config->archivehotpot) {
                $records = $DB->get_records_select('hotpot_attempts', $attemptsselectsql, $params);
                foreach ($records as $record) {
                    $record->course = $course->id;
                }
                $DB->insert_records('local_recompletion_hpa', $records);
            }

            $DB->delete_records_select('hotpot_details', $extrasql, $params);
            $DB->delete_records_select('hotpot_responses', $extrasql, $params);
            $DB->delete_records_select('hotpot_attempts', $attemptsselectsql, $params);
        }
    }

    /**
     * Helper function to check if the plugin is installed.
     * @return bool
     */
    public static function installed(): bool {
        global $CFG;
        if (!file_exists($CFG->dirroot . '/mod/hotpot/version.php')) {
            return false;
        }
        return true;
    }
}
