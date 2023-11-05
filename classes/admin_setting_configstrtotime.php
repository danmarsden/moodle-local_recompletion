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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/adminlib.php');

/**
 * A strtotime based admin setting config
 *
 * @package    local_recompletion
 * @author     Kevin Pham <kevinpham@catalyst-au.net>
 * @copyright  Catalyst IT, 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_configstrtotime extends \admin_setting {

    /**
     * Return the structure configuration for this setting if it has been set.
     *
     * @return array|null
     */
    public function get_setting() {
        $currentvalue = $this->config_read($this->name);
        if (!$currentvalue) {
            return null;
        }

        return $currentvalue;
    }

    /**
     * Returns true if no errors were detected, otherwise the error message for the bad field.
     *
     * @param string $data
     * @return array|true
     */
    public function validate($data) {
        $value = local_recompletion_calculate_schedule_time($data);
        if ($value === 0) {
            return  get_string('invalidscheduledate', 'local_recompletion');
        }

        return true;
    }

    /**
     * Store new setting
     *
     * @param string $data string or array, must not be NULL
     * @return string empty string if ok, string error message otherwise
     */
    public function write_setting($data) {
        $validated = $this->validate($data);
        if ($validated !== true) {
            return $validated;
        }

        return ($this->config_write($this->name, $data) ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Return HTML for the form control
     *
     * @param mixed $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query='') {
        $prefix = $this->get_full_name();
        // Use MoodleQuickForm to build the form.
        $mform = new \MoodleQuickForm('unused', 'unused', 'unused');

        // Handle display of errors.
        $errors = self::validate($data ?: '');
        if ($errors !== true) {
            foreach ($errors as $field => $value) {
                $mform->setElementError($field, $value);
            }
        }

        $mform->addElement('text', $prefix, get_string('recompletionschedule', 'local_recompletion'), 'size = "80"');
        $mform->setType($prefix, PARAM_TEXT);
        $mform->addHelpButton($prefix, 'recompletionschedule', 'local_recompletion');
        $mform->disabledIf($prefix, 'recompletiontype', 'neq', 'schedule');
        $mform->setDefault($prefix, $data);

        if ($data) {
            $calculated = local_recompletion_calculate_schedule_time($data);
            $formatted = userdate($calculated, get_string('strftimedatetime', 'langconfig'));
            $mform->addElement('static', 'calculatedtime', get_string('recompletioncalculateddate', 'local_recompletion', $formatted));
        }

        $html = $mform->toHtml();

        return \format_admin_setting(
            $this,
            $this->visiblename,
            \html_writer::div($html, 'w-100'),
            $this->description,
            true,
            '',
            '',
            $query,
        );
    }
}
