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
 * Defines restore_local_recompletion class.
 *
 * @package     local_recompletion
 * @author      Dan Marsden
 * @copyright   2018 Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Restore plugin class that provides the necessary information
 * needed to restore recompletion data.
 */
class restore_local_recompletion_plugin extends restore_local_plugin {

    /**
     * Returns the paths to be handled by the plugin at course level.
     */
    protected function define_course_plugin_structure() {

        $paths = array();

        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element('recompletion', $elepath.'/recompletion');
        $paths[] = new restore_path_element('recompletion_cc', $elepath.'/course_completion/coursecompletion');
        $paths[] = new restore_path_element('recompletion_cc_cc', $elepath.'/course_completion/course_completion_crit_completions/course_completion_crit_compl');
        $paths[] = new restore_path_element('recompletion_completion', $elepath.'/course_completion/completions/completion');

        return $paths;
    }

    /**
     * Process local_recompletion table.
     */
    public function process_recompletion($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();

        $DB->insert_record('local_recompletion', $data);
    }

    /**
     * Process local_recompletion_cc table.
     */
    public function process_recompletion_cc($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recompletion_cc', $data);
    }

    /**
     * Process course_completion_crit_compl table.
     */
    public function process_recompletion_cc_cc($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->criteriaid = $this->get_mappingid('course_completion_criteria', $data->criteriaid);

        $DB->insert_record('local_recompletion_cc_cc', $data);
    }


    /**
     * Process local_recompletion_cmc table.
     */
    public function process_recompletion_completion($data) {
        global $DB;

        $data = (object) $data;
        $data->coursemoduleid = $this->get_mappingid('course_module', $data->coursemoduleid);
        $data->userid = $this->get_mappingid('user', $data->userid);
        if (empty($data->coursemoduleid)) {
            $this->log(
                'Could not match the module instance in local_recompletion_cmc, so skipping',
                backup::LOG_DEBUG
            );
            return;
        }

        $DB->insert_record('local_recompletion_cmc', $data);
    }
}