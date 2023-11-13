<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_recompletion\plugins;

use stdClass;
use lang_string;
use admin_setting_configselect;
use admin_setting_configcheckbox;
use admin_settingpage;
use core\output\notification;
use MoodleQuickForm;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/recompletion/locallib.php');

/**
 * Course certificate handler event.
 *
 * @package    local_recompletion
 * @author     2023 Dmitrii Metelkin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_coursecertificate {

    /**
     * Add params to form.
     *
     * @param MoodleQuickForm $mform
     */
    public static function editingform(MoodleQuickForm $mform): void {
        global $OUTPUT;

        if (!self::installed()) {
            return;
        }
        $config = get_config('local_recompletion');

        $cba = [];
        $cba[] = $mform->createElement('radio', 'coursecertificate', '',
                get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'coursecertificate', '',
                get_string('deletecoursecertificate', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'coursecertificate', get_string('coursecertificate', 'local_recompletion'), [' '], false);
        $mform->addHelpButton('coursecertificate', 'coursecertificate', 'local_recompletion');
        $mform->setDefault('coursecertificate', $config->coursecertificate);

        $mform->addElement('checkbox', 'archivecoursecertificate',
                get_string('archivecoursecertificate', 'local_recompletion'));
        $mform->setDefault('archivecoursecertificate', $config->archivecoursecertificate);

        $verifywarngroup = [];
        $verifywarn = new notification(
                get_string('coursecertificateverifywarn', 'local_recompletion'),
                notification::NOTIFY_WARNING);
        $verifywarn->set_show_closebutton(false);
        $verifywarngroup[] =
                $mform->createElement('static', 'coursecertificateverifywarn', '', $OUTPUT->render($verifywarn));
        $mform->addGroup($verifywarngroup, 'certverifywarngroup', '', ' ', false);

        $mform->disabledIf('coursecertificate', 'enable');
        $mform->disabledIf('archivecoursecertificate', 'enable');
        $mform->hideIf('archivecoursecertificate', 'coursecertificate', 'noteq', LOCAL_RECOMPLETION_DELETE);
        $mform->hideIf('certverifywarngroup', 'coursecertificate', 'noteq', LOCAL_RECOMPLETION_DELETE);
    }

    /**
     * Add site level settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings(admin_settingpage $settings) {

        if (!self::installed()) {
            return;
        }

        $choices = [
            LOCAL_RECOMPLETION_NOTHING => get_string('donothing', 'local_recompletion'),
                LOCAL_RECOMPLETION_DELETE => get_string('customcertresetcertificates', 'local_recompletion')
        ];

        $settings->add(new admin_setting_configselect('local_recompletion/coursecertificate',
                new lang_string('coursecertificate', 'local_recompletion'),
                new lang_string('coursecertificate_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new admin_setting_configcheckbox('local_recompletion/archivecoursecertificate',
                new lang_string('archivecoursecertificate', 'local_recompletion'),
                new lang_string('archivecoursecertificate_help', 'local_recompletion'), 1));
    }

    /**
     * Reset records.
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

        if (empty($config->coursecertificate)) {
            return;
        }

        if ($config->coursecertificate == LOCAL_RECOMPLETION_DELETE) {
            $params = [
                'courseid' => $course->id,
                'component' => 'mod_coursecertificate',
                'userid' => $userid,
            ];

            if ($config->archivecoursecertificate) {
                // Archive all user's certificate within a given course.
                $DB->execute(
                    "UPDATE {tool_certificate_issues}
                        SET archived = 1
                      WHERE courseid = :courseid
                            AND component = :component
                            AND userid = :userid
                            AND archived = 0",
                    $params
                );
            } else {
                // Revoke all user's certificate within a given course.
                $records = $DB->get_records('tool_certificate_issues', $params);
                foreach ($records as $record) {
                    \tool_certificate\template::instance($record->templateid)->revoke_issue($record->id);
                }
            }
        }
    }

    /**
     * Helper function to check if it's installed.
     * @return bool
     */
    public static function installed(): bool {
        global $CFG;

        if (!file_exists($CFG->dirroot . '/mod/coursecertificate/version.php')) {
            return false;
        }

        if (!file_exists($CFG->dirroot . '/admin/tool/certificate/version.php')) {
            return false;
        }

        return true;
    }
}
