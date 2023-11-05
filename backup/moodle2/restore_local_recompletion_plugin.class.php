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
        $paths[] = new restore_path_element('recompletion_cha', $elepath.'/choiceanswers/choiceanswer');
        $paths[] = new restore_path_element('recompletion_hvp', $elepath.'/hvpattempts/hvpattempt');
        $paths[] = new restore_path_element('recompletion_h5p', $elepath.'/h5ps/h5p');
        $paths[] = new restore_path_element('recompletion_h5pr', $elepath.'/h5ps/h5p/h5presults/h5presult');
        $paths[] = new restore_path_element('recompletion_lessonattempt', $elepath.'/lessonattempts/lessonattempt');
        $paths[] = new restore_path_element('recompletion_lessongrade', $elepath.'/lessongrades/lessongrade');
        $paths[] = new restore_path_element('recompletion_lessontimer', $elepath.'/lessontimers/lessontimer');
        $paths[] = new restore_path_element('recompletion_lessonbrach', $elepath.'/lessonbraches/lessonbrach');
        $paths[] = new restore_path_element('recompletion_lessonoverride', $elepath.'/lessonoverrides/lessonoverride');
        $paths[] = new restore_path_element('recompletion_hpa', $elepath.'/hotpotattempts/hotpotattempt');

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
     * Process local_recompletion_cha table.
     * @param stdClass $data
     */
    public function process_recompletion_cha($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recompletion_cha', $data);
    }

    /**
     * Process local_recompletion_hvp table.
     * @param stdClass $data
     */
    public function process_recompletion_hvp($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->user_id = $this->get_mappingid('user', $data->user_id);

        $DB->insert_record('local_recompletion_hvp', $data);
    }

    /**
     * Process local_recompletion_h5p table.
     * @param stdClass $data
     */
    public function process_recompletion_h5p($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('local_recompletion_h5p', $data);
        $this->set_mapping('recompletion_h5p', $oldid, $newitemid);
    }

    /**
     * Process local_recompletion_h5pr table.
     * @param stdClass $data
     */
    public function process_recompletion_h5pr($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->attemptid = $this->get_new_parentid('recompletion_h5p');
        $DB->insert_record('local_recompletion_h5pr', $data);
    }

    /**
     * Process local_recompletion_lessonattempt.
     * @param stdClass $data
     */
    public function process_recompletion_lessonattempt($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('local_recompletion_la', $data);
        $this->set_mapping('recompletion_lessonattempt', $oldid, $newitemid);
    }

    /**
     * Process local_recompletion_lessongrade.
     * @param stdClass $data
     */
    public function process_recompletion_lessongrade($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('local_recompletion_lg', $data);
        $this->set_mapping('recompletion_lessongrade', $oldid, $newitemid);
    }

    /**
     * Process local_recompletion_lessontimer.
     * @param stdClass $data
     */
    public function process_recompletion_lessontimer($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('local_recompletion_lt', $data);
        $this->set_mapping('recompletion_lessontimer', $oldid, $newitemid);
    }

    /**
     * Process local_recompletion_lessonbrach.
     * @param stdClass $data
     */
    public function process_recompletion_lessonbrach($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('local_recompletion_lb', $data);
        $this->set_mapping('recompletion_lessonbrach', $oldid, $newitemid);
    }

    /**
     * Process local_recompletion_lessonoverride.
     * @param stdClass $data
     */
    public function process_recompletion_lessonoverride($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('local_recompletion_lo', $data);
        $this->set_mapping('recompletion_lessonoverride', $oldid, $newitemid);
    }

    /**
     * Process local_recompletion_hpa table.
     * @param stdClass $data
     */
    public function process_recompletion_hpa($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_recompletion_hpa', $data);
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

        // Fix Choice answers.
        $rcm = $DB->get_recordset('local_recompletion_cha', array('course' => $this->task->get_courseid()));
        foreach ($rcm as $rc) {
            $rc->choiceid = $this->get_mappingid('choice', $rc->choiceid);
            $DB->update_record('local_recompletion_cha', $rc);
        }
        $rcm->close();

        // Fix hvp attempts.
        $rcm = $DB->get_recordset('local_recompletion_hvp', array('course' => $this->task->get_courseid()));
        foreach ($rcm as $rc) {
            $rc->hvp_id = $this->get_mappingid('hvp', $rc->hvp_id);
            $DB->update_record('local_recompletion_hvp', $rc);
        }
        $rcm->close();

        // Fix h5p attempts.
        $rcm = $DB->get_recordset('local_recompletion_h5p', array('course' => $this->task->get_courseid()));
        foreach ($rcm as $rc) {
            $rc->h5pactivityid = $this->get_mappingid('h5pactivity', $rc->h5pactivityid);
            $rc->originalattemptid = 0; // Don't restore orginal attempt id.

            $DB->update_record('local_recompletion_h5p', $rc);
        }
        $rcm->close();

        // Fix lesson tables.
        $tables = array('local_recompletion_la', 'local_recompletion_lg', 'local_recompletion_lt',
            'local_recompletion_lb', 'local_recompletion_lo');

        foreach ($tables as $table) {
            $rcm = $DB->get_recordset($table, array('course' => $this->task->get_courseid()));
            foreach ($rcm as $rc) {
                $rc->lessonid = $this->get_mappingid('lesson', $rc->lessonid);
                $DB->update_record($table, $rc);
            }
            $rcm->close();
        }

        // Fix hotpot attempts.
        $rcm = $DB->get_recordset('local_recompletion_hpa', array('course' => $this->task->get_courseid()));
        foreach ($rcm as $rc) {
            $rc->hotpotid = $this->get_mappingid('hotpot', $rc->hotpotid);
            $DB->update_record('local_recompletion_hpa', $rc);
        }
        $rcm->close();
    }
}
