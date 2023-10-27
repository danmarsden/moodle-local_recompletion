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

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\course_selector;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_renderer;
use html_writer;
use lang_string;

/**
 * Report builder entity for activity completion archived records.
 *
 * @package    local_recompletion
 * @author     Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_modules_completion extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return string[] Array of $tablename => $alias
     */
    protected function get_default_table_aliases(): array {
        return [
            'local_recompletion_cmc' => 'cmc'
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:local_recompletion_cmc', 'local_recompletion');
    }

    /**
     * Initialise.
     *
     * @return \core_reportbuilder\local\entities\base
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        foreach ($this->get_all_filters() as $filter) {
            $this->add_filter($filter)->add_condition($filter);
        }

        return $this;
    }

    /**
     * Returns list of available columns.
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $completion = $this->get_table_alias('local_recompletion_cmc');

        // Completion state.
        $columns[] = (new column(
            'completionstate',
            new lang_string('status', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$completion}.completionstate")
            ->set_is_sortable(true)
            ->add_callback(static function($completionstate): string {
                $states = [
                    0 => get_string('notcompleted', 'completion'),
                    1 => get_string('completion-y', 'completion'),
                    2 => get_string('completion-pass', 'completion'),
                    3 => get_string('completion-fail', 'completion'),
                ];

                return $states[$completionstate] ?? $completionstate;
            });

        // Course module ID.
        $columns[] = (new column(
            'coursemodule',
            new lang_string('module', 'course'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$completion}.coursemoduleid, {$completion}.course")
            ->set_is_sortable(true)
            ->add_callback(static function($value, $row): string {
                global $PAGE;

                $renderer = new core_renderer($PAGE, RENDERER_TARGET_GENERAL);
                $modinfo = get_fast_modinfo($row->course);

                if (!empty($modinfo) && !empty($modinfo->get_cms()[$row->coursemoduleid])) {
                    $cm = $modinfo->get_cms()[$row->coursemoduleid];
                    $modulename = get_string('modulename', $cm->modname);
                    $activityicon = $renderer->pix_icon('monologo', $modulename, $cm->modname, ['class' => 'icon']);

                    return $activityicon . html_writer::link($cm->url, format_string($cm->name), []);
                } else {
                    return (string) $row->coursemoduleid;
                }
            });

        // Time started.
        $columns[] = (new column(
            'timemodified',
            new lang_string('timemodified', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$completion}.timemodified")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $coursecompletion = $this->get_table_alias('local_recompletion_cmc');

        // Time completed filter.
        $filters[] = (new filter(
            select::class,
            'completionstate',
            new lang_string('status', 'local_recompletion'),
            $this->get_entity_name(),
            "{$coursecompletion}.completionstate"
        ))
            ->add_joins($this->get_joins())
            ->set_options([
                0 => get_string('notcompleted', 'completion'),
                1 => get_string('completion-y', 'completion'),
                2 => get_string('completion-pass', 'completion'),
                3 => get_string('completion-fail', 'completion'),
            ]);

        // Custom course selector filter.
        $filters[] = (new filter(
            course_selector::class,
            'courseselector',
            new lang_string('courseselect', 'core_reportbuilder'),
            $this->get_entity_name(),
            "{$coursecompletion}.course"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }
}
