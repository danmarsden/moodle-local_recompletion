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

use local_recompletion\admin_setting_configstrtotime;

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    global $CFG;
    require_once($CFG->dirroot . '/local/recompletion/locallib.php');
    $settings = new admin_settingpage('local_recompletion', new lang_string('defaultsettings', 'local_recompletion'));
    $ADMIN->add('localplugins', $settings);

    // Type of recompletion - range(duration) or schedule(absolute times, based on cron schedule).
    $settings->add(new admin_setting_configselect('local_recompletion/recompletiontype',
        new lang_string('recompletiontype', 'local_recompletion'),
        new lang_string('recompletiontype_help', 'local_recompletion'), 'range', [
            local_recompletion_recompletion_form::RECOMPLETION_TYPE_DISABLED => get_string(
                'recompletiontype:disabled',
                'local_recompletion'
            ),
            local_recompletion_recompletion_form::RECOMPLETION_TYPE_PERIOD => get_string(
                'recompletiontype:period',
                'local_recompletion',
            ),
            local_recompletion_recompletion_form::RECOMPLETION_TYPE_ONDEMAND => get_string(
                'recompletiontype:ondemand',
                'local_recompletion',
            ),
            local_recompletion_recompletion_form::RECOMPLETION_TYPE_SCHEDULE => get_string(
                'recompletiontype:schedule',
                'local_recompletion',
            ),
        ]));

    $settings->add(new admin_setting_configstrtotime('local_recompletion/schedule',
        new lang_string('recompletionschedule', 'local_recompletion'),
        new lang_string('recompletionschedule_help', 'local_recompletion'), 'Jan 1', PARAM_TEXT));

    $settings->add(new admin_setting_configduration('local_recompletion/duration',
        new lang_string('recompletionrange', 'local_recompletion'),
        new lang_string('recompletionrange_help', 'local_recompletion'), YEARSECS, PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('local_recompletion/emailenable',
        new lang_string('recompletionemailenable', 'local_recompletion'),
        new lang_string('recompletionemailenable_help', 'local_recompletion'), 1));

    $settings->add(new admin_setting_configtext('local_recompletion/emailsubject',
        new lang_string('recompletionemailsubject', 'local_recompletion'),
        new lang_string('recompletionemailsubject_help', 'local_recompletion'), '', PARAM_TEXT));

    $settings->add(new admin_setting_confightmleditor('local_recompletion/emailbody',
        new lang_string('recompletionemailbody', 'local_recompletion'),
        new lang_string('recompletionemailbody_help', 'local_recompletion'), ''));

    $settings->add(new admin_setting_configcheckbox('local_recompletion/unenrolenable',
        new lang_string('recompletionunenrolenable', 'local_recompletion'),
        new lang_string('recompletionunenrolenable_help', 'local_recompletion'), 0));

    $settings->add(new admin_setting_configcheckbox('local_recompletion/deletegradedata',
        new lang_string('deletegradedata', 'local_recompletion'),
        new lang_string('deletegradedata_help', 'local_recompletion'), 1));

    $settings->add(new admin_setting_configcheckbox('local_recompletion/archivecompletiondata',
        new lang_string('archivecompletiondata', 'local_recompletion'),
        new lang_string('archivecompletiondata_help', 'local_recompletion'), 1));

    $settings->add(new admin_setting_configcheckbox('local_recompletion/forcearchivecompletiondata',
        new lang_string('forcearchivecompletiondata', 'local_recompletion'),
        new lang_string('forcearchivecompletiondata_help', 'local_recompletion'), 0));

    $settings->add(new admin_setting_heading('local_recompletion/pluginsettings',
        get_string('pluginssettings', 'local_recompletion'),
        ''
    ));

    $plugins = local_recompletion_get_supported_plugins();
    foreach ($plugins as $plugin) {
        $fqn = 'local_recompletion\\plugins\\' . $plugin;
        $fqn::settings($settings);
    }

    $settings->add(new admin_setting_heading('local_recompletion/restrictionsettings',
        get_string('restrictionsettings', 'local_recompletion'),
        ''
    ));

    $restrictions = local_recompletion_get_supported_restrictions();
    foreach ($restrictions as $plugin) {
        $fqn = 'local_recompletion\\local\\restrictions\\' . $plugin;
        $fqn::settings($settings);
    }

}
