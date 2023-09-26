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

namespace local_recompletion;

/**
 * A cron based admin setting config
 *
 * @package    local_recompletion
 * @author     Kevin Pham <kevinpham@catalyst-au.net>
 * @copyright  Catalyst IT, 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_configcron extends \admin_setting {

    /**
     * Always returns true
     * @return bool Always returns true
     */
    public function get_setting() {
        $currentvalue = $this->config_read($this->name);
        if (!$currentvalue) {
            return null;
        }

        return self::string_to_setting($currentvalue);
    }

    // Steal the same validation used for scheduled tasks.
    public static function validate($data) {
        $error = [];

        // Use a checker class.
        $checker = new \tool_task\scheduled_checker_task();
        $checker->set_minute($data['minute']);
        $checker->set_hour($data['hour']);
        $checker->set_month($data['month']);
        $checker->set_day_of_week($data['dayofweek']);
        $checker->set_day($data['day']);
        $checker->set_disabled(false);
        $checker->set_customised(false);

        if (!$checker->is_valid($checker::FIELD_MINUTE)) {
            $error['minutegroup'] = get_string('invaliddata', 'core_error');
        }
        if (!$checker->is_valid($checker::FIELD_HOUR)) {
            $error['hourgroup'] = get_string('invaliddata', 'core_error');
        }
        if (!$checker->is_valid($checker::FIELD_DAY)) {
            $error['daygroup'] = get_string('invaliddata', 'core_error');
        }
        if (!$checker->is_valid($checker::FIELD_MONTH)) {
            $error['monthgroup'] = get_string('invaliddata', 'core_error');
        }
        if (!$checker->is_valid($checker::FIELD_DAYOFWEEK)) {
            $error['dayofweekgroup'] = get_string('invaliddata', 'core_error');
        }

        if (!empty($error)) {
            return $error;
        }

        return true;
    }

    public function write_setting($data) {
        // Validate.
        $validated = self::validate($data);
        if ($validated !== true) {
            $fields = implode(', ', array_keys($validated));
            $message = array_pop($validated);
            return "{$message} ({$fields})";
        }

        return ($this->config_write($this->name, $this->setting_to_string($data)) ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Convert the setting (array) to a string (cron formatted string)
     *
     * @param array $data
     */
    public static function setting_to_string(array $data): string {
        // Remove all whitespace from the values.
        $data = array_map(function ($v) {
            return str_replace(' ', '', $v);
        }, $data);

        // Store the values in a cron formatted string.
        return "{$data['minute']} {$data['hour']} {$data['day']} {$data['month']} {$data['dayofweek']}";
    }

    /**
     * Convert the setting from a given string
     *
     * @param $string
     */
    public static function string_to_setting($string) {
        return array_combine(['minute', 'hour', 'day', 'month', 'dayofweek'], explode(' ', $string));
    }

    public function output_html($data, $query = '') {
        $currentsettings = $this->get_setting();
        $data = $data === true ? [] : $data;

        $prefix = $this->get_full_name();
        // Use MoodleQuickForm to build the form.
        $mform = new \MoodleQuickForm('unused', 'unused', 'unused');

        self::add_cron_fields($mform, $data, $prefix);
        $html = $mform->toHtml();
        $default = get_string('yearly', 'local_recompletion');

        return \format_admin_setting(
            $this,
            $this->visiblename,
            \html_writer::div($html, 'w-100'),
            $this->description,
            true,
            '',
            $default,
            $query,
        );
    }

    public static function add_cron_fields($mform, $data, $prefix) {
        // Defaults to every year.
        $defaulttask = (object) self::string_to_setting('0 0 1 1 *');
        foreach ($data ?: $defaulttask as $field => $value) {
            $mform->setDefault($prefix . "[$field]", $value);
        }

        // Errors.
        $errors = self::validate((array) ($data ?: $defaulttask));
        if ($errors !== true) {
            foreach ($errors as $field => $value) {
                $mform->setElementError($field, $value);
            }
        }

        $mform->addGroup([
                $mform->createElement('text', $prefix . '[minute]'),
                $mform->createElement('static', 'minutedefault', '',
                        get_string('defaultx', 'tool_task', $defaulttask->minute)),
            ], 'minutegroup', get_string('taskscheduleminute', 'tool_task'), null, false);
        $mform->setType($prefix . '[minute]', PARAM_RAW);
        $mform->disabledIf($prefix . '[minute]', 'recompletiontype', 'neq', 'schedule');
        $mform->disabledIf($prefix . '[minute]', 'enable', 'notchecked');
        $mform->addHelpButton('minutegroup', 'taskscheduleminute', 'tool_task');

        $mform->addGroup([
                $mform->createElement('text', $prefix . '[hour]'),
                $mform->createElement('static', 'hourdefault', '',
                        get_string('defaultx', 'tool_task', $defaulttask->hour)),
        ], 'hourgroup', get_string('taskschedulehour', 'tool_task'), null, false);
        $mform->setType($prefix . '[hour]', PARAM_RAW);
        $mform->disabledIf($prefix . '[hour]', 'recompletiontype', 'neq', 'schedule');
        $mform->disabledIf($prefix . '[hour]', 'enable', 'notchecked');
        $mform->addHelpButton('hourgroup', 'taskschedulehour', 'tool_task');

        $mform->addGroup([
                $mform->createElement('text', $prefix . '[day]'),
                $mform->createElement('static', 'daydefault', '',
                        get_string('defaultx', 'tool_task', $defaulttask->day)),
        ], 'daygroup', get_string('taskscheduleday', 'tool_task'), null, false);
        $mform->setType($prefix . '[day]', PARAM_RAW);
        $mform->disabledIf($prefix . '[day]', 'recompletiontype', 'neq', 'schedule');
        $mform->disabledIf($prefix . '[day]', 'enable', 'notchecked');
        $mform->addHelpButton('daygroup', 'taskscheduleday', 'tool_task');

        $mform->addGroup([
                $mform->createElement('text', $prefix . '[month]'),
                $mform->createElement('static', 'monthdefault', '',
                        get_string('defaultx', 'tool_task', $defaulttask->month)),
        ], 'monthgroup', get_string('taskschedulemonth', 'tool_task'), null, false);
        $mform->setType($prefix . '[month]', PARAM_RAW);
        $mform->disabledIf($prefix . '[month]', 'recompletiontype', 'neq', 'schedule');
        $mform->disabledIf($prefix . '[month]', 'enable', 'notchecked');
        $mform->addHelpButton('monthgroup', 'taskschedulemonth', 'tool_task');

        $mform->addGroup([
                $mform->createElement('text', $prefix . '[dayofweek]'),
                $mform->createElement('static', 'dayofweekdefault', '',
                        get_string('defaultx', 'tool_task', $defaulttask->dayofweek)),
        ], 'dayofweekgroup', get_string('taskscheduledayofweek', 'tool_task'), null, false);
        $mform->setType($prefix . '[dayofweek]', PARAM_RAW);
        $mform->disabledIf($prefix . '[dayofweek]', 'recompletiontype', 'neq', 'schedule');
        $mform->disabledIf($prefix . '[dayofweek]', 'enable', 'notchecked');
        $mform->addHelpButton('dayofweekgroup', 'taskscheduledayofweek', 'tool_task');
    }
}
