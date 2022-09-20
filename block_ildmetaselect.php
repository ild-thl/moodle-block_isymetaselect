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
 * Definition of a block that displays a list of moodle courses, that can be searched and filtered.
 *
 * @package     block_ildmetaselect
 * @author      Dustin Neß <dustin.ness@th-luebeck.de>
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

global $CFG;

require_once($CFG->dirroot . '/blocks/ildmetaselect/lib.php');
require_once($CFG->dirroot . '/blocks/ildmetaselect/locallib.php');
require_once($CFG->dirroot . '/blocks/ildmetaselect/ildmetaselect_form.php');
require_once($CFG->dirroot . '/blocks/ildmetaselect/search_form.php');
require_once($CFG->dirroot . '/blocks/ildmetaselect/get_metacourses.php');

use local_ildmeta\manager;

class block_ildmetaselect extends block_base {


    public $searchresults;

    public function init() {
        global $PAGE;
        $this->title = get_string('pluginname', 'block_ildmetaselect');
    }
    public function has_config() {
      return true;
    }
    public function hide_header() {
        return false;
    }

    function array_search_partial($arr, $keyword) {
        foreach ($arr as $index => $string) {
            if (strpos($string, $keyword) !== FALSE)
                return $index;
        }
    }

    public function searchterm($searchterm) {
        global $DB;

        $providers = manager::get_providers();

        $providerquery = '';
        foreach ($providers as $key => $provider) {
            if (strpos($provider['name'], $searchterm) !== false) {
                $providerquery .= "OR provider LIKE '%$key%' ";
            }
        }

        // Get subjectarea vocabulary from ildmeta_vocabulary.
        $vocabulary = $DB->get_record('ildmeta_vocabulary', array('title' => 'subjectarea'), '*', MUST_EXIST);
        $subjectareas = manager::filter_vocabulary_lang($vocabulary, current_language());

        $subjectareaquery = '';
        foreach ($subjectareas as $key => $subjectarea) {
            if (stripos($subjectarea, $searchterm) !== false) {
                $subjectareaquery .= "OR subjectarea LIKE '%$key%' ";
            }
        }

        // Zu durchsuchende Bereiche: Kurstitel, Tags, Kursbeschreibungstext, Metainfos, Vorname + Nachname weiterer Autoren.
        $searchquery = "SELECT * FROM mdl_ildmeta
						WHERE
                        (coursetitle LIKE :coursetitle
							OR tags LIKE :tags
							OR teasertext LIKE :teasertext
							OR lecturer LIKE :lecturer
							$providerquery
							$subjectareaquery
							OR courselanguage LIKE :courselanguage
							OR detailslecturer LIKE :detailslecturer
                            OR detailsmorelecturer LIKE :detailsmorelecturer)";

        $to_midnight = strtotime('today midnight');

        $searchquery_past = $searchquery . " AND starttime <= $to_midnight ORDER BY starttime DESC, coursetitle ASC";
        $searchquery_future = $searchquery . " AND starttime > $to_midnight ORDER BY starttime ASC, coursetitle ASC";

        $param = array(
            'coursetitle' => '%' . $searchterm . '%',
            'tags' => '%' . $searchterm . '%',
            'teasertext' => '%' . $searchterm . '%',
            'lecturer' => '%' . $searchterm . '%',
            'courselanguage' => '%' . $searchterm . '%',
            'detailslecturer' => '%' . $searchterm . '%',
            'detailsmorelecturer' => '%' . $searchterm . '%',
        );

        $past = $DB->get_records_sql($searchquery_past, $param);
        $future = $DB->get_records_sql($searchquery_future, $param);

        $this->searchresults = array_merge($past, $future);
    }

    public function get_content() {
        global $USER, $PAGE, $CFG, $DB;
        $table = 'ildmeta';
        $result = '';
        $context = context_system::instance();
        $this->content = new stdClass();

        $sform = new search_form($PAGE->url);
        $result .= $sform->render();


        // #TODO
        $searchparam = optional_param('searchterm', null, PARAM_ALPHANUM);
        if (isset($searchparam)) {
            $this->searchterm($searchparam);
        }


        // Meta search form
        if ($fromsform = $sform->get_data()) {
            $this->searchterm($fromsform->search);
        }

        // Meta select form
        $data = new stdClass();
        $data->courselanguage = optional_param('courselanguage', 1, PARAM_INT);
        $data->subjectarea = optional_param('subjectarea', 1, PARAM_INT);
        $data->provider = optional_param('provider', 1, PARAM_INT);
        $data->processingtime = optional_param('processingtime', "all", PARAM_TEXT);
        $data->starttime = optional_param('starttime', "all", PARAM_TEXT);

        $records = get_courses_records($data);

        $customdata = array();
        $customdata['provider_list'] = get_filtered_provider_list($records);
        $customdata['subjectarea_list'] = get_filtered_subjectarea_list($records);
        $customdata['processingtime_list'] = get_filtered_processingtime_list($records);
        $customdata['starttime_list'] = get_filtered_starttime_list($records);
        $customdata['lang_list'] = get_filtered_lang_list($records);
        $customdata['data'] = $data;

        $mform = new ildmetaselect_form($PAGE->url->out(false), $customdata);
        $result .= $mform->render();

        if ($mform->is_cancelled()) {
            // not possible
            redirect($CFG->wwwroot);
        } else if ($fromform = $mform->get_data()) {
            if (isset($fromform->search) && $fromform->search !== '') {
                $this->searchterm($fromform->search);
                $result .= get_metacourses($this->searchresults, $context);
            } else {
                $coursestodisplay = get_courses_records($data);
                $result .= get_metacourses($coursestodisplay, $context);
            }
            //$coursestodisplay = get_courses_records($fromform);
        } else {
            if (!isset($this->searchresults)) {

                //get today midnight
                $to_midnight = strtotime('today midnight');

                //$sql_future = "SELECT * FROM {ildmeta} ORDER BY starttime ASC, coursetitle ASC";
                //$coursestodisplay = $DB->get_records_sql($sql_future);


                //first of all get all courses which already started (newest first)
                $sql_running = "SELECT * FROM {ildmeta} WHERE starttime < ? ORDER BY starttime DESC, coursetitle ASC";
                $sql_param_r = array('starttime' => $to_midnight);
                $coursestodisplay_runnig = $DB->get_records_sql($sql_running, $sql_param_r);

                // and now get all courses which will start in the future (starting soon first)
                $sql_future = "SELECT * FROM {ildmeta} WHERE starttime >= ? ORDER BY starttime ASC, coursetitle ASC";
                $sql_param_f = array('starttime' => $to_midnight);
                $coursestodisplay_future = $DB->get_records_sql($sql_future, $sql_param_f);

                $coursestodisplay = array_merge($coursestodisplay_runnig, $coursestodisplay_future);

                $result .= get_metacourses($coursestodisplay, $context);
            } else {
                $result .= get_metacourses($this->searchresults, $context);
            }
        }
        $this->content->text = $result;

        $this->page->requires->js_call_amd('block_ildmetaselect/ildmetaselect', 'init', array());

        return $this->content;
    }
}
