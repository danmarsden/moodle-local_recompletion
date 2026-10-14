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

use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\course;
use core_reportbuilder\local\entities\user;
use local_recompletion\reportbuilder\entities\assign_submission_comments as assign_submission_comments_entity;

/**
 * Archived assignment submission comments datasource.
 *
 * @package    local_recompletion
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class archived_assign_submission_comments extends datasource {
    /**
     * Return user friendly name of the datasource.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('datasource:local_recompletion_asc', 'local_recompletion');
    }

    /**
     * Initialise.
     */
    protected function initialise(): void {
        $comments = new assign_submission_comments_entity();
        $commentsalias = $comments->get_table_alias('local_recompletion_asc');
        $submissionsalias = $comments->get_table_alias('local_recompletion_as');
        $coursesubmissionsalias = 'asc_submission_course';
        $this->set_main_table('local_recompletion_asc', $commentsalias);
        $this->add_entity($comments
            ->add_join(
                "LEFT JOIN {local_recompletion_as} {$submissionsalias} ON "
                . "{$submissionsalias}.id = {$commentsalias}.submission"
            ));

        $courseentity = new course();
        $coursealias = $courseentity->get_table_alias('course');
        $this->add_entity($courseentity
            ->add_join(
                "LEFT JOIN {local_recompletion_as} {$coursesubmissionsalias} ON "
                . "{$coursesubmissionsalias}.id = {$commentsalias}.submission"
            )
            ->add_join(
                "JOIN {course} {$coursealias} ON "
                . "{$coursealias}.id = {$coursesubmissionsalias}.course"
            ));

        $userentity = new user();
        $useralias = $userentity->get_table_alias('user');
        $this->add_entity($userentity
            ->add_join("JOIN {user} {$useralias} ON {$useralias}.id = {$commentsalias}.userid"));

        $this->add_all_from_entities();
    }

    /**
     * Return the columns that will be added to the report upon creation.
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'user:fullnamewithlink',
            'course:coursefullnamewithlink',
            'assign_submission_comments:assignment',
            'assign_submission_comments:attemptnumber',
            'assign_submission_comments:content',
            'assign_submission_comments:timecreated',
        ];
    }

    /**
     * Return the filters that will be added to the report upon creation.
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return [
            'course:courseselector',
        ];
    }

    /**
     * Return the conditions that will be added to the report upon creation.
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [];
    }
}
