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
        // Add additional fields to local_recompletion table.
        // Define field archivecompletiondata to be added to local_recompletion table.
        $table = new xmldb_table('local_recompletion');
        $field = new xmldb_field('archivecompletiondata', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'recompletionduration');

        // Conditionally launch add field archivecompletiondata.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field deletegradedata to be added to local_recompletion table.
        $table = new xmldb_table('local_recompletion');
        $field = new xmldb_field('deletegradedata', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'archivecompletiondata');

        // Conditionally launch add field deletegradedata.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field archivegradedata to be added to local_recompletion table.
        $table = new xmldb_table('local_recompletion');
        $field = new xmldb_field('archivegradedata', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'deletegradedata');

        // Conditionally launch add field archivegradedata.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field deletequizdata to be added to local_recompletion table.
        $table = new xmldb_table('local_recompletion');
        $field = new xmldb_field('deletequizdata', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'archivegradedata');

        // Conditionally launch add field deletequizdata.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field archivequizdata to be added to local_recompletion table.
        $table = new xmldb_table('local_recompletion');
        $field = new xmldb_field('archivequizdata', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'deletequizdata');

        // Conditionally launch add field archivequizdata.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field deletescormdata to be added to local_recompletion table.
        $table = new xmldb_table('local_recompletion');
        $field = new xmldb_field('deletescormdata', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'archivequizdata');

        // Conditionally launch add field deletescormdata.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field archivescormdata to be added to local_recompletion table.
        $table = new xmldb_table('local_recompletion');
        $field = new xmldb_field('archivescormdata', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'deletescormdata');

        // Conditionally launch add field archivescormdata.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field recompletionemailenabled to be added to local_recompletion table.
        $table = new xmldb_table('local_recompletion');
        $field = new xmldb_field('recompletionemailenable', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'archivescormdata');

        // Conditionally launch add field recompletionemailenabled.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field recompletionemailheader to be added to local_recompletion table.
        $table = new xmldb_table('local_recompletion');
        $field = new xmldb_field('recompletionemailheader', XMLDB_TYPE_TEXT, null, null, null, null, null, 'recompletionemailenabled');

        // Conditionally launch add field recompletionemailheader.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field recompletionemailbody to be added to local_recompletion table.
        $table = new xmldb_table('local_recompletion');
        $field = new xmldb_field('recompletionemailbody', XMLDB_TYPE_TEXT, null, null, null, null, null, 'recompletionemailheader');

        // Conditionally launch add field recompletionemailbody.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define table local_recompletion_gg to be created.
        $table = new xmldb_table('local_recompletion_gg');

        // Adding fields to table local_recompletion_gg.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('rawgrade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null);
        $table->add_field('rawgrademax', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '100');
        $table->add_field('rawgrademin', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('rawscaleid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('finalgrade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null);
        $table->add_field('hidden', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('locked', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('locktime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('exported', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('overridden', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('excluded', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('feedback', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('feedbackformat', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('information', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('informationformat', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('aggregationstatus', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'unknown');
        $table->add_field('aggregationweight', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null);

        // Adding keys to table local_recompletion_gg.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('itemid', XMLDB_KEY_FOREIGN, array('itemid'), 'grade_items', array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $table->add_key('rawscaleid', XMLDB_KEY_FOREIGN, array('rawscaleid'), 'scale', array('id'));
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, array('usermodified'), 'user', array('id'));

        // Adding indexes to table local_recompletion_gg.
        $table->add_index('locked-locktime', XMLDB_INDEX_NOTUNIQUE, array('locked', 'locktime'));

        // Conditionally launch create table for local_recompletion_gg.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

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
        // Define table local_recompletion_sas to be created.
        $table = new xmldb_table('local_recompletion_sas');

        // Adding fields to table local_recompletion_sas.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('scormid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('hacpsession', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('scoid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('scormmode', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('scormstatus', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('attempt', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('lessonstatus', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('sessiontime', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_recompletion_sas.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('scormid', XMLDB_KEY_FOREIGN, array('scormid'), 'scorm', array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Conditionally launch create table for local_recompletion_sas.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2018012300, 'local', 'recompletion');
    }
}