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
 * lti handler event.
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
 * lti handler event.
 *
 * @package    local_recompletion
 * @author     Dan Marsden
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class lti {
    /**
     * Add params to form.
     * @param moodleform $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform) : void {
        if (!enrol_is_enabled('lti')) {
            return;
        }

        $options = [];
        $options[] = $mform->createElement('radio', 'ltigrade', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $options[] = $mform->createElement('radio', 'ltigrade', '',
            get_string('resetltigrade', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($options, 'lti', get_string('resetltigrades', 'local_recompletion'), [' '], false);
        $mform->addHelpButton('lti', 'resetltigrades', 'local_recompletion');

        $mform->addElement('checkbox', 'archiveltidata',
            get_string('archive', 'local_recompletion'));
        $mform->setDefault('archiveltidata', get_config('local_recompletion', 'archiveltidata'));

        $mform->disabledIf('ltigrade', 'enable', 'notchecked');
        $mform->disabledIf('archiveltidata', 'enable', 'notchecked');
        $mform->hideIf('archiveltidata', 'ltigrade', 'noteq', LOCAL_RECOMPLETION_DELETE);
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {

    }

    /**
     * Reset lti grade
     *
     * @param int       $userid
     * @param \stdClass $course
     * @param \stdClass $config
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function reset(int $userid, \stdClass $course, \stdClass $config) : void {
        global $DB;

        if (empty($config->ltigrade)) {
            return;
        }

        // Make sure LTI is enabled.
        if (!enrol_is_enabled('lti')) {
            return;
        }

        $context = \context_course::instance($course->id);
        $toolid = $DB->get_field('enrol_lti_tools', 'id', ['contextid' => $context->id]);

        if (empty($toolid)) {
            return;
        }

        $params = [
            'userid' => $userid,
            'toolid' => $toolid,
        ];
        if ($config->archiveltidata) {
            // If set we archive records.
            $ltiusers = $DB->get_records('enrol_lti_users', $params, '', 'toolid,userid,lastaccess,lastgrade,timecreated');
            $DB->insert_records('local_recompletion_ltia', $ltiusers);
        }

        // Reset.
        $sql = 'UPDATE {enrol_lti_users}
                SET lastgrade = 0
                WHERE userid = :userid
                AND toolid = :toolid';

        $DB->execute($sql, $params);
    }
}
