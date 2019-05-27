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

		$processingtime_list = [
			'-' => get_string('proctime_name','block_ildmetaselect'),
			'all' => get_string('proctime_all','block_ildmetaselect'),
			'upto15' => get_string('proctime_upto15','block_ildmetaselect'),
			'between16and20' => get_string('proctime_between16and20','block_ildmetaselect'),
			'between21and25' => get_string('proctime_between21and25','block_ildmetaselect'),
			'morethan25' => get_string('proctime_morethan25','block_ildmetaselect')
		];

		$starttime_list = [
			'-' => get_string('starttime_name','block_ildmetaselect'),
			'all' => get_string('starttime_all','block_ildmetaselect'),
			'current' => get_string('starttime_current','block_ildmetaselect'),
			'less2weeks' => get_string('starttime_less2weeks','block_ildmetaselect'),
			'between3and4weeks' => get_string('starttime_between3and4weeks','block_ildmetaselect'),
			'between5and6weeks' => get_string('starttime_between5and6weeks','block_ildmetaselect'),
			'between7and8weeks' => get_string('starttime_between7and8weeks', 'block_ildmetaselect')
		];

		// temporär bis dynamische Methode gefunden wird
		$lang_list = [
			'Kurssprache',
			'Alle Kurssprachen',
			'Deutsch',
			'Englisch'
		];

		global $CFG, $DB;

		$mform = $this->_form;

		$mform->disable_form_change_checker();

		$universities = $DB->get_record('user_info_field', array('shortname' => 'universities'));
		$university_list =  explode("\n", $universities->param1);
		array_unshift($university_list, 'Alle Hochschulen');
		array_unshift($university_list, 'Hochschule');

		$subjectareas = $DB->get_record('user_info_field', array('shortname' => 'subjectareas'));
		$subjectarea_list =  explode("\n", $subjectareas->param1);
		array_unshift($subjectarea_list, 'Alle Wissensgebiete');
		array_unshift($subjectarea_list, 'Wissensgebiet');
	    $subjectarea = $mform->createElement('select', 'subjectarea', '', [], array('onchange' => 'javascript:this.form.submit();'));
	    $mform->setType('subjectarea', PARAM_RAW);

		foreach ($subjectarea_list as $value => $label) {
			$attributes = array();
			if ($label === 'Wissensgebiet') {
				$attributes['disabled'] = 'disabled';
				$attributes['selected'] = 'selected';
			}
			$subjectarea->addOption($label, $value, $attributes);
		}
		$mform->addElement($subjectarea);

	    $university = $mform->createElement('select', 'university', '', [], array('onchange' => 'javascript:this.form.submit();'));
	    $mform->setType('university', PARAM_RAW);

		foreach ($university_list as $value => $label) {
			$attributes = array();
			if ($label === 'Hochschule') {
				$attributes['disabled'] = 'disabled';
				$attributes['selected'] = 'selected';
			}
			$university->addOption($label, $value, $attributes);
		}
		$mform->addElement($university);

	    $courselanguage = $mform->createElement('select', 'courselanguage', '', [], array('onchange' => 'javascript:this.form.submit();'));
	    $mform->setType('courselanguage', PARAM_RAW);

		foreach ($lang_list as $value => $label) {
			$attributes = array();
			if ($label === 'Kurssprache') {
				$attributes['disabled'] = 'disabled';
				$attributes['selected'] = 'selected';
			}
			$courselanguage->addOption($label, $value, $attributes);
		}
		$mform->addElement($courselanguage);


	    $processingtime = $mform->createElement('select', 'processingtime', '', [], array('onchange' => 'javascript:this.form.submit();'));
	    $mform->setType('processingtime', PARAM_RAW);


		foreach ($processingtime_list as $value => $label) {
		       $attributes = array();
				if ($value === '-') {
					$attributes['disabled'] = 'disabled';
					$attributes['selected'] = 'selected';
				}
				$processingtime->addOption($label, $value, $attributes);
			}

		$mform->addElement($processingtime);

	    $starttime = $mform->createElement('select', 'starttime', '', [], array('onchange' => 'javascript:this.form.submit();'));
	    $mform->setType('starttime', PARAM_RAW);

	    foreach ($starttime_list as $value => $label) {
	       $attributes = array();
			if ($value === '-') {
				$attributes['disabled'] = 'disabled';
				$attributes['selected'] = 'selected';
			}
			$starttime->addOption($label, $value, $attributes);
	}
	$mform->addElement($starttime);

	}

}
