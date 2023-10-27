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
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_renderer;
use html_writer;
use lang_string;

/**
 * Report builder entity for archived h5pactivity attempts.
 *
 * @package    local_recompletion
 * @author     Dmitrii Metelkin <dmitriim@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class h5pactivity_attempts extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return string[] Array of $tablename => $alias
     */
    protected function get_default_table_aliases(): array {
        return [
            'local_recompletion_h5p' => 'h5p'
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:local_recompletion_h5p', 'local_recompletion');
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

        return $this;
    }

    /**
     * Returns list of available columns.
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $alias = $this->get_table_alias('local_recompletion_h5p');

        $columns[] = (new column(
            'h5pactivityid',
            new lang_string('pluginname', 'h5pactivity'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$alias}.h5pactivityid, {$alias}.course")
            ->set_is_sortable(true)
            ->add_callback(static function($value, $row): string {
                global $PAGE;

                $renderer = new core_renderer($PAGE, RENDERER_TARGET_GENERAL);
                $modinfo = get_fast_modinfo($row->course);

                if (!empty($modinfo) && !empty($modinfo->get_instances_of('h5pactivity')
                        && !empty($modinfo->get_instances_of('h5pactivity')[$row->h5pactivityid]))) {
                    $cm = $modinfo->get_instances_of('h5pactivity')[$row->h5pactivityid];
                    $modulename = get_string('modulename', $cm->modname);
                    $activityicon = $renderer->pix_icon('monologo', $modulename, $cm->modname, ['class' => 'icon']);

                    return $activityicon . html_writer::link($cm->url, format_string($cm->name), []);
                } else {
                    return (string) $row->h5pactivityid;
                }
            });

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
            'rawscore',
            new lang_string('score', 'h5pactivity'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.rawscore")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'maxscore',
            new lang_string('maxscore', 'h5pactivity'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.maxscore")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'duration',
            new lang_string('duration', 'h5pactivity'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.duration")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'completion',
            new lang_string('completion', 'h5pactivity'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.completion")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'success',
            new lang_string('outcome', 'h5pactivity'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.success")
            ->set_is_sortable(true)
            ->add_callback(static function($success): string {
                if ($success === null) {
                    return get_string('attempt_success_unknown', 'mod_h5pactivity');
                } else if ($success) {
                    return get_string('attempt_success_pass', 'mod_h5pactivity');
                } else {
                    return get_string('attempt_success_fail', 'mod_h5pactivity');
                }
            });

        $columns[] = (new column(
            'timecreated',
            new lang_string('timecreated', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$alias}.timecreated")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);

        $columns[] = (new column(
            'timemodified',
            new lang_string('timemodified', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$alias}.timemodified")
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
        $alias = $this->get_table_alias('local_recompletion_h5p');

        // Time completed filter.
        $filters[] = (new filter(
            select::class,
            'success',
            new lang_string('outcome', 'local_recompletion'),
            $this->get_entity_name(),
            "{$alias}.success"
        ))
            ->add_joins($this->get_joins())
            ->set_options([
                0 => get_string('attempt_success_fail', 'mod_h5pactivity'),
            ]);

        return $filters;
    }
}
