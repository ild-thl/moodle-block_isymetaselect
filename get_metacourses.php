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
 * Function that generates a list of coursedata for a set of courses that are indexed by the local_ildmeta plugin.
 *
 * @package     block_ildmetaselect
 * @author      Dustin Neß <dustin.ness@th-luebeck.de>
 * @author      Markus Strehling (modified) <markus.strehling@oncampus.de>
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_ildmeta\manager;

function get_metacourses($coursestodisplay, $context) {
    global $DB, $CFG, $OUTPUT;

    $lang_list = [
        get_string('german', 'block_ildmetaselect'),
        get_string('english', 'block_ildmetaselect'),
    ];


    $string = '';

    $string .= '<div class="metatile-container">';
    $string .= '<span id="ildmetaselect_form_anchor_results"></span>';

    $fs = get_file_storage();

    if (!empty($coursestodisplay)) {

        foreach ($coursestodisplay as $data) {
            if ($coursecheck = $DB->get_record('course', array('id' => $data->courseid))) {

                if ($data->noindexcourse == 1) {
                    continue;
                } // Hide course when index setting is 'no'.

                // Get subjectarea vocabulary from ildmeta_vocabulary.
                $providers = manager::get_providers();
                $vocabulary = $DB->get_record('ildmeta_vocabulary', array('title' => 'subjectarea'), '*', MUST_EXIST);
                $subjectareas = manager::filter_vocabulary_lang($vocabulary, current_language());
                $subject = $subjectareas[$data->subjectarea];
                $fileurl = '';

                $uni = "<span>" . $providers[$data->provider]['name'] . "</span>";

                //if starttime < today then echo "fortlaufend" instead of date
                //get today midnight
                $to_midnight = strtotime('today midnight');
                $starttime = date('d.m.y', $data->starttime);
// CHANGED tinjohn 20220920.
                $add_detail_page = get_config('block_ildmetaselect','add_detail_page');
                if($add_detail_page) {
                  $url = $CFG->wwwroot . '/blocks/ildmetaselect/detailpage.php?id=' . $data->courseid;
                } else {
                  // Set ÚRL of course. SingleSignOn enabled.
                  $url = new \moodle_url('/course/view.php', array('id' => $data->courseid, 'saml' => 'on'));
                }
// END CHANGED.

                //$files = $fs->get_area_files($context->id, 'local_ildmeta', 'overviewimage', $data->overviewimage);
                $coursecontext = context_course::instance($data->courseid);

                $getdb = $DB->get_record('ildmeta', array('courseid' => $data->courseid));

                $language = $lang_list[$getdb->courselanguage];

                // Get url of overview image.
                // If no custom image ist set in ildmeta, then use the course image instead.
                if (isset($getdb->overviewimage)) {
                    $files = $fs->get_area_files($coursecontext->id, 'local_ildmeta', 'overviewimage', 0);
                } else {
                    $files = $fs->get_area_files($coursecontext->id, 'course', 'overviewfiles', 0);
                }
                foreach ($files as $file) {
                    if ($file->is_valid_image()) {
                        $fileurl = moodle_url::make_pluginfile_url(
                            $file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            isset($getdb->overviewimage) ? $file->get_itemid() : null,
                            $file->get_filepath(),
                            $file->get_filename(),
                            false
                        );
                        break;
                    }
                }

                $render_data = new stdClass();
                $render_data->url = $url;
                $render_data->fileurl = $fileurl;
                $render_data->coursetitle = $data->coursetitle;
                $render_data->lecturer = $data->lecturer;

                if (get_config('block_ildmetaselect','add_formmenu_courselanguage')) {
                  $render_data->language = $language;
                }
                if(	get_config('block_ildmetaselect','add_formmenu_provider')) {
                  $render_data->uni = $uni;
                }
                $render_data->subject = $subject;
                $render_data->processingtime = $data->processingtime;
                $render_data->link_detailpage = $data->noindexcourse == 0;
                if ($data->starttime > $to_midnight) {
                    $render_data->starttime = $starttime;
                }

                $render_data->lecturer_detail = get_string('lecturer_detail', 'block_ildmetaselect');
                $render_data->provider_detail = get_string('provider_detail', 'block_ildmetaselect');
                $render_data->courselanguage_detail = get_string('courselanguage_detail', 'block_ildmetaselect');
                $render_data->subjectarea_detail = get_string('subjectarea_detail', 'block_ildmetaselect');
                $render_data->avgworkload_detail = get_string('avgworkload_detail', 'block_ildmetaselect');
// CHANGED tinjohn 20220920.
                if (get_config('block_ildmetaselect','workload_in_min')) {
                  $render_data->hours = get_string('minutes', 'block_ildmetaselect');
                } else {
                  $render_data->hours = get_string('hours', 'block_ildmetaselect');
                }
// END CHANGED.

                $render_data->starttime_detail = get_string('starttime_detail', 'block_ildmetaselect');

                $string .= $OUTPUT->render_from_template("block_ildmetaselect/get_metacourse", $render_data);
            }
        }
    } else {
        $string .= '<span class="nocoursefound">' . get_string('noresultsfound', 'block_ildmetaselect') . '</span>';
    }
    $string .= '</div>';

    return $string;
}
