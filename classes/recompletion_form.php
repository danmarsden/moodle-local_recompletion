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
        $mform->addHelpButton('enable', 'enablerecompletion', 'local_recompletion');

        $options = array('optional' => false, 'defaultunit' => 86400);
        $mform->addElement('duration', 'recompletionduration', get_string('recompletionrange', 'local_recompletion'), $options);
        $mform->addHelpButton('recompletionduration', 'recompletionrange', 'local_recompletion');
        $mform->disabledIf('recompletionduration', 'enable', 'notchecked');
        $mform->addElement('checkbox', 'archivecompletiondata', get_string('archivecompletiondata', 'local_recompletion'));
        $mform->addHelpButton('archivecompletiondata', 'archivecompletiondata', 'local_recompletion');
        $mform->disabledIf('archivecompletiondata', 'enable', 'notchecked');

        // Grade data deletion and archiving settings.
        $mform->addElement('header', 'gradeheader', get_string('graderecompletiontitle', 'local_recompletion'));
        $mform->setExpanded('gradeheader');
        $mform->addElement('checkbox', 'deletegradedata', get_string('deletegradedata', 'local_recompletion'));
        $mform->addHelpButton('deletegradedata', 'deletegradedata', 'local_recompletion');
        $mform->disabledIf('deletegradedata', 'enable', 'notchecked');
        $mform->addElement('checkbox', 'archivegradedata', get_string('archivegradedata', 'local_recompletion'));
        $mform->addHelpButton('archivegradedata', 'archivegradedata', 'local_recompletion');
        $mform->disabledIf('archivegradedata', 'enable', 'notchecked');
        $mform->disabledIf('archivegradedata', 'deletegradedata', 'notchecked');

        // Quiz data deletion and archiving settings.
        $mform->addElement('header', 'quizheader', get_string('quizrecompletiontitle', 'local_recompletion'));
        $mform->setExpanded('quizheader');
        $mform->addElement('checkbox', 'deletequizdata', get_string('deletequizdata', 'local_recompletion'));
        $mform->addHelpButton('deletequizdata', 'deletequizdata', 'local_recompletion');
        $mform->disabledIf('deletequizdata', 'enable', 'notchecked');
        $mform->addElement('checkbox', 'archivequizdata', get_string('archivequizdata', 'local_recompletion'));
        $mform->addHelpButton('archivequizdata', 'archivequizdata', 'local_recompletion');
        $mform->disabledIf('archivequizdata', 'enable', 'notchecked');
        $mform->disabledIf('archivequizdata', 'deletequizdata', 'notchecked');

        // Quiz data deletion and archiving settings.
        $mform->addElement('header', 'scormheader', get_string('scormrecompletiontitle', 'local_recompletion'));
        $mform->setExpanded('scormheader');
        $mform->addElement('checkbox', 'deletescormdata', get_string('deletescormdata', 'local_recompletion'));
        $mform->addHelpButton('deletescormdata', 'deletescormdata', 'local_recompletion');
        $mform->disabledIf('deletescormdata', 'enable', 'notchecked');
        $mform->addElement('checkbox', 'archivescormdata', get_string('archivescormdata', 'local_recompletion'));
        $mform->addHelpButton('archivescormdata', 'archivescormdata', 'local_recompletion');
        $mform->disabledIf('archivescormdata', 'enable', 'notchecked');
        $mform->disabledIf('archivescormdata', 'deletescormdata', 'notchecked');

        // Email Notification settings.
        $mform->addElement('header', 'emailheader', get_string('emailrecompletiontitle', 'local_recompletion'));
        $mform->setExpanded('emailheader');
        $mform->addElement('checkbox', 'recompletionemailenable', get_string('recompletionemailenable', 'local_recompletion'));
        $mform->addHelpButton('recompletionemailenable', 'recompletionemailenable', 'local_recompletion');
        $mform->disabledIf('recompletionemailenable', 'enable', 'notchecked');
        $mform->addElement('text', 'recompletionemailheader', get_string('recompletionemailheader', 'local_recompletion'), 'size = "80"');
        $mform->setType('recompletionemailheader', PARAM_RAW);
        $mform->addHelpButton('recompletionemailheader', 'recompletionemailheader', 'local_recompletion');
        $mform->disabledIf('recompletionemailheader', 'enable', 'notchecked');
        $mform->disabledIf('recompletionemailheader', 'recompletionemailenable', 'notchecked');
        $options = array('cols' => '60', 'rows' => '8');
        $mform->addElement('textarea', 'recompletionemailbody', get_string('recompletionemailbody', 'local_recompletion'), $options);
        $mform->addHelpButton('recompletionemailbody', 'recompletionemailbody', 'local_recompletion');
        $mform->disabledIf('recompletionemailbody', 'enable', 'notchecked');
        $mform->disabledIf('recompletionemailbody', 'recompletionemailenable', 'notchecked');

        // Add common action buttons.
        $this->add_action_buttons();

        // Add hidden fields.
        $mform->addElement('hidden', 'course', $course->id);
        $mform->setType('course', PARAM_INT);
    }
}
