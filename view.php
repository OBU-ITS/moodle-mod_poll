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
 * Poll activity - poll view
 *
 * @package    mod_poll
 * @author     Peter Welham
 * @copyright  2016, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require('../../config.php');
require_once($CFG->dirroot . '/mod/poll/locallib.php');
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID
$p = optional_param('p', 0, PARAM_INT);  // Poll instance ID
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

if ($p) {
    if (!$poll = $DB->get_record('poll', array('id' => $p))) {
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('poll', $poll->id, $poll->course, false, MUST_EXIST);
} else {
    if (!$cm = get_coursemodule_from_id('poll', $id)) {
        print_error('invalidcoursemodule');
    }
    $poll = $DB->get_record('poll', array('id' => $cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/poll:view', $context);

// Trigger module viewed event.
$event = \mod_poll\event\course_module_viewed::create(array(
   'objectid' => $poll->id,
   'context' => $context
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('poll', $poll);
$event->trigger();

// Update 'viewed' state if required by completion system
require_once($CFG->libdir . '/completionlib.php');
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/poll/view.php', array('id' => $cm->id));

$options = empty($poll->displayoptions) ? array() : unserialize($poll->displayoptions);

if ($inpopup and $poll->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
    $PAGE->set_title($course->shortname . ': ' . $poll->name);
    $PAGE->set_heading($course->fullname);
} else {
    $PAGE->set_title($course->shortname . ': ' . $poll->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($poll);
}
echo $OUTPUT->header();
if (!isset($options['printheading']) || !empty($options['printheading'])) {
    echo $OUTPUT->heading(format_string($poll->name), 2);
}

if (!empty($options['printintro'])) {
    if (trim(strip_tags($poll->intro))) {
        echo $OUTPUT->box_start('mod_introbox', 'pollintro');
        echo format_module_intro('poll', $poll, $cm->id);
        echo $OUTPUT->box_end();
    }
}

$config = get_config('poll');

echo '<center><iframe width="' . $config->iframewidth . '" height="' . $config->iframeheight . '" src="https://polls.brookes.ac.uk/#/poll/' . $poll->number . '"></iframe></center>';

echo $OUTPUT->footer();
