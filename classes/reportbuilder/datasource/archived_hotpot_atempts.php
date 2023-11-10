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
use local_recompletion\reportbuilder\entities\hotpot_attempts;

/**
 * Hotpot attempts archive datasource.
 *
 * @package    local_recompletion
 * @author     Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class archived_hotpot_atempts extends datasource {

    /**
     * Return user friendly name of the datasource
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('datasource:local_recompletion_hpa', 'local_recompletion');
    }

    /**
     * Initialise.
     */
    protected function initialise(): void {
        $attempts = new hotpot_attempts();
        $attemptsalias = $attempts->get_table_alias('local_recompletion_hpa');
        $this->add_entity($attempts);

        $this->set_main_table('local_recompletion_hpa', $attemptsalias);

        // Join the course entity.
        $courseentity = new course();
        $coursealias = $courseentity->get_table_alias('course');
        $this->add_entity($courseentity
            ->add_join("JOIN {course} {$coursealias} ON {$coursealias}.id = {$attemptsalias}.course"));

        // Join the course category entity.
        $coursecatentity = new course_category();
        $categoriesalias  = $coursecatentity->get_table_alias('course_categories');
        $this->add_entity($coursecatentity
            ->add_join("JOIN {course_categories} {$categoriesalias} ON {$categoriesalias}.id = {$coursealias}.category"));

        // Join the user entity.
        $userentity = new user();
        $useralias = $userentity->get_table_alias('user');
        $this->add_entity($userentity
            ->add_join("JOIN {user} {$useralias} ON {$useralias}.id = {$attemptsalias}.userid"));

        $this->add_all_from_entities();
    }

    /**
     * Return the columns that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'user:fullnamewithlink',
            'course:coursefullnamewithlink',
            'hotpot_attempts:hotpotid',
            'hotpot_attempts:starttime',
            'hotpot_attempts:endtime',
            'hotpot_attempts:score',
            'hotpot_attempts:status',
        ];
    }

    /**
     * Return the filters that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return [
            'course:courseselector',
            'hotpot_attempts:status',
        ];
    }

    /**
     * Return the conditions that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [];
    }
}
