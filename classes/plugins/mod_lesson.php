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

namespace local_recompletion\plugins;

use stdClass;
use lang_string;
use admin_setting_configselect;
use admin_setting_configcheckbox;
use admin_settingpage;
use MoodleQuickForm;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/recompletion/locallib.php');

/**
 * Lesson handler event.
 *
 * @package    local_recompletion
 * @author     2023 Dmitrii Metelkin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_lesson {

    /**
     * Add params to form.
     *
     * @param MoodleQuickForm $mform
     */
    public static function editingform(MoodleQuickForm $mform): void {
        $config = get_config('local_recompletion');

        $cba = [];
        $cba[] = $mform->createElement('radio', 'lesson', '',
                get_string('donothing', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING);
        $cba[] = $mform->createElement('radio', 'lesson', '',
                get_string('delete', 'local_recompletion'), LOCAL_RECOMPLETION_DELETE);

        $mform->addGroup($cba, 'lesson', get_string('lessonattempts', 'local_recompletion'), [' '], false);
        $mform->addHelpButton('lesson', 'lessonattempts', 'local_recompletion');
        $mform->setDefault('lesson', $config->lessonattempts);

        $mform->addElement('checkbox', 'archivelesson', get_string('archive', 'local_recompletion'));
        $mform->setDefault('archivelesson', $config->archivelesson);

        $mform->disabledIf('archivelesson', 'enable');
        $mform->hideIf('archivelesson', 'lesson');
        $mform->disabledIf('lesson', 'enable');
    }

    /**
     * Add site level settings for this plugin.
     *
     * @param admin_settingpage $settings
     */
    public static function settings(admin_settingpage $settings): void {
        $choices = [
            LOCAL_RECOMPLETION_NOTHING => get_string('donothing', 'local_recompletion'),
            LOCAL_RECOMPLETION_DELETE => get_string('delete', 'local_recompletion')
        ];

        $settings->add(new admin_setting_configselect('local_recompletion/lessonattempts',
                new lang_string('lessonattempts', 'local_recompletion'),
                new lang_string('lessonattempts_help', 'local_recompletion'), LOCAL_RECOMPLETION_NOTHING, $choices));

        $settings->add(new admin_setting_configcheckbox('local_recompletion/archivelesson',
            new lang_string('archivelesson', 'local_recompletion'), '', 1));
    }

    /**
     * Reset h5pactivity attempt records.
     *
     * @param int $userid - user id
     * @param stdClass $course - course record.
     * @param stdClass $config - recompletion config.
     */
    public static function reset(int $userid, stdClass $course, stdClass $config): void {
        global $DB;

        if (empty($config->lesson)) {
            return;
        }

        if ($config->lesson == LOCAL_RECOMPLETION_DELETE) {

            $tables = [
                'lesson_attempts' => 'local_recompletion_la',
                'lesson_grades' => 'local_recompletion_lg',
                'lesson_timer' => 'local_recompletion_lt',
                'lesson_branch' => 'local_recompletion_lb',
                'lesson_overrides' => 'local_recompletion_lo',
            ];

            $params = ['userid' => $userid, 'course' => $course->id];
            $sql = 'userid = :userid AND lessonid IN (SELECT id FROM {lesson} WHERE course = :course)';

            if ($config->archivelesson) {
                foreach ($tables as $originaltable => $archivetable) {
                    $records = $DB->get_records_select($originaltable, $sql, $params);
                    if (!empty($records)) {
                        foreach ($records as $record) {
                            $record->course = $course->id;
                        }
                        $DB->insert_records($archivetable, $records);
                    }
                }
            }

            // Finally can delete records.
            foreach ($tables as $originaltable => $archivetable) {
                $DB->delete_records_select($originaltable, $sql, $params);
            }
        }
    }
}
