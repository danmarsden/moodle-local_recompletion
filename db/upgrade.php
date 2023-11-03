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

    if ($oldversion < 2020092200) {

        // Define table local_recompletion_ltia to be created.
        $table = new xmldb_table('local_recompletion_ltia');

        // Adding fields to table local_recompletion_ltia.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('toolid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lastgrade', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lastaccess', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_recompletion_ltia.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_recompletion_ltia.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2020092200, 'local', 'recompletion');
    }

    if ($oldversion < 2021091400) {
        // We renamed some site level config settings.
        set_config('archivequiz', get_config('archivequizdata', 'local_recompletion'), 'local_recompletion');
        set_config('archivescorm', get_config('archivescormdata', 'local_recompletion'), 'local_recompletion');

        // We also renamed some coursemodule level config items.
        $sql = "UPDATE {local_recompletion_config} SET name = 'assign' WHERE name = 'assigndata'";
        $DB->execute($sql);

        $sql = "UPDATE {local_recompletion_config} SET name = 'lti' WHERE name = 'ltigrade'";
        $DB->execute($sql);

        $sql = "UPDATE {local_recompletion_config} SET name = 'archivelti' WHERE name = 'archiveltidata'";
        $DB->execute($sql);

        $sql = "UPDATE {local_recompletion_config} SET name = 'quiz' WHERE name = 'quizdata'";
        $DB->execute($sql);

        $sql = "UPDATE {local_recompletion_config} SET name = 'archivequiz' WHERE name = 'archivequizdata'";
        $DB->execute($sql);

        $sql = "UPDATE {local_recompletion_config} SET name = 'scorm' WHERE name = 'scormdata'";
        $DB->execute($sql);

        $sql = "UPDATE {local_recompletion_config} SET name = 'archivescorm' WHERE name = 'archivescormdata'";
        $DB->execute($sql);

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2021091400, 'local', 'recompletion');
    }

    if ($oldversion < 2021100700) {

        // Define table local_recompletion_qr to be created.
        $table = new xmldb_table('local_recompletion_qr');

        // Adding fields to table local_recompletion_qr.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('originalresponseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('questionnaireid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('submitted', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('complete', XMLDB_TYPE_CHAR, '1', null, XMLDB_NOTNULL, null, 'n');
        $table->add_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_qr.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('questionnaireid', XMLDB_KEY_FOREIGN, ['questionnaireid'], 'questionnaire', ['id']);

        // Conditionally launch create table for local_recompletion_qr.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_qr_bool to be created.
        $table = new xmldb_table('local_recompletion_qr_bool');

        // Adding fields to table local_recompletion_qr_bool.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('response_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('question_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('choice_id', XMLDB_TYPE_CHAR, '1', null, XMLDB_NOTNULL, null, 'y');

        // Adding keys to table local_recompletion_qr_bool.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_recompletion_qr_bool.
        $table->add_index('response_question', XMLDB_INDEX_NOTUNIQUE, ['response_id', 'question_id']);

        // Conditionally launch create table for local_recompletion_qr_bool.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_qr_date to be created.
        $table = new xmldb_table('local_recompletion_qr_date');

        // Adding fields to table local_recompletion_qr_date.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('response_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('question_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('response', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table local_recompletion_qr_date.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_recompletion_qr_date.
        $table->add_index('response_question', XMLDB_INDEX_NOTUNIQUE, ['response_id', 'question_id']);

        // Conditionally launch create table for local_recompletion_qr_date.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_qr_m to be created.
        $table = new xmldb_table('local_recompletion_qr_m');

        // Adding fields to table local_recompletion_qr_m.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('response_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('question_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('choice_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_qr_m.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_recompletion_qr_m.
        $table->add_index('response_question', XMLDB_INDEX_NOTUNIQUE, ['response_id', 'question_id', 'choice_id']);

        // Conditionally launch create table for local_recompletion_qr_m.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_qr_other to be created.
        $table = new xmldb_table('local_recompletion_qr_other');

        // Adding fields to table local_recompletion_qr_other.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('response_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('question_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('choice_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('response', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table local_recompletion_qr_other.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_recompletion_qr_other.
        $table->add_index('response_question', XMLDB_INDEX_NOTUNIQUE, ['response_id', 'question_id', 'choice_id']);

        // Conditionally launch create table for local_recompletion_qr_other.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_qr_rank to be created.
        $table = new xmldb_table('local_recompletion_qr_rank');

        // Adding fields to table local_recompletion_qr_rank.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('response_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('question_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('choice_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('rankvalue', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_qr_rank.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_recompletion_qr_rank.
        $table->add_index('response_question', XMLDB_INDEX_NOTUNIQUE, ['response_id', 'question_id', 'choice_id']);

        // Conditionally launch create table for local_recompletion_qr_rank.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_qr_single to be created.
        $table = new xmldb_table('local_recompletion_qr_single');

        // Adding fields to table local_recompletion_qr_single.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('response_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('question_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('choice_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_qr_single.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_recompletion_qr_single.
        $table->add_index('response_question', XMLDB_INDEX_NOTUNIQUE, ['response_id', 'question_id']);

        // Conditionally launch create table for local_recompletion_qr_single.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_qr_text to be created.
        $table = new xmldb_table('local_recompletion_qr_text');

        // Adding fields to table local_recompletion_qr_text.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('response_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('question_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('response', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table local_recompletion_qr_text.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_recompletion_qr_text.
        $table->add_index('response_question', XMLDB_INDEX_NOTUNIQUE, ['response_id', 'question_id']);

        // Conditionally launch create table for local_recompletion_qr_text.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2021100700, 'local', 'recompletion');
    }

    if ($oldversion < 2021112400) {
        // Correct default value for couse on the local_recompletion_qr table to be zero.
        $table = new xmldb_table('local_recompletion_qr');
        $field = new xmldb_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'userid');
        $dbman->change_field_default($table, $field);
        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2021112400, 'local', 'recompletion');
    }

    if ($oldversion < 2023022100) {

        // Define field id to be added to local_recompletion_cha.
        $table = new xmldb_table('local_recompletion_cha');

        // Adding fields to table local_recompletion_cha.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('choiceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'choiceid');
        $table->add_field('optionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'userid');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'optionid');
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timemodified');

        // Adding keys to table local_recompletion_cha.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('choiceid', XMLDB_KEY_FOREIGN, ['choiceid'], 'choice', ['id']);

        // Adding indexes to table local_recompletion_cha.
        $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, ['course']);

        // Conditionally launch create table for local_recompletion_cha.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2023022100, 'local', 'recompletion');
    }

    if ($oldversion < 2023040300) {

        // Update the format of older recompletionemailbody field data to html.
        $recompletionconfig = $DB->get_recordset('local_recompletion_config', array('name' => 'recompletionemailbody'));
        foreach ($recompletionconfig as $record) {
            $message = $record->value;
            if (strpos($message, '<') === false) {
                // Plain text only.
                $messagehtml = text_to_html($message, null, false, true);
            } else {
                // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                $messagehtml = format_text($message, FORMAT_MOODLE, array('para' => false,
                    'newlines' => true, 'filter' => true));
            }
            // Update record with html formatted text.
            $record->value = $messagehtml;
            $DB->update_record('local_recompletion_config', $record);
            // Add format record for editor element.
            $record->name = 'recompletionemailbody_format';
            $record->value = '1';
            $DB->insert_record('local_recompletion_config', $record);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2023040300, 'local', 'recompletion');
    }

    if ($oldversion < 2023040301) {

        // Define table local_recompletion_ccert_is to be created.
        $table = new xmldb_table('local_recompletion_ccert_is');

        // Adding fields to table local_recompletion_ccert_is.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        $table->add_field('customcertid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'userid');
        $table->add_field('code', XMLDB_TYPE_CHAR, '40', null, null, null, null, 'customcertid');
        $table->add_field('emailed', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'code');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'emailed');
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');

        // Adding keys to table local_recompletion_ccert_is.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('customcert', XMLDB_KEY_FOREIGN, ['customcertid'], 'customcert', ['id']);

        // Adding indexes to table local_recompletion_ccert_is.
        $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, ['course']);

        // Conditionally launch create table for local_recompletion_ccert_is.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2023040301, 'local', 'recompletion');
    }

    if ($oldversion < 2023092022) {

        // Define table local_recompletion_cmv to be created.
        $table = new xmldb_table('local_recompletion_cmv');

        // Adding fields to table local_recompletion_cmv.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('coursemoduleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_cmv.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_recompletion_cmv.
        $table->add_index('coursemoduleid', XMLDB_INDEX_NOTUNIQUE, ['coursemoduleid']);
        $table->add_index('userid-coursemoduleid', XMLDB_INDEX_UNIQUE, ['userid', 'coursemoduleid']);

        // Conditionally launch create table for local_recompletion_cmv.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2023092022, 'local', 'recompletion');
    }

    if ($oldversion < 2023092801) {
        // Enable setting moved to a select option.
        // Multiple sql calls for best cross db compatible option.
        $sql = "UPDATE {local_recompletion_config} SET name = 'recompletiontype' WHERE name = 'enable' and value = '1'";
        $DB->execute($sql);
        $sql = "UPDATE {local_recompletion_config} SET value = 'period' WHERE name = 'recompletiontype' and value = '1'";
        $DB->execute($sql);
        // Clean up old empty enabled/disabled config settings.
        $DB->delete_records('local_recompletion_config', ['name' => 'enable']);

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2023092801, 'local', 'recompletion');

    }

    if ($oldversion < 2023101800) {
        // Define table local_recompletion_hvp to be created.
        $table = new xmldb_table('local_recompletion_hvp');

        // Adding fields to table local_recompletion_hvp.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('hvp_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sub_content_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('data_id', XMLDB_TYPE_CHAR, '127', null, null, null, null);
        $table->add_field('data', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('preloaded', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('delete_on_content_change', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_hvp.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_recompletion_hvp.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2023101800, 'local', 'recompletion');
    }

    if ($oldversion < 2023101900) {

        // Define table local_recompletion_h5p to be created.
        $table = new xmldb_table('local_recompletion_h5p');

        // Adding fields to table local_recompletion_h5p.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('originalattemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('h5pactivityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('attempt', XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('rawscore', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('maxscore', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('scaled', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('completion', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('success', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_h5p.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_recompletion_h5p.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_h5pr to be created.
        $table = new xmldb_table('local_recompletion_h5pr');

        // Adding fields to table local_recompletion_h5pr.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('attemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('subcontent', XMLDB_TYPE_CHAR, '128', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('interactiontype', XMLDB_TYPE_CHAR, '128', null, null, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('correctpattern', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('response', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('additionals', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('rawscore', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('maxscore', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('completion', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('success', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_h5pr.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_recompletion_h5pr.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2023101900, 'local', 'recompletion');
    }

    if ($oldversion < 2023110301) {

        // Define table local_recompletion_la to be created.
        $table = new xmldb_table('local_recompletion_la');

        // Adding fields to table local_recompletion_la.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('lessonid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('pageid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('answerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('retry', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('correct', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('useranswer', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timeseen', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_la.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_recompletion_la.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_lg to be created.
        $table = new xmldb_table('local_recompletion_lg');

        // Adding fields to table local_recompletion_lg.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('lessonid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('grade', XMLDB_TYPE_FLOAT, null, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('late', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('completed', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_lg.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_recompletion_lg.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_lt to be created.
        $table = new xmldb_table('local_recompletion_lt');

        // Adding fields to table local_recompletion_lt.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('lessonid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('lessontime', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('completed', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
        $table->add_field('timemodifiedoffline', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_lt.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_recompletion_lt.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_lb to be created.
        $table = new xmldb_table('local_recompletion_lb');

        // Adding fields to table local_recompletion_lb.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('lessonid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('pageid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('retry', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('flag', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeseen', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('nextpageid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_lb.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_recompletion_lb.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_recompletion_lo to be created.
        $table = new xmldb_table('local_recompletion_lo');

        // Adding fields to table local_recompletion_lo.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('lessonid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('available', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('deadline', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timelimit', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('review', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('maxattempts', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('retake', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('password', XMLDB_TYPE_CHAR, '32', null, null, null, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_lo.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_recompletion_lo.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2023110301, 'local', 'recompletion');
    }

    return true;
}
