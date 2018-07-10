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
 * Defines backup_local_recompletion class.
 *
 * @package     local_recompletion
 * @author      Dan Marsden
 * @copyright   2018 Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the steps to perform one complete backup of the recompletion plugin.
 */
class backup_local_recompletion_plugin extends backup_local_plugin {

    /**
     * Returns the format information to attach to course element.
     */
    protected function define_course_plugin_structure() {

        // Are we including usercompletion info in this backup.
        $usercompletion = $this->get_setting_value('userscompletion');

        $plugin = $this->get_plugin_element();
        $recompletion = new backup_nested_element($this->get_recommended_name());

        $recompletiondata = new backup_nested_element('recompletion', null, array(
            'enable', 'recompletionduration', 'deletegradedata', 'deletequizdata', 'deletescormdata', 'archivecompletiondata',
            'archivequizdata', 'archivescormdata', 'recompletionemailenable', 'recompletionemailsubject', 'recompletionemailbody'));

        // Handle Historical course completions.
        $cc = new backup_nested_element('course_completion');

        $coursecompletions = new backup_nested_element('coursecompletion', array('id'), array(
            'userid', 'course', 'timeenrolled', 'timestarted', 'timecompleted', 'reaggregate'
        ));

        // Now Handle historical course_completion_crit_compl table.
        $criteriacompletions = new backup_nested_element('course_completion_crit_completions');

        $criteriacomplete = new backup_nested_element('course_completion_crit_compl', array('id'), array(
            'criteriaid', 'userid', 'gradefinal', 'unenrolled', 'timecompleted'
        ));

        $completions = new backup_nested_element('completions');

        $completion = new backup_nested_element('completion', array('id'), array(
            'userid', 'completionstate', 'viewed', 'timemodified', 'coursemoduleid'));

        $plugin->add_child($recompletion);
        $recompletion->add_child($recompletiondata);
        $recompletion->add_child($cc);
        $cc->add_child($coursecompletions);
        $cc->add_child($criteriacompletions);
        $criteriacompletions->add_child($criteriacomplete);
        $cc->add_child($completions);
        $completions->add_child($completion);

        // Set source to populate the data.
        $recompletiondata->set_source_table('local_recompletion', array(
            'course' => backup::VAR_PARENTID));

        // Only include the archive info if usercompletion is also being saved to backup.
        if ($usercompletion) {
            $coursecompletions->set_source_table('local_recompletion_cc', array('course' => backup::VAR_COURSEID));
            $criteriacomplete->set_source_table('local_recompletion_cc_cc', array('course' => backup::VAR_COURSEID));
            $sql = 'SELECT * 
                      FROM {local_recompletion_cmc}
                     WHERE coursemoduleid IN (SELECT id FROM {course_modules} WHERE course = ?)';
            $completion->set_source_sql($sql, array(backup::VAR_COURSEID));
        }
        $coursecompletions->annotate_ids('user', 'userid');
        $criteriacomplete->annotate_ids('user', 'userid');
        $criteriacomplete->annotate_ids('course_completion_criteria', 'criteriaid');
        $completion->annotate_ids('user', 'userid');
        $completion->annotate_ids('course_module', 'coursemoduleid');

        return $plugin;
    }

}
