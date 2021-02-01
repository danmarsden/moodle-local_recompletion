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
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/recompletion/locallib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');

$id = required_param('id', PARAM_INT); // Course id.
$confirm = optional_param('confirm', '', PARAM_INT);
$action = optional_param('action', null, PARAM_TEXT);
$userid = optional_param('user', 0, PARAM_INT);
$users = optional_param_array('users', array(), PARAM_INT);

if ($id == SITEID) {
    // Don't allow editing of 'site course' using this form.
    print_error('cannoteditsiteform');
}

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}
require_login($course);

$config = $DB->get_records_menu('local_recompletion_config', array('course' => $course->id), '', 'name, value');
$config = (object) $config;

if (empty($config->enable)) {
    print_error('recompletionnotenabled', 'local_recompletion');
}
$forwhat = "";
switch ($action) {
    case "allusers":
        $forwhat = $action;

        break;
    case "filtered":
        $forwhat = $action;

        break;
    case "selected":
        $forwhat = get_string('resetcompletionselected', 'local_recompletion');
        if (empty($users)) {
            // The first time list hack.
            if ($post = data_submitted()) {
                foreach ($post as $k => $v) {
                    if (preg_match('/^user(\d+)$/', $k, $m)) {
                        $users[] = $m[1];
                    }
                }
            }
            if (empty($users)) {
                redirect($CFG->wwwroot . '/local/recompletion/participants.php?id=' . $course->id,
                        get_string('nousersselected', 'local_recompletion'));
            }
        }

        if (!empty($users) && !empty($confirm) && confirm_sesskey()) {
            // Update course completion.
            foreach ($users as $user) {
                $reset = new local_recompletion\task\check_recompletion();
                $errors = $reset->reset_user($user, $course, $config);
                $returnurl = new moodle_url('/local/recompletion/participants.php', array('id' => $course->id));
            }
            if (!empty($errors)) {
                redirect($returnurl, $errors, '', \core\output\notification::NOTIFY_WARNING);
            } else {
                redirect($returnurl, get_string('completionresetuser', 'local_recompletion', get_string('resetcompletionselected', 'local_recompletion')));
            }
        }

        break;

    default:

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
        $forwhat = fullname($user);

        if (!empty($confirm) && confirm_sesskey()) {
            $reset = new local_recompletion\task\check_recompletion();
            $errors = $reset->reset_user($userid, $course, $config);
            if ($USER->id <> $userid) {
                $returnurl = new moodle_url('/local/recompletion/participants.php', array('id' => $course->id));
            } else {
                $returnurl = course_get_url($course);
            }
            if (!empty($errors)) {
                redirect($returnurl, $errors, '', \core\output\notification::NOTIFY_WARNING);
            } else {
                redirect($returnurl, get_string('completionresetuser', 'local_recompletion', fullname($user)));
            }
        }
        break;
}

// Set up the page.
$PAGE->set_course($course);
$PAGE->set_url('/local/recompletion/resetcompletion.php', array('id' => $course->id));
$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);

// Print the form.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("resetcompletionfor", "local_recompletion", $forwhat));

$cancelurl = course_get_url($course);
$confirmurl = $PAGE->url;
$confirmurl->param('confirm', 1);
$confirmurl->param('action', $action);
if (!empty($users)) {
    foreach ($users as $value) {
        $confirmurl->param('users[]', $value);
    }

    $userlist = user_get_users_by_id($users);
    echo html_writer::start_div('userlist');
    foreach ($userlist as $user) {
        echo html_writer::div(fullname($user));
    }
    echo html_writer::end_div();
} else {
    $confirmurl->param('user', $userid);
}
$message = get_string("resetcompletionconfirm", "local_recompletion", $forwhat);
echo $OUTPUT->confirm($message, $confirmurl, $cancelurl);

echo $OUTPUT->footer();
