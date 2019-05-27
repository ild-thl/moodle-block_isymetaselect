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

$url = new moodle_url('/block/ildmetaselect/detailpage.php');
//require_login();

$tbl = 'ildmeta';
$courseid = required_param('id', PARAM_INT);
$getdb = $DB->get_record($tbl, array('courseid' => $courseid));

$context = context_system::instance();

$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($getdb->coursetitle);
$PAGE->set_heading($getdb->coursetitle);

$universities = $DB->get_record('user_info_field', array('shortname' => 'universities'));
$subjectareas = $DB->get_record('user_info_field', array('shortname' => 'subjectareas'));
$uni = explode("\n", $universities->param1)[$getdb->university];
$subject = explode("\n", $subjectareas->param1)[$getdb->subjectarea];
$starttime = date('d.m.y', $getdb->starttime);

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
//echo $fileurl;
$url = $CFG->wwwroot . '/course/view.php?id=' . $courseid;

$video = '';
if ($getdb->videocode != null) {

/* Regex stolen from https://stackoverflow.com/questions/3392993/php-regex-to-get-youtube-video-id */ 
preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $getdb->videocode, $ytcode);
    
    $video = format_text('<a href="'.$getdb->videocode.'">'.$getdb->videocode.'</a>');
    $videocode = $getdb->videocode;
}


$teasertext = '';
$learninggoals = '';
$gliederung = '';
$detailslecturer = '';
$detailsmorelecturer = '';
$certificate = '';
$targetgroup = '';
$license = '';

$lang_list = [
    'Deutsch',
    'Englisch'
];

$language = $lang_list[$getdb->courselanguage];

$licenses = $DB->get_records('license');


if ($getdb->license != null && $getdb->license != 0) {
    $license = $licenses[$getdb->license + 1]->fullname;
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
$render_data->detailsmorelecturer = $getdb->detailsmorelecturer;
$render_data->targetgroup = $getdb->targetgroup;
$render_data->certificate = $getdb->certificateofachievement;
$render_data->license =  $license;
$render_data->taglist = $taglist;
$render_data->coursetitle = $getdb->coursetitle;
$render_data->edit_string = $edit_string;
$render_data->lecturer = $getdb->lecturer;
$render_data->uni = $uni;
$render_data->language = $language;
$render_data->subject = $subject;
$render_data->processingtime = $getdb->processingtime;
$render_data->starttime = $starttime;
$render_data->fileurl = $fileurl;
$render_data->is_enrolled = $is_enrolled;


$display = $OUTPUT->render_from_template("block_ildmetaselect/detailpage", $render_data);

echo $display;

echo $OUTPUT->footer();