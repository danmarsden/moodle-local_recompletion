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
 * Certificate handler event.
 *
 * @package    local_recompletion
 * @author     2023 Dmitrii Metelkin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_certificate {

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
        $cba[] = $mform->createElement('radio', 'certificate', '',
                get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'certificate', '',
                get_string('deletecertificate', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'certificate', get_string('certificate', 'local_recompletion'), [' '], false);
        $mform->addHelpButton('certificate', 'certificate', 'local_recompletion');
        $mform->setDefault('certificate', $config->certificate);

        $mform->addElement('checkbox', 'archivecertificate',
                get_string('archivecertificate', 'local_recompletion'));
        $mform->setDefault('archivecertificate', $config->archivecertificate);

        $verifywarngroup = [];
        $verifywarn = new notification(
                get_string('certificateverifywarn', 'local_recompletion'),
                notification::NOTIFY_WARNING);
        $verifywarn->set_show_closebutton(false);
        $verifywarngroup[] =
                $mform->createElement('static', 'certificateverifywarn', '', $OUTPUT->render($verifywarn));
        $mform->addGroup($verifywarngroup, 'certificateverifywarngroup', '', ' ', false);

        $mform->disabledIf('certificate', 'enable');
        $mform->disabledIf('archivecertificate', 'enable');
        $mform->hideIf('archivecertificate', 'certificate', 'noteq', LOCAL_RECOMPLETION_DELETE);
        $mform->hideIf('certificateverifywarngroup', 'certificate', 'noteq', LOCAL_RECOMPLETION_DELETE);
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

        $settings->add(new admin_setting_configselect('local_recompletion/certificate',
                new lang_string('certificate', 'local_recompletion'),
                new lang_string('certificate_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new admin_setting_configcheckbox('local_recompletion/archivecertificate',
                new lang_string('archivecertificate', 'local_recompletion'),
                new lang_string('archivecertificate_help', 'local_recompletion'), 1));
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

        if (empty($config->certificate)) {
            return;
        }

        if ($config->certificate == LOCAL_RECOMPLETION_DELETE) {
            $params = [
                'userid' => $userid,
                'courseid' => $course->id,
            ];

            if ($config->archivecertificate) {

                // Archive the issued certificates.
                $sql = "SELECT ci.*, c.printdate
                          FROM {certificate_issues} ci
                          JOIN {certificate} c ON c.id = ci.certificateid
                         WHERE ci.userid = :userid AND ci.certificateid IN (SELECT id FROM {certificate} WHERE course = :courseid)";
                $issuedcerts = $DB->get_records_sql($sql, $params);

                foreach (array_keys($issuedcerts) as $ic) {
                    $issuedcerts[$ic]->course = $course->id;
                    // Depending on activity settings actual date printed on a certificate can be different
                    // to a date of issue. Let's try to build printed date and archive it as well for future verification.
                    $issuedcerts[$ic]->printdate = self::certificate_get_date(
                        $issuedcerts[$ic]->timecreated,
                        $issuedcerts[$ic]->printdate,
                        (object) ['id' => $course->id],
                        $userid
                    );
                }

                // Archive records.
                $DB->insert_records('local_recompletion_cert', $issuedcerts);
            }

            // Finally delete records.
            $selectsql = 'userid = :userid AND certificateid IN (SELECT id FROM {certificate} WHERE course = :courseid)';
            $DB->delete_records_select('certificate_issues', $selectsql, $params);
        }
    }

    /**
     * Helper function to check if it's installed.
     * @return bool
     */
    public static function installed(): bool {
        global $CFG;

        if (!file_exists($CFG->dirroot . '/mod/certificate/version.php')) {
            return false;
        }

        return true;
    }

    /**
     * Returns the date to display for the certificate.
     *
     * This is pretty much replication of certificate_get_date from locallib.php of mod_certificate.
     *
     * @param string $issueddate Issue date.
     * @param string $printdate Print date setting.
     * @param stdClass $course Course object.
     * @param int $userid User ID.
     *
     * @return string the date
     */
    protected static function certificate_get_date(string $issueddate, string $printdate, stdClass $course, int $userid): string {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/certificate/locallib.php');

        $date = $issueddate;

        if ($printdate == '2') {
            $sql = "SELECT MAX(c.timecompleted) as timecompleted
                      FROM {course_completions} c
                     WHERE c.userid = :userid
                           AND c.course = :courseid";
            if ($timecompleted = $DB->get_record_sql($sql, ['userid' => $userid, 'courseid' => $course->id])) {
                if (!empty($timecompleted->timecompleted)) {
                    $date = $timecompleted->timecompleted;
                }
            }
        } else if ($printdate > 2) {
            if ($modinfo = certificate_get_mod_grade($course, $printdate, $userid)) {
                $date = $modinfo->dategraded;
            }
        }

        return $date;
    }
}
