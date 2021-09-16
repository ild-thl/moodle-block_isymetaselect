<?php
/**
 *
 * @package        block_isymetaselect
 * @author         Dustin Neß <dustin.ness@th-luebeck.de>
 * @author         Markus Strehling (modified) <markus.strehling@oncampus.de>
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 // returns tiles as HTML string
function get_tiles($coursestodisplay, $context)
{
    global $DB, $CFG, $OUTPUT;

    $comp = 'block_isymetaselect';

    $lang_list = [
        get_string('filter_german', $comp),
        get_string('filter_english', $comp),
    ];

    $string = '';
    // $string .= $a[0];
    $string .= '<div class="metatile-container"><div class="metatile-grid">';

    $fs = get_file_storage();

    if (!empty($coursestodisplay)) {

        foreach ($coursestodisplay as $data) {

            if ($DB->get_record('course', array('id' => $data->courseid))) {

                if ($data->noindexcourse == 1) continue; // hide course when index setting is 'no'

                $meta2s = $DB->get_record('user_info_field', array('shortname' => 'isymeta_de_targetgroups'));
                $meta2s_en = $DB->get_record('user_info_field', array('shortname' => 'isymeta_en_targetgroups'));
                $meta6s = $DB->get_record('user_info_field', array('shortname' => 'isymeta_de_formats'));
                $meta6s_en = $DB->get_record('user_info_field', array('shortname' => 'isymeta_en_formats'));

                $fileurl = '';
                switch(current_language()){
                    case 'de':
                        $meta2vals = explode("\n", $meta2s->param1);
                        $meta6val = explode("\n", $meta6s->param1)[$data->meta6];
                        break;
                    case 'en':
                        $meta2vals = explode("\n", $meta2s_en->param1);
                        $meta6val = explode("\n", $meta6s_en->param1)[$data->meta6];
                        break;
                    default:
                        $meta2vals = explode("\n", $meta2s->param1);
                        $meta6val = explode("\n", $meta6s->param1)[$data->meta6];
                        break;
                }
                $meta2val = "";
                foreach(explode(",", $data->meta2) as $meta2val_select){
                    $meta2val .= "<span>" . $meta2vals[$meta2val_select] . "</span>";
                }
                
                //if meta5 < today then echo "fortlaufend" instead of date
                //get today midnight
                $to_midnight = strtotime('today midnight');
                $meta5 = date('d.m.y', $data->meta5);

                $url = $CFG->wwwroot . '/blocks/isymetaselect/coursedetails.php?id=' . $data->courseid;
                //$files = $fs->get_area_files($context->id, 'local_isymeta', 'overviewimage', $data->overviewimage);
                $coursecontext = context_course::instance($data->courseid);
                $files = $fs->get_area_files($coursecontext->id, 'local_isymeta', 'overviewimage', 0);


                $getdb = $DB->get_record('isymeta', array('courseid' => $data->courseid));

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
                $render_data->meta3 = $data->meta3; // Dozent*in
                $render_data->uni = $meta2val;
                $render_data->language = $language;
                $render_data->subject = $meta6val;
                $render_data->meta4 = $data->meta4;
                $render_data->link_coursedetails = $data->noindexcourse == 0;
                




                // Meta strings
                $userfield_isymeta_de = $DB->get_record('user_info_field', array('shortname' => 'isymeta_de'));

                if(isset($userfield_isymeta_de)) {
                    $userfield_metas = explode("\n", $userfield_isymeta_de->param1);
                    
                    $render_data->meta1_name = $userfield_metas[0];
                    $render_data->meta2_name = $userfield_metas[1];
                    $render_data->meta3_name = $userfield_metas[2];
                    $render_data->meta4_name = $userfield_metas[3];
                    $render_data->meta5_name = $userfield_metas[4];
                    $render_data->meta6_name = $userfield_metas[5];
                } else {
                    $render_data->meta1_name = get_string('meta1_name', $comp); // target group
                    $render_data->meta2_name = get_string('meta2_name', $comp); // program
                    $render_data->meta3_name = get_string('meta3_name', $comp); // lecturer
                    $render_data->meta4_name = get_string('meta4_name', $comp); // worktime
                    $render_data->meta5_name = get_string('meta5_name', $comp); // course start
                    $render_data->meta6_name = get_string('meta6_name', $comp); // format
                }

                $render_data->courselanguage_detail = get_string('filter_courselanguage', $comp);
                $render_data->hours = get_string('hours', $comp);
                $render_data->button_search = get_string('button_search', $comp);
                $render_data->button_reset = get_string('button_reset', $comp);

                // determine if author or lecturer string
                $render_data->lecturer_type = 'Autor/in';

                if (explode("\n", $meta6s->param1)[$getdb->meta6] == 'Betreuter Kurs') {
                    $render_data->lecturer_type = 'Dozent/in';
                }
                
                $render_data->meta5 = 'Flexibel';
                
                if (explode("\n", $meta6s->param1)[$getdb->meta6] == 'Betreuter Kurs') {
                    if ($data->meta5 > $to_midnight) {
                        $render_data->meta5 = $meta5;
                    } else {
                        $render_data->meta5 = get_string('started', $comp) . ' (' . $meta5 . ')';
                    }
                }
                
                // $display = $OUTPUT->render_from_template("block_isymetaselect/coursedetails", $render_data);

                $string .= $OUTPUT->render_from_template("block_isymetaselect/tile", $render_data);
            }
        }
    } else {
        $string .= '<span class="nocoursefound">' . get_string('noresultsfound', $comp) . '</span>';
    }
    $string .= '</div></div>';

    return $string;
}