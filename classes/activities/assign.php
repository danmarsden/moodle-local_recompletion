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
 * Assign handler event.
 *
 * @package     local_recompletion
 * @author      Dan Marsden
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\activities;

use lang_string;

/**
 * Quiz handler event.
 *
 * @package    local_recompletion
 * @author     Dan Marsden
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class assign {
    /**
     * Add params to form.
     * @param moodleform $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform): void {
        $config = get_config('local_recompletion');

        $cba = array();
        $cba[] = $mform->createElement('radio', 'assign', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'assign', '',
            get_string('extraattempt', 'local_recompletion'), LOCAL_RECOMPLETION_EXTRAATTEMPT);
        $cba[] = $mform->createElement('checkbox', 'assignevent', '', get_string('assignevent', 'local_recompletion'));
        $mform->addGroup($cba, 'assign', get_string('assignattempts', 'local_recompletion'), array(' '), false);
        $mform->addHelpButton('assign', 'assignattempts', 'local_recompletion');

        $mform->setDefault('assign', $config->assignattempts);
        $mform->setDefault('assignevent', $config->assignevent);

        $mform->disabledIf('assign', 'enable', 'notchecked');
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {
        $choices = array(LOCAL_RECOMPLETION_NOTHING => new lang_string('donothing', 'local_recompletion'),
            LOCAL_RECOMPLETION_EXTRAATTEMPT => new lang_string('extraattempt', 'local_recompletion'));

        $settings->add(new \admin_setting_configselect('local_recompletion/assignattempts',
            new lang_string('assignattempts', 'local_recompletion'),
            new lang_string('assignattempts_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new \admin_setting_configcheckbox('local_recompletion/assignevent',
            new lang_string('assignevent', 'local_recompletion'),
            '', 0));
    }

    /**
     * Reset assign records.
     * @param \int $userid - record with user information for recompletion
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;
        if (empty($config->assign)) {
            return '';
        } else if ($config->assign == LOCAL_RECOMPLETION_EXTRAATTEMPT) {
            $sql = "SELECT DISTINCT a.*
                      FROM {assign} a
                      JOIN {assign_submission} s ON a.id = s.assignment
                     WHERE a.course = ? AND s.userid = ?";
            $assigns = $DB->get_recordset_sql($sql, array($course->id, $userid));
            $nopermissions = false;
            foreach ($assigns as $assign) {
                $cm = get_coursemodule_from_instance('assign', $assign->id);
                $context = \context_module::instance($cm->id);
                if (has_capability('mod/assign:grade', $context)) {
                    // Assign add_attempt() is protected - use reflection so we don't have to write our own.
                    $r = new \ReflectionMethod('assign', 'add_attempt');
                    $r->setAccessible(true);
                    $r->invoke(new \assign($context, $cm, $course), $userid);
                } else {
                    $nopermissions = true;
                }
            }
            if ($nopermissions) {
                return get_string('noassigngradepermission', 'local_recompletion');
            }
        }
        return '';
    }
}
