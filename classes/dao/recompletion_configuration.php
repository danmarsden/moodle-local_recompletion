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
 * recompletion.
 *
 * @package     local_recompletion
 * @author      Glenn Poder <glennpoder@catalyst-au.net>
 * @copyright   2023 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\dao;

/**
 * Class recompletion
 *
 * @package local_recompletion
 */
class recompletion_configuration {

    /**
     * Constructor.
     *
     * @param int $courseid
     * @throws \dml_exception
     */
    public function __construct($courseid) {
        global $DB;
        $config = $DB->get_records_list('local_recompletion_config', 'course', array($courseid), '', 'name, id, value');
        foreach ($config as $name => $item) {
            $this->$name = $item;
        }

    }

    /**
     * Return the data that will be used upon saving.
     *
     * @return array|false
     */
    public function get_data() {
        $data = get_object_vars($this);
        $keys = array_column($data, 'name');
        $values = array_column($data, 'value');
        return array_combine($keys, $values);
    }

    /**
     * Returns expected db field names.
     *
     * @return string[]
     * @throws \coding_exception
     */
    public function setnames() {
        $setnames = array('enable', 'recompletionduration', 'deletegradedata', 'archivecompletiondata',
            'recompletionemailenable', 'recompletionemailsubject', 'recompletionemailbody_text', 'recompletionemailbody_format',
            'assignevent');

        $activities = local_recompletion_get_supported_activities();
        foreach ($activities as $activity) {
            $setnames[] = $activity;
            $setnames[] = 'archive'.$activity;
        }

        return $setnames;
    }

    /**
     * Loads form data.
     *
     * @param string[] $mformdata
     * @return array
     */
    public function set_form_data($mformdata) {
        $data = (array)$mformdata;
        if (key_exists('recompletionemailbody', $data)) {
            $recompletionemailbody = $data['recompletionemailbody'];
            $data['recompletionemailbody_text'] = $recompletionemailbody['text'];
            $data['recompletionemailbody_format'] = $recompletionemailbody['format'];
            unset($data['recompletionemailbody']);
        }
        return $data;
    }

    /**
     * Returns the data for loading into mform.
     *
     * @return array
     */
    public function prepare_form_data() {
        $data = $this->get_data();
        $emailbody = array('text' => $data['recompletionemailbody_text'], 'format' => $data['recompletionemailbody_format']);
        $data['recompletionemailbody'] = $emailbody;
        return $data;
    }

    /**
     * Checks if data is loaded.
     *
     * @return bool
     */
    public function is_empty() {
        return empty((array)$this);
    }
}
