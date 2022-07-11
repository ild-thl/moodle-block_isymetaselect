<?php

/**
 *
 * @package		block_ildmetaselect
 * @author		Dustin Neß <dustin.ness@th-luebeck.de>
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class ildmetaselect_form extends moodleform {

	public function definition() {

		global $CFG, $DB;

		$mform = $this->_form;

		$mform->disable_form_change_checker();

		$provider_list = $this->_customdata['provider_list'];
		$subjectarea_list = $this->_customdata['subjectarea_list'];
		$lang_list =  $this->_customdata['lang_list'];
		$processingtime_list =  $this->_customdata['processingtime_list'];
		$starttime_list =  $this->_customdata['starttime_list'];

		$data = $this->_customdata['data'];

		$subjectarea = $mform->createElement('select', 'subjectarea', '', [], array());
		$mform->setType('subjectarea', PARAM_RAW);

		foreach ($subjectarea_list as $value => $label) {
			$attributes = array();
			if ($value === 0) {
				$attributes['disabled'] = 'disabled';
				$attributes['selected'] = 'selected';
			}
			$subjectarea->addOption(explode("=>", $label)[1], explode("=>", $label)[0], $attributes);
		}
		$mform->addElement($subjectarea);

		$provider = $mform->createElement('select', 'provider', '', [], array());
		$mform->setType('provider', PARAM_RAW);

		foreach ($provider_list as $value => $label) {
			$attributes = array();
			if ($value === 0) {
				$attributes['disabled'] = 'disabled';
				$attributes['selected'] = 'selected';
			}
			$provider->addOption(explode("=>", $label)[1], explode("=>", $label)[0], $attributes);
		}
		$mform->addElement($provider);

		$courselanguage = $mform->createElement('select', 'courselanguage', '', [], array());
		$mform->setType('courselanguage', PARAM_RAW);

		foreach ($lang_list as $value => $label) {
			$attributes = array();
			if ($value === 0) {
				$attributes['disabled'] = 'disabled';
				$attributes['selected'] = 'selected';
			}
			$courselanguage->addOption(explode("=>", $label)[1], explode("=>", $label)[0], $attributes);
		}
		$mform->addElement($courselanguage);


		$processingtime = $mform->createElement('select', 'processingtime', '', [], array());
		$mform->setType('processingtime', PARAM_RAW);


		foreach ($processingtime_list as $value => $label) {
			$attributes = array();
			if ($value === '-') {
				$attributes['disabled'] = 'disabled';
				$attributes['selected'] = 'selected';
			}
			$processingtime->addOption(explode("=>", $label)[1], explode("=>", $label)[0], $attributes);
		}

		$mform->addElement($processingtime);

		$starttime = $mform->createElement('select', 'starttime', '', [], array());
		$mform->setType('starttime', PARAM_RAW);

		foreach ($starttime_list as $value => $label) {
			$attributes = array();
			if ($value === '-') {
				$attributes['disabled'] = 'disabled';
				$attributes['selected'] = 'selected';
			}
			$starttime->addOption(explode("=>", $label)[1], explode("=>", $label)[0], $attributes);
		}
		$mform->addElement($starttime);

		$mform->addElement('submit', 'submitbutton', get_string('search'));
		$mform->addElement('cancel', 'cancel', get_string('reset'));
	}
}
