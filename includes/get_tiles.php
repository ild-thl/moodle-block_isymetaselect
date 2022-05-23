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

    $metastring = new Metastring();
    $metaselection = new Metaselection();

    $string = '';
    // $string .= $a[0];
    $string .= '<div class="metatile-container"><div class="metatile-grid">';

    $fs = get_file_storage();

    if (!empty($coursestodisplay)) {

        foreach ($coursestodisplay as $data) {

            if ($DB->get_record('course', array('id' => $data->courseid))) {

                if ($data->noindexcourse == 1) continue; // hide course when index setting is 'no'

                //if meta5 < today then echo "fortlaufend" instead of date
                //get today midnight
                
                $meta5 = date('d.m.y', $data->meta5);

                $url = $CFG->wwwroot . '/blocks/isymetaselect/coursedetails.php?id=' . $data->courseid;
                //$files = $fs->get_area_files($context->id, 'local_isymeta', 'overviewimage', $data->overviewimage);
                $coursecontext = context_course::instance($data->courseid);
                $files = $fs->get_area_files($coursecontext->id, 'local_isymeta', 'overviewimage', 0);


                $getdb = $DB->get_record('isymeta', array('courseid' => $data->courseid));

                // $language = $lang_list[$getdb->courselanguage];

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
                $render_data->fileurl = $fileurl ?? '';
                $render_data->coursetitle = $data->coursetitle;

                // Meta 1 - Standard: Zielgruppe
                $render_data->meta1_name = $metastring->get(0);
                $render_data->meta1 = $metaselection->get_meta(1)[$data->meta1];

                // Meta 2 - Standard: Programm
                $render_data->meta2_name = $metastring->get(1);
                $render_data->meta2 = $metaselection->get_meta(2)[$data->meta2];

                // Meta 3 - Standard: Autor*in
                $render_data->meta3_name = $metastring->get(2);

                if($metastring->get(2) === 'Autor/in' || $metastring->get(2) === 'Autor*in') { // if not changed string (or * variant) switch author types
                    if($data->supervised == '1') {
                        $render_data->meta3_name = 'Dozent*in';
                    } else {
                        $render_data->meta3_name = $metastring->get(2);
                    }
                }
                
                $render_data->meta3 = $data->meta3;

                // Meta 4 - Standard: Arbeitsaufwand
                $render_data->meta4_name = $metastring->get(3); 
                $render_data->meta4 = $data->meta4;

                // Meta 5 - Standard: Kursbeginn
                $to_midnight = strtotime('today midnight');
                $render_data->meta5_name = $metastring->get(4);
                $render_data->meta5 = 'flexibel';
                $render_data->hours = get_string('hours', $comp);

                if($data->supervised == '1') {
                    if ($data->meta5 > $to_midnight) {
                        $render_data->meta5 = $meta5;
                    } else {
                        $render_data->meta5 = get_string('started', $comp) . ' (' . $meta5 . ')';
                    }
                }
                
                // Meta 6 - Standard: Format
                $render_data->meta6_name = $metastring->get(5);
                $render_data->meta6 = $metaselection->get_meta(6)[$data->meta6];
                
                // $render_data->language = $language;
                
                
                $render_data->link_coursedetails = $data->noindexcourse == 0;

        
                $render_data->courselanguage_detail = get_string('filter_courselanguage', $comp);
                
                $render_data->button_search = get_string('button_search', $comp);
                $render_data->button_reset = get_string('button_reset', $comp);

                // $display = $OUTPUT->render_from_template("block_isymetaselect/coursedetails", $render_data);

                $string .= $OUTPUT->render_from_template("block_isymetaselect/tile", $render_data);
            }
        }
    } else {
        // no courses found
        $string .= '<span class="nocoursefound">' . get_string('noresultsfound', $comp) . '</span>';
    }
    $string .= '</div></div>';

    return $string;
}