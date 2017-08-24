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
 * Edit course completion settings - the form definition.
 *
 * @package     local_recompletion
 * @copyright   2017 Dan Marsden
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Defines the course completion settings form.
 *
 * @copyright   2017 Dan Marsden
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_recompletion_recompletion_form extends moodleform {

    /**
     * Defines the form fields.
     */
    public function definition() {

        $mform = $this->_form;
        $course = $this->_customdata['course'];

        $mform->addElement('checkbox', 'enable', get_string('enablerecompletion', 'local_recompletion'));

        $options = array('optional' => false, 'defaultunit' => 86400);
        $mform->addElement('duration', 'recompletionduration', get_string('recompletionrange', 'local_recompletion'), $options);
        $mform->disabledIf('recompletionduration', 'enable', 'notchecked');
        // Add common action buttons.
        $this->add_action_buttons();

        // Add hidden fields.
        $mform->addElement('hidden', 'course', $course->id);
        $mform->setType('course', PARAM_INT);

    }
}
