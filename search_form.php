<?php
/**
 *
 * @package		block_ildmetaselect
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

		// ADDED tinjohn 20221211.
		$sform->addElement('html', '<span id="ildmetaselect_form_anchor_freetxtsearch"></span>');
		$sform->addElement('text', 'search', 'Suche');
		$sform->setType('search', PARAM_RAW);

		$sform->addElement('submit', 'submitbutton', get_string('search'));
	}

}
