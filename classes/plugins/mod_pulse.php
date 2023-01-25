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
 * Pulse handler event.
 *
 * @package     local_recompletion
 * @copyright   2022 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @copyright   based on code by Dan Marsden, Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\plugins;

use lang_string;

/**
 * Pulse handler event.
 *
 * @package    local_recompletion
 * @copyright  2022 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @copyright  based on code by Dan Marsden, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mod_pulse {
    /**
     * Add params to form.
     *
     * @param moodleform $mform
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform): void {
        if (!self::installed()) {
            return;
        }
        $config = get_config('local_recompletion');

        $cba = array();
        $cba[] = $mform->createElement('radio', 'pulse', '',
                get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'pulse', '',
                get_string('pulseresetnotifications', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'pulse', get_string('pulsenotifications', 'local_recompletion'), array(' '), false);
        $mform->addHelpButton('pulse', 'pulsenotifications', 'local_recompletion');
        $mform->setDefault('pulse', $config->pulsenotifications);
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
                LOCAL_RECOMPLETION_DELETE => get_string('pulseresetnotifications', 'local_recompletion'));
        $settings->add(new \admin_setting_configselect('local_recompletion/pulsenotifications',
                new lang_string('pulsenotifications', 'local_recompletion'),
                new lang_string('pulsenotifications_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));
    }

    /**
     * Reset pulse notification records.
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

        if (empty($config->pulse)) {
            return;
        } else if ($config->pulse == LOCAL_RECOMPLETION_DELETE) {
            // Prepare SQL Query.
            $params = array('userid' => $userid, 'course' => $course->id);
            $selectsql = 'userid = ? AND pulseid IN (SELECT id FROM {pulse} WHERE course = ?)';

            // Delete records from pulse_users.
            $DB->delete_records_select('pulse_users', $selectsql, $params);

            // If Pulse Pro is installed, delete records from local_pulsepro_availability as well.
            if (file_exists($CFG->dirroot . '/local/pulsepro/version.php')) {
                $DB->delete_records_select('local_pulsepro_availability', $selectsql, $params);
            }
        }
    }

    /**
     * Helper function to check if pulse is installed.
     * @return bool
     */
    public static function installed() {
        global $CFG;
        if (!file_exists($CFG->dirroot.'/mod/pulse/version.php')) {
            return false;
        }
        return true;
    }
}
