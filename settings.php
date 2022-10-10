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
 *  Block ISymetaselect config form definition class
 *
 * @package		block_ildmetaselect
 * @author		Dustin Neß <dustin.ness@th-luebeck.de>
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
  $yesno = array(0 => get_string('no'), 1 => get_string('yes'));
  $minhours = array(0 => get_string('hours'), 1 => get_string('minutes'));
//  $settings->add(new admin_setting_configcheckbox('block_ildmetaselect/add_detail_page',get_string('add_detail_page', 'block_ildmetaselect'),get_string('add_detail_page_help', 'block_ildmetaselect'),1));
  $settings->add(new admin_setting_configselect('block_ildmetaselect/add_detail_page',
         get_string('add_detail_page', 'block_ildmetaselect'),get_string('add_detail_page_help', 'block_ildmetaselect'),
         array('value' => 1, 'adv' => true), $yesno));
  $settings->add(new admin_setting_configselect('block_ildmetaselect/workload_in_min',
        get_string('workload_in_min', 'block_ildmetaselect'),get_string('workload_in_min_help', 'block_ildmetaselect'),
        array('value' => 1, 'adv' => true), $minhours));
  $settings->add(new admin_setting_configcheckbox('block_ildmetaselect/add_formmenu_provider',
        get_string('add_formmenu_provider', 'block_ildmetaselect'),get_string('formmenu_provider_help', 'block_ildmetaselect'), 1));
  $settings->add(new admin_setting_configcheckbox('block_ildmetaselect/add_formmenu_courselanguage',
        get_string('add_formmenu_courselanguage', 'block_ildmetaselect'),get_string('formmenu_courselanguage_help', 'block_ildmetaselect'),1));
  $settings->add(new admin_setting_configcheckbox('block_ildmetaselect/add_formmenu_starttime',
        get_string('add_formmenu_starttime', 'block_ildmetaselect'),get_string('formmenu_starttime_help', 'block_ildmetaselect'),1));

}
