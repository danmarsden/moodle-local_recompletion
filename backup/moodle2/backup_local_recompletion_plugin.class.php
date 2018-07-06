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

        $plugin = $this->get_plugin_element();

        $pluginwrapper = new backup_nested_element($this->get_recommended_name(), null, array(
            'enable', 'recompletionduration', 'deletegradedata', 'deletequizdata', 'deletescormdata', 'archivecompletiondata',
            'archivequizdata', 'archivescormdata', 'recompletionemailenable', 'recompletionemailsubject', 'recompletionemailbody'));

        $plugin->add_child($pluginwrapper);

        // Set source to populate the data.
        $pluginwrapper->set_source_table('local_recompletion', array(
            'course' => backup::VAR_PARENTID));

        // Handle Historical course completions.
        $coursecompletions = new backup_nested_element('local_recompletion_cc', array('id'), array(
            'userid', 'course', 'timeenrolled', 'timestarted', 'timecompleted', 'reaggregate'
        ));

        $plugin->add_child($coursecompletions);

        // Only include the archive info if usercompletion is also being saved to backup.
        $usercompletion = $this->get_setting_value('userscompletion');
        if ($usercompletion) {
            $coursecompletions->set_source_table('local_recompletion_cc', array('course' => backup::VAR_COURSEID));
        }
        $coursecompletions->annotate_ids('user', 'userid');

        return $plugin;
    }

}
