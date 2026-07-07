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
use local_recompletion\reportbuilder\entities\activity_completion;
use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\{course, user};

/**
 * Class activities
 *
 * @package    local_recompletion
 * @copyright  2026 Sumaiya Javed <sumaiya.javed@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_completions extends datasource {
    /**
     * Return user friendly name of the report source
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('activitycompletions', 'local_recompletion');
    }

    /**
     * Initialise report
     */
    protected function initialise(): void {

        // Our main entity.
        $mainentity = new activity_completion();
        $mainalias = $mainentity->get_table_alias('course_modules_completion');
        $this->set_main_table('course_modules_completion', $mainalias);
        $this->add_entity($mainentity);

        // Join the course entity.
        $courseentity = new course();
        $coursealias = $courseentity->get_table_alias('course');
        $coursemodulealias = $mainentity->get_table_alias('course_modules');
        $this->add_entity($courseentity
            ->add_joins($mainentity->get_joins())
            ->add_join("LEFT JOIN {course} {$coursealias} ON {$coursealias}.id = {$coursemodulealias}.course"));

        // Join the course category entity.
        $coursecatentity = new course_category();
        $coursecatalias = $coursecatentity->get_table_alias('course_categories');
        $this->add_entity($coursecatentity
            ->add_joins($courseentity->get_joins())
            ->add_join("LEFT JOIN {course_categories} {$coursecatalias} ON {$coursecatalias}.id = {$coursealias}.category"));

        // Join the user entity.
        $userentity = new user();
        $useralias = $userentity->get_table_alias('user');
        $this->add_entity($userentity
            ->add_join("LEFT JOIN {user} {$useralias} ON {$useralias}.id = {$mainalias}.userid"));

        // Add report elements from each of the entities we added to the report.
        $this->add_all_from_entities([
            $mainentity->get_entity_name(),
            $coursecatentity->get_entity_name(),
            $courseentity->get_entity_name(),
            $userentity->get_entity_name(),
        ]);
    }

    /**
     * Return the columns that will be added to the report upon creation
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'activity_completion:activityname',
            'activity_completion:activitytype',
            'course:shortname',
            'course:fullname',
            'user:username',
            'user:firstname',
            'user:lastname',
            'activity_completion:completionstate',
            'activity_completion:timemodified',
            'activity_completion:coursecompletionstatus',
        ];
    }

    /**
     * Return the column sorting that will be added to the report upon creation
     *
     * @return int[]
     */
    public function get_default_column_sorting(): array {
        return [];
    }

    /**
     * Return the filters that will be added to the report upon creation
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return [
            'activity_completion:timemodified',
            'activity_completion:activityname',
            'activity_completion:activitytype',
            'course:shortname',
            'course:fullname',
            'user:username',
            'user:firstname',
            'user:lastname',
        ];
    }

    /**
     * Return the conditions that will be added to the report upon creation
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [];
    }
}
