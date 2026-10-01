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

namespace local_recompletion\reportbuilder\datasource;

use core_course\reportbuilder\local\entities\course_category;
use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\course;
use core_reportbuilder\local\entities\user;
use core_reportbuilder\local\filters\select;
use local_recompletion\reportbuilder\entities\archived_assign_files as archived_assign_files_entity;

/**
 * Archived assignment files datasource.
 *
 * @package    local_recompletion
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class archived_assign_files extends datasource {
    /**
     * Return user friendly name of the datasource.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('datasource:local_recompletion_af', 'local_recompletion');
    }

    /**
     * Initialise.
     */
    protected function initialise(): void {
        $fileentity = new archived_assign_files_entity();
        $filesalias = $fileentity->get_table_alias('files');
        $submissionsalias = $fileentity->get_table_alias('local_recompletion_as');
        $gradesalias = $fileentity->get_table_alias('local_recompletion_ag');
        $this->set_main_table('files', $filesalias);
        $this->add_entity($fileentity
            ->add_join(
                "LEFT JOIN {local_recompletion_as} {$submissionsalias} ON "
                . "{$submissionsalias}.id = {$filesalias}.itemid AND {$filesalias}.component = 'local_recompletion' AND "
                . "{$filesalias}.filearea = 'submission_files'"
            )
            ->add_join(
                "LEFT JOIN {local_recompletion_ag} {$gradesalias} ON "
                . "{$gradesalias}.id = {$filesalias}.itemid AND {$filesalias}.component = 'local_recompletion' AND "
                . "{$filesalias}.filearea IN ('feedback_files', 'download', 'readonlypages')"
            ));

        $this->add_entity(
            (new course())
                ->add_join("JOIN {course} course ON course.id = COALESCE({$submissionsalias}.course, {$gradesalias}.course)")
        );

        $this->add_entity(
            (new course_category())
                ->add_join("JOIN {course_categories} course_categories ON course_categories.id = course.category")
        );

        $this->add_entity(
            (new user())
                ->add_join("JOIN {user} user ON user.id = COALESCE({$submissionsalias}.userid, {$gradesalias}.userid)")
        );

        $this->add_all_from_entities();
    }

    /**
     * Return the columns that will be added to the report once is created.
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'user:fullnamewithlink',
            'course:coursefullnamewithlink',
            'archived_assign_files:assignment',
            'archived_assign_files:attemptnumber',
            'archived_assign_files:filearea',
            'archived_assign_files:filename',
            'archived_assign_files:filesize',
            'archived_assign_files:timecreated',
        ];
    }

    /**
     * Return the filters that will be added to the report once is created.
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return [
            'course:courseselector',
        ];
    }

    /**
     * Return the conditions that will be added to the report once is created.
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [
            'archived_assign_files:component',
        ];
    }

    /**
     * Return the condition values that will be set for the report upon creation.
     *
     * @return array
     */
    public function get_default_condition_values(): array {
        return [
            'archived_assign_files:component_operator' => select::EQUAL_TO,
            'archived_assign_files:component_value' => 'local_recompletion',
        ];
    }
}
