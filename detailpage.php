<?php
/**
 *
 * @package        block_ildmetaselect
 * @author         Dustin Neß <dustin.ness@th-luebeck.de>
 * @author         Markus Strehling (modified) <markus.strehling@oncampus.de>
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../config.php');
require_once('lib.php');
//require_once('ildmeta_form.php');

//require_login();

$tbl = 'ildmeta';
$courseid = required_param('id', PARAM_INT);
$getdb = $DB->get_record($tbl, array('courseid' => $courseid));

$url = new moodle_url('/blocks/ildmetaselect/detailpage.php?id='.$courseid);
$url_edit = new moodle_url('/local/ildmeta/pages/ildmeta.php?courseid='.$courseid);

$context = context_system::instance();

$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($getdb->coursetitle);
$PAGE->set_heading($getdb->coursetitle);

$universities = $DB->get_record('user_info_field', array('shortname' => 'targetgroups')); // vorher: universities
$subjectareas = $DB->get_record('user_info_field', array('shortname' => 'formats')); // vorher: subjectareas
#$unis = explode("\n", $universities->param1);
switch(current_language()){
    case 'de':
        $unis = explode("\n", $universities->param1);
        $subject = explode("\n", $subjectareas->param1)[$getdb->subjectarea];
        break;
    case 'en':
        $unis = explode("\n", $universities->param2);
        $subject = explode("\n", $subjectareas->param2)[$getdb->subjectarea];
        break;
    default:
        $unis = explode("\n", $universities->param1);
        $subject = explode("\n", $subjectareas->param1)[$getdb->subjectarea];
        break;
}
$uni = "";
foreach(explode(",", $getdb->university) as $uni_select){
    $uni .= "<span>" . $unis[$uni_select] . "</span></br>";
}
#$subject = explode("\n", $subjectareas->param1)[$getdb->subjectarea];
$starttime = date('d.m.y', $getdb->starttime);
$started = $getdb->starttime < time();

$fs = get_file_storage();
$fileurl = '';
$context = context_system::instance();
$coursecontext = context_course::instance($courseid);
$files = $fs->get_area_files($coursecontext->id, 'local_ildmeta', 'overviewimage', 0);

foreach ($files as $file) {
    //if ($file->get_itemid() == $getdb->overviewimage && $file->get_filename() !== '.') {
	if ($file->get_filename() !== '.') {
        $fileurl = moodle_url::make_pluginfile_url(
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

$files = $fs->get_area_files($coursecontext->id, 'local_ildmeta', 'detailimage', 0);
foreach ($files as $file) {
    //if ($file->get_itemid() == $getdb->overviewimage && $file->get_filename() !== '.') {
	if ($file->get_filename() !== '.') {
        $fileurl_di = moodle_url::make_pluginfile_url(
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

//echo $fileurl;
$url = $CFG->wwwroot . '/course/view.php?id=' . $courseid;

#$detailimage = $CFG->wwwroot."/draftfile.php/4090/user/draft/$getdb->detailimage/Unbenannt.PNG";
#echo "<img src='$detailimage'></img>";

$video = '';
if ($getdb->videocode != null) {

/* Regex stolen from https://stackoverflow.com/questions/3392993/php-regex-to-get-youtube-video-id */ 
preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $getdb->videocode, $ytcode);
    
    // $video = format_text('<a href="'.$getdb->videocode.'">'.$getdb->videocode.'</a>');

    // $video = format_text(
    //     '<iframe width="100%" height="315" src="' . $getdb->videocode . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'
    // );
    #$video = str_replace('width="400"', 'width="100%"', $video);
    $videocode = $getdb->videocode;
    $video = '<iframe width="100%" height="315" src="https://www.youtube.com/embed/' . $videocode . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
}


$teasertext = '';
$learninggoals = '';
$gliederung = '';
$detailslecturer = '';
$detailsmorelecturer = '';
$detailssponsor = '';
$detailsmoresponsor = '';
$certificate = '';
$targetgroup = '';
$license = '';
// $sponsor = '';

/*
$tosearch->university = ($fromform->university == 0) ? '%' : $fromform->university-1;
*/

$lang_list = [
    'Deutsch',
    'Englisch'
];

$language = $lang_list[$getdb->courselanguage];

$licenses = $DB->get_records('license');

$license_text = 'Lizenziert unter'; //todo: in lang-file

if ($getdb->license != null) {
    $license_sn = $licenses[$getdb->license + 1]->shortname;

    switch ($license_sn) {
        case 'unknown':
            $license = '';
            break;
        case 'allrightsreserved':
        case 'public':
            $license = $licenses[$getdb->license + 1]->fullname;
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

$tags = null;
$tagarray = explode(',', $getdb->tags);
$taglist = array();
if ($getdb->tags != null) {
    foreach ($tagarray as $item) {
        $tagurl = new moodle_url($CFG->wwwroot, ['searchterm' => $item]);
        $taglist[] = array('tag'=>$item, 'url'=>strval($tagurl));
    }
}

$imgtbl = 'ildmeta_additional';
$image_count = $DB->count_records($imgtbl) / 3; // divide by 3 because 3 rows are generated per entry
$db_metadd = $DB->get_records($imgtbl, ['courseid' => $courseid]);


for ($i = 1; $i < $image_count; $i++) {

    $lect_text = $DB->get_records($imgtbl, ['courseid' => $courseid, 'name' => 'detailslecturer_editor_' . $i]);
    $lect_img = $DB->get_records($imgtbl, ['courseid' => $courseid, 'name' => 'detailslecturer_image_' . $i]);
    $lect_type = $DB->get_records($imgtbl, ['courseid' => $courseid, 'name' => 'lecturer_type_' . $i]);

    foreach ($lect_type as $type) {

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
            //$lecturer_files = $lect_fs->get_area_files($lect_context->id, 'local_ildmeta', 'detailslecturer_image_' . $i, $item->value);
			$coursecontext = context_course::instance($courseid);
			$lecturer_files = $lect_fs->get_area_files($coursecontext->id, 'local_ildmeta', 'detailslecturer_image_' . $i, 0);

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
    foreach ($lecturer_array as $lecturer => $data) {
        $class = '';

        //if empty or url broken
        if(empty($data[1]) || strpos($data[1], '/.')) {
            $class = 'lecturer-noimg';
        }

        $alllecturers[] = array('class' => $class, 'img' => $data[1], 'div' => $data[0]);
    }
}

$allauthors = array();
if (isset($authors_array)) {
    foreach ($authors_array as $author => $data) {
        $class = '';
        //if empty or url broken
        if(empty($data[1]) || strpos($data[1], '/.')) {
            $class = 'lecturer-noimg';
        }
        $allauthors[] = array('class' => $class, 'img' => $data[1], 'div' => $data[0]);
    }
}



/* Sponsors */

$imgtblspons = 'ildmeta_sponsors';
$image_count2 = $DB->count_records($imgtblspons);

for ($i = 1; $i < $image_count2; $i++) {

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
			$sponsor_files = $spons_fs->get_area_files($coursecontext->id, 'local_ildmeta', 'detailssponsor_image_' . $i, 0);

            foreach ($sponsor_files as $file) {
                $fileurl_sponsor = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
            }

            $sponsor_array[$i][] = $fileurl_sponsor; 
        }
}

$allsponsors = array();
if (isset($sponsor_array)) {
    
    // var_dump($sponsor_array);
    foreach ($sponsor_array as $sponsor => $data) {
        $class = '';
        //if empty or url broken
        if(empty($data[1]) || strpos($data[1], '/.')) {
            $class = 'sponsor-noimg';
        }
        // print_r($data[2]); die();
        $allsponsors[] = array('classspons' => $class, 'imgspons' => $data[1], 'divspons' => $data[0]);


    }
}

// print_r($allsponsors); die();











/*
 * START is_enrolled verification
 * let's check, if a user is enrolled
 * if true, display direct to course btn
 * if false, display enroll btn
 * output $enroll_btn in line 284 (11-30-2018)
 * ATTENTION: Make sure the enrollment plugin "auto enrol" is available and activated!
*/

$context = context_course::instance($courseid);
$is_enrolled = false;

if (isset($USER->id)) {
    $is_enrolled = is_enrolled($context, $USER->id, '', true);
}

/*
 * END is_enrolled verification
*/

echo $OUTPUT->header();

$render_data = new stdClass();
$render_data->url = $url;
$render_data->teasertext = $getdb->teasertext;
$render_data->videocode = $video;
$render_data->learninggoals = $getdb->learninggoals;
$render_data->gliederung = $getdb->structure;
$render_data->alllecturers = $alllecturers;
$render_data->alllecturers_notempty = !empty($alllecturers);
$render_data->allauthors = $allauthors;
$render_data->allauthors_notempty = !empty($allauthors);


$render_data->allsponsors = $allsponsors;
$render_data->allsponsors_notempty = !empty($allsponsors);

// print_r($render_data->allsponsors); die();

// var_dump($allsponsors); die();
// $render_data->sponsor = $sponsor;
$render_data->detailsmorelecturer = $getdb->detailsmorelecturer;
$render_data->targetgroup = $getdb->targetgroup;
$render_data->certificate = $getdb->certificateofachievement;
$render_data->license =  $license;
$render_data->taglist = $taglist;
$render_data->coursetitle = $getdb->coursetitle;
if(isset($edit_string)) {
    $render_data->edit_string = $edit_string;
}
$render_data->lecturer = $getdb->lecturer;
$render_data->uni = $uni;
$render_data->language = $language;
$render_data->subject = $subject;
$render_data->processingtime = $getdb->processingtime;

$render_data->started = $started;
$render_data->fileurl = $fileurl;
$render_data->is_enrolled = $is_enrolled;
if(isset($fileurl_di)) {
    $render_data->altpic = $fileurl_di;
}

$render_data->lecturer_detail = get_string('lecturer_detail', 'block_ildmetaselect');
$render_data->university_detail = get_string('university_detail', 'block_ildmetaselect');
$render_data->courselanguage_detail = get_string('courselanguage_detail', 'block_ildmetaselect');
$render_data->subjectarea_detail = get_string('subjectarea_detail', 'block_ildmetaselect');
$render_data->avgworkload_detail = get_string('avgworkload_detail', 'block_ildmetaselect');
$render_data->starttime_detail = get_string('starttime_detail', 'block_ildmetaselect');
$render_data->hours = get_string('hours', 'block_ildmetaselect');
$render_data->free = get_string('free', 'block_ildmetaselect');
$render_data->enrol = get_string('enrol', 'block_ildmetaselect');
$render_data->tocourse = get_string('tocourse', 'block_ildmetaselect');

$render_data->h_awaits = get_string('h_awaits', 'block_ildmetaselect');
$render_data->h_learn = get_string('h_learn', 'block_ildmetaselect');
$render_data->h_outline = get_string('h_outline', 'block_ildmetaselect');
$render_data->h_further_auth = get_string('h_further_auth', 'block_ildmetaselect');
$render_data->h_target_group = get_string('h_target_group', 'block_ildmetaselect');
$render_data->h_confirmation = get_string('h_confirmation', 'block_ildmetaselect');
$render_data->licensetitle = get_string('license', 'block_ildmetaselect');

$render_data->emptyteaser = 'emptyteaser';

$render_data->lecturer_type = 'Autor/in';

$render_data->edit_string = '';

$coursecontext = context_course::instance($courseid);
if (has_capability('local/ildmeta:allowaccess', $coursecontext)) {
    $render_data->edit_string = '<a href="' . $url_edit . '" class="metaedit"><i class="icon fa fa-cog fa-fw"></i></a>';
}



if(explode("\n", $subjectareas->param1)[$getdb->subjectarea] == 'Betreuter Kurs') {
    $render_data->lecturer_type = 'Dozent/in';
}

$render_data->starttime = 'Flexibel';

if(explode("\n", $subjectareas->param1)[$getdb->subjectarea] == 'Betreuter Kurs') {
    $render_data->starttime = $starttime;
}
// print_r($allsponsors); die();
$display = $OUTPUT->render_from_template("block_ildmetaselect/detailpage", $render_data);

//$mform->display();
echo $display;
//$cluster = $DB->get_records($tbl);

echo $OUTPUT->footer();
