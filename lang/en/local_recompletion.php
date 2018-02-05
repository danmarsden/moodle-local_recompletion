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
$string['recompletionemailsubject'] = 'Course {$a->coursename} recompletion required';
$string['recompletionemailcontent'] = 'Hi there, please recomplete the course {$a->coursename} {$a->link}';
$string['archivecompletiondata'] = 'Archive completion data';
$string['archivecompletiondata_help'] = 'Writes course completion data to an archive table. Course completion data is deleted if this is not selected.';
$string['graderecompletiontitle'] = 'Current grade data settings';
$string['deletegradedata'] = 'Delete current grade data';
$string['deletegradedata_help'] = 'Deletes current grade completion data. Grade recompletion data is permanently deleted if archive grade data is not selected. Grade history data is not affected.';
$string['archivegradedata'] = 'Archive current grade data';
$string['archivegradedata_help'] = 'Writes current grade completion data to an archive table before removing. Current grade completion data is permanentely deleted if this is not selected. Grade history data is not affected.';
$string['quizrecompletiontitle'] = 'Quiz data settings';
$string['deletequizdata'] = 'Delete quiz data';
$string['deletequizdata_help'] = 'Deletes quiz module completion data. Quiz module completion data is permanently deleted if archive quiz data is not selected.';
$string['archivequizdata'] = 'Archive quiz data';
$string['archivequizdata_help'] = 'Writes quiz module data to an archive table before removing. Quiz module completion data is permanentely deleted if this is not selected.';
$string['scormrecompletiontitle'] = 'SCORM data settings';
$string['deletescormdata'] = 'Delete SCORM data';
$string['deletescormdata_help'] = 'Deletes SCORM module completion data. SCORM module completion data is permanently deleted if archive quiz data is not selected.';
$string['archivescormdata'] = 'Archive SCORM data';
$string['archivescormdata_help'] = 'Writes SCORM module data to an archive table before removing. SCORM module completion data is permanentely deleted if this is not selected.';
$string['emailrecompletiontitle'] = 'Recompletion message settings';
$string['recompletionemailenable'] = 'Send recompletion message';
$string['recompletionemailenable_help'] = 'Enable email to use to notify them that recompletion is required';
$string['recompletionemailheader'] = 'Custom recompletion message subject';
$string['recompletionemailheader_help'] = 'A custom recompletion email subject may be added as plain text

The folowing placeholders may be included in the message:

* Course name {$a->coursename}
* User fullname {$a->fullname}';

$string['recompletionemailbody'] = 'Custom recompletion message body';
$string['recompletionemailbody_help'] = 'A custom recompletion email subject may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags

The folowing placeholders may be included in the message:

* Course name {$a->coursename}
* Link to course {$a->link}
* Link to user\'s profile page {$a->profileurl}
* User email {$a->email}
* User fullname {$a->fullname}';