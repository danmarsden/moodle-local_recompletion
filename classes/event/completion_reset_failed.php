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
 * completion_reset_failed event.
 *
 * @package     local_recompletion
 * @author      Dan Marsden
 * @copyright   Dan Marsden
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\event;

/**
 * completion_reset_failed event class.
 *
 * @property-read int $relateduserid user whose completion reset failed
 *
 * @package    local_recompletion
 * @author     Dan Marsden
 * @copyright  Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion_reset_failed extends \core\event\base {
    /**
     * Init method.
     */
    protected function init(): void {
        $this->data['objecttable'] = 'local_recompletion_config';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventrecompletionfailed', 'local_recompletion');
    }

    /**
     * Returns non-localised event description with ids for admin use only.
     *
     * @return string
     */
    public function get_description(): string {
        $errors = $this->other['errors'] ?? '';

        return "Completion reset failed for user id '{$this->relateduserid}' in course id '{$this->courseid}'. Errors: '{$errors}'";
    }
}
