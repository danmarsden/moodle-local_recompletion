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
 * local recompletion default settings
 *
 * @package    local_recompletion
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    global $CFG;
    require_once($CFG->dirroot . '/local/recompletion/locallib.php');
    $settings = new admin_settingpage('local_recompletion', new lang_string('defaultsettings', 'local_recompletion'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configduration('local_recompletion/duration',
        new lang_string('recompletionrange', 'local_recompletion'),
        new lang_string('recompletionrange_help', 'local_recompletion'), YEARSECS, PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('local_recompletion/emailenable',
        new lang_string('recompletionemailenable', 'local_recompletion'),
        new lang_string('recompletionemailenable_help', 'local_recompletion'), 1));

    $settings->add(new admin_setting_configtext('local_recompletion/emailsubject',
        new lang_string('recompletionemailsubject', 'local_recompletion'),
        new lang_string('recompletionemailsubject_help', 'local_recompletion'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtextarea('local_recompletion/emailbody',
        new lang_string('recompletionemailbody', 'local_recompletion'),
        new lang_string('recompletionemailbody_help', 'local_recompletion'), ''));

    $settings->add(new admin_setting_configcheckbox('local_recompletion/deletegradedata',
        new lang_string('deletegradedata', 'local_recompletion'),
        new lang_string('deletegradedata_help', 'local_recompletion'), 1));

    $settings->add(new admin_setting_configcheckbox('local_recompletion/archivecompletiondata',
        new lang_string('archivecompletiondata', 'local_recompletion'),
        new lang_string('archivecompletiondata_help', 'local_recompletion'), 1));

    $choices = array(LOCAL_RECOMPLETION_NOTHING => get_string('donothing', 'local_recompletion'),
                     LOCAL_RECOMPLETION_DELETE => get_string('delete', 'local_recompletion'));

    $settings->add(new admin_setting_configselect('local_recompletion/scormattempts',
        new lang_string('scormattempts', 'local_recompletion'),
        new lang_string('scormattempts_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

    $settings->add(new admin_setting_configcheckbox('local_recompletion/archivescormdata',
        new lang_string('archivescormdata', 'local_recompletion'), '', 1));

    $choices[LOCAL_RECOMPLETION_EXTRAATTEMPT] = get_string('extraattempt', 'local_recompletion');
    $settings->add(new admin_setting_configselect('local_recompletion/quizattempts',
        new lang_string('quizattempts', 'local_recompletion'),
        new lang_string('quizattempts_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

    $settings->add(new admin_setting_configcheckbox('local_recompletion/archivequizdata',
        new lang_string('archivequizdata', 'local_recompletion'), '', 1));

    unset($choices[LOCAL_RECOMPLETION_DELETE]); // Not supported in assign.
    $settings->add(new admin_setting_configselect('local_recompletion/assignattempts',
        new lang_string('assignattempts', 'local_recompletion'),
        new lang_string('assignattempts_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

    $settings->add(new admin_setting_configcheckbox('local_recompletion/assignevent',
        new lang_string('assignevent', 'local_recompletion'),
        '', 0));
}
