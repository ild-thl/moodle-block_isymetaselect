<?php
/**
 *
 * @package        block_ildmetaselect
 * @author        Dustin Neß <dustin.ness@th-luebeck.de>
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once 'lib.php';
require_once 'locallib.php';
require_once 'ildmetaselect_form.php';
require_once 'search_form.php';
require_once 'get_metacourses.php';

class block_ildmetaselect extends block_base
{

    public $searchresults;

    public function init()
    {
        global $PAGE;
        $this->title = get_string('pluginname', 'block_ildmetaselect');
    }

    public function hide_header()
    {
        return false;
    }

    function array_search_partial($arr, $keyword)
    {
        foreach ($arr as $index => $string) {
            if (strpos($string, $keyword) !== FALSE)
                return $index;
        }
    }

    public function searchterm($searchterm)
    {
        global $DB;

        $universities = $DB->get_record('user_info_field', array('shortname' => 'targetgroups')); // vorher: universities
        $university = explode("\n", $universities->param1);

        $university_query = '';
        foreach ($university as $key => $part) {
            if (stripos($part, $searchterm) !== false) {
                $university_query .= "OR university LIKE '$key' ";
            }
        }


        $subjectareas = $DB->get_record('user_info_field', array('shortname' => 'formats')); // vorher: subjectareas
        $subjectarea = explode("\n", $subjectareas->param1);
        $subjectarea_query = '';
        foreach ($subjectarea as $key => $part) {
            if (stripos($part, $searchterm) !== false) {
                $subjectarea_query .= "OR subjectarea LIKE '%$key%' ";
            }
        }

        //Zu durchsuchende Bereiche: Kurstitel, Tags, Kursbeschreibungstext, Metainfos, Vorname + Nachname weiterer Autoren
        $searchquery = "SELECT * FROM mdl_ildmeta
						WHERE
							(coursetitle LIKE '%$searchterm%'
							OR tags LIKE '%$searchterm%'
							OR teasertext LIKE '%$searchterm%'
							OR lecturer LIKE '%$searchterm%'
							$university_query
							$subjectarea_query
							OR courselanguage LIKE '%$searchterm%'
							OR detailslecturer LIKE '%$searchterm%'
                            OR detailsmorelecturer LIKE '%$searchterm%')";
        
        $to_midnight = strtotime('today midnight');

        $searchquery_past = $searchquery . " AND starttime < $to_midnight ORDER BY starttime DESC, coursetitle ASC";
        $searchquery_future = $searchquery . " AND starttime >= $to_midnight ORDER BY starttime ASC, coursetitle ASC";

        $past = $DB->get_records_sql($searchquery_past);
        $future = $DB->get_records_sql($searchquery_future);

        $this->searchresults = array_merge($past, $future);
    }

    public function get_content()
    {
        global $USER, $PAGE, $CFG, $DB;
        $table = 'ildmeta';
        $result = '';
        $context = context_system::instance();
        $this->content = new stdClass();

        $sform = new search_form($PAGE->url);
        $result .= $sform->render();


        // #TODO
        $searchparam = optional_param('searchterm', null, PARAM_RAW);
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
        $data->university = optional_param('university', 1, PARAM_INT);
        $data->processingtime = optional_param('processingtime', "all", PARAM_TEXT);
        $data->starttime = optional_param('starttime', "all", PARAM_TEXT);

        $records = get_courses_records($data);

        $customdata = array();
        $customdata['university_list'] = get_filtered_university_list($records);
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
            if(isset($fromform->search) && $fromform->search !== ''){
                $this->searchterm($fromform->search);
                $result .= get_metacourses($this->searchresults, $context);
            }else{
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
