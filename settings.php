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
 * Poll activity - admin settings and defaults
 *
 * @package    mod_poll
 * @author     Peter Welham
 * @copyright  2016, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_OPEN, RESOURCELIB_DISPLAY_POPUP));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_OPEN);

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configcheckbox('poll/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 1));
    $settings->add(new admin_setting_configmultiselect('poll/displayoptions', get_string('displayoptions', 'poll'), get_string('configdisplayoptions', 'poll'), $defaultdisplayoptions, $displayoptions));
    $settings->add(new admin_setting_configtext('poll/iframewidth', get_string('iframewidth', 'poll'), get_string('iframewidthexplain', 'poll'), 360, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('poll/iframeheight', get_string('iframeheight', 'poll'), get_string('iframeheightexplain', 'poll'), 640, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('poll/popupwidth', get_string('popupwidth', 'poll'), get_string('popupwidthexplain', 'poll'), 480, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('poll/popupheight', get_string('popupheight', 'poll'), get_string('popupheightexplain', 'poll'), 800, PARAM_INT, 7));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('pollmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('poll/printheading', get_string('printheading', 'poll'), get_string('printheadingexplain', 'poll'), 1));
    $settings->add(new admin_setting_configcheckbox('poll/printintro', get_string('printintro', 'poll'), get_string('printintroexplain', 'poll'), 0));
    $settings->add(new admin_setting_configselect('poll/display', get_string('displayselect', 'poll'), get_string('displayselectexplain', 'poll'), RESOURCELIB_DISPLAY_OPEN, $displayoptions));
}
