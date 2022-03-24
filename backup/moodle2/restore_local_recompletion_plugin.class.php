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

/**
 * Restore plugin class.
 *
 * @package    local_recompletion
 * @author     Dan Marsden http://danmarsden.com
 * @copyright  2018 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_local_recompletion_plugin extends restore_local_plugin {
    /**
     * Returns the paths to be handled by the plugin at course level.
     */
    protected function define_course_plugin_structure() {
        $paths = array();

        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element('recompletion', $elepath.'/recompletion_config');
        $paths[] = new restore_path_element('recompletion_cc', $elepath.'/course_completion/coursecompletion');
        $paths[] = new restore_path_element('recompletion_cc_cc',
            $elepath.'/course_completion/course_completion_crit_completions/course_completion_crit_compl');
        $paths[] = new restore_path_element('recompletion_completion', $elepath.'/course_completion/completions/completion');
        $paths[] = new restore_path_element('recompletion_qa', $elepath.'/quizattempts/attempt');
        $paths[] = new restore_path_element('recompletion_qg', $elepath.'/quizgrades/grade');
        $paths[] = new restore_path_element('recompletion_sst', $elepath.'/scormtracks/sco_track');

        return $paths;
    }

    /**
     * Process local_recompletion table.
     * @param stdClass $data
     */
    public function process_recompletion($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();

        $DB->insert_record('local_recompletion_config', $data);
    }

    /**
     * Process local_recompletion_cc table.
     * @param stdClass $data
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
     * @param stdClass $data
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
     * @param stdClass $data
     */
    public function process_recompletion_completion($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recompletion_cmc', $data);
    }

    /**
     * Process local_recompletion_qa table.
     * @param stdClass $data
     */
    public function process_recompletion_qa($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recompletion_qa', $data);
    }

    /**
     * Process local_recompletion_qg table.
     * @param stdClass $data
     */
    public function process_recompletion_qg($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recompletion_qg', $data);
    }

    /**
     * Process local_recompletion_sst table.
     * @param stdClass $data
     */
    public function process_recompletion_sst($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recompletion_sst', $data);
    }

    /**
     * We call the after restore_course to update the coursemodule ids we didn't know when creating.
     */
    protected function after_restore_course() {
        global $DB;
        // Fix local_recompletion_cmc records.
        $rcm = $DB->get_recordset('local_recompletion_cmc', array('course' => $this->task->get_courseid()));
        foreach ($rcm as $rc) {
            $rc->coursemoduleid = $this->get_mappingid('course_module', $rc->coursemoduleid);
            $DB->update_record('local_recompletion_cmc', $rc);
        }
        $rcm->close();

        // Fix SCORM tracks.
        $rcm = $DB->get_recordset('local_recompletion_sst', array('course' => $this->task->get_courseid()));
        foreach ($rcm as $rc) {
            $rc->scormid = $this->get_mappingid('scorm', $rc->scormid);
            $rc->scoid = $this->get_mappingid('scorm_sco', $rc->scoid);
            $DB->update_record('local_recompletion_sst', $rc);
        }
        $rcm->close();

        // Fix Quiz.
        $rcm = $DB->get_recordset('local_recompletion_qg', array('course' => $this->task->get_courseid()));
        foreach ($rcm as $rc) {
            $rc->quiz = $this->get_mappingid('quiz', $rc->quiz);
            $DB->update_record('local_recompletion_qg', $rc);
        }
        $rcm->close();

        $rcm = $DB->get_recordset('local_recompletion_qa', array('course' => $this->task->get_courseid()));
        foreach ($rcm as $rc) {
            $rc->quiz = $this->get_mappingid('quiz', $rc->quiz);
            $rc->uniqueid = $this->get_mappingid('question_usage', $rc->uniqueid);
            $DB->update_record('local_recompletion_qa', $rc);
        }
        $rcm->close();
    }
}
