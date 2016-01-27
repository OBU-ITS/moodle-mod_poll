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
 * Poll activity - list of all polls in course
 *
 * @package    mod_poll
 * @author     Peter Welham
 * @copyright  2016, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require('../../config.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

// Trigger instances list viewed event.
$event = \mod_poll\event\course_module_instance_list_viewed::create(array('context' => context_course::instance($course->id)));
$event->add_record_snapshot('course', $course);
$event->trigger();

$strpoll = get_string('modulename', 'poll');
$strpolls = get_string('modulenameplural', 'poll');
$strname = get_string('name');
$strintro = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/poll/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strpolls);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strpolls);
echo $OUTPUT->header();
echo $OUTPUT->heading($strpolls);
if (!$polls = get_all_instances_in_course('poll', $course)) {
    notice(get_string('thereareno', 'moodle', $strpolls), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($polls as $poll) {
    $cm = $modinfo->cms[$poll->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($poll->section !== $currentsection) {
            if ($poll->section) {
                $printsection = get_section_name($course, $poll->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $poll->section;
        }
    } else {
        $printsection = '<span class="smallinfo">' . userdate($poll->timemodified) . "</span>";
    }

    $class = $poll->visible ? '' : 'class="dimmed"'; // hidden modules are dimmed

    $table->data[] = array (
        $printsection,
        "<a $class href=\"view.php?id=$cm->id\">".format_string($poll->name)."</a>",
        format_module_intro('poll', $poll, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
