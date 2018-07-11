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
$string['editrecompletion'] = 'Edit course recompletion settings';
$string['enablerecompletion'] = 'Enable recompletion';
$string['enablerecompletion_help'] = 'The recompletion plugin allows a course completion details to be reset after a defined period.';
$string['recompletionrange'] = 'Recompletion period';
$string['recompletionrange_help'] = 'Set the period of time before a users completion results are reset.';
$string['recompletionsettingssaved'] = 'Recompletion settings saved';
$string['recompletion:manage'] = 'Allow course recompletion settings to be changed';
$string['recompletiontask'] = 'Check for users that need to recomplete';
$string['completionnotenabled'] = 'Completion is not enabled in this course';
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
$string['deletewhichdata'] = 'Select deletion criteria';
$string['deletegradedata'] = 'Delete current grade data';
$string['deletegradedata_help'] = 'Delete current grade completion data from grade_grades table. Grade recompletion data is permanently deleted but data retained in Grade history data table.';
$string['deletequizdata'] = 'Delete quiz data';
$string['deletequizdata_help'] = 'Delete quiz module completion data from quiz_attempts and quiz_grades tables. Quiz module completion data is permanently deleted if archive quiz data is not selected.';
$string['deletescormdata'] = 'Delete SCORM data';
$string['deletescormdata_help'] = 'Delete SCORM module completion data from the scorm_scoes_track and scorm_aicc_session tables. SCORM module completion data is permanently deleted if archive quiz data is not selected.';
$string['archivewhichdata'] = 'Select archive criteria';
$string['archivecompletiondata'] = 'Archive completion data';
$string['archivecompletiondata_help'] = 'Writes course completion data to the local_recompletion_cc, local_recompletion_cc_cc and local_recompletion_cmc tables. Completion data will be permanently deleted if this is not selected.';
$string['archivequizdata'] = 'Archive quiz data';
$string['archivequizdata_help'] = 'Archive quiz module data to the local_recompletion_qa and local_recompletion_qg tables before removing. Quiz module completion data is permanentely deleted if this is not selected.';
$string['archivescormdata'] = 'Archive SCORM data';
$string['archivescormdata_help'] = 'Archive SCORM module data to the local_recompletion_sst table before removing. SCORM module completion data is permanentely deleted if this is not selected.';
$string['emailrecompletiontitle'] = 'Custom recompletion message settings';
$string['eventrecompletion'] = 'Course recompletion';

