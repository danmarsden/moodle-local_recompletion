<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_recompletion\plugins;

/**
 * Tests for mod_scorm.
 *
 * @package    local_recompletion
 * @copyright  2026 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \local_recompletion\plugins\mod_scorm
 */
final class mod_scorm_test extends \advanced_testcase {
    /**
     * Verify archived SCORM tracks reference archived attempt rows.
     */
    public function test_reset_archives_scorm_tracks_linked_to_archived_attempts(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        $scormid = $DB->insert_record('scorm', (object) [
            'course' => $course->id,
            'name' => 'SCORM activity',
            'reference' => 'dummy.zip',
            'intro' => '',
            'introformat' => FORMAT_HTML,
            'version' => 'SCORM_1.2',
            'md5hash' => str_repeat('a', 32),
            'options' => '',
        ]);

        $scoid = $DB->insert_record('scorm_scoes', (object) [
            'scorm' => $scormid,
            'manifest' => 'mani',
            'organization' => 'org',
            'parent' => '/',
            'identifier' => 'item-1',
            'launch' => 'index.html',
            'scormtype' => 'sco',
            'title' => 'SCO 1',
            'sortorder' => 1,
        ]);

        $elementid = $DB->insert_record('scorm_element', (object) [
            'element' => 'cmi.core.lesson_status',
        ]);

        // Pre-seed archive attempts so new archived id differs from live attempt id.
        $DB->insert_record('local_recompletion_sa', (object) [
            'userid' => $user->id,
            'scormid' => $scormid,
            'attempt' => 99,
            'courseid' => $course->id,
        ]);

        $liveattemptid = $DB->insert_record('scorm_attempt', (object) [
            'userid' => $user->id,
            'scormid' => $scormid,
            'attempt' => 2,
        ]);

        $DB->insert_record('scorm_scoes_value', (object) [
            'scoid' => $scoid,
            'attemptid' => $liveattemptid,
            'elementid' => $elementid,
            'value' => 'completed',
            'timemodified' => time(),
        ]);

        mod_scorm::reset($user->id, $course, (object) [
            'scorm' => LOCAL_RECOMPLETION_DELETE,
            'archivescorm' => 1,
        ]);

        $archivedattempt = $DB->get_record('local_recompletion_sa', [
            'userid' => $user->id,
            'scormid' => $scormid,
            'attempt' => 2,
            'courseid' => $course->id,
        ], '*', MUST_EXIST);

        $archivedtrack = $DB->get_record('local_recompletion_ssv', [
            'courseid' => $course->id,
        ], '*', MUST_EXIST);

        $this->assertEquals($archivedattempt->id, $archivedtrack->attemptid);
        $this->assertNotEquals($liveattemptid, $archivedtrack->attemptid);

        $this->assertFalse($DB->record_exists('scorm_attempt', ['id' => $liveattemptid]));
        $this->assertFalse($DB->record_exists('scorm_scoes_value', ['attemptid' => $liveattemptid]));
    }
}
