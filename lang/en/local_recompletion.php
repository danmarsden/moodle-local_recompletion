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
 * Strings for local_recompletion
 *
 * @package    local_recompletion
 * @copyright  2017 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Course recompletion';
$string['recompletion'] = 'recompletion';
$string['editrecompletion'] = 'Edit course recompletion settings';
$string['enablerecompletion'] = 'Enable recompletion';
$string['enablerecompletion_help'] = 'The recompletion plugin allows a course completion details to be reset after a defined period.';
$string['recompletionrange'] = 'Recompletion period';
$string['recompletionrange_help'] = 'Set the period of time before a users completion results are reset.';
$string['recompletionsettingssaved'] = 'Recompletion settings saved';
$string['recompletion:manage'] = 'Allow course recompletion settings to be changed';
$string['recompletion:resetmycompletion'] = 'Reset my own completion';
$string['resetmycompletion'] = 'Reset my activity completion';
$string['recompletiontask'] = 'Check for users that need to recomplete';
$string['completionnotenabled'] = 'Completion is not enabled in this course';
$string['recompletionnotenabled'] = 'Recompletion is not enabled in this course';
$string['recompletionemailenable'] = 'Send recompletion message';
$string['recompletionemailenable_help'] = 'Enable email messaging to notifiy users that recompletion is required';
$string['recompletionemailsubject'] = 'Recompletion message subject';
$string['recompletionemailsubject_help'] = 'A custom recompletion email subject may be added as plain text

The following placeholders may be included in the message:

* Course name {$a->coursename}
* User fullname {$a->fullname}';
$string['recompletionemaildefaultsubject'] = 'Course {$a->coursename} recompletion required';
$string['recompletionemailbody'] = 'Recompletion message body';
$string['recompletionemailbody_help'] = 'A custom recompletion email subject may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags

The following placeholders may be included in the message:

* Course name {$a->coursename}
* Link to course {$a->link}
* Link to user\'s profile page {$a->profileurl}
* User email {$a->email}
* User fullname {$a->fullname}';
$string['recompletionemaildefaultbody'] = 'Hi there, please recomplete the course {$a->coursename} {$a->link}';
$string['advancedrecompletiontitle'] = 'Advanced';
$string['deletegradedata'] = 'Delete all grades for the user';
$string['deletegradedata_help'] = 'Delete current grade completion data from grade_grades table. Grade recompletion data is permanently deleted but data retained in Grade history data table.';
$string['archivecompletiondata'] = 'Archive completion data';
$string['archivecompletiondata_help'] = 'Writes completion data to the local_recompletion_cc, local_recompletion_cc_cc and local_recompletion_cmc tables. Completion data will be permanently deleted if this is not selected.';
$string['emailrecompletiontitle'] = 'Custom recompletion message settings';
$string['eventrecompletion'] = 'Course recompletion';
$string['assignattempts'] = 'Assign attempts';
$string['assignattempts_help'] = 'How to handle assignment attempts within the course.';
$string['extraattempt'] = 'Give student extra attempt/s';
$string['quizattempts'] = 'Quiz attempts';
$string['quizattempts_help'] = 'What to do with existing Quiz attempts. If delete and archive is selected, the old quiz attempts will be archived in the local_recompletion tables,
 if set to give extra attempts this will add a quiz override to allow the user to have the maximum number of allowed attempts set.';
$string['scormattempts'] = 'SCORM attempts';
$string['scormattempts_help'] = 'Should existing SCORM attempts be deleted - if archive is selected, the old SCORM attempts will be archived in the local_recompletion_sst table.';
$string['archive'] = 'Archive old attempts';
$string['delete'] = 'Delete existing attempts';
$string['donothing'] = 'Do nothing';
$string['resetmycompletionconfirm'] = 'Are you sure you want to reset all your completion data in this course?  Warning - this may permanently delete some of your submitted content.';
$string['completionreset'] = 'Your completion in this course has been reset.';
$string['privacy:metadata:local_recompletion_cc'] = 'Archive of previous course completions.';
$string['privacy:metadata:local_recompletion_cmc'] = 'Archive of previous course module completions.';
$string['privacy:metadata:local_recompletion_cc_cc'] = 'Archive of previous course_completion_crit_compl';
$string['privacy:metadata:userid'] = 'The user ID linked to this table.';
$string['privacy:metadata:course'] = 'The course ID linked to this table.';
$string['privacy:metadata:timecompleted'] = 'The time that the course was completed.';
$string['privacy:metadata:timeenrolled'] = 'The time that the user was enrolled in the course';
$string['privacy:metadata:timemodified'] = 'The time that the record was modified';
$string['privacy:metadata:timestarted'] = 'The time the course was started.';
$string['privacy:metadata:coursesummary'] = 'Stores the course completion data for a user.';
$string['privacy:metadata:gradefinal'] = 'Final grade received for course completion';
$string['privacy:metadata:overrideby'] = 'The user ID of the person who overrode the activity completion';
$string['privacy:metadata:reaggregate'] = 'If the course completion was reaggregated.';
$string['privacy:metadata:unenroled'] = 'If the user has been unenrolled from the course';
$string['privacy:metadata:quiz_attempts'] = 'Archived details about each attempt on a quiz.';
$string['privacy:metadata:quiz_attempts:attempt'] = 'The attempt number.';
$string['privacy:metadata:quiz_attempts:currentpage'] = 'The current page that the user is on.';
$string['privacy:metadata:quiz_attempts:preview'] = 'Whether this is a preview of the quiz.';
$string['privacy:metadata:quiz_attempts:state'] = 'The current state of the attempt.';
$string['privacy:metadata:quiz_attempts:sumgrades'] = 'The sum of grades in the attempt.';
$string['privacy:metadata:quiz_attempts:timecheckstate'] = 'The time that the state was checked.';
$string['privacy:metadata:quiz_attempts:timefinish'] = 'The time that the attempt was completed.';
$string['privacy:metadata:quiz_attempts:timemodified'] = 'The time that the attempt was updated.';
$string['privacy:metadata:quiz_attempts:timemodifiedoffline'] = 'The time that the attempt was updated via an offline update.';
$string['privacy:metadata:quiz_attempts:timestart'] = 'The time that the attempt was started.';
$string['privacy:metadata:quiz_grades'] = 'Archived details about the overall grade for previous quiz attempts.';
$string['privacy:metadata:quiz_grades:grade'] = 'The overall grade for this quiz.';
$string['privacy:metadata:quiz_grades:quiz'] = 'The quiz that was graded.';
$string['privacy:metadata:quiz_grades:timemodified'] = 'The time that the grade was modified.';
$string['privacy:metadata:quiz_grades:userid'] = 'The user who was graded.';
$string['privacy:metadata:scoes_track:element'] = 'The name of the element to be tracked';
$string['privacy:metadata:scoes_track:value'] = 'The value of the given element';
$string['privacy:metadata:coursemoduleid'] = 'The activity ID';
$string['privacy:metadata:completionstate'] = 'If the activity has been completed';
$string['privacy:metadata:viewed'] = 'If the activity was viewed';
$string['privacy:metadata:attempt'] = 'The attempt number';
$string['privacy:metadata:scorm_scoes_track'] = 'Archive of the tracked data of the SCOes belonging to the activity';
$string['noassigngradepermission'] = 'Your completion was reset, but this course contains an assignment that could not be reset, please ask your teacher to do this for you if required.';