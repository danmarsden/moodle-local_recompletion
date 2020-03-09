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
 * Interval form element
 *
 * Contains class to create length of time for element.
 *
 * @package local_recompletion
 * @author Duran CHEN <duran.chen@outlook.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/form/group.php');
require_once($CFG->libdir . '/form/duration.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/form/text.php');

class MoodleQuickForm_interval extends MoodleQuickForm_duration {

    /**
     * Returns time associative array of unit length.
     *
     * @return array unit length in seconds => string unit name.
     */
    public function get_units() {

        return [
                'Y' => get_string('years'),
                'M' => get_string('months'),
                'D' => get_string('days'),
                'W' => get_string('weeks')
        ];
    }

    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param string $event Name of event
     * @param mixed $arg event arguments
     * @param object $caller calling object
     * @return bool
     */
    function onQuickFormEvent($event, $arg, &$caller) {
        $this->setMoodleForm($caller);
        switch ($event) {
            case 'updateValue':
                // constant values override both default and submitted ones
                // default values are overriden by submitted
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    // if no boxes were checked, then there is no value in the array
                    // yet we don't want to display default value in this case
                    if ($caller->isSubmitted()) {
                        $value = $this->_findValue($caller->_submitValues);
                    } else {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if (!is_array($value)) {
                    list($number, $unit) = $this->intervalspec_to_unit($value);
                    $value = array('number' => $number, 'timeunit' => $unit);
                    // If optional, default to off, unless a date was provided
                    if ($this->_options['optional']) {
                        $value['enabled'] = $number != 0;
                    }
                } else {
                    $value['enabled'] = isset($value['enabled']);
                }
                if (null !== $value) {
                    $this->setValue($value);
                }
                break;

            case 'createElement':
                if ($arg[2]['optional']) {
                    $caller->disabledIf($arg[0], $arg[0] . '[enabled]');
                }
                $caller->setType($arg[0] . '[number]', PARAM_FLOAT);
                return parent::onQuickFormEvent($event, $arg, $caller);
                break;

            default:
                return parent::onQuickFormEvent($event, $arg, $caller);
        }
    }

    public function intervalspec_to_unit($intervalspec) {
        if (empty($intervalspec) == true) {
            return array(0, $this->_options['defaultunit']);
        }

        return array(substr($intervalspec, 1, -1), substr($intervalspec, -1));
    }

    /**
     * Output a timestamp. Give it the name of the group.
     * Override of standard quickforms method.
     *
     * @param  array $submitValues
     * @param  bool $notused Not used.
     * @return array field name => value. The value is the time interval in seconds.
     */
    function exportValue(&$submitValues, $notused = false) {
        // Get the values from all the child elements.
        $valuearray = array();
        foreach ($this->_elements as $element) {
            $thisexport = $element->exportValue($submitValues[$this->getName()], true);
            if (!is_null($thisexport)) {
                $valuearray += $thisexport;
            }
        }

        // Convert the value to an integer number of seconds.
        if (empty($valuearray)) {
            return null;
        }
        if ($this->_options['optional'] && empty($valuearray['enabled'])) {
            return array($this->getName() => 0);
        }

        return array($this->getName() => 'P' . $valuearray['number'] . $valuearray['timeunit']);
    }

}