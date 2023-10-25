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
class mod_hvp {

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
        $cba[] = $mform->createElement('radio', 'hvp', '',
                get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'hvp', '',
                get_string('delete', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'hvp', get_string('hvpattempts', 'local_recompletion'), [' '], false);
        $mform->addHelpButton('hvp', 'hvpattempts', 'local_recompletion');
        $mform->setDefault('hvp', $config->hvpattempts);

        $mform->addElement('checkbox', 'archivehvp', get_string('archive', 'local_recompletion'));
        $mform->setDefault('archivehvp', $config->archivehvp);

        $mform->disabledIf('archivehvp', 'enable');
        $mform->hideIf('archivehvp', 'hvp');
        $mform->disabledIf('hvp', 'enable');
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

        $settings->add(new admin_setting_configselect('local_recompletion/hvpattempts',
                new lang_string('hvpattempts', 'local_recompletion'),
                new lang_string('hvpattempts_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new admin_setting_configcheckbox('local_recompletion/archivehvp',
            new lang_string('archivehvp', 'local_recompletion'), '', 1));
    }

    /**
     * Reset hvp attempt records.
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

        if (empty($config->hvp)) {
            return;
        }

        if ($config->hvp == LOCAL_RECOMPLETION_DELETE) {
            $params = [
                'userid' => $userid,
                'course' => $course->id
            ];

            $selectsql = 'user_id = :userid AND hvp_id IN (SELECT id FROM {hvp} WHERE course = :course)';

            if ($config->archivehvp) {
                $records = $DB->get_records_select('hvp_content_user_data', $selectsql, $params);
                foreach ($records as $record) {
                    $record->course = $course->id;
                }
                $DB->insert_records('local_recompletion_hvp', $records);
            }

            $DB->delete_records_select('hvp_content_user_data', $selectsql, $params);
        }
    }

    /**
     * Helper function to check if the plugin is installed.
     * @return bool
     */
    public static function installed(): bool {
        global $CFG;
        if (!file_exists($CFG->dirroot . '/mod/hvp/version.php')) {
            return false;
        }
        return true;
    }
}
