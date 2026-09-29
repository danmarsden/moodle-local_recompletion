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
use core_renderer;
use html_writer;
use lang_string;

/**
 * Report builder entity for archived assignment submissions.
 *
 * @package    local_recompletion
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submissions extends base {
    /**
     * Database tables that this entity uses.
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return [
            'local_recompletion_as',
        ];
    }

    /**
     * The default title for this entity.
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:local_recompletion_as', 'local_recompletion');
    }

    /**
     * Initialise.
     *
     * @return \core_reportbuilder\local\entities\base
     */
    public function initialise(): base {
        foreach ($this->get_all_columns() as $column) {
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
        $alias = $this->get_table_alias('local_recompletion_as');

        $columns[] = (new column(
            'assignment',
            new lang_string('pluginname', 'assign'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$alias}.assignment, {$alias}.course")
            ->set_is_sortable(true)
            ->add_callback(static function ($value, $row): string {
                global $PAGE;

                $renderer = new core_renderer($PAGE, RENDERER_TARGET_GENERAL);
                $modinfo = get_fast_modinfo($row->course);

                if (
                    !empty($modinfo) && !empty($modinfo->get_instances_of('assign')
                        && !empty($modinfo->get_instances_of('assign')[$row->assignment]))
                ) {
                    $cm = $modinfo->get_instances_of('assign')[$row->assignment];
                    $modulename = get_string('pluginname', 'assign');
                    $activityicon = $renderer->pix_icon('monologo', $modulename, $cm->modname, ['class' => 'icon']);

                    return $activityicon . html_writer::link($cm->url, format_string($cm->name), []);
                }

                return (string) $row->assignment;
            });

        $columns[] = (new column(
            'submissionstatus',
            new lang_string('assignsubmissionstatus', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.status")
            ->set_is_sortable(true)
            ->add_callback(static function (?string $status): string {
                if ($status === null || $status === '') {
                    return '';
                }

                return ucfirst($status);
            });

        $columns[] = (new column(
            'attemptnumber',
            new lang_string('assignattemptnumber', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.attemptnumber")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'timestarted',
            new lang_string('assignsubmissiontimestarted', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$alias}.timestarted")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);

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

        $columns[] = (new column(
            'latest',
            new lang_string('assignsubmissionlatest', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.latest")
            ->set_is_sortable(true);

        return $columns;
    }
}
