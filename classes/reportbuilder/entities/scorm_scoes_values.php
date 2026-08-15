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
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_renderer;
use html_writer;
use lang_string;

/**
 * Report builder entity for archived SCORM tracking values.
 *
 * @package    local_recompletion
 * @author     Dan Marsden
 * @copyright  Copyright Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class scorm_scoes_values extends base {
    /**
     * Database tables that this entity uses.
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return [
            'local_recompletion_ssv',
            'local_recompletion_sa',
        ];
    }

    /**
     * The default title for this entity.
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:local_recompletion_ssv', 'local_recompletion');
    }

    /**
     * Initialise.
     *
     * @return base
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
        $ssvalias = $this->get_table_alias('local_recompletion_ssv');
        $saalias = $this->get_table_alias('local_recompletion_sa');

        $columns[] = (new column(
            'scormid',
            new lang_string('pluginname', 'scorm'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$saalias}.scormid, {$ssvalias}.courseid")
            ->set_is_sortable(true)
            ->add_callback(static function ($value, $row): string {
                global $PAGE;

                $renderer = new core_renderer($PAGE, RENDERER_TARGET_GENERAL);
                $modinfo = get_fast_modinfo($row->courseid);

                if (
                    !empty($modinfo) && !empty($modinfo->get_instances_of('scorm')
                        && !empty($modinfo->get_instances_of('scorm')[$row->scormid]))
                ) {
                    $cm = $modinfo->get_instances_of('scorm')[$row->scormid];
                    $modulename = get_string('modulename', $cm->modname);
                    $activityicon = $renderer->pix_icon('monologo', $modulename, $cm->modname, ['class' => 'icon']);

                    return $activityicon . html_writer::link($cm->url, format_string($cm->name), []);
                } else {
                    return (string) $row->scormid;
                }
            });

        $columns[] = (new column(
            'attempt',
            new lang_string('attempt', 'scorm'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$saalias}.attempt")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'scoid',
            new lang_string('scoid', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$ssvalias}.scoid")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'elementid',
            new lang_string('elementid', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$ssvalias}.elementid")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'value',
            new lang_string('value', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$ssvalias}.value")
            ->set_is_sortable(false);

        $columns[] = (new column(
            'timemodified',
            new lang_string('timemodified', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$ssvalias}.timemodified")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);

        return $columns;
    }

    /**
     * Return list of all available filters.
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $ssvalias = $this->get_table_alias('local_recompletion_ssv');
        $saalias = $this->get_table_alias('local_recompletion_sa');

        $filters[] = (new filter(
            number::class,
            'scormid',
            new lang_string('pluginname', 'scorm'),
            $this->get_entity_name(),
            "{$saalias}.scormid"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'attempt',
            new lang_string('attempt', 'scorm'),
            $this->get_entity_name(),
            "{$saalias}.attempt"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'scoid',
            new lang_string('scoid', 'local_recompletion'),
            $this->get_entity_name(),
            "{$ssvalias}.scoid"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'elementid',
            new lang_string('elementid', 'local_recompletion'),
            $this->get_entity_name(),
            "{$ssvalias}.elementid"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'value',
            new lang_string('value', 'local_recompletion'),
            $this->get_entity_name(),
            "{$ssvalias}.value"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timemodified',
            new lang_string('timemodified', 'core_reportbuilder'),
            $this->get_entity_name(),
            "{$ssvalias}.timemodified"
        ))
            ->add_joins($this->get_joins())
            ->set_limited_operators([
                date::DATE_ANY,
                date::DATE_NOT_EMPTY,
                date::DATE_EMPTY,
                date::DATE_RANGE,
                date::DATE_LAST,
                date::DATE_CURRENT,
            ]);

        return $filters;
    }
}
