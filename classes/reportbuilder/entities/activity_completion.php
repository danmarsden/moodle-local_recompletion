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

namespace local_recompletion\reportbuilder\entities;

use lang_string;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\filters\select;

/**
 * Activity completion entity class implementation
 *
 * Defines all the columns and filters that can be added to reports that use this entity.
 *
 * @package    local_recompletion
 * @copyright  2026 Sumaiya Javed <sumaiya.javed@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_completion extends base {
    /**
     * Activities supported by the report.
     */
    const SUPPORTED_ACTIVITIES = [
        "assign" => "Assign",
        "quiz" => "Quiz",
        "forum" => "Forum",
        "choice" => "Choice",
    ];

    /**
     * Database tables that this entity uses
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return [
            'course_modules_completion',
            'course_modules',
            'modules',
            'groups_members',
            'groups',
            'course_completions',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entityactivitycompletion', 'local_recompletion');
    }

    /**
     * Initialize the entity
     *
     * @return base
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this->add_filter($filter)->add_condition($filter);
        }

        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        global $DB;

        $mainalias = $this->get_table_alias('course_modules_completion');
        $coursemodulealias = $this->get_table_alias('course_modules');
        $modulealias = $this->get_table_alias('modules');
        $groupsmembersalias = $this->get_table_alias('groups_members');
        $groupsalias = $this->get_table_alias('groups');
        $coursecomalias = $this->get_table_alias('course_completions');

        // Activitiy completion date column.
        $columns[] = (new column(
            'timemodified',
            new lang_string('activitycompletiondate', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$mainalias}.timemodified")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);

        // Activitiy completion status column.
        $columns[] = (new column(
            'completionstate',
            new lang_string('activitycompletionstatus', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$mainalias}.completionstate")
            ->set_is_sortable(true)
            ->add_callback(static function (string $state): string {
                $output = '';
                $completiontype = 'n';
                switch ($state) {
                    case COMPLETION_INCOMPLETE:
                        $completiontype = 'n';
                        break;
                    case COMPLETION_COMPLETE:
                        $completiontype = 'y';
                        break;
                    case COMPLETION_COMPLETE_PASS:
                        $completiontype = 'pass';
                        break;
                    case COMPLETION_COMPLETE_FAIL:
                        $completiontype = 'fail';
                        break;
                }
                $output = get_string('completion-' . $completiontype, 'completion');
                return $output;
            });

        // Activitiy ID column.
        $columns[] = (new column(
            'activityid',
            new lang_string('activityid', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_join($this->get_module_join())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$coursemodulealias}.instance")
            ->set_is_sortable(true);

        // Activitiy type column.
        $columns[] = (new column(
            'activitytype',
            new lang_string('activitytype', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_join($this->get_module_join())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$modulealias}.name")
            ->set_is_sortable(true);

        // Activitiy name column.
        $columns[] = (new column(
            'activityname',
            new lang_string('activityname', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_join($this->get_module_join())
            ->add_join($this->get_activity_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("activitynames.name AS activityname")
            ->set_is_sortable(true);

            // Activitiy completion user group column.
        $usergroups = $DB->sql_group_concat("{$groupsalias}.name");
        $columns[] = (new column(
            'groupname',
            new lang_string('groupname', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("
                (SELECT {$usergroups}
                FROM {groups_members} {$groupsmembersalias}
                JOIN {groups} {$groupsalias} ON {$groupsalias}.id = {$groupsmembersalias}.groupid
                WHERE {$groupsmembersalias}.userid = {$mainalias}.userid
                    AND {$groupsalias}.courseid = {$coursemodulealias}.course)
            ", 'groupname')
            ->set_is_sortable(true);

        // Course completion user column.
        $columns[] = (new column(
            'coursecompletionstatus',
            new lang_string('coursecompletionstatus', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("(SELECT COALESCE({$coursecomalias}.timecompleted, 0)
                        FROM {course_completions} {$coursecomalias}
                        WHERE {$coursecomalias}.course = {$coursemodulealias}.course
                        AND {$coursecomalias}.userid = {$mainalias}.userid)", 'coursecompletionstatus')
            ->set_is_sortable(true)
            ->add_callback(static function ($value): string {
                $output = get_string('notcompleted', 'completion');
                if (!empty($value)) {
                    $output = get_string('completed', 'completion');
                }
                return $output;
            });

        return $columns;
    }

    /**
     * Return activity join for the different activity types.
     *
     * @return string
     */
    public function get_activity_joins(): string {
        global $CFG;
        $coursemodulealias = $this->get_table_alias('course_modules');
        $modulealias = $this->get_table_alias('modules');
        $activities = self::SUPPORTED_ACTIVITIES;

        // Build a UNION subquery across only the modules actually used in this course.
        $joinactivities = "LEFT JOIN (";
        foreach ($activities as $key => $activity) {
            $joinactivities .= " SELECT id, name, '{$key}' AS modname FROM {$CFG->prefix}{$key}";
            if ($key !== array_key_last($activities)) {
                $joinactivities .= " UNION ALL";
            }
        }
        $joinactivities .= ") activitynames ON activitynames.id = {$coursemodulealias}.instance
                AND activitynames.modname = {$modulealias}.name";
        return $joinactivities;
    }

    /**
     * Return module join used by columns
     *
     * @return string
     */
    public function get_module_join(): string {
        $mainalias = $this->get_table_alias('course_modules_completion');
        $coursemodulealias = $this->get_table_alias('course_modules');
        $modulealias = $this->get_table_alias('modules');
        return "LEFT JOIN {course_modules} {$coursemodulealias}
                    ON {$coursemodulealias}.id = {$mainalias}.coursemoduleid
                LEFT JOIN {modules} {$modulealias}
                    ON {$modulealias}.id = {$coursemodulealias}.module";
    }

    /**
     * Return module join used by columns
     *
     * @return string
     */
    public function get_module_activity_join(): string {
        $mainalias = $this->get_table_alias('course_modules_completion');
        $coursemodulealias = $this->get_table_alias('course_modules');
        $modulealias = $this->get_table_alias('modules');
        return "LEFT JOIN {course_modules} {$coursemodulealias}
                    ON {$coursemodulealias}.id = {$mainalias}.coursemoduleid
                LEFT JOIN {modules} {$modulealias}
                    ON {$modulealias}.id = {$coursemodulealias}.module";
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $mainalias = $this->get_table_alias('course_modules_completion');
        $coursemodulealias = $this->get_table_alias('course_modules');
        $modulealias = $this->get_table_alias('modules');

        // Activitiy ID filter.
        $filters[] = (new filter(
            number::class,
            'activityid',
            new lang_string('activityid', 'local_recompletion'),
            $this->get_entity_name(),
            "{$coursemodulealias}.instance"
        ))
            ->add_joins($this->get_joins());

        // Activitiy name filter.
        $filters[] = (new filter(
            text::class,
            'activityname',
            new lang_string('activityname', 'local_recompletion'),
            $this->get_entity_name(),
            'activitynames.name'
        ))
            ->add_joins($this->get_joins())
            ->add_join($this->get_activity_joins());

        // Activitiy type filter.
        $filters[] = (new filter(
            select::class,
            'activitytype',
            new lang_string('activitytype', 'local_recompletion'),
            $this->get_entity_name(),
            "{$modulealias}.name"
        ))
            ->add_joins($this->get_joins())
            ->set_options(self::SUPPORTED_ACTIVITIES);

        // Activitiy completion date filter.
        $filters[] = (new filter(
            date::class,
            'timemodified',
            new lang_string('activitycompletiondate', 'local_recompletion'),
            $this->get_entity_name(),
            "{$mainalias}.timemodified"
        ))
            ->add_joins($this->get_joins())
            ->set_limited_operators([
                date::DATE_ANY,
                date::DATE_RANGE,
                date::DATE_PREVIOUS,
                date::DATE_CURRENT,
            ]);

            return $filters;
    }
}
