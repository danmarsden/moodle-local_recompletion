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
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_renderer;
use html_writer;
use lang_string;
use moodle_url;
use stdClass;

/**
 * Report builder entity for archived assignment files.
 *
 * @package    local_recompletion
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class archived_assign_files extends base {
    /**
     * Database tables that this entity uses.
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return [
            'files',
            'local_recompletion_as',
            'local_recompletion_ag',
        ];
    }

    /**
     * The default title for this entity.
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:local_recompletion_af', 'local_recompletion');
    }

    /**
     * Initialise the entity.
     *
     * @return base
     */
    public function initialise(): base {
        foreach ($this->get_all_columns() as $column) {
            $this->add_column($column);
        }

        foreach ($this->get_all_filters() as $filter) {
            $this->add_filter($filter)->add_condition($filter);
        }

        return $this;
    }

    /**
     * Returns list of available filters.
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $filesalias = $this->get_table_alias('files');

        $filters[] = (new filter(
            text::class,
            'component',
            new lang_string('plugin', 'core'),
            $this->get_entity_name(),
            "{$filesalias}.component"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }

    /**
     * Returns list of available columns.
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $filesalias = $this->get_table_alias('files');
        $submissionsalias = $this->get_table_alias('local_recompletion_as');
        $gradesalias = $this->get_table_alias('local_recompletion_ag');

        $columns[] = (new column(
            'assignment',
            new lang_string('pluginname', 'assign'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("COALESCE({$submissionsalias}.assignment, {$gradesalias}.assignment)", 'assignment')
            ->add_field("COALESCE({$submissionsalias}.course, {$gradesalias}.course)", 'course')
            ->set_is_sortable(true)
            ->add_callback(static function ($value, stdClass $row): string {
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
            'filearea',
            new lang_string('pluginarea'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$filesalias}.filearea")
            ->set_is_sortable(true)
            ->add_callback(static function (?string $filearea): string {
                $areas = [
                    'submission_files' => get_string('archivedassignsubmissionfiles', 'local_recompletion'),
                    'feedback_files' => get_string('archivedassignfeedbackfiles', 'local_recompletion'),
                    'download' => get_string('archivedassignfeedbackpdf', 'local_recompletion'),
                    'readonlypages' => get_string('archivedassignfeedbackpdfpages', 'local_recompletion'),
                ];

                return $areas[$filearea] ?? (string) $filearea;
            });

        $columns[] = (new column(
            'filename',
            new lang_string('filename', 'core_repository'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields(
                "{$filesalias}.contextid, {$filesalias}.component, {$filesalias}.filearea, "
                . "{$filesalias}.itemid, {$filesalias}.filepath, {$filesalias}.filename"
            )
            ->set_is_sortable(true)
            ->add_callback(static function (?string $filename, stdClass $fileinfo): string {
                if ($filename === null || $filename === '.') {
                    return '';
                }

                $url = moodle_url::make_pluginfile_url(
                    $fileinfo->contextid,
                    $fileinfo->component,
                    $fileinfo->filearea,
                    $fileinfo->itemid,
                    $fileinfo->filepath,
                    $fileinfo->filename,
                    true
                );

                return html_writer::link($url, s($filename));
            });

        $columns[] = (new column(
            'filesize',
            new lang_string('size'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$filesalias}.filesize")
            ->set_is_sortable(true)
            ->add_callback(static function ($filesize, stdClass $fileinfo): string {
                if ($fileinfo->filesize === null) {
                    return '';
                }

                return display_size($fileinfo->filesize);
            });

        $columns[] = (new column(
            'attemptnumber',
            new lang_string('assignattemptnumber', 'local_recompletion'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("COALESCE({$submissionsalias}.attemptnumber, {$gradesalias}.attemptnumber)", 'attemptnumber')
            ->set_is_sortable(true);

        $columns[] = (new column(
            'timecreated',
            new lang_string('timecreated', 'core_reportbuilder'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$filesalias}.timecreated")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);

        return $columns;
    }
}
