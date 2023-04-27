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
 * Custom certificate handler event.
 *
 * @package     local_recompletion
 * @copyright   2023 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @copyright   based on code by Dan Marsden, Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\plugins;

use lang_string;

/**
 * Custom certificate handler event.
 *
 * @package    local_recompletion
 * @copyright  2023 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @copyright  based on code by Dan Marsden, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mod_customcert {
    /**
     * Add params to form.
     *
     * @param moodleform $mform
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform): void {
        global $OUTPUT;

        if (!self::installed()) {
            return;
        }
        $config = get_config('local_recompletion');

        $cba = array();
        $cba[] = $mform->createElement('radio', 'customcert', '',
                get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'customcert', '',
                get_string('customcertresetcertificates', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'customcert', get_string('customcertcertificates', 'local_recompletion'), array(' '), false);
        $mform->addHelpButton('customcert', 'customcertcertificates', 'local_recompletion');
        $mform->setDefault('customcert', $config->customcertcertificates);

        $mform->addElement('checkbox', 'archivecustomcert',
                get_string('archivecustomcertcertificates', 'local_recompletion'));
        $mform->setDefault('archivecustomcert', $config->archivecustomcert);

        $verifywarngroup = []; // Use a workaround to hide a static mform element based on MDL-66251.
        $verifywarn = new \core\output\notification(
                get_string('customcertresetcertificatesverifywarn', 'local_recompletion'),
                \core\output\notification::NOTIFY_WARNING);
        $verifywarn->set_show_closebutton(false);
        $verifywarngroup[] =
                $mform->createElement('static', 'customcertresetcertificatesverifywarn', '', $OUTPUT->render($verifywarn));
        $mform->addGroup($verifywarngroup, 'verifywarngroup', '', ' ', false);

        $mform->disabledIf('customcert', 'enable', 'notchecked');
        $mform->disabledIf('archivecustomcert', 'enable', 'notchecked');
        $mform->hideIf('archivecustomcert', 'customcert', 'noteq', LOCAL_RECOMPLETION_DELETE);
        $mform->hideIf('verifywarngroup', 'customcert', 'noteq', LOCAL_RECOMPLETION_DELETE);
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
        $choices = array(LOCAL_RECOMPLETION_NOTHING => get_string('donothing', 'local_recompletion'),
                LOCAL_RECOMPLETION_DELETE => get_string('customcertresetcertificates', 'local_recompletion'));

        $settings->add(new \admin_setting_configselect('local_recompletion/customcertcertificates',
                new lang_string('customcertcertificates', 'local_recompletion'),
                new lang_string('customcertcertificates_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new \admin_setting_configcheckbox('local_recompletion/archivecustomcert',
                new lang_string('archivecustomcertcertificates', 'local_recompletion'),
                new lang_string('archivecustomcertcertificates_help', 'local_recompletion'), 1));
    }

    /**
     * Reset custom certificate records.
     *
     * @param \stdclass $userid - user id
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    public static function reset($userid, $course, $config) {
        global $CFG, $DB;
        if (!self::installed()) {
            return;
        }

        if (empty($config->customcert)) {
            return;
        } else if ($config->customcert == LOCAL_RECOMPLETION_DELETE) {
            // Prepare SQL Query.
            $params = array('userid' => $userid, 'course' => $course->id);
            $selectsql = 'userid = ? AND customcertid IN (SELECT id FROM {customcert} WHERE course = ?)';

            // If archiving is activated.
            if ($config->archivecustomcert) {
                // Archive the issued certificates.
                $issuedcerts = $DB->get_records_select('customcert_issues', $selectsql, $params);
                foreach ($issuedcerts as $ic => $unused) {
                    // Add courseid to records to help with restore process.
                    $issuedcerts[$ic]->course = $course->id;
                }
                $DB->insert_records('local_recompletion_ccert_is', $issuedcerts);
            }

            // Delete records from customcert_issues.
            $DB->delete_records_select('customcert_issues', $selectsql, $params);
        }
    }

    /**
     * Helper function to check if custom certificate is installed.
     * @return bool
     */
    public static function installed() {
        global $CFG;
        if (!file_exists($CFG->dirroot.'/mod/customcert/version.php')) {
            return false;
        }
        return true;
    }
}
