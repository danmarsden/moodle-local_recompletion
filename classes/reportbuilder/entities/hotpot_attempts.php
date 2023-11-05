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
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_reportbuilder\local\filters\select;
use core_renderer;
use html_writer;
use lang_string;

/**
 * Report builder entity for archived hotpot attempts.
 *
 * @package    local_recompletion
 * @author     Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hotpot_attempts extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return string[] Array of $tablename => $alias
     */
    protected function get_default_table_aliases(): array {
        return [
            'local_recompletion_hpa' => 'hpa'
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:local_recompletion_hpa', 'local_recompletion');
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
        $alias = $this->get_table_alias('local_recompletion_hpa');

        $columns[] = (new column(
            'hotpotid',
            new lang_string('pluginname', 'hotpot'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$alias}.hotpotid, {$alias}.course")
            ->set_is_sortable(true)
            ->add_callback(static function($value, $row): string {
                global $PAGE;

                $renderer = new core_renderer($PAGE, RENDERER_TARGET_GENERAL);
                $modinfo = get_fast_modinfo($row->course);

                if (!empty($modinfo) && !empty($modinfo->get_instances_of('hotpot')
                        && !empty($modinfo->get_instances_of('hotpot')[$row->hotpotid]))) {
                    $cm = $modinfo->get_instances_of('hotpot')[$row->hotpotid];
                    $modulename = get_string('modulename', $cm->modname);
                    $activityicon = $renderer->pix_icon('monologo', $modulename, $cm->modname, ['class' => 'icon']);

                    return $activityicon . html_writer::link($cm->url, format_string($cm->name), []);
                } else {
                    return (string) $row->hotpotid;
                }
            });

        $columns[] = (new column(
            'starttime',
            new lang_string('starttime', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$alias}.starttime")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);

        $columns[] = (new column(
            'endtime',
            new lang_string('endtime', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$alias}.endtime")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);

        $columns[] = (new column(
            'score',
            new lang_string('score', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.score")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'penalties',
            new lang_string('penalties', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.penalties")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'attempt',
            new lang_string('attempt', 'h5pactivity'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.attempt")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'status',
            new lang_string('status', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.status")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'status',
            new lang_string('status', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.status")
            ->set_is_sortable(true)
            ->add_callback(static function($status): string {
                switch ($status) {
                    case 1:
                        return get_string('inprogress', 'local_recompletion');
                    case 2:
                        return get_string('timedout', 'local_recompletion');
                    case 3:
                        return get_string('abandoned', 'local_recompletion');
                    case 4:
                        return get_string('completed', 'local_recompletion');
                    default:
                        return $status;
                }
            });

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $alias = $this->get_table_alias('local_recompletion_hpa');

        // Time completed filter.
        $filters[] = (new filter(
            select::class,
            'status',
            new lang_string('status', 'local_recompletion'),
            $this->get_entity_name(),
            "{$alias}.status"
        ))
            ->add_joins($this->get_joins())
            ->set_options([
                1 => get_string('inprogress', 'local_recompletion'),
                2 => get_string('timedout', 'local_recompletion'),
                3 => get_string('abandoned', 'local_recompletion'),
                4 => get_string('completed', 'local_recompletion')
            ]);

        return $filters;
    }
}
