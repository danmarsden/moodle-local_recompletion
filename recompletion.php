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
 * Edit course recompletion settings
 *
 * @package     local_recompletion
 * @copyright   2017 Dan Marsden
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/local/recompletion/locallib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/formslib.php');

$id = required_param('id', PARAM_INT);

// Perform some basic access control checks.
if ($id) {
    if ($id == SITEID) {
        // Don't allow editing of 'site course' using this form.
        throw new moodle_exception('cannoteditsiteform');
    }
    $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

    require_login($course);
    $context = context_course::instance($course->id);
    require_capability('local/recompletion:manage', $context);

    $completion = new completion_info($course);

    // Check if completion is enabled site-wide, or for the course.
    if (!$completion->is_enabled()) {
        throw new moodle_exception('completionnotenabled', 'local_recompletion');
    }

} else {
    require_login();
    throw new moodle_exception('needcourseid');
}

// Set up the page.
$PAGE->set_course($course);
$PAGE->set_url('/local/recompletion/recompletion.php', array('id' => $course->id));
$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('admin');

// This seems a bit messy - would be nice to tidy this up a bit.
$config = $DB->get_records_menu('local_recompletion_config', array('course' => $course->id), '', 'name, value');
$idmap = $DB->get_records_menu('local_recompletion_config', array('course' => $course->id), '', 'name, id');

$setnames = array('enable', 'recompletionduration', 'deletegradedata', 'archivecompletiondata',
    'recompletionemailenable', 'recompletionemailsubject', 'recompletionemailbody',
    'assignevent');

$activities = local_recompletion_get_supported_activities();
foreach ($activities as $activity) {
    $setnames[] = $activity;
    $setnames[] = 'archive'.$activity;
}

// Create the settings form instance.
$form = new local_recompletion_recompletion_form('recompletion.php?id='.$id, array('course' => $course));

if ($form->is_cancelled()) {
    redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);

} else if ($data = $form->get_data()) {
    foreach ($setnames as $name) {
        if (isset($data->$name)) {
            $value = $data->$name;
        } else {
            if ($name == 'recompletionemailsubject' || $name == 'recompletionemailbody') {
                $value = '';
            } else {
                $value = 0;
            }
        }
        if (!isset($config[$name]) || $config[$name] <> $value) {
            $rc = new stdclass();
            if (isset($idmap[$name])) {
                $rc->id = $idmap[$name];
            }
            $rc->name = $name;
            $rc->value = $value;
            $rc->course = $course->id;
            if (empty($rc->id)) {
                $DB->insert_record('local_recompletion_config', $rc);
            } else {
                $DB->update_record('local_recompletion_config', $rc);
            }
            if ($name == 'enable' && empty($value)) {
                // Don't overwrite any other settings when recompletion disabled.
                break;
            }
        }
    }
    // Redirect to the course main page.
    $url = new moodle_url('/course/view.php', array('id' => $course->id));
    redirect($url, get_string('recompletionsettingssaved', 'local_recompletion'));
} else if (!empty($config)) {
    $form->set_data($config);
}

// Print the form.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editrecompletion', 'local_recompletion'));

$form->display();

echo $OUTPUT->footer();
