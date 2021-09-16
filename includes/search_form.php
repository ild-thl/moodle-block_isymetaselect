<?php
/**
 *
 * @package		block_isymetaselect
 * @author		Dustin Neß <dustin.ness@th-luebeck.de>
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class search_form extends moodleform {

	public function definition() {
		global $CFG, $DB;

		$sform = $this->_form;
		$sform->disable_form_change_checker();

		$sform->addElement('text', 'search', 'Suche');
		$sform->setType('search', PARAM_RAW);

		$sform->addElement('submit', 'submitbutton', get_string('button_search', 'block_isymetaselect'));
	}

}