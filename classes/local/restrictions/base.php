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
use MoodleQuickForm;
use stdClass;

/**
 * Base restriction class.
 *
 * @package    local_recompletion
 * @author     Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base {

    /**
     * Check if needs to reset completion for a given user.
     *
     * @param int $userid - user id.
     * @param stdClass $course - course record.
     * @param stdClass $config - recompletion config.
     */
    abstract public static function should_reset(int $userid, stdClass $course, stdClass $config): bool;

    /**
     * Add params to form.
     *
     * @param MoodleQuickForm $mform
     */
    public static function editingform(MoodleQuickForm $mform): void {
    }

    /**
     * Set form data after submitting.
     *
     * @param stdClass $data
     */
    public static function set_form_data(stdClass $data): void {
    }

    /**
     * Add site level settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings(admin_settingpage $settings): void {
    }

    /**
     * Get restriction reason.
     * @return string
     */
    public static function get_restriction_reason(): string {
        return get_string('restricted', 'local_recompletion');
    }
}
