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
        $config = get_config('local_recompletion');

        $mform->addElement('checkbox', 'enable', get_string('enablerecompletion', 'local_recompletion'));
        $mform->addHelpButton('enable', 'enablerecompletion', 'local_recompletion');

        $options = array('optional' => false, 'defaultunit' => 86400);
        $mform->addElement('duration', 'recompletionduration', get_string('recompletionrange', 'local_recompletion'), $options);
        $mform->addHelpButton('recompletionduration', 'recompletionrange', 'local_recompletion');
        $mform->disabledIf('recompletionduration', 'enable', 'notchecked');
        $mform->setDefault('recompletionduration', $config->duration);

        $mform->addElement('checkbox', 'recompletionemailenable', get_string('recompletionemailenable', 'local_recompletion'));
        $mform->setDefault('recompletionemailenable', $config->emailenable);
        $mform->addHelpButton('recompletionemailenable', 'recompletionemailenable', 'local_recompletion');
        $mform->disabledIf('recompletionemailenable', 'enable', 'notchecked');

        // Email Notification settings.
        $mform->addElement('header', 'emailheader', get_string('emailrecompletiontitle', 'local_recompletion'));
        $mform->setExpanded('emailheader', false);
        $mform->addElement('text', 'recompletionemailsubject', get_string('recompletionemailsubject', 'local_recompletion'),
                'size = "80"');
        $mform->setType('recompletionemailsubject', PARAM_TEXT);
        $mform->addHelpButton('recompletionemailsubject', 'recompletionemailsubject', 'local_recompletion');
        $mform->disabledIf('recompletionemailsubject', 'enable', 'notchecked');
        $mform->disabledIf('recompletionemailsubject', 'recompletionemailenable', 'notchecked');
        $mform->setDefault('recompletionemailsubject', $config->emailsubject);

        $options = array('cols' => '60', 'rows' => '8');
        $mform->addElement('textarea', 'recompletionemailbody', get_string('recompletionemailbody', 'local_recompletion'),
                $options);
        $mform->addHelpButton('recompletionemailbody', 'recompletionemailbody', 'local_recompletion');
        $mform->disabledIf('recompletionemailbody', 'enable', 'notchecked');
        $mform->disabledIf('recompletionemailbody', 'recompletionemailenable', 'notchecked');
        $mform->setDefault('recompletionemailbody', $config->emailbody);

        // Advanced recompletion settings.
        // Delete data section.
        $mform->addElement('header', 'advancedheader', get_string('advancedrecompletiontitle', 'local_recompletion'));
        $mform->setExpanded('advancedheader', false);

        $mform->addElement('checkbox', 'deletegradedata', get_string('deletegradedata', 'local_recompletion'));
        $mform->setDefault('deletegradedata', $config->deletegradedata);
        $mform->addHelpButton('deletegradedata', 'deletegradedata', 'local_recompletion');

        $mform->addElement('checkbox', 'archivecompletiondata', get_string('archivecompletiondata', 'local_recompletion'));
        $mform->setDefault('archivecompletiondata', $config->archivecompletiondata);
        $mform->addHelpButton('archivecompletiondata', 'archivecompletiondata', 'local_recompletion');

        $cba = array();
        $cba[] = $mform->createElement('radio', 'scormdata', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'scormdata', '',
            get_string('delete', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'scorm', get_string('scormattempts', 'local_recompletion'), array(' '), false);
        $mform->addHelpButton('scorm', 'scormattempts', 'local_recompletion');
        $mform->setDefault('scormdata', $config->scormattempts);

        $mform->addElement('checkbox', 'archivescormdata',
            get_string('archive', 'local_recompletion'));
        $mform->setDefault('archivescormdata', $config->archivescormdata);

        $cba = array();
        $cba[] = $mform->createElement('radio', 'quizdata', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'quizdata', '',
            get_string('delete', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);
        $cba[] = $mform->createElement('radio', 'quizdata', '',
            get_string('extraattempt', 'local_recompletion'), LOCAL_RECOMPLETION_EXTRAATTEMPT);

        $mform->addGroup($cba, 'quiz', get_string('quizattempts', 'local_recompletion'), array(' '), false);
        $mform->addHelpButton('quiz', 'quizattempts', 'local_recompletion');
        $mform->setDefault('quizdata', $config->quizattempts);

        $mform->addElement('checkbox', 'archivequizdata',
            get_string('archive', 'local_recompletion'));
        $mform->setDefault('archivequizdata', $config->archivequizdata);

        $cba = array();
        $cba[] = $mform->createElement('radio', 'assigndata', '',
            get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'assigndata', '',
            get_string('extraattempt', 'local_recompletion'), LOCAL_RECOMPLETION_EXTRAATTEMPT);
        $cba[] = $mform->createElement('checkbox', 'assignevent', '', get_string('assignevent', 'local_recompletion'));
        $mform->addGroup($cba, 'assign', get_string('assignattempts', 'local_recompletion'), array(' '), false);
        $mform->addHelpButton('assign', 'assignattempts', 'local_recompletion');

        $mform->setDefault('assigndata', $config->assignattempts);
        $mform->setDefault('assignevent', $config->assignevent);

        $mform->disabledIf('scormdata', 'enable', 'notchecked');
        $mform->disabledIf('deletegradedata', 'enable', 'notchecked');
        $mform->disabledIf('quizdata', 'enable', 'notchecked');
        $mform->disabledIf('archivecompletiondata', 'enable', 'notchecked');
        $mform->disabledIf('archivequizdata', 'enable', 'notchecked');
        $mform->disabledIf('archivescormdata', 'enable', 'notchecked');
        $mform->disabledIf('assigndata', 'enable', 'notchecked');
        $mform->hideIf('archivequizdata', 'quizdata', 'noteq', LOCAL_RECOMPLETION_DELETE);
        $mform->hideIf('archivescormdata', 'scormdata', 'notchecked');

        // Add common action buttons.
        $this->add_action_buttons();

        // Add hidden fields.
        $mform->addElement('hidden', 'course', $course->id);
        $mform->setType('course', PARAM_INT);
    }
}
