<?php
/**
 *
 * @package        block_isymetaselect
 * @author         Dustin Neß <dustin.ness@th-luebeck.de>
 * @author         Markus Strehling (modified) <markus.strehling@oncampus.de>
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
require_once 'lib.php';
require_once 'classes/metastring.php';
require_once 'classes/metaselection.php';

/* Rendering og course detail page */

$comp = 'block_isymetaselect';
$tbl = 'isymeta';
$courseid = required_param('id', PARAM_INT);
$url = $CFG->wwwroot . '/course/view.php?id=' . $courseid;
$data = $DB->get_record($tbl, array('courseid' => $courseid));
$url = new moodle_url('/blocks/isymetaselect/detailpage.php?id='.$courseid);
$url_edit = new moodle_url('/local/isymeta/pages/isymeta.php?courseid='.$courseid);
$context = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($data->coursetitle);
$PAGE->set_heading($data->coursetitle);

// Helpers
$metastring = new Metastring(); // classes/metastring.php -> gets the correct string based on lang
$metaselection = new Metaselection(); // classes/metaselection.php -> gets the string of the meta the admin defined
$fs = get_file_storage();

$context = context_system::instance();
$coursecontext = context_course::instance($courseid);


$meta5 = date('d.m.y', $data->meta5);
$started = $data->meta5 < time();

$teasertext = '';
$targetgroup = '';
$learninggoals = '';
$gliederung = '';
$certificate = '';

// tags
$rawtags = $data->tags;
$tags = null;
$tagarray = explode(',', $rawtags);
$taglist = array();
if ($data->tags != null) {
    foreach ($tagarray as $item) {
        $tagurl = new moodle_url($CFG->wwwroot, ['searchterm' => $item]);
        $taglist[] = array('tag'=>$item, 'url'=>strval($tagurl));
    }
}

/*
    Lecturers
*/ 

$imgtbl = 'isymeta_additional'; // isymeta_additional serves the lectureres
$image_count = $DB->count_records($imgtbl) / 3; // divide by 3 because 3 rows are generated per entry
$db_metadd = $DB->get_records($imgtbl, ['courseid' => $courseid]);

for ($i = 1; $i <= $image_count; $i++) {

    $lect_text = $DB->get_records($imgtbl, ['courseid' => $courseid, 'name' => 'detailslecturer_editor_' . $i]); // from textarea
    $lect_img = $DB->get_records($imgtbl, ['courseid' => $courseid, 'name' => 'detailslecturer_image_' . $i]); // from image upload form
    $lect_type = $DB->get_records($imgtbl, ['courseid' => $courseid, 'name' => 'lecturer_type_' . $i]); // from author or lecturer selection

    foreach ($lect_type as $type) {

        // if no text, skip. Otherwise assign to authors/lecturers array
        foreach ($lect_text as $item) {
            if ($item->value == null) {
                break 2;
            }

            if ($type->value == 1) {
                $lecturer_array[$i][] = $item->value;
            } else {
                $authors_array[$i][] = $item->value;
            }
        }
        
        foreach ($lect_img as $item) {

            $lect_fs = get_file_storage();
            $fileurl_lecturer = '';
            $lect_context = context_system::instance();
			$coursecontext = context_course::instance($courseid);
			$lecturer_files = $lect_fs->get_area_files($coursecontext->id, 'local_isymeta', 'detailslecturer_image_' . $i, 0);

            foreach ($lecturer_files as $file) {
                $fileurl_lecturer = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
            }

            if ($type->value == 1) {
                $lecturer_array[$i][] = $fileurl_lecturer;
                
            } else {
                $authors_array[$i][] = $fileurl_lecturer;
                
            }
        }
    }
}

$alllecturers = array();
if (isset($lecturer_array)) {
   
    foreach ($lecturer_array as $lecturer => $data1) {
        $class = '';

        //if empty or url broken
        if(empty($data1[1]) || strpos($data1[1], '/.') !== false) {
            $class = 'lecturer-noimg';
        }

        $alllecturers[] = array('class' => $class, 'img' => $data1[1], 'div' => $data1[0]);
    }
}

$allauthors = array();
if (isset($authors_array)) {
    foreach ($authors_array as $author => $data2) {
        $class = '';

        //if empty or url broken
        if(empty($data2[1]) || strpos($data2[1], '/.') !== false) {
            $class = 'lecturer-noimg';
        }
        $allauthors[] = array('class' => $class, 'img' => $data2[1], 'div' => $data2[0]);
    }
}

/*
    Sponsors
*/

$detailssponsor = '';
$detailsmoresponsor = '';
$imgtblspons = 'isymeta_sponsors';
$image_count2 = $DB->count_records($imgtblspons) / 2;
// print_r($image_count2);

for ($i = 1; $i <= $image_count2; $i++) {

    $spons_text = $DB->get_records($imgtblspons, ['courseid' => $courseid, 'name' => 'detailssponsor_link_' . $i]);
    $spons_img = $DB->get_records($imgtblspons, ['courseid' => $courseid, 'name' => 'detailssponsor_image_' . $i]);

        foreach ($spons_text as $item) {
            if ($item->value == null) {
                break 2;
            }

            $sponsor_array[$i][] = $item->value;
        }

        foreach ($spons_img as $item) {
            $spons_fs = get_file_storage();
            $fileurl_sponsor = '';
            $spons_context = context_system::instance();
			$coursecontext = context_course::instance($courseid);
			$sponsor_files = $spons_fs->get_area_files($coursecontext->id, 'local_isymeta', 'detailssponsor_image_' . $i, 0);

            foreach ($sponsor_files as $file) {
                $fileurl_sponsor = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
            }

            $sponsor_array[$i][] = $fileurl_sponsor; 
        }
}

$allsponsors = array();
if (isset($sponsor_array)) {
    
    foreach ($sponsor_array as $sponsor => $data2) {
        $class2 = '';
        $imgspons = '';
        //if empty or url broken
        if(empty($data2[1]) || strpos($data2[1], '/.') !== false) {
            $class2 = 'sponsor-noimg';
        } else {
            $imgspons = $data2[1];
        }
        $allsponsors[] = array('classspons' => $class2, 'imgspons' => $imgspons, 'divspons' => $data2[0]);


    }
}




/*
    OUTPUT
*/

echo $OUTPUT->header();

$render_data = new stdClass();
$render_data->url = $url;

// Edit cog
if(isset($edit_string)) $render_data->edit_string = $edit_string;

// Tile image
$files_tileimage = $fs->get_area_files($coursecontext->id, 'local_isymeta', 'overviewimage', 0);
foreach ($files_tileimage as $file) {
	if ($file->get_filename() !== '.') {
        $overviewimage = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename(),
			false
        );
    }
}

$render_data->coursetitle = $data->coursetitle ?? '';
$render_data->overviewimage = $overviewimage ?? '';

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

/*
    Course Detail Metas
*/

$render_data->teasertext    = $data->teasertext;
$render_data->targetgroup   = $data->targetgroup;
$render_data->learninggoals = $data->learninggoals;

// License
$license = '';
$licenses = $DB->get_records('license');
$license_text = 'Lizenziert unter';

if ($data->license != null) {
    $license_sn = $licenses[$data->license + 1]->shortname;

    // we need to manually add this licenses as moodles built in ones are either not complete or not that markup we need
    switch ($license_sn) {
        case 'unknown':
            $license = '';
            break;
        case 'allrightsreserved':
        case 'public':
            $license = $licenses[$data->license + 1]->fullname;
            break;
        case 'cc':
            $license = '<p xmlns:cc="http://creativecommons.org/ns#" >'.$license_text.' <a href="http://creativecommons.org/licenses/by/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC BY 4.0<img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1"></a></p>';
            break;
        case 'cc-sa':
            $license = '<p xmlns:cc="http://creativecommons.org/ns#" >'.$license_text.' <a href="http://creativecommons.org/licenses/by-sa/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC BY-SA 4.0<img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/sa.svg?ref=chooser-v1"></a></p>';
            break;
        case 'cc-nd':
            $license = '<p xmlns:cc="http://creativecommons.org/ns#" >'.$license_text.' <a href="http://creativecommons.org/licenses/by-nd/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC BY-ND 4.0<img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/nd.svg?ref=chooser-v1"></a></p>';
            break;
        case 'cc-nc-nd':
            $license = '<p xmlns:cc="http://creativecommons.org/ns#" >'.$license_text.' <a href="http://creativecommons.org/licenses/by-nc-nd/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC BY-NC-ND 4.0<img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/nc.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/nd.svg?ref=chooser-v1"></a></p>';
            break;
        case 'cc-nc':
            $license = '<p xmlns:cc="http://creativecommons.org/ns#" >'.$license_text.' <a href="http://creativecommons.org/licenses/by-nc/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC BY-NC 4.0<img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/nc.svg?ref=chooser-v1"></a></p>';
            break;
        case 'cc-nc-sa':
            $license = '<p xmlns:cc="http://creativecommons.org/ns#" >'.$license_text.' <a href="http://creativecommons.org/licenses/by-nc-sa/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC BY-NC-SA 4.0<img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/nc.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/sa.svg?ref=chooser-v1"></a></p>';
            break;
        default:
        break;
    }
}

$render_data->license =  $license ?? '';

// Video
$video = '';

if ($data->videocode != null) {
    /* Regex from https://stackoverflow.com/questions/3392993/php-regex-to-get-youtube-video-id */ 
    preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $getdb->videocode, $ytcode);
    
    $videocode = $data->videocode;
    $video = '<nolink><iframe width="100%" height="315" src="https://www.youtube.com/embed/' . $videocode . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></nolink>';
}

$render_data->videocode = $video ?? '';

// Detailimage
$files_3 = $fs->get_area_files($coursecontext->id, 'local_isymeta', 'detailimage', 0);

foreach ($files_3 as $file) {
	if ($file->get_filename() !== '.') {
        $detailimage = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename(),
			false
        );
    }
}

$render_data->detailimage = $detailimage ?? '';

// Show course button if enroled
$context_course = context_course::instance($courseid);
$is_enrolled = false;

// for determining of button variant in meta header
if (isset($USER->id)) {
    $is_enrolled = is_enrolled($context_course, $USER->id, '', true);
}

$render_data->learninggoals = $data->learninggoals ?? '';
$render_data->gliederung = $data->structure ?? '';
$render_data->alllecturers = $alllecturers;
$render_data->alllecturers_notempty = !empty($alllecturers);
$render_data->allauthors = $allauthors ?? '';
$render_data->allauthors_notempty = !empty($allauthors);
$render_data->allsponsors = $allsponsors;
$render_data->allsponsors_notempty = !empty($allsponsors);
// $render_data->detailsmorelecturer = $data->detailsmorelecturer;
// $render_data->targetgroup = $data->targetgroup;
$render_data->certificate = $data->certificateofachievement ?? '';

$render_data->taglist = $taglist;


$render_data->started = $started;

$render_data->is_enrolled = $is_enrolled;
if(isset($fileurl_di)) $render_data->altpic = $fileurl_di;




// $render_data->lecturer_detail = get_string('lecturer_detail');
// $render_data->meta2_detail = get_string('meta2_name');
$render_data->courselanguage_detail = get_string('filter_courselanguage', $comp);
// $render_data->meta6_detail = get_string('meta6_name');
$render_data->avgworkload_detail = get_string('meta4_name', $comp);
// $render_data->meta5_detail = get_string('meta5_name');
$render_data->hours = get_string('hours', $comp);
$render_data->free = get_string('free', $comp);
$render_data->enrol = get_string('enrol', $comp);
$render_data->tocourse = get_string('tocourse', $comp);
$render_data->h_awaits = get_string('h_awaits', $comp);
$render_data->h_learn = get_string('h_learn', $comp);
$render_data->h_outline = get_string('h_outline', $comp);
$render_data->h_further_auth = get_string('h_further_auth', $comp);
$render_data->h_target_group = get_string('h_target_group', $comp);
$render_data->h_confirmation = get_string('h_confirmation', $comp);
$render_data->h_sponsors = get_string('h_sponsors', $comp);
$render_data->licensetitle = get_string('h_license', $comp);
$render_data->emptyteaser = 'emptyteaser';
$render_data->lecturer_type = 'Autor/in';
$render_data->edit_string = '';




// Add edit cog if allowed
if (has_capability('local/isymeta:allowaccess', $coursecontext)) {
    $render_data->edit_string = '<a href="' . $url_edit . '" class="metaedit"><i class="icon fa fa-cog fa-fw"></i></a>';
}



// $render_data->meta5 = 'Flexibel';

// if(explode("\n", $meta6s->param1)[$data->meta6] == 'Betreuter Kurs') {
//     $render_data->meta5 = $meta5;
// }


// explode("\n", $meta6s->param1)[$data->meta6] == 'Betreuter Kurs' ? $render_data->meta5 = $meta5 : $render_data->meta5 = 'Flexibel';

$display = $OUTPUT->render_from_template("block_isymetaselect/detailpage", $render_data);

echo $display;

echo $OUTPUT->footer();
