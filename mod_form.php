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
 * Poll activity - poll configuration form
 *
 * @package    mod_poll
 * @author     Peter Welham
 * @copyright  2016, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

 defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/poll/locallib.php');

class mod_poll_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
		
        $config = get_config('poll');

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('pollname', 'poll'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'pollname', 'poll');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        //-------------------------------------------------------
        $mform->addElement('header', 'numbersection', get_string('number', 'poll'));
        $mform->addElement('html', get_string('staffexplain', poll) . '<p />');
		$options = "width=$config->popupwidth,height=$config->popupheight,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
		$onclick = "javascript:window.open('https://polls.brookes.ac.uk/', '', '$options')";
		$qr = '<img width="256" height="256" src="http://chart.apis.google.com/chart?cht=qr&amp;choe=UTF-8&amp;chs=256x256&amp;chl=https://polls.brookes.ac.uk/">';
        $mform->addElement('html', '<a href="javascript:void(0)" onclick="' . $onclick . '">' . $qr . '</a>');
        $mform->addElement('text', 'number', get_string('number', 'poll'));
        $mform->setType('number', PARAM_INT);
        $mform->addRule('number', get_string('required'), 'required', null, 'client');

        //-------------------------------------------------------
        $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
		
		// override 'Open' string
		if (isset($options[RESOURCELIB_DISPLAY_OPEN])) {
			$options[RESOURCELIB_DISPLAY_OPEN] = get_string('displayopen', 'poll');
		}
		
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'poll'), $options);
            $mform->setDefault('display', $config->display);
        }

        //-------------------------------------------------------
        $mform->addElement('advcheckbox', 'printheading', get_string('printheading', 'poll'));
        $mform->setDefault('printheading', $config->printheading);
        $mform->addElement('advcheckbox', 'printintro', get_string('printintro', 'poll'));
        $mform->setDefault('printintro', $config->printintro);

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}
