<?php
/**
 *
 * @package        block_ildmetaselect
 * @author         Dustin Neß <dustin.ness@th-luebeck.de>
 * @author         Markus Strehling (modified) <markus.strehling@oncampus.de>
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function get_metacourses($coursestodisplay, $context)
{
    global $DB, $CFG, $OUTPUT;

    $lang_list = [
        get_string('german', 'block_ildmetaselect'),
        get_string('english', 'block_ildmetaselect'),
    ];


    $string = '';

    $string .= '<div class="metatile-container">';

    $fs = get_file_storage();

    if (!empty($coursestodisplay)) {

        foreach ($coursestodisplay as $data) {
            if ($coursecheck = $DB->get_record('course', array('id' => $data->courseid))) {

                if ($data->noindexcourse == 1) continue; // hide course when index setting is 'no'

                $universities = $DB->get_record('user_info_field', array('shortname' => 'universities'));
                $subjectareas = $DB->get_record('user_info_field', array('shortname' => 'subjectareas'));

                $fileurl = '';
                switch(current_language()){
                    case 'de':
                        $unis = explode("\n", $universities->param1);
                        $subject = explode("\n", $subjectareas->param1)[$data->subjectarea];
                        break;
                    case 'en':
                        $unis = explode("\n", $universities->param2);
                        $subject = explode("\n", $subjectareas->param2)[$data->subjectarea];
                        break;
                    default:
                        $unis = explode("\n", $universities->param1);
                        $subject = explode("\n", $subjectareas->param1)[$data->subjectarea];
                        break;
                }
                $uni = "";
                foreach(explode(",", $data->university) as $uni_select){
                    $uni .= "<span>" . $unis[$uni_select] . "</span>";
                }
                
                //if starttime < today then echo "fortlaufend" instead of date
                //get today midnight
                $to_midnight = strtotime('today midnight');
                $starttime = date('d.m.y', $data->starttime);

                $url = $CFG->wwwroot . '/blocks/ildmetaselect/detailpage.php?id=' . $data->courseid;
                //$files = $fs->get_area_files($context->id, 'local_ildmeta', 'overviewimage', $data->overviewimage);
                $coursecontext = context_course::instance($data->courseid);
                $files = $fs->get_area_files($coursecontext->id, 'local_ildmeta', 'overviewimage', 0);


                $getdb = $DB->get_record('ildmeta', array('courseid' => $data->courseid));

                $language = $lang_list[$getdb->courselanguage];

                foreach ($files as $file) {
                    //if ($file->get_itemid() == $data->overviewimage && $file->get_filename() !== '.') {
                    if ($file->get_itemid() == 0 && $file->get_filename() !== '.') {
                        $fileurl = moodle_url::make_pluginfile_url(
                            $file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            $file->get_itemid(),
                            $file->get_filepath(),
                            $file->get_filename()
                        );
                    }
                }

                $render_data = new stdClass();
                $render_data->url = $url;
                $render_data->fileurl = $fileurl;
                $render_data->coursetitle = $data->coursetitle;
                $render_data->lecturer = $data->lecturer;
                $render_data->uni = $uni;
                $render_data->language = $language;
                $render_data->subject = $subject;
                $render_data->processingtime = $data->processingtime;
                $render_data->link_detailpage = $data->noindexcourse == 0;
                if ($data->starttime > $to_midnight) {
                    $render_data->starttime = $starttime;
                }

                $render_data->lecturer_detail = get_string('lecturer_detail', 'block_ildmetaselect');
                $render_data->university_detail = get_string('university_detail', 'block_ildmetaselect');
                $render_data->courselanguage_detail = get_string('courselanguage_detail', 'block_ildmetaselect');
                $render_data->subjectarea_detail = get_string('subjectarea_detail', 'block_ildmetaselect');
                $render_data->avgworkload_detail = get_string('avgworkload_detail', 'block_ildmetaselect');
                $render_data->hours = get_string('hours', 'block_ildmetaselect');
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
