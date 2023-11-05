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
 * Tests for mod_lesson.
 *
 * @package    local_recompletion
 * @copyright  2023 Dmitrii Metelkin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \local_recompletion\plugins\mod_lesson
 */
class mod_lesson_test extends \advanced_testcase {

    /**
     * Lesson object.
     * @var \stdClass
     */
    protected $lesson;

    /**
     * User object.
     * @var \stdClass
     */
    protected $user;

    /**
     * A helper method to generate a lesson attempt.
     */
    protected function attempt_lesson() {
        global $DB;

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_lesson');
        $page = $generator->create_question_truefalse($this->lesson);
        $panswers = $DB->get_records('lesson_answers', array('lessonid' => $this->lesson->id, 'pageid' => $page->id), 'id');
        $answerid = reset($panswers)->id;

        $newpageattempt = [
            'lessonid' => $this->lesson->id,
            'pageid' => $page->id,
            'userid' => $this->user->id,
            'answerid' => $answerid,
            'retry' => 1,
            'correct' => 1,
            'useranswer' => '1',
            'timeseen' => time(),
        ];
        $DB->insert_record('lesson_attempts', (object) $newpageattempt);

        $newgrade = [
            'lessonid' => $this->lesson->id,
            'userid' => $this->user->id,
            'grade' => 50,
            'late' => 0,
            'completed' => time(),
        ];
        $DB->insert_record('lesson_grades', (object) $newgrade);

        $timer = (object)[
            'lessonid' => $this->lesson->id,
            'userid' => $this->user->id,
            'completed' => 1,
            'starttime' => time(),
            'lessontime' => time(),
        ];
        $DB->insert_record("lesson_timer", $timer);

        $branch = (object)[
            'lessonid' => $this->lesson->id,
            'userid' => $this->user->id,
            'pageid' => $page->id,
            'retry' => 1,
            'flag' => 0,
            'timeseen' => time(),
        ];
        $DB->insert_record("lesson_branch", $branch);

        $useroverride = (object)[
            'lessonid' => $this->lesson->id,
            'userid' => $this->user->id,
            'sortorder' => 1,
            'available' => 100,
            'deadline' => 200
        ];
        $DB->insert_record('lesson_overrides', $useroverride);
    }

    /**
     * Test mod_lesson recompletion.
     */
    public function test_mod_lesson() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $this->lesson = $this->getDataGenerator()->create_module('lesson', ['course' => $course->id]);
        $this->user = $this->getDataGenerator()->create_user();

        // Check that tables are empty initially.
        $this->assertFalse($DB->record_exists('lesson_attempts', ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));
        $this->assertFalse($DB->record_exists('lesson_grades', ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));
        $this->assertFalse($DB->record_exists('lesson_timer', ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));
        $this->assertFalse($DB->record_exists('lesson_branch', ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));
        $this->assertFalse($DB->record_exists('lesson_overrides', ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));

        $this->attempt_lesson();

        $tables = [
            'lesson_attempts' => 'local_recompletion_la',
            'lesson_grades' => 'local_recompletion_lg',
            'lesson_timer' => 'local_recompletion_lt',
            'lesson_branch' => 'local_recompletion_lb',
            'lesson_overrides' => 'local_recompletion_lo',
        ];

        foreach ($tables as $originaltable => $archivetable) {
            $this->assertTrue($DB->record_exists($originaltable, ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));
            $this->assertFalse($DB->record_exists($archivetable, ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));
        }

        mod_lesson::reset($this->user->id, $course, (object)['lesson' => 0, 'archivelesson' => 0]);
        $this->assertTrue($DB->record_exists($originaltable, ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));
        $this->assertFalse($DB->record_exists($archivetable, ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));

        mod_lesson::reset($this->user->id, $course, (object)['lesson' => 0, 'archivelesson' => 1]);
        $this->assertTrue($DB->record_exists($originaltable, ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));
        $this->assertFalse($DB->record_exists($archivetable, ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));

        mod_lesson::reset($this->user->id, $course, (object)['lesson' => 1, 'archivelesson' => 0]);
        $this->assertFalse($DB->record_exists($originaltable, ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));
        $this->assertFalse($DB->record_exists($archivetable, ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));

        $this->attempt_lesson();
        mod_lesson::reset($this->user->id, $course, (object)['lesson' => 1, 'archivelesson' => 1]);
        $this->assertFalse($DB->record_exists($originaltable, ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));
        $this->assertTrue($DB->record_exists($archivetable, ['userid' => $this->user->id, 'lessonid' => $this->lesson->id]));
    }
}
