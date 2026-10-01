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
 * Assign handler event.
 *
 * @package     local_recompletion
 * @author      Dan Marsden
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\plugins;

use lang_string;

/**
 * Quiz handler event.
 *
 * @package    local_recompletion
 * @author     Dan Marsden
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mod_assign {
    /**
     * Add params to form.
     * @param moodleform $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function editingform($mform): void {
        $config = get_config('local_recompletion');

        $choices = [
            LOCAL_RECOMPLETION_NOTHING => get_string('donothing', 'local_recompletion'),
            LOCAL_RECOMPLETION_DELETE => get_string('delete', 'local_recompletion'),
            LOCAL_RECOMPLETION_EXTRAATTEMPT => get_string('extraattempt', 'local_recompletion'),
        ];

        $mform->addElement('select', 'assign', get_string('assignattempts', 'local_recompletion'), $choices);
        $mform->addHelpButton('assign', 'assignattempts', 'local_recompletion');
        $mform->setDefault('assign', $config->assign ?? LOCAL_RECOMPLETION_NOTHING);

        $mform->addElement('checkbox', 'archiveassign', get_string('archive', 'local_recompletion'));
        $mform->setDefault('archiveassign', $config->archiveassign ?? 1);

        $mform->addElement('checkbox', 'assignevent', '', get_string('assignevent', 'local_recompletion'));
        $mform->setDefault('assignevent', $config->assignevent ?? 0);

        $mform->disabledIf('assignevent', 'enable', 'notchecked');
        $mform->disabledIf('assign', 'enable', 'notchecked');
        $mform->disabledIf('archiveassign', 'enable', 'notchecked');
        $mform->hideIf('archiveassign', 'assign', 'noteq', LOCAL_RECOMPLETION_DELETE);
    }

    /**
     * Add sitelevel settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings($settings) {
        $choices = [LOCAL_RECOMPLETION_NOTHING => new lang_string('donothing', 'local_recompletion'),
                    LOCAL_RECOMPLETION_DELETE => new lang_string('delete', 'local_recompletion'),
                    LOCAL_RECOMPLETION_EXTRAATTEMPT => new lang_string('extraattempt', 'local_recompletion')];

        $settings->add(new \admin_setting_configselect(
            'local_recompletion/assign',
            new lang_string('assignattempts', 'local_recompletion'),
            new lang_string('assignattempts_help', 'local_recompletion'),
            LOCAL_RECOMPLETION_NOTHING,
            $choices
        ));

        $settings->add(new \admin_setting_configcheckbox(
            'local_recompletion/archiveassign',
            new lang_string('archiveassign', 'local_recompletion'),
            '',
            1
        ));

        $settings->add(new \admin_setting_configcheckbox(
            'local_recompletion/assignevent',
            new lang_string('assignevent', 'local_recompletion'),
            '',
            0
        ));
    }

    /**
     * Reset assign records.
     * @param \int $userid - record with user information for recompletion
     * @param \stdClass $course - course record.
     * @param \stdClass $config - recompletion config.
     */
    public static function reset($userid, $course, $config) {
        global $DB;
        if (empty($config->assign)) {
            return '';
        } else if ($config->assign == LOCAL_RECOMPLETION_DELETE) {
            $assignments = $DB->get_records('assign', ['course' => $course->id]);
            $assignmentcontexts = [];
            foreach ($assignments as $assignment) {
                $cm = get_coursemodule_from_instance('assign', $assignment->id);
                if (!$cm) {
                    continue;
                }
                $assignmentcontexts[$assignment->id] = \context_module::instance($cm->id);
            }

            if (!empty($config->archiveassign)) {
                $params = ['userid' => $userid, 'course' => $course->id];
                $selectsql = 'userid = ? AND assignment IN (SELECT id FROM {assign} WHERE course = ?)';

                $submissions = $DB->get_records_select('assign_submission', $selectsql, $params);
                foreach ($submissions as $submission) {
                    $submission->course = $course->id;
                    $submissionarchiveid = $DB->insert_record('local_recompletion_as', $submission);
                    if (!empty($assignmentcontexts[$submission->assignment])) {
                        self::archive_submission_files(
                            $assignmentcontexts[$submission->assignment],
                            $submission->id,
                            $submissionarchiveid
                        );
                    }
                }

                $grades = $DB->get_records_select('assign_grades', $selectsql, $params);
                foreach ($grades as $grade) {
                    $grade->course = $course->id;
                    $gradearchiveid = $DB->insert_record('local_recompletion_ag', $grade);
                    if (!empty($assignmentcontexts[$grade->assignment])) {
                        self::archive_grade_files($assignmentcontexts[$grade->assignment], $grade->id, $gradearchiveid);
                    }
                }
            }

            foreach ($assignments as $assignment) {
                $cm = get_coursemodule_from_instance('assign', $assignment->id);
                if (!$cm) {
                    continue;
                }
                $context = \context_module::instance($cm->id);
                if (!$context) {
                    continue;
                }
                $assign = new \assign($context, $cm, $course);
                $assign->remove_submission($userid);
                self::delete_feedback_data($assign, $context, $userid);
            }
        } else if ($config->assign == LOCAL_RECOMPLETION_EXTRAATTEMPT) {
            $sql = "SELECT DISTINCT a.*
                      FROM {assign} a
                      JOIN {assign_submission} s ON a.id = s.assignment
                     WHERE a.course = ? AND s.userid = ?";
            $assigns = $DB->get_recordset_sql($sql, [$course->id, $userid]);
            $nopermissions = false;
            foreach ($assigns as $assign) {
                $cm = get_coursemodule_from_instance('assign', $assign->id);
                if (!$cm) {
                    continue;
                }
                /** @var \context $context */
                $context = \context_module::instance($cm->id);
                if (has_capability('mod/assign:grade', $context)) {
                    // Assign add_attempt() is protected and requires sesskey, use reflection so we don't have to write our own.
                    $_POST['sesskey'] = sesskey();
                    $r = new \ReflectionMethod('assign', 'add_attempt');
                    $r->setAccessible(true);
                    $r->invoke(new \assign($context, $cm, $course), $userid);
                } else {
                    $nopermissions = true;
                }
            }
            if ($nopermissions) {
                return get_string('noassigngradepermission', 'local_recompletion');
            }
        }
        return '';
    }

    /**
     * Archive files from the assignment submission area.
     *
     * @param \context_module $context
     * @param int $sourceitemid
     * @param int $archiveitemid
     */
    private static function archive_submission_files(\context_module $context, int $sourceitemid, int $archiveitemid): void {
        self::archive_files(
            $context->id,
            'assignsubmission_file',
            ASSIGNSUBMISSION_FILE_FILEAREA,
            $sourceitemid,
            $archiveitemid
        );
    }

    /**
     * Archive files from assignfeedback_file and assignfeedback_editpdf.
     *
     * @param \context_module $context
     * @param int $sourceitemid
     * @param int $archiveitemid
     */
    private static function archive_grade_files(\context_module $context, int $sourceitemid, int $archiveitemid): void {
        self::archive_files(
            $context->id,
            'assignfeedback_file',
            ASSIGNFEEDBACK_FILE_FILEAREA,
            $sourceitemid,
            $archiveitemid
        );
        self::archive_files(
            $context->id,
            'assignfeedback_editpdf',
            \assignfeedback_editpdf\document_services::FINAL_PDF_FILEAREA,
            $sourceitemid,
            $archiveitemid
        );
        self::archive_files(
            $context->id,
            'assignfeedback_editpdf',
            \assignfeedback_editpdf\document_services::PAGE_IMAGE_READONLY_FILEAREA,
            $sourceitemid,
            $archiveitemid
        );
    }

    /**
     * Copy files from one file area to the matching local_recompletion file area.
     *
     * @param int $contextid
     * @param string $fromcomponent
     * @param string $filearea
     * @param int $fromitemid
     * @param int $toitemid
     */
    private static function archive_files(
        int $contextid,
        string $fromcomponent,
        string $filearea,
        int $fromitemid,
        int $toitemid,
    ): void {
        $fs = get_file_storage();

        $filerecord = (object) [
            'contextid' => $contextid,
            'component' => 'local_recompletion',
            'filearea' => $filearea,
            'itemid' => $toitemid,
        ];

        $files = $fs->get_area_files($contextid, $fromcomponent, $filearea, $fromitemid, 'id', false);
        foreach ($files as $file) {
            if ($file->is_directory() && $file->get_filepath() === '/') {
                continue;
            }

            $existingfile = $fs->get_file(
                $contextid,
                'local_recompletion',
                $filearea,
                $toitemid,
                $file->get_filepath(),
                $file->get_filename()
            );
            if ($existingfile) {
                $existingfile->delete();
            }

            $fs->create_file_from_storedfile($filerecord, $file);
        }
    }

    /**
     * Delete assignment feedback data for a user across supported feedback plugins.
     *
     * @param \assign $assign
     * @param mixed $context
     * @param int $userid
     */
    private static function delete_feedback_data(\assign $assign, $context, int $userid): void {
        global $DB;

        $requestdata = new \mod_assign\privacy\assign_plugin_request_data($context, $assign);
        $requestdata->set_userids([$userid]);
        $requestdata->populate_submissions_and_grades();
        if (class_exists('\\assignfeedback_comments\\privacy\\provider')) {
            \assignfeedback_comments\privacy\provider::delete_feedback_for_grades($requestdata);
        }

        if (
            class_exists('\\assignfeedback_file\\privacy\\provider')
            && $assign->get_plugin_by_type('assignfeedback', 'file')
        ) {
            \assignfeedback_file\privacy\provider::delete_feedback_for_grades($requestdata);
        }

        if (
            class_exists('\\assignfeedback_editpdf\\privacy\\provider')
            && $assign->get_plugin_by_type('assignfeedback', 'editpdf')
        ) {
            \assignfeedback_editpdf\privacy\provider::delete_feedback_for_grades($requestdata);
        }

        if (
            class_exists('\\assignsubmission_comments\\privacy\\provider')
            && $assign->get_plugin_by_type('assignsubmission', 'comments')
        ) {
            \assignsubmission_comments\privacy\provider::delete_submissions($requestdata);

            $submissionids = $requestdata->get_submissionids();
            if (!empty($submissionids) && class_exists('\\core_comment\\privacy\\provider')) {
                [$insql, $inparams] = $DB->get_in_or_equal($submissionids, SQL_PARAMS_NAMED);
                \core_comment\privacy\provider::delete_comments_for_all_users_select(
                    $context,
                    'assignsubmission_comments',
                    'submission_comments',
                    $insql,
                    $inparams
                );
            }
        }

        $DB->delete_records('assign_grades', [
            'assignment' => $assign->get_instance()->id,
            'userid' => $userid,
        ]);

        $DB->delete_records('assign_submission', [
            'assignment' => $assign->get_instance()->id,
            'userid' => $userid,
        ]);

        $DB->delete_records('assign_user_flags', [
            'assignment' => $assign->get_instance()->id,
            'userid' => $userid,
        ]);

        $DB->delete_records('assign_user_mapping', [
            'assignment' => $assign->get_instance()->id,
            'userid' => $userid,
        ]);
    }
}
