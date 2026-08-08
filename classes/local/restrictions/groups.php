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

use MoodleQuickForm;
use stdClass;

/**
 * Course group restriction class.
 *
 * @package    local_recompletion
 * @copyright Copyright Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class groups extends base {
    /**
     * Add params to form.
     *
     * @param MoodleQuickForm $mform
     */
    public static function editingform(MoodleQuickForm $mform): void {
        global $COURSE;

        $config = get_config('local_recompletion');
        $options = [
            'multiple' => true,
            'noselectionstring' => get_string('all'),
        ];

        $coursegroups = groups_get_all_groups($COURSE->id);
        $groupoptions = array_map(fn (stdClass $group): string => $group->name, $coursegroups);

        $mform->addElement(
            'autocomplete',
            'restrictgroups',
            get_string('restrictgroups', 'local_recompletion'),
            $groupoptions,
            $options
        );
        if (!empty($config->restrictgroups)) {
            $mform->setDefault('restrictgroups', $config->restrictgroups);
        }

        $mform->addHelpButton('restrictgroups', 'restrictgroups', 'local_recompletion');
    }

    /**
     * Set form data after submitting.
     * @param stdClass $data
     */
    public static function set_form_data(stdClass $data): void {
        if (isset($data->restrictgroups) && is_array($data->restrictgroups)) {
            $data->restrictgroups = implode(',', $data->restrictgroups);
        }
    }

    /**
     * Check if needs to reset completion for a given user.
     *
     * @param int $userid - user id.
     * @param stdClass $course - course record.
     * @param stdClass $config - recompletion config.
     */
    public static function should_reset(int $userid, stdClass $course, stdClass $config): bool {
        // Not restricted to any groups.
        if (empty($config->restrictgroups) || !is_string($config->restrictgroups)) {
            return true;
        }

        $allowedgroupids = explode(',', $config->restrictgroups);
        foreach ($allowedgroupids as $groupid) {
            if (groups_is_member((int) $groupid, $userid)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get restriction reason.
     * @return string
     */
    public static function get_restriction_reason(): string {
        return get_string('restrictedbygroup', 'local_recompletion');
    }
}
