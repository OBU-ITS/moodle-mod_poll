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
 * Poll activity - view an instance of a poll
 *
 * @package    mod_poll
 * @author     Peter Welham
 * @copyright  2016, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot . '/mod/poll/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID or ...
$p = optional_param('p', 0, PARAM_INT);  // ... poll instance ID.

if ($id) {
    $cm = get_coursemodule_from_id('poll', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $poll = $DB->get_record('poll', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($p) {
    $poll = $DB->get_record('poll', array('id' => $p), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $poll->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('poll', $poll->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

require_login($course, true, $cm);

$event = \mod_poll\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $poll);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/poll/view.php', array('id' => $cm->id));
if ($inpopup and $poll->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
} else {
    $PAGE->set_activity_record($poll);
}
$PAGE->set_title($course->shortname . ': ' . format_string($poll->name));
$PAGE->set_heading(format_string($course->fullname));

// Output starts here.
echo $OUTPUT->header();

if ($poll->printheading && !empty($poll->name)) {
    echo $OUTPUT->heading(format_string($poll->name), 2);
}

if ($poll->printintro && !empty($poll->intro)) {
    echo $OUTPUT->box(format_module_intro('poll', $poll, $cm->id), 'generalbox mod_introbox', 'pollintro');
}

// Our page content is just an iframe containing the Brookes Polls app.

$config = get_config('poll');
echo '<center><iframe width="' . $config->iframewidth . '" height="' . $config->iframeheight . '" src="https://polls.brookes.ac.uk/#/poll/' . $poll->number . '"></iframe></center>';

// Finish the page.
echo $OUTPUT->footer();
