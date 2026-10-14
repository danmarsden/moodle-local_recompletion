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
 * Report builder entity for archived assignment submission comments.
 *
 * @package    local_recompletion
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_comments extends base {
    /**
     * Database tables that this entity uses.
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return [
            'local_recompletion_asc',
            'local_recompletion_as',
        ];
    }

    /**
     * The default title for this entity.
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:local_recompletion_asc', 'local_recompletion');
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
        $commentsalias = $this->get_table_alias('local_recompletion_asc');
        $submissionsalias = $this->get_table_alias('local_recompletion_as');

        $columns[] = (new column(
            'assignment',
            new lang_string('pluginname', 'assign'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$submissionsalias}.assignment, {$submissionsalias}.course")
            ->set_is_sortable(true)
            ->add_callback(static function ($value, $row): string {
                global $PAGE;

                $assignmentid = (int) ($row->assignment ?? 0);
                $courseid = (int) ($row->course ?? 0);
                if (!$assignmentid || !$courseid) {
                    return (string) $assignmentid;
                }

                $renderer = new core_renderer($PAGE, RENDERER_TARGET_GENERAL);
                $modinfo = get_fast_modinfo($courseid);
                $instances = $modinfo->get_instances_of('assign');

                if (!empty($instances[$assignmentid])) {
                    $cm = $instances[$assignmentid];
                    $activityicon = $renderer->pix_icon(
                        'monologo',
                        get_string('pluginname', 'assign'),
                        $cm->modname,
                        ['class' => 'icon']
                    );

                    return $activityicon . html_writer::link($cm->url, format_string($cm->name), []);
                }

                return (string) $assignmentid;
            });

        $columns[] = (new column(
            'attemptnumber',
            new lang_string('assignattemptnumber', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$submissionsalias}.attemptnumber")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'content',
            new lang_string('assignsubmissioncontent', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$commentsalias}.content")
            ->set_is_sortable(false);

        $columns[] = (new column(
            'format',
            new lang_string('assignsubmissioncommentformat', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$commentsalias}.format")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'timecreated',
            new lang_string('timecreated', 'core_reportbuilder'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$commentsalias}.timecreated")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);

        $columns[] = (new column(
            'component',
            new lang_string('assignsubmissioncomponent', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$commentsalias}.component")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'commentarea',
            new lang_string('assignsubmissioncommentarea', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$commentsalias}.commentarea")
            ->set_is_sortable(true);

        return $columns;
    }
}
