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
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');

$id = required_param('id', PARAM_INT); // Course id.
$confirm = optional_param('confirm', '', PARAM_INT);
$userid = optional_param('user', 0, PARAM_INT);

if ($id == SITEID) {
    // Don't allow editing of 'site course' using this form.
    throw new moodle_exception('cannoteditsiteform');
}
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_login($course);

if (empty($userid)) {
    $userid = $USER->id;
}

$context = context_course::instance($course->id);
if ($USER->id <> $userid) {
    require_capability('local/recompletion:manage', $context);
    $user = $DB->get_record('user', array('id' => $userid));
} else {
    require_capability('local/recompletion:resetmycompletion', $context);
    $user = $USER;
}

$config = $DB->get_records_menu('local_recompletion_config', array('course' => $course->id), '', 'name, value');
$config = (object) $config;

if (empty($config->enable)) {
    throw new moodle_exception('recompletionnotenabled', 'local_recompletion');
}

if (!empty($confirm) && confirm_sesskey()) {
    $reset = new local_recompletion\task\check_recompletion();
    $errors = $reset->reset_user($userid, $course, $config);
    if ($USER->id <> $userid) {
        $returnurl = new moodle_url('/local/recompletion/participants.php', array('id' => $course->id));
    } else {
        $returnurl = course_get_url($course);
    }
    if (!empty($errors)) {
        redirect($returnurl, explode(',', $errors), '',  \core\output\notification::NOTIFY_WARNING);
    } else {
        redirect($returnurl, get_string('completionresetuser', 'local_recompletion', fullname($user)));
    }

}

// Set up the page.
$PAGE->set_course($course);
$PAGE->set_url('/local/recompletion/resetcompletion.php', array('id' => $course->id));
$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);

// Print the form.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("resetcompletionfor", "local_recompletion", fullname($user)));

$cancelurl = course_get_url($course);
$confirmurl = $PAGE->url;
$confirmurl->param('confirm', 1);
$confirmurl->param('user', $userid);
$message = get_string("resetcompletionconfirm", "local_recompletion", fullname($user));
echo $OUTPUT->confirm($message, $confirmurl, $cancelurl);

echo $OUTPUT->footer();
