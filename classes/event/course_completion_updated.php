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
 * course_completion_updated event.
 *
 * @package     local_recompletion
 * @author      Dan Marsden
 * @copyright   Dan Marsden
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\event;

/**
 * course_completion_updated event class.
 *
 * @property-read int $relateduserid user whose completion date was changed
 *
 * @package    local_recompletion
 * @author     Dan Marsden
 * @copyright  Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_completion_updated extends \core\event\base {
    /**
     * Init method.
     */
    protected function init(): void {
        $this->data['objecttable'] = 'course_completions';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventcoursecompletionupdated', 'local_recompletion');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description(): string {
        $timecompleted = $this->other['timecompleted'] ?? 0;

        return "The course completion date for user id '{$this->relateduserid}' in course id " .
            "'{$this->courseid}' was updated to '{$timecompleted}'.";
    }
}
