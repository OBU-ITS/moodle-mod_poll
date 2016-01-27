<?php

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
 * Poll activity - PHPUnit data generator tests
 *
 * @package    mod_poll
 * @author     Peter Welham
 * @copyright  2016, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

/**
 * PHPUnit data generator testcase
 */
class mod_poll_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB, $SITE;

        $this->resetAfterTest(true);

        $this->assertEquals(0, $DB->count_records('poll'));

        /** @var mod_poll_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_poll');
        $this->assertInstanceOf('mod_poll_generator', $generator);
        $this->assertEquals('poll', $generator->get_modulename());

        $generator->create_instance(array('course' => $SITE->id));
        $generator->create_instance(array('course' => $SITE->id));
        $poll = $generator->create_instance(array('course' => $SITE->id));
        $this->assertEquals(3, $DB->count_records('poll'));

        $cm = get_coursemodule_from_instance('poll', $poll->id);
        $this->assertEquals($poll->id, $cm->instance);
        $this->assertEquals('poll', $cm->modname);
        $this->assertEquals($SITE->id, $cm->course);

        $context = context_module::instance($cm->id);
        $this->assertEquals($poll->cmid, $context->instanceid);
    }
}
