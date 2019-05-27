<?php
/**
 *
 * @package        block_ildmetaselect
 * @author        Dustin Neß <dustin.ness@th-luebeck.de>
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once 'lib.php';
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

    public function searchterm($data)
    {
        global $DB;
        $searchterm = $data;

        $universities = $DB->get_record('user_info_field', array('shortname' => 'universities'));
        $university = explode("\n", $universities->param1);

        $university_query = '';
        foreach ($university as $key => $part) {
            if (stripos($part, $searchterm) !== false) {
                $university_query .= "OR university LIKE '$key' ";
            }
        }


        $subjectareas = $DB->get_record('user_info_field', array('shortname' => 'subjectareas'));
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
							coursetitle LIKE '%$searchterm%'
							OR tags LIKE '%$searchterm%'
							OR teasertext LIKE '%$searchterm%'
							OR lecturer LIKE '%$searchterm%'
							$university_query
							$subjectarea_query
							OR courselanguage LIKE '%$searchterm%'
							OR detailslecturer LIKE '%$searchterm%'
							OR detailsmorelecturer LIKE '%$searchterm%'";

        $this->searchresults = $DB->get_records_sql($searchquery);
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
        $searchparam = optional_param('searchteam', null, PARAM_RAW);
        if (isset($searchparam)) {
            $this->searchterm($searchparam);
        }


// Meta search form
        if ($fromsform = $sform->get_data()) {
            $this->searchterm($fromsform->search);
        }

// Meta select form
        $mform = new ildmetaselect_form($PAGE->url->out(false));
        $result .= $mform->render();

        if ($mform->is_cancelled()) {
            // not possible
        } else if ($fromform = $mform->get_data()) {


            // temporär bis dynamische Methode gefunden wird
            $lang_list = [
                'Kurssprachen',
                'Alle Kurssprachen',
                'Deutsch',
                'Englisch'
            ];


            $tosearch = new stdClass;

            if ($fromform->courselanguage == 0 || $fromform->courselanguage == 1) {
                $tosearch->courselanguage = '%';
            } else {
                $tosearch->courselanguage = $fromform->courselanguage - 2;
            }

            if ($fromform->subjectarea == 1 || $fromform->subjectarea == 0) {
                $tosearch->subjectarea = '%';
            } else {
                $tosearch->subjectarea = $fromform->subjectarea - 2;
            }

            if ($fromform->university == 1 || $fromform->university == 0) {
                $tosearch->university = '%';
            } else {
                $tosearch->university = $fromform->university - 2;
            }

            switch ($fromform->processingtime) {
                case '-':
                case 'all':
                    $tosearch->processingtime = "LIKE '%'";
                    break;
                case 'upto15':
                    $tosearch->processingtime = '<= 15';
                    break;
                case 'between16and20':
                    $tosearch->processingtime = 'BETWEEN 16 AND 20';
                    break;
                case 'between21and25':
                    $tosearch->processingtime = 'BETWEEN 21 AND 25';
                    break;
                case 'morethan25':
                    $tosearch->processingtime = '>= 26';
                    break;
            }

            $time_now = time();
            $time_week = 604800;
            $time_day = 86400;

            switch ($fromform->starttime) {
                case '-':
                case 'all':
                    $tosearch->starttime = "LIKE '%'";
                    break;
                case 'current':
                    $tosearch->starttime = "< " . $time_now;
                    break;
                case 'less2weeks':
                    $tosearch->starttime = '>= ' . ($time_now + ($time_week * 2));
                    break;
                case 'between3and4weeks':
                    $tosearch->starttime = 'BETWEEN ' . ($time_now + ($time_week * 2)) . ' AND ' . ($time_now + ($time_week * 4));
                    break;
                case 'between5and6weeks':
                    $tosearch->starttime = 'BETWEEN ' . ($time_now + ($time_week * 4)) . ' AND ' . ($time_now + ($time_week * 6));
                    break;
                case 'between7and8weeks':
                    $tosearch->starttime = 'BETWEEN ' . ($time_now + ($time_week * 6)) . ' AND ' . ($time_now + ($time_week * 8));
                    break;
            }

            $query = "
						SELECT * FROM mdl_ildmeta
						WHERE
							university LIKE '$tosearch->university'
							AND subjectarea LIKE '$tosearch->subjectarea'
							AND courselanguage LIKE '$tosearch->courselanguage'
							AND processingtime $tosearch->processingtime
							AND starttime $tosearch->starttime";

            $coursestodisplay = $DB->get_records_sql($query);

            $result .= get_metacourses($coursestodisplay, $context);

        } else {
            if (!isset($this->searchresults)) {

                //get today midnight
                $to_midnight = strtotime('today midnight');

                //first of all get all courses which didn't started yet
                $sql_future = "SELECT * FROM {ildmeta} WHERE starttime >= ? ORDER BY starttime ASC, coursetitle ASC";
                $sql_param_f = array('starttime' => $to_midnight);
                $coursestodisplay_future = $DB->get_records_sql($sql_future, $sql_param_f);

                $result .= get_metacourses($coursestodisplay_future, $context);

                //and now get all courses which already begun
                $sql = "SELECT * FROM {ildmeta} WHERE starttime < ? ORDER BY starttime ASC, coursetitle ASC";
                $sql_param = array('starttime' => $to_midnight);
                $coursestodisplay = $DB->get_records_sql($sql, $sql_param);

                $result .= get_metacourses($coursestodisplay, $context);

            } else {
                $result .= get_metacourses($this->searchresults, $context);
            }
        }
        $this->content->text = $result;
        return $this->content;
    }
}
