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
 * Poll activity - list of features supported in Poll module
 *
 * @package    mod_poll
 * @author     Peter Welham
 * @copyright  2016, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in Poll module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function poll_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function poll_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function poll_reset_userdata($data) {
    return array();
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function poll_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function poll_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add poll instance.
 * @param stdClass $data
 * @param mod_poll_mod_form $mform
 * @return int new poll instance id
 */
function poll_add_instance($data, $mform = null) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid = $data->coursemodule;

    $data->timemodified = time();

    $displayoptions = array();
    $displayoptions['printheading'] = $data->printheading;
    $displayoptions['printintro'] = $data->printintro;
    $data->displayoptions = serialize($displayoptions);

    if ($mform) {
        $data->number = $data->poll['number'];
    }
	
    //********************
	$data->number = '461767';

    $data->id = $DB->insert_record('poll', $data);

    $DB->set_field('course_modules', 'instance', $data->id, array('id' => $cmid));

    return $data->id;
}

/**
 * Update poll instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function poll_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid = $data->coursemodule;

    $data->timemodified = time();
    $data->id = $data->instance;
    $data->revision++;

    $displayoptions = array();
    $displayoptions['printheading'] = $data->printheading;
    $displayoptions['printintro'] = $data->printintro;
    $data->displayoptions = serialize($displayoptions);

    $data->number = $data->poll['number'];

    //********************
	$data->number = '461767';

    $DB->update_record('poll', $data);

    return true;
}

/**
 * Delete poll instance.
 * @param int $id
 * @return bool true
 */
function poll_delete_instance($id) {
    global $DB;

    if (!$poll = $DB->get_record('poll', array('id' => $id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('poll', array('id' => $poll->id));

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param stdClass $coursemodule
 * @return cached_cm_info Info to customise main poll display
 */
function poll_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if (!$poll = $DB->get_record('poll', array('id' => $coursemodule->instance), 'id, name, display, intro, introformat, number')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $poll->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('poll', $poll, $coursemodule->id, false);
    }
	
	$info->content .= get_string('studentexplain', 'poll');
	$info->content .= '<p />';
	$info->content .= '<img width="256" height="256" src="http://chart.apis.google.com/chart?cht=qr&amp;choe=UTF-8&amp;chs=256x256&amp;chl=https://polls.brookes.ac.uk/%23/poll/' . $poll->number . '">';

    if ($poll->display != RESOURCELIB_DISPLAY_POPUP) {
        return $info;
    }

    $fullurl = "$CFG->wwwroot/mod/poll/view.php?id=$coursemodule->id&amp;inpopup=1";
    $config = get_config('poll');
    $wh = "width=$config->popupwidth,height=$config->popupheight,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
    $info->onclick = "window.open('$fullurl', '', '$wh'); return false;";

    return $info;
}

/**
 * Lists all browsable file areas
 *
 * @package  mod_poll
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @return array
 */
function poll_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['content'] = get_string('content', 'poll');
    return $areas;
}

/**
 * File browsing support for poll module content area.
 *
 * @package  mod_poll
 * @category files
 * @param stdClass $browser file browser instance
 * @param stdClass $areas file areas
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param int $itemid item ID
 * @param string $filepath file path
 * @param string $filename file name
 * @return file_info instance or null if not found
 */
function poll_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        // students can not peak here!
        return null;
    }

    $fs = get_file_storage();

    if ($filearea === 'content') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_poll', 'content', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_poll', 'content', 0);
            } else {
                // not found
                return null;
            }
        }
        require_once("$CFG->dirroot/mod/poll/locallib.php");
        return new poll_content_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, true, false);
    }

    // note: poll_intro handled in file_browser automatically

    return null;
}

/**
 * Serves the poll files.
 *
 * @package  mod_poll
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function poll_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);
    if (!has_capability('mod/poll:view', $context)) {
        return false;
    }

    if ($filearea !== 'content') {
        // intro is handled automatically in pluginfile.php
        return false;
    }

    // $arg could be revision number or index.html
    $arg = array_shift($args);
    if ($arg == 'index.html' || $arg == 'index.htm') {
        // serve poll content
        $filename = $arg;

        if (!$poll = $DB->get_record('poll', array('id'=>$cm->instance), '*', MUST_EXIST)) {
            return false;
        }

        // remove @@PLUGINFILE@@/
        $content = str_replace('@@PLUGINFILE@@/', '', $poll->content);

        $formatoptions = new stdClass;
        $formatoptions->noclean = true;
        $formatoptions->overflowdiv = true;
        $formatoptions->context = $context;
        $content = format_text($content, $poll->contentformat, $formatoptions);

        send_file($content, $filename, 0, 0, true, true);
    } else {
        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_poll/$filearea/0/$relativepath";
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            $poll = $DB->get_record('poll', array('id'=>$cm->instance), 'id, legacyfiles', MUST_EXIST);
            if ($poll->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
                return false;
            }
            if (!$file = resourcelib_try_file_migration('/'.$relativepath, $cm->id, $cm->course, 'mod_poll', 'content', 0)) {
                return false;
            }
            //file migrate - update flag
            $poll->legacyfileslast = time();
            $DB->update_record('poll', $poll);
        }

        // finally send the file
        send_stored_file($file, null, 0, $forcedownload, $options);
    }
}

/**
 * Return a list of poll types
 * @param string $polltype current poll type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function poll_poll_type_list($polltype, $parentcontext, $currentcontext) {
    $module_polltype = array('mod-poll-*' => get_string('poll-mod-poll-x', 'poll'));
    return $module_polltype;
}

/**
 * Export poll resource contents
 *
 * @return array of file content
 */
function poll_export_contents($cm, $baseurl) {
    global $CFG, $DB;
    $contents = array();
    $context = context_module::instance($cm->id);

    $poll = $DB->get_record('poll', array('id'=>$cm->instance), '*', MUST_EXIST);

    // poll contents
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_poll', 'content', 0, 'sortorder DESC, id ASC', false);
    foreach ($files as $fileinfo) {
        $file = array();
        $file['type'] = 'file';
        $file['filename'] = $fileinfo->get_filename();
        $file['filepath'] = $fileinfo->get_filepath();
        $file['filesize'] = $fileinfo->get_filesize();
        $file['fileurl'] = file_encode_url("$CFG->wwwroot/" . $baseurl, '/'.$context->id.'/mod_poll/content/'.$poll->revision.$fileinfo->get_filepath().$fileinfo->get_filename(), true);
        $file['timecreated'] = $fileinfo->get_timecreated();
        $file['timemodified'] = $fileinfo->get_timemodified();
        $file['sortorder'] = $fileinfo->get_sortorder();
        $file['userid'] = $fileinfo->get_userid();
        $file['author'] = $fileinfo->get_author();
        $file['license'] = $fileinfo->get_license();
        $contents[] = $file;
    }

    // poll html conent
    $filename = 'index.html';
    $pollfile = array();
    $pollfile['type'] = 'file';
    $pollfile['filename'] = $filename;
    $pollfile['filepath'] = '/';
    $pollfile['filesize'] = 0;
    $pollfile['fileurl'] = file_encode_url("$CFG->wwwroot/" . $baseurl, '/'.$context->id.'/mod_poll/content/' . $filename, true);
    $pollfile['timecreated'] = null;
    $pollfile['timemodified'] = $poll->timemodified;
    // make this file as main file
    $pollfile['sortorder'] = 1;
    $pollfile['userid'] = null;
    $pollfile['author'] = null;
    $pollfile['license'] = null;
    $contents[] = $pollfile;

    return $contents;
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function poll_dndupload_register() {
    return array('types' => array(
        array('identifier' => 'text/html', 'message' => get_string('createpoll', 'poll')),
        array('identifier' => 'text', 'message' => get_string('createpoll', 'poll'))
    ));
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function poll_dndupload_handle($uploadinfo) {
    // Gather the required info.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '<p>' . $uploadinfo->displayname . '</p>';
    $data->introformat = FORMAT_HTML;
    $data->coursemodule = $uploadinfo->coursemodule;

    // Set the display options to the site defaults.
    $config = get_config('poll');
    $data->display = $config->display;
    $data->printheading = $config->printheading;
    $data->printintro = $config->printintro;

    return poll_add_instance($data, null);
}
