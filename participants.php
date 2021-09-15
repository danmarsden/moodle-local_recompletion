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
 * Lists all the users within a given course - copy of user/index.php
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package local_recompletion
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/notes/lib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');

use core_table\local\filter\filter;
use core_table\local\filter\integer_filter;
use core_table\local\filter\string_filter;

define('DEFAULT_PAGE_SIZE', 20);

$page         = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage      = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$contextid    = optional_param('contextid', 0, PARAM_INT); // One of this or.
$courseid     = optional_param('id', 0, PARAM_INT); // This are required.
$newcourse    = optional_param('newcourse', false, PARAM_BOOL);
$roleid       = optional_param('roleid', 0, PARAM_INT);
$urlgroupid   = optional_param('group', 0, PARAM_INT);

$PAGE->set_url('/local/recompletion/participants.php', array(
    'page' => $page,
    'perpage' => $perpage,
    'contextid' => $contextid,
    'id' => $courseid,
    'newcourse' => $newcourse));

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSE) {
        throw new moodle_exception('invalidcontext');
    }
    $course = $DB->get_record('course', array('id' => $context->instanceid), '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);
}
// Not needed anymore.
unset($contextid);
unset($courseid);

require_login($course);

$systemcontext = context_system::instance();
$isfrontpage = ($course->id == SITEID);

$frontpagectx = context_course::instance(SITEID);

if ($isfrontpage) {
    $PAGE->set_pagelayout('admin');
    course_require_view_participants($systemcontext);
} else {
    $PAGE->set_pagelayout('incourse');
    course_require_view_participants($context);
}

// Trigger events.
user_list_view($course, $context);

$bulkoperations = has_capability('moodle/course:bulkmessaging', $context);

$PAGE->set_title("$course->shortname: ".get_string('participants'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagetype('course-view-' . $course->format);
$PAGE->set_docs_path('enrol/users');
$PAGE->add_body_class('path-user');                     // So we can style it independently.
$PAGE->set_other_editing_capability('moodle/course:manageactivities');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('participants'));

$filterset = new \local_recompletion\table\participants_filterset();
$filterset->add_filter(new integer_filter('courseid', filter::JOINTYPE_DEFAULT, [(int)$course->id]));

$participanttable = new \local_recompletion\table\participants("user-recompletion-participants-{$course->id}");

$canaccessallgroups = has_capability('moodle/site:accessallgroups', $context);
$filtergroupids = $urlgroupid ? [$urlgroupid] : [];

// Force group filtering if user should only see a subset of groups' users.
if ($course->groupmode != NOGROUPS && !$canaccessallgroups) {
    if ($filtergroupids) {
        $filtergroupids = array_intersect(
            $filtergroupids,
            array_keys(groups_get_all_groups($course->id, $USER->id))
        );
    } else {
        $filtergroupids = array_keys(groups_get_all_groups($course->id, $USER->id));
    }

    if (empty($filtergroupids)) {
        if ($course->groupmode == SEPARATEGROUPS) {
            // The user is not in a group so show message and exit.
            echo $OUTPUT->notification(get_string('notingroup'));
            echo $OUTPUT->footer();
            exit();
        } else {
            $filtergroupids = [(int) groups_get_course_group($course, true)];
        }
    }
}

// Apply groups filter if included in URL or forced due to lack of capabilities.
if (!empty($filtergroupids)) {
    $filterset->add_filter(new integer_filter('groups', filter::JOINTYPE_DEFAULT, $filtergroupids));
}

// Display single group information if requested in the URL.
if ($urlgroupid > 0 && ($course->groupmode != SEPARATEGROUPS || $canaccessallgroups)) {
    $grouprenderer = $PAGE->get_renderer('core_group');
    $groupdetailpage = new \core_group\output\group_details($urlgroupid);
    echo $grouprenderer->group_details($groupdetailpage);
}

// Filter by role if passed via URL (used on profile page).
if ($roleid) {
    $viewableroles = get_profile_roles($context);

    // Apply filter if the user can view this role.
    if (array_key_exists($roleid, $viewableroles)) {
        $filterset->add_filter(new integer_filter('roles', filter::JOINTYPE_DEFAULT, [$roleid]));
    }
}

// Manage enrolments.
$manager = new course_enrolment_manager($PAGE, $course);
$enrolbuttons = $manager->get_manual_enrol_buttons();
$enrolrenderer = $PAGE->get_renderer('core_enrol');
$enrolbuttonsout = '';
foreach ($enrolbuttons as $enrolbutton) {
    $enrolbuttonsout .= $enrolrenderer->render($enrolbutton);
}

echo html_writer::div($enrolbuttonsout, 'd-flex justify-content-end', [
    'data-region' => 'wrapper',
    'data-table-uniqueid' => $participanttable->uniqueid,
]);

// Render the user filters.
$userrenderer = $PAGE->get_renderer('core_user');
echo $userrenderer->participants_filter($context, $participanttable->uniqueid);

echo '<div class="userlist">';

// Do this so we can get the total number of rows.
ob_start();
$participanttable->set_filterset($filterset);
$participanttable->out($perpage, true);
$participanttablehtml = ob_get_contents();
ob_end_clean();

echo html_writer::start_tag('form', [
    'action' => 'editcompletion.php',
    'method' => 'post',
    'id' => 'participantsform',
    'data-course-id' => $course->id,
    'data-table-unique-id' => $participanttable->uniqueid,
]);
echo '<div>';
echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
echo '<input type="hidden" name="returnto" value="'.s($PAGE->url->out(false)).'" />';

echo html_writer::tag(
    'p',
    get_string('countparticipantsfound', 'core_user', $participanttable->totalrows),
    [
        'data-region' => 'participant-count',
    ]
);

echo $participanttablehtml;

if ($bulkoperations) {
    echo '<br /><div class="buttons"><div class="form-inline">';
    echo '<input type="submit" name="submit" value="'.get_string('bulkchangedate', 'local_recompletion').'"/>';
    echo '<input type="hidden" name="id" value="' . $course->id . '" />';
    echo '</div></div>';
}

echo '</form>';

echo '</div>';  // Userlist.

$enrolrenderer = $PAGE->get_renderer('core_enrol');
// Need to re-generate the buttons to avoid having elements with duplicate ids on the page.
$enrolbuttons = $manager->get_manual_enrol_buttons();
$enrolbuttonsout = '';
foreach ($enrolbuttons as $enrolbutton) {
    $enrolbuttonsout .= $enrolrenderer->render($enrolbutton);
}
echo html_writer::div($enrolbuttonsout, 'd-flex justify-content-end', [
    'data-region' => 'wrapper',
    'data-table-uniqueid' => $participanttable->uniqueid,
]);

echo $OUTPUT->footer();
