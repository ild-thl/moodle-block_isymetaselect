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
 * Library of funtions for block_ildmetaselect.
 *
 * @package     block_ildmetaselect
 * @author     Markus Strehling <markus.strehling@oncampus.de>
 * @copyright   2022 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_ildmeta\manager;

function starttime_to_sql($starttime) {
    $time_now = time();
    $time_week = 604800;
    $time_day = 86400;

    switch ($starttime) {
        case '-':
        case 'all':
            return "LIKE '%'";
            break;
        case 'current':
            return "< " . $time_now;
            break;
        case 'less2weeks':
            return '>= ' . ($time_now + ($time_week * 2));
            break;
        case 'between3and4weeks':
            return 'BETWEEN ' . ($time_now + ($time_week * 2)) . ' AND ' . ($time_now + ($time_week * 4));
            break;
        case 'between5and6weeks':
            return 'BETWEEN ' . ($time_now + ($time_week * 4)) . ' AND ' . ($time_now + ($time_week * 6));
            break;
        case 'between7and8weeks':
            return 'BETWEEN ' . ($time_now + ($time_week * 6)) . ' AND ' . ($time_now + ($time_week * 8));
            break;
    }
    return '';
}

function is_time_in_area($starttime, $time) {
    $time_now = time();
    $time_week = 604800;
    $time_day = 86400;

    switch ($starttime) {
        case '-':
        case 'all':
            return true;
            break;
        case 'current':
            return  $time < $time_now;
            break;
        case 'less2weeks':
            return $time >= ($time_now + ($time_week * 2));
            break;
        case 'between3and4weeks':
            return ($time_now + ($time_week * 2)) <= $time && $time <= ($time_now + ($time_week * 4));
            break;
        case 'between5and6weeks':
            return ($time_now + ($time_week * 4)) <= $time && $time <= ($time_now + ($time_week * 6));
            break;
        case 'between7and8weeks':
            return ($time_now + ($time_week * 6)) <= $time && $time <= ($time_now + ($time_week * 8));
            break;
    }
    return false;
}

function processingtime_to_sql($processingtime) {
    switch ($processingtime) {
        case '-':
        case 'all':
            return "LIKE '%'";
            break;
        case 'upto15':
            return '<= 15';
            break;
        case 'between16and20':
            return 'BETWEEN 16 AND 20';
            break;
        case 'between21and25':
            return 'BETWEEN 21 AND 25';
            break;
        case 'morethan25':
            return '>= 26';
            break;
    }
    return '';
}

function it_time_in_processingtime($processingtime, $time) {
    switch ($processingtime) {
        case '-':
        case 'all':
            return true;
            break;
        case 'upto15':
            return $time <= 15;
            break;
        case 'between16and20':
            return 16 <= $time && $time <= 20;
            break;
        case 'between21and25':
            return 21 <= $time && $time <= 25;
            break;
        case 'morethan25':
            return $time >= 26;
            break;
    }
    return false;
}

function select_prepare($select) {
    if ($select == 1 || $select == 0) {
        return '%';
    } else {
        return $select - 2;
    }
}

function get_courses_records($fromform) {
    $past = get_courses_records_time($fromform, true);
    $future = get_courses_records_time($fromform, false);

    return array_merge($past, $future);
}

function get_courses_records_time($fromform, $past) {
    global $DB;

    $tosearch = new stdClass;
    $tosearch->courselanguage = select_prepare($fromform->courselanguage);
    $tosearch->subjectarea = select_prepare($fromform->subjectarea);
    $tosearch->provider = select_prepare($fromform->provider);
    $tosearch->processingtime = processingtime_to_sql($fromform->processingtime);
    $tosearch->starttime = starttime_to_sql($fromform->starttime);

    $to_midnight = strtotime('today midnight');

    $query = "
        SELECT * FROM {ildmeta}
            WHERE
                subjectarea LIKE '$tosearch->subjectarea'
                AND courselanguage LIKE '$tosearch->courselanguage'
                AND processingtime $tosearch->processingtime
                AND starttime $tosearch->starttime
                AND noindexcourse != 1";
    if ($past) {
        $query .= " AND starttime < $to_midnight ORDER BY starttime DESC, coursetitle ASC";
    } else {
        $query .= " AND starttime >= $to_midnight ORDER BY starttime ASC, coursetitle ASC";
    }

    // TODO: Improve the processing time
    $filtered = array();
    $records = $DB->get_records_sql($query);
    if ($tosearch->provider != "%") {
        foreach ($records as $id => $record) {
            $unis = explode(",", $record->provider);
            foreach ($unis as $uni) {
                if ($uni == $tosearch->provider) {
                    $filtered[$id] = $record;
                    break;
                }
            }
        }
    } else {
        $filtered = $records;
    }

    return $filtered;
}

function exist_courses_records($fromform) {
    $records = get_courses_records($fromform);

    return count($records) > 0;
}

function get_filtered_provider_list($records) {
    global $DB;

    $providers = manager::get_providers();

    $filtered = array();

    foreach ($providers as $key => $provider) {
        foreach ($records as $record) {
            if ($record->provider == $key) {
                $filtered[$key + 2] = ($key + 2) . "=>" . $provider['name'];
                break;
            }
        }
    }

    array_unshift($filtered, '1=>' . get_string('provider_all', 'block_ildmetaselect'));
    array_unshift($filtered, '0=>' . get_string('provider', 'block_ildmetaselect'));

    return $filtered;
}

function get_provider_list($input) {
    global $DB;

    $providers = manager::get_providers();

    $data = copy_to_data($input);
    $filtered = array();

    foreach ($providers as $key => $provider) {
        $data->provider = $key;
        if (exist_courses_records($data)) {
            $filtered[$key] = "$key=>" . $provider['name'];
        }
    }
    array_unshift($filtered, '1=>' . get_string('provider_all', 'block_ildmetaselect'));
    array_unshift($filtered, '0=>' . get_string('provider', 'block_ildmetaselect'));

    return $filtered;
}

function get_filtered_subjectarea_list($records) {
    global $DB;

    // Get subjectarea vocabulary from ildmeta_vocabulary.
    $vocabulary = $DB->get_record('ildmeta_vocabulary', array('title' => 'subjectarea'), '*', MUST_EXIST);
    $terms = manager::filter_vocabulary_lang($vocabulary, current_language());

    $filtered = array();

    foreach ($terms as $key => $value) {
        foreach ($records as $record) {
            if ($record->subjectarea == $key) {
                $filtered[$key + 2] = ($key + 2) . "=>$value";
                break;
            }
        }
    }

    array_unshift($filtered, '1=>' . get_string('subjectarea_all', 'block_ildmetaselect'));
    array_unshift($filtered, '0=>' . get_string('subjectarea', 'block_ildmetaselect'));

    return $filtered;
}


function get_subjectarea_list($input) {
    global $DB;

    // Get subjectarea vocabulary from ildmeta_vocabulary.
    $vocabulary = $DB->get_record('ildmeta_vocabulary', array('title' => 'subjectarea'), '*', MUST_EXIST);
    $terms = manager::filter_vocabulary_lang($vocabulary, current_language());

    $data = copy_to_data($input);
    $filtered = array();

    foreach ($terms as $key => $value) {
        $data->subjectarea = $key;
        if (exist_courses_records($data)) {
            $filtered[$key + 2] = ($key * 2) . "$key=>$value";
        }
    }

    array_unshift($filtered, '1=>' . get_string('subjectarea_all', 'block_ildmetaselect'));
    array_unshift($filtered, '0=>' . get_string('subjectarea', 'block_ildmetaselect'));

    return $filtered;
}

function get_filtered_processingtime_list($records) {
    global $DB;

    if (get_config('block_ildmetaselect','workload_in_min')){
                  $timeunit = get_string('minutes', 'block_ildmetaselect');
                } else {
                  $timeunit = get_string('hours', 'block_ildmetaselect');
                }

    $processingtime_list = [
        'upto15' => get_string('proctime_upto15', 'block_ildmetaselect', $timeunit),
        'between16and20' => get_string('proctime_between16and20', 'block_ildmetaselect', $timeunit),
        'between21and25' => get_string('proctime_between21and25', 'block_ildmetaselect', $timeunit),
        'morethan25' => get_string('proctime_morethan25', 'block_ildmetaselect', $timeunit)
    ];

    $filtered = array();
    $filtered['-'] =  "-=>" . get_string('proctime_name', 'block_ildmetaselect');
    $filtered['all'] = 'all=>' . get_string('proctime_all', 'block_ildmetaselect');

    foreach ($processingtime_list as $key => $value) {
        foreach ($records as $record) {
            if (it_time_in_processingtime($key, $record->processingtime)) {
                $filtered[$key] = "$key=>$value";
            }
        }
    }

    return $filtered;
}

function get_processingtime_list($input) {
    $processingtime_list = [
        '-' => get_string('proctime_name', 'block_ildmetaselect'),
        'all' => get_string('proctime_all', 'block_ildmetaselect'),
        'upto15' => get_string('proctime_upto15', 'block_ildmetaselect'),
        'between16and20' => get_string('proctime_between16and20', 'block_ildmetaselect'),
        'between21and25' => get_string('proctime_between21and25', 'block_ildmetaselect'),
        'morethan25' => get_string('proctime_morethan25', 'block_ildmetaselect')
    ];

    $data = copy_to_data($input);
    $filtered = array();

    foreach ($processingtime_list as $key => $value) {
        $data->processingtime = ($key);
        if (exist_courses_records($data)) {
            $filtered[$key] = "$key=>$value";
        }
    }

    return $filtered;
}

function get_filtered_starttime_list($records) {
    global $DB;

    $starttime_list = [
        'current' => get_string('starttime_current', 'block_ildmetaselect'),
        'less2weeks' => get_string('starttime_less2weeks', 'block_ildmetaselect'),
        'between3and4weeks' => get_string('starttime_between3and4weeks', 'block_ildmetaselect'),
        'between5and6weeks' => get_string('starttime_between5and6weeks', 'block_ildmetaselect'),
        'between7and8weeks' => get_string('starttime_between7and8weeks', 'block_ildmetaselect')
    ];

    $filtered = array();
    $filtered['-'] =  "-=>" . get_string('starttime_name', 'block_ildmetaselect');
    $filtered['all'] = 'all=>' . get_string('starttime_all', 'block_ildmetaselect');

    foreach ($starttime_list as $key => $value) {
        foreach ($records as $record) {
            if (is_time_in_area($key, $record->starttime)) {
                $filtered[$key] = "$key=>$value";
            }
        }
    }

    return $filtered;
}


function get_starttime_list($input) {
    $starttime_list = [
        '-' => get_string('starttime_name', 'block_ildmetaselect'),
        'all' => get_string('starttime_all', 'block_ildmetaselect'),
        'current' => get_string('starttime_current', 'block_ildmetaselect'),
        'less2weeks' => get_string('starttime_less2weeks', 'block_ildmetaselect'),
        'between3and4weeks' => get_string('starttime_between3and4weeks', 'block_ildmetaselect'),
        'between5and6weeks' => get_string('starttime_between5and6weeks', 'block_ildmetaselect'),
        'between7and8weeks' => get_string('starttime_between7and8weeks', 'block_ildmetaselect')
    ];

    $data = copy_to_data($input);
    $filtered = array();

    foreach ($starttime_list as $key => $value) {
        $data->starttime = ($key);
        if (exist_courses_records($data)) {
            $filtered[$key] = "$key=>$value";
        }
    }

    return $filtered;
}

function get_filtered_lang_list($records) {
    global $DB;

    $lang_list = [
        get_string('german', 'block_ildmetaselect'),
        get_string('english', 'block_ildmetaselect')
    ];

    $filtered = array();
    $filtered[] = '0=>' . get_string('courselanguage', 'block_ildmetaselect');
    $filtered[] = '1=>' . get_string('courselanguage_all', 'block_ildmetaselect');

    foreach ($lang_list as $key => $value) {
        foreach ($records as $record) {
            if ($key == $record->courselanguage) {
                $filtered[$key + 2] = ($key + 2) . "=>$value";
            }
        }
    }

    return $filtered;
}

function get_lang_list($input) {
    $lang_list = [
        'Kurssprache',
        'Alle Kurssprachen',
        'Deutsch',
        'Englisch'
    ];

    $data = copy_to_data($input);
    $filtered = array();

    foreach ($lang_list as $key => $value) {
        $data->courselanguage = ($key);
        if (exist_courses_records($data)) {
            $filtered[$key] = "$key=>$value";
        }
    }

    return $filtered;
}

function copy_to_data($curData) {
    $data = new stdClass();
    $data->courselanguage = $curData->courselanguage;
    $data->subjectarea = $curData->subjectarea;
    $data->provider = $curData->provider;
    $data->processingtime = $curData->processingtime;
    $data->starttime = $curData->starttime;
    return $data;
}

function get_all_request_data() {
    $data = new stdClass();
    $data->courselanguage = 1;
    $data->subjectarea = 1;
    $data->provider = 1;
    $data->processingtime = 'all';
    $data->starttime = 'all';
    return $data;
}
