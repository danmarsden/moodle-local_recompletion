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
 * Upgrade code for local_recompletion
 *
 * @package    local_recompletion
 * @author     Dan Marsden http://danmarsden.com
 * @copyright  2018 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * upgrade this recompletion
 * @param int $oldversion The old version of the assign module
 * @return bool
 */
function xmldb_local_recompletion_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2018011600) {

        // Define table local_recompletion_cc to be created.
        $table = new xmldb_table('local_recompletion_cc');

        // Adding fields to table local_recompletion_cc.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeenrolled', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timestarted', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecompleted', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('reaggregate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_cc.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_recompletion_cc.
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));
        $table->add_index('timecompleted', XMLDB_INDEX_NOTUNIQUE, array('timecompleted'));

        // Conditionally launch create table for local_recompletion_cc.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_cc_cc to be created.
        $table = new xmldb_table('local_recompletion_cc_cc');

        // Adding fields to table local_recompletion_cc_cc.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('criteriaid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('gradefinal', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null);
        $table->add_field('unenroled', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecompleted', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table local_recompletion_cc_cc.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_recompletion_cc_cc.
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));
        $table->add_index('criteriaid', XMLDB_INDEX_NOTUNIQUE, array('criteriaid'));
        $table->add_index('timecompleted', XMLDB_INDEX_NOTUNIQUE, array('timecompleted'));

        // Conditionally launch create table for local_recompletion_cc_cc.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_cmc to be created.
        $table = new xmldb_table('local_recompletion_cmc');

        // Adding fields to table local_recompletion_cmc.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('coursemoduleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('completionstate', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('viewed', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('overrideby', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_recompletion_cmc.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_recompletion_cmc.
        $table->add_index('coursemoduleid', XMLDB_INDEX_NOTUNIQUE, array('coursemoduleid'));

        // Conditionally launch create table for local_recompletion_cmc.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2018011600, 'local', 'recompletion');
    }
    if ($oldversion < 2018012300) {

        // Define table local_recompletion_qa to be created.
        $table = new xmldb_table('local_recompletion_qa');

        // Adding fields to table local_recompletion_qa.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('quiz', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('attempt', XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('uniqueid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('layout', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('currentpage', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('preview', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('state', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL, null, 'inprogress');
        $table->add_field('timestart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timefinish', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecheckstate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('sumgrades', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null);

        // Adding keys to table local_recompletion_qa.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('quiz', XMLDB_KEY_FOREIGN, array('quiz'), 'quiz', array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Adding indexes to table local_recompletion_qa.
        $table->add_index('state-timecheckstate', XMLDB_INDEX_NOTUNIQUE, array('state', 'timecheckstate'));

        // Conditionally launch create table for local_recompletion_qa.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_qg to be created.
        $table = new xmldb_table('local_recompletion_qg');

        // Adding fields to table local_recompletion_qg.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('quiz', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('grade', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_qg.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('quiz', XMLDB_KEY_FOREIGN, array('quiz'), 'quiz', array('id'));

        // Adding indexes to table local_recompletion_qg.
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch create table for local_recompletion_qg.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Define table local_recompletion_sst to be created.
        $table = new xmldb_table('local_recompletion_sst');

        // Adding fields to table local_recompletion_sst.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('scormid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('scoid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('attempt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('element', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_sst.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('scormid', XMLDB_KEY_FOREIGN, array('scormid'), 'scorm', array('id'));
        $table->add_key('scoid', XMLDB_KEY_FOREIGN, array('scoid'), 'scorm_scoes', array('id'));

        // Adding indexes to table local_recompletion_sst.
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        $table->add_index('element', XMLDB_INDEX_NOTUNIQUE, array('element'));

        // Conditionally launch create table for local_recompletion_sst.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2018012300, 'local', 'recompletion');
    }

    if ($oldversion < 2018071000) {
        $table = new xmldb_table('local_recompletion_sas');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        upgrade_plugin_savepoint(true, 2018071000, 'local', 'recompletion');
    }

    if ($oldversion < 2018071100) {

        // Define field course to be added to local_recompletion_cmc.
        $table = new xmldb_table('local_recompletion_cmc');
        $field = new xmldb_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timemodified');

        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('local_recompletion_qg');
        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('local_recompletion_sst');
        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // This table has "sumgrades" as the last field.
        $table = new xmldb_table('local_recompletion_qa');
        $field = new xmldb_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'sumgrades');
        // Conditionally launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2018071100, 'local', 'recompletion');
    }

    if ($oldversion < 2018071900) {

        // Define table local_recompletion_config to be created.
        $table = new xmldb_table('local_recompletion_config');

        // Adding fields to table local_recompletion_config.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_recompletion_config.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_recompletion_config.
        $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, ['course']);

        // Conditionally launch create table for local_recompletion_config.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2018071900, 'local', 'recompletion');
    }

    if ($oldversion < 2018071901) {
        // Convert old local_recompletion records to new structure.
        $recompletion = $DB->get_recordset('local_recompletion');
        $newrecords = array();
        foreach ($recompletion as $r) {
            $newrecords[] = array('course' => $r->course,
                'name' => 'enable',
                'value' => $r->enable);
            $newrecords[] = array('course' => $r->course,
                'name' => 'recompletionduration',
                'value' => $r->recompletionduration);
            $newrecords[] = array('course' => $r->course,
                'name' => 'deletegradedata',
                'value' => $r->deletegradedata);
            $newrecords[] = array('course' => $r->course,
                'name' => 'quizdata',
                'value' => $r->deletequizdata);
            $newrecords[] = array('course' => $r->course,
                'name' => 'deletescormdata',
                'value' => $r->deletescormdata);
            $newrecords[] = array('course' => $r->course,
                'name' => 'archivecompletiondata',
                'value' => $r->archivecompletiondata);
            $newrecords[] = array('course' => $r->course,
                'name' => 'archivequizdata',
                'value' => $r->archivequizdata);
            $newrecords[] = array('course' => $r->course,
                'name' => 'archivescormdata',
                'value' => $r->archivescormdata);
            $newrecords[] = array('course' => $r->course,
                'name' => 'recompletionemailenable',
                'value' => $r->recompletionemailenable);
            $newrecords[] = array('course' => $r->course,
                'name' => 'recompletionemailsubject',
                'value' => $r->recompletionemailsubject);
            $newrecords[] = array('course' => $r->course,
                'name' => 'recompletionemailbody',
                'value' => $r->recompletionemailbody);
        }
        $recompletion->close();
        foreach ($newrecords as $id => $rec) {
            if ($rec['value'] == null) {
                $newrecords[$id]['value'] = '';
            }
        }
        $DB->insert_records('local_recompletion_config', $newrecords);

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2018071901, 'local', 'recompletion');
    }

    if ($oldversion < 2018071902) {
        // Define table local_recompletion to be dropped.
        $table = new xmldb_table('local_recompletion');

        // Conditionally launch drop table for local_recompletion.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2018071902, 'local', 'recompletion');
    }

    return true;
}