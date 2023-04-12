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
 * Local functions and constants for recompletion plugin.
 *
 * @package    local_recompletion
 * @copyright  2018 Catalyst IT
 * @author     Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Used by settings to decide if attempts should be deleted or an extra attempt allowed.
define('LOCAL_RECOMPLETION_NOTHING', 0);
define('LOCAL_RECOMPLETION_DELETE', 1);
define('LOCAL_RECOMPLETION_EXTRAATTEMPT', 2);

/**
 * Get list of supported activity classes.
 * @return array
 * @throws coding_exception
 */
function local_recompletion_get_supported_activities() {
    global $CFG;
    $activites = [];
    $files = scandir($CFG->dirroot. '/local/recompletion/classes/activities');
    foreach ($files as $file) {
        $activity = clean_param(str_replace('.php', '', $file), PARAM_ALPHA);
        if (!empty($activity) && file_exists($CFG->dirroot.'/mod/'.$activity)) {
            $activities[] = $activity;
        }

    }
    return $activities;
}

/**
 * Loads form data.
 *
 * @param string[] $mformdata
 * @return object
 */
function local_recompletion_set_form_data($mformdata) {
    $data = (array)$mformdata;
    if (key_exists('recompletionemailbody', $data)) {
        $recompletionemailbody = $data['recompletionemailbody'];
        $data['recompletionemailbody_format'] = $recompletionemailbody['format'];
        $data['recompletionemailbody'] = $recompletionemailbody['text'];
    }
    return (object)$data;
}

/**
 * Return the data that will be used upon saving.
 * @param string[] $data
 * @return array|false
 */
function local_recompletion_get_data(array $data) {
    $keys = array_column($data, 'name');
    $values = array_column($data, 'value');
    $result = array_combine($keys, $values);
    // Set default format for email body editor.
    if (isset($result['recompletionemailbody']) && !isset($result['recompletionemailbody_format'])) {
        $result['recompletionemailbody_format'] = FORMAT_HTML;
    }
    // Prepare email body for editor.
    $emailbody = array('text' => $result['recompletionemailbody'], 'format' => $result['recompletionemailbody_format']);
    $result['recompletionemailbody'] = $emailbody;

    return $result;
}

