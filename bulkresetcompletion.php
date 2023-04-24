<?php
/**
 * Manually reset completion date for a course for a selected amount of users.
 *
 * @package     local_recompletion
 * @copyright   2023 ChatGPT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../../config.php';
require_once $CFG->dirroot . '/user/lib.php';
require_once $CFG->libdir . '/formslib.php';

$courseid = required_param('id', PARAM_INT);
$userid   = optional_param('user', 0, PARAM_INT);
$users    = optional_param_array('users', [], PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course);

$context = context_course::instance($course->id);
require_capability('local/recompletion:manage', $context);

$PAGE->set_url('/local/recompletion/editcompletion.php', ['id' => $course->id]);

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
        redirect($CFG->wwwroot . '/local/recompletion/participants.php?id=' . $course->id,
            get_string('nousersselected', 'local_recompletion'));
    }
}

if (empty($users)) {
    $users = [$userid];
    // Get this users current completion date and use that in the form.
    $params = ['userid' => $userid, 'course' => $courseid];
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
    ['course' => $courseid, 'users' => $users, 'date' => $date]);

if ($form->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/recompletion/participants.php?id=' . $course->id);
} elseif ($data = $form->get_data()) {
    if (!empty($data->newcompletion)) {
        // Update course completion.
        foreach ($users as $user) {
            $params = ['userid' => $user, 'course' => $courseid];
            $ccompletion = new \completion_completion($params);
            if ($ccompletion->is_complete()) {
                // If we already have a completion date, clear it first so that mark_complete works.
                $ccompletion->timecompleted = null;
            }
            $ccompletion->mark_complete($data->newcompletion);
        }
        redirect($CFG->wwwroot . '/local/recompletion/participants.php?id=' . $course->id,
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
