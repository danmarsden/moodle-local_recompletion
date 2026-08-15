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

/**
 * Web service for local recompletion.
 *
 * @package    local_recompletion
 * @author     Noémie Ariste <noemie.ariste@catalyst.net.nz>
 * @copyright  2024 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = [

    'local_recompletion_reset_course' => [
        'classname'     => 'local_recompletion\\external\\reset_course',
        'methodname'    => 'execute',
        'description'   => 'Reset course completion for a given course and user.',
        'type'          => 'write',
        'capabilities'  => 'local/recompletion:resetcompletion',
    ],
    'local_recompletion_set_course_completion_date' => [
        'classname'     => 'local_recompletion\\external\\set_course_completion_date',
        'methodname'    => 'execute',
        'description'   => 'Set course completion date for a given course and user.',
        'type'          => 'write',
        'capabilities'  => 'local/recompletion:resetcompletion',
    ],
];
