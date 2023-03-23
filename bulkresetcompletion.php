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
 * Manually edit completion date for a course.
 *
 * @copyright 2020 Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package local_recompletion
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->libdir.'/formslib.php');

$courseid = required_param('id', PARAM_INT);
$userid   = optional_param('user', 0, PARAM_INT);
$users   = optional_param_array('users', array(), PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
require_login($course);

$context = context_course::instance($course->id);
require_capability('local/recompletion:manage', $context);

$PAGE->set_url('/local/recompletion/editcompletion.php', array('id' => $course->id));
if (empty($users) && empty($userid)) {
    // The first time list hack.
    if ($post = data_submitted()) {
        foreach ($post as $k => $v) {
            if (preg_match('/^user(\d+)$/', $k, $m)) {
                $users[] = $m[1];
            }
        }
    }
    if (empty($users)) {
        redirect($CFG->wwwroot.'/local/recompletion/participants.php?id='.$course->id,
            get_string('nousersselected', 'local_recompletion'));
    }

}
if (empty($users)) {
    $users = array();
    $users[] = $userid;
    // Get this users current completion date and use that in the form.
    $params = array(
        'userid'    => $userid,
        'course'    => $courseid
    );
    $ccompletion = new \completion_completion($params);
    if ($ccompletion->is_complete()) {
        $date = $ccompletion->timecompleted;
    }
}
if (empty($date)) {
    // Use current time as default.
    $date = time();
}

$form = new local_recompletion_coursecompletion_form('editcompletion.php',
    array('course' => $courseid, 'users' => $users, 'date' => $date));

if ($form->is_cancelled()) {
    redirect($CFG->wwwroot.'/local/recompletion/participants.php?id='.$course->id);

} else if ($data = $form->get_data()) {
    if (!empty($data->newcompletion)) {
        // Update course completion.
        foreach ($users as $user) {
            $params = array(
                'userid'    => $user,
                'course'    => $courseid
            );
            $ccompletion = new \completion_completion($params);
            if ($ccompletion->is_complete()) {
                // If we already have a completion date, clear it first so that mark_complete works.
                $ccompletion->timecompleted = null;
            }
            $ccompletion->mark_complete($data->newcompletion);
        }
        redirect($CFG->wwwroot.'/local/recompletion/participants.php?id='.$course->id,
            get_string('completionupdated', 'local_recompletion'));
    }
}

$userlist = user_get_users_by_id($users);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editcompletion', 'local_recompletion'));
echo $OUTPUT->box(get_string('editcompletion_desc', 'local_recompletion'));

echo html_writer::start_div('userlist');
foreach ($userlist as $user) {
    echo html_writer::div(fullname($user));
}
echo html_writer::end_div();
echo html_writer::start_div('userform');
$form->display();
echo html_writer::end_div();
echo $OUTPUT->footer();
