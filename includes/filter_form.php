<?php
/**
 *
 * @package		block_isymetaselect
 * @author		Dustin Neß <dustin.ness@th-luebeck.de>
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class filter_form extends moodleform {

	public function definition() {

		global $CFG, $DB;

		$mform = $this->_form;
		$comp = 'block_isymetaselect';
		$mform->disable_form_change_checker();

		$meta2_list = $this->_customdata['meta2_list'];
		$meta6_list = $this->_customdata['meta6_list'];
		$lang_list =  $this->_customdata['lang_list'];
		$meta4_list =  $this->_customdata['meta4_list'];
		$meta5_list =  $this->_customdata['meta5_list'];

		$data = $this->_customdata['data'];

		// jetzt: Programm
		$meta2 = $mform->createElement('select', 'meta2', '', [], []);
	    $mform->setType('meta2', PARAM_RAW);

		foreach ($meta2_list as $value => $label) {
			$attributes = array();
			if ($value === 0) {
				$attributes['disabled'] = 'disabled';
				$attributes['selected'] = 'selected';
			}
			$meta2->addOption(explode("=>", $label)[1], explode("=>", $label)[0], $attributes);
		}
		$mform->addElement($meta2);

	    $meta6 = $mform->createElement('select', 'meta6', '', [], []);
	    $mform->setType('meta6', PARAM_RAW);

		foreach ($meta6_list as $value => $label) {
			$attributes = array();
			if ($value === 0) {
				$attributes['disabled'] = 'disabled';
				$attributes['selected'] = 'selected';
			}
			$meta6->addOption(explode("=>", $label)[1], explode("=>", $label)[0], $attributes);
		}
		$mform->addElement($meta6);

	    // $courselanguage = $mform->createElement('select', 'courselanguage', '', [], array());
	    // $mform->setType('courselanguage', PARAM_RAW);

		// foreach ($lang_list as $value => $label) {
		// 	$attributes = array();
		// 	if ($value === 0) {
		// 		$attributes['disabled'] = 'disabled';
		// 		$attributes['selected'] = 'selected';
		// 	}
		// 	$courselanguage->addOption(explode("=>", $label)[1], explode("=>", $label)[0], $attributes);
		// }
		// $mform->addElement($courselanguage);


	    $meta4 = $mform->createElement('select', 'meta4', '', [], []);
	    $mform->setType('meta4', PARAM_RAW);


		foreach ($meta4_list as $value => $label) {
		       $attributes = array();
				if ($value === '-') {
					$attributes['disabled'] = 'disabled';
					$attributes['selected'] = 'selected';
				}
				$meta4->addOption(explode("=>", $label)[1], explode("=>", $label)[0], $attributes);
			}

		$mform->addElement($meta4);

	    // $meta5 = $mform->createElement('select', 'meta5', '', [], array());
	    // $mform->setType('meta5', PARAM_RAW);

	    // foreach ($meta5_list as $value => $label) {
	    //    $attributes = array();
		// 	if ($value === '-') {
		// 		$attributes['disabled'] = 'disabled';
		// 		$attributes['selected'] = 'selected';
		// 	}
		// 	$meta5->addOption(explode("=>", $label)[1], explode("=>", $label)[0], $attributes);
		// }
		// $mform->addElement($meta5);

		$mform->addElement('submit', 'submitbutton', get_string('button_search', $comp));
		$mform->addElement('cancel', 'cancel', get_string('button_reset', $comp));

	}

}
