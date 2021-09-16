<?php
/**
 *
 * @package       block_isymetaselect
 * @author        Dustin Neß <dustin.ness@th-luebeck.de>
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once 'lib.php';
require_once 'locallib.php';
require_once 'includes/filter_form.php';
require_once 'includes/search_form.php';
require_once 'includes/get_tiles.php';

class block_isymetaselect extends block_base {

    public $searchresults;

    public function init() {
        global $PAGE;
        
        $this->title = get_string('pluginname', 'block_isymetaselect');
    }

    public function hide_header() { return false; }

    function array_search_partial($arr, $keyword) {
        foreach ($arr as $index => $string) {
            if (strpos($string, $keyword) !== false) return $index;
        }
    }

    public function searchterm($searchterm) {

        global $DB;

        $meta2s = $DB->get_record('user_info_field', array('shortname' => 'isymeta_de_targetgroups')); // vorher: universities
        $meta2 = explode("\n", $meta2s->param1);
        $meta2_query = '';

        foreach ($meta2 as $key => $part) {
            if (stripos($part, $searchterm) !== false) {
                $meta2_query .= "OR meta2 LIKE '$key' ";
            }
        }

        $meta6s = $DB->get_record('user_info_field', array('shortname' => 'isymeta_de_formats')); // vorher: meta6s
        $meta6 = explode("\n", $meta6s->param1);
        $meta6_query = '';

        foreach ($meta6 as $key => $part) {
            if (stripos($part, $searchterm) !== false) {
                $meta6_query .= "OR meta6 LIKE '%$key%' ";
            }
        }

        //Zu durchsuchende Bereiche: Kurstitel, Tags, Kursbeschreibungstext, Metainfos, Vorname + Nachname weiterer Autoren
        $searchquery = "SELECT * FROM mdl_isymeta
						WHERE
                        (coursetitle LIKE :coursetitle
							OR tags LIKE :tags
							OR teasertext LIKE :teasertext
							OR meta3 LIKE :meta3
							$meta2_query
							$meta6_query
							OR courselanguage LIKE :courselanguage
							OR detailslecturer LIKE :detailslecturer
                            OR detailsmorelecturer LIKE :detailsmorelecturer)";
        $to_midnight = strtotime('today midnight');
        $searchquery_past = $searchquery . " AND meta5 <= $to_midnight ORDER BY meta5 DESC, coursetitle ASC";
        $searchquery_future = $searchquery . " AND meta5 > $to_midnight ORDER BY meta5 ASC, coursetitle ASC";
        $param = array(
          'coursetitle' => '%' . $searchterm . '%',
          'tags' => '%' . $searchterm . '%',
          'teasertext' => '%' . $searchterm . '%',
          'meta3' => '%' . $searchterm . '%',
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

        $table = 'isymeta';
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
        $data->meta6 = optional_param('meta6', 1, PARAM_INT);
        $data->meta2 = optional_param('meta2', 1, PARAM_INT);
        $data->meta4 = optional_param('meta4', "all", PARAM_TEXT);
        $data->meta5 = optional_param('meta5', "all", PARAM_TEXT);

        $records = get_courses_records($data);

        $customdata = array();
        $customdata['meta2_list'] = get_filtered_meta2_list($records);
        $customdata['meta6_list'] = get_filtered_meta6_list($records);
        $customdata['meta4_list'] = get_filtered_meta4_list($records);
        $customdata['meta5_list'] = get_filtered_meta5_list($records);
        $customdata['lang_list'] = get_filtered_lang_list($records);
        $customdata['data'] = $data;
        
        $mform = new filter_form($PAGE->url->out(false), $customdata);
        $result .= $mform->render();

        if ($mform->is_cancelled()) {
            // not possible
            redirect($CFG->wwwroot);
        } else if ($fromform = $mform->get_data()) {
            if(isset($fromform->search) && $fromform->search !== '') {
                $this->searchterm($fromform->search);
                $result .= get_tiles($this->searchresults, $context);
            } else {
                $coursestodisplay = get_courses_records($data);
                $result .= get_tiles($coursestodisplay, $context);
            }
            //$coursestodisplay = get_courses_records($fromform);
        } else {
            if (!isset($this->searchresults)) {

                //get today midnight
                $to_midnight = strtotime('today midnight');

                //$sql_future = "SELECT * FROM {isymeta} ORDER BY meta5 ASC, coursetitle ASC";
                //$coursestodisplay = $DB->get_records_sql($sql_future);


                //first of all get all courses which already started (newest first)
                $sql_running = "SELECT * FROM {isymeta} WHERE meta5 < ? ORDER BY meta5 DESC, coursetitle ASC";
                $sql_param_r = array('meta5' => $to_midnight);
                $coursestodisplay_runnig = $DB->get_records_sql($sql_running, $sql_param_r);

                // and now get all courses which will start in the future (starting soon first)
                $sql_future = "SELECT * FROM {isymeta} WHERE meta5 >= ? ORDER BY meta5 ASC, coursetitle ASC";
                $sql_param_f = array('meta5' => $to_midnight);
                $coursestodisplay_future = $DB->get_records_sql($sql_future, $sql_param_f);
                
                $coursestodisplay = array_merge($coursestodisplay_runnig, $coursestodisplay_future);

                $result .= get_tiles($coursestodisplay, $context);
            } else {
                $result .= get_tiles($this->searchresults, $context);
            }
        }
        $this->content->text = $result;

        $this->page->requires->js_call_amd('block_isymetaselect/isymetaselect', 'init', array());

        return $this->content;
    }
}
