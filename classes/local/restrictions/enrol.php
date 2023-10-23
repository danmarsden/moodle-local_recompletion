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

namespace local_recompletion\local\restrictions;

use admin_settingpage;
use core_component;
use MoodleQuickForm;
use stdClass;

/**
 * Enrolment method restriction class.
 *
 * @package    local_recompletion
 * @author     Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol extends base {

    /**
     * Add params to form.
     *
     * @param MoodleQuickForm $mform
     */
    public static function editingform(MoodleQuickForm $mform): void {
        $config = get_config('local_recompletion');
        $options = [
            'multiple' => true,
            'noselectionstring' => get_string('all'),
        ];

        $plugins = array_keys(core_component::get_plugin_list('enrol'));
        $enrolplugins = array_map(
            fn (string $plugin): string => get_string('pluginname', 'enrol_' . $plugin),
            array_combine($plugins, $plugins)
        );

        $mform->addElement(
            'autocomplete',
            'restrictenrol',
            get_string('restrictenrol', 'local_recompletion'),
            $enrolplugins,
            $options
        );

        $mform->setDefault('restrictenrol', $config->restrictenrol);
        $mform->addHelpButton('restrictenrol', 'restrictenrol', 'local_recompletion');
    }

    /**
     * Set form data after submitting.
     * @param stdClass $data
     */
    public static function set_form_data(stdClass $data): void {
        if (isset($data->restrictenrol) && is_array($data->restrictenrol)) {
            $data->restrictenrol = implode(',', $data->restrictenrol);
        }
    }

    /**
     * Add site level settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings(admin_settingpage $settings): void {
        $enrolplugins = array_keys(core_component::get_plugin_list('enrol'));
        $options = array_map(
            fn (string $plugin): string => get_string('pluginname', 'enrol_' . $plugin),
            array_combine($enrolplugins, $enrolplugins)
        );

        $settings->add(new \admin_setting_configmulticheckbox(
            'local_recompletion/restrictenrol',
            get_string('restrictenrol', 'local_recompletion'),
            get_string('restrictenrol_help', 'local_recompletion'),
            [],
            $options
        ));
    }

    /**
     * Check if needs to reset completion for a given user.
     *
     * @param int $userid - user id.
     * @param stdClass $course - course record.
     * @param stdClass $config - recompletion config.
     */
    public static function should_reset(int $userid, stdClass $course, stdClass $config): bool {
        global $DB;

        // Not restricted to any enrol methods.
        if (empty($config->restrictenrol) || !is_string($config->restrictenrol)) {
            return true;
        }

        $allowedenrols = explode(',', $config->restrictenrol);
        $courseinstances = enrol_get_instances($course->id, false);

        $courseallowedinstances = array_filter($courseinstances, function ($courseinstance) use ($allowedenrols){
            return in_array($courseinstance->enrol, $allowedenrols);
        });

        // Course doesn't have allowed enrolment methods.
        if (empty($courseallowedinstances)) {
            return false;
        }

        // Check if a user is enrolled using one of the allowed instances.
        list($sql, $params) = $DB->get_in_or_equal(array_keys($courseallowedinstances), SQL_PARAMS_NAMED);
        $params['userid'] = $userid;
        $userenrolments = $DB->get_records_select('user_enrolments', "enrolid $sql AND userid = :userid", $params);

        return !empty($userenrolments);
    }

    /**
     * Get restriction reason.
     * @return string
     */
    public static function get_restriction_reason(): string {
        return get_string('restrictedbyenrol', 'local_recompletion');
    }
}
