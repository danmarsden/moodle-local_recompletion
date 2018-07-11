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
 * local_recompletion Data provider.
 *
 * @package    local_recompletion
 * @author     Dan Marsden <dan@danmarsden.com>
 * @copyright  2018 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recompletion\privacy;
defined('MOODLE_INTERNAL') || die();

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\{writer, transform, helper, contextlist, approved_contextlist};
use stdClass;

/**
 * Data provider for local_recompletion.
 *
 * @author     Dan Marsden <dan@danmarsden.com>
 * @copyright  2018 Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider implements
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\metadata\provider
{

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table('local_recompletion_cc', [
            'userid' => 'privacy:metadata:userid',
            'course' => 'privacy:metadata:course',
            'timeenrolled' => 'privacy:metadata:timeenrolled',
            'timestarted' => 'privacy:metadata:timestarted',
            'timecompleted' => 'privacy:metadata:timecompleted',
            'reaggregate' => 'privacy:metadata:reaggregate'
        ], 'privacy:metadata:local_recompletion_cc');

        $collection->add_database_table('local_recompletion_cmc', [
            'userid' => 'privacy:metadata:userid',
            'coursemoduleid' => 'privacy:metadata:coursemoduleid',
            'completionstate' => 'privacy:metadata:completionstate',
            'viewed' => 'privacy:metadata:viewed',
            'overrideby' => 'privacy:metadata:overrideby',
            'timemodified' => 'privacy:metadata:timemodified'
        ], 'privacy:metadata:local_recompletion_cmc');

        $collection->add_database_table('local_recompletion_cc_cc', [
            'userid' => 'privacy:metadata:userid',
            'course' => 'privacy:metadata:course',
            'gradefinal' => 'privacy:metadata:gradefinal',
            'unenroled' => 'privacy:metadata:unenroled',
            'timecompleted' => 'privacy:metadata:timecompleted'
        ], 'privacy:metadata:local_recompletion_cc_cc');

        $collection->add_database_table('local_recompletion_qa', [
            'attempt'               => 'privacy:metadata:quiz_attempts:attempt',
            'currentpage'           => 'privacy:metadata:quiz_attempts:currentpage',
            'preview'               => 'privacy:metadata:quiz_attempts:preview',
            'state'                 => 'privacy:metadata:quiz_attempts:state',
            'timestart'             => 'privacy:metadata:quiz_attempts:timestart',
            'timefinish'            => 'privacy:metadata:quiz_attempts:timefinish',
            'timemodified'          => 'privacy:metadata:quiz_attempts:timemodified',
            'timemodifiedoffline'   => 'privacy:metadata:quiz_attempts:timemodifiedoffline',
            'timecheckstate'        => 'privacy:metadata:quiz_attempts:timecheckstate',
            'sumgrades'             => 'privacy:metadata:quiz_attempts:sumgrades',
        ], 'privacy:metadata:quiz_attempts');

        $collection->add_database_table('local_recompletion_qg', [
            'quiz'                  => 'privacy:metadata:quiz_grades:quiz',
            'userid'                => 'privacy:metadata:quiz_grades:userid',
            'grade'                 => 'privacy:metadata:quiz_grades:grade',
            'timemodified'          => 'privacy:metadata:quiz_grades:timemodified',
        ], 'privacy:metadata:quiz_grades');

        $collection->add_database_table('local_recompletion_sst', [
            'userid' => 'privacy:metadata:userid',
            'attempt' => 'privacy:metadata:attempt',
            'element' => 'privacy:metadata:scoes_track:element',
            'value' => 'privacy:metadata:scoes_track:value',
            'timemodified' => 'privacy:metadata:timemodified'
        ], 'privacy:metadata:scorm_scoes_track');

        return $collection;
    }

    public static function export_user_data(approved_contextlist $contextlist) {
        // TODO: Implement export_user_data() method.
    }


    public static function delete_data_for_all_users_in_context(\context $context) {
        // TODO: Implement delete_data_for_all_users_in_context() method.
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        // TODO: Implement delete_data_for_user() method.
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        // TODO: Implement get_contexts_for_userid() method.
    }

}