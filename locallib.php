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
* @package    isymetaselect
* @author     Markus Strehling <markus.strehling@oncampus.de>
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

function meta5_to_sql($meta5) {
    $time_now = time();
    $time_week = 604800;
    $time_day = 86400;

    switch ($meta5) {
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

function is_time_in_areas($meta5, $time) {
    $time_now = time();
    $time_week = 604800;
    $time_day = 86400;

    switch ($meta5) {
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

function meta4_to_sql($meta4) {
    switch ($meta4) {
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

function it_time_in_meta4($meta4, $time) {
    switch ($meta4) {
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

/*function get_courses_records($fromform) {
    global $DB;

    $tosearch = new stdClass;
    $tosearch->courselanguage = select_prepare($fromform->courselanguage);
    $tosearch->meta6 = select_prepare($fromform->meta6);
    $tosearch->meta2 = select_prepare($fromform->meta2);
    $tosearch->meta4 = meta4_to_sql($fromform->meta4);
    $tosearch->meta5 = meta5_to_sql($fromform->meta5);

    $query = "
			SELECT * FROM mdl_isymeta
			WHERE
			meta2 LIKE '$tosearch->meta2'
			AND meta6 LIKE '$tosearch->meta6'
			AND courselanguage LIKE '$tosearch->courselanguage'
			AND meta4 $tosearch->meta4
            AND meta5 $tosearch->meta5
            AND noindexcourse != 1";

    return $DB->get_records_sql($query);
}*/

function get_courses_records($fromform) {
    $past = get_courses_records_time($fromform, true);
    $future = get_courses_records_time($fromform, false);

    return array_merge($past, $future);
}

function get_courses_records_time($fromform, $past) {
    global $DB;

    $tosearch = new stdClass;
    $tosearch->courselanguage = select_prepare($fromform->courselanguage);
    $tosearch->meta6 = select_prepare($fromform->meta6);
    $tosearch->meta2 = select_prepare($fromform->meta2);
    $tosearch->meta4 = meta4_to_sql($fromform->meta4);
    $tosearch->meta5 = meta5_to_sql($fromform->meta5);

    $to_midnight = strtotime('today midnight');

    $query = "
        SELECT * FROM {isymeta}
            WHERE
                meta6 LIKE '$tosearch->meta6'
                AND meta2 LIKE '$tosearch->meta2'
                AND courselanguage LIKE '$tosearch->courselanguage'
                AND meta4 $tosearch->meta4
                AND meta5 $tosearch->meta5
                AND noindexcourse != 1";
    if ($past) {
        $query .= " AND meta5 < $to_midnight ORDER BY meta5 DESC, coursetitle ASC";
    }else{
        $query .= " AND meta5 >= $to_midnight ORDER BY meta5 ASC, coursetitle ASC";
    }
    
    // TODO: Improve the processing time
    $filtered = array();
    $records = $DB->get_records_sql($query);
    if($tosearch->meta2 != "%") {
        foreach($records as $id => $record) {
            $meta2vals = explode(",", $record->meta2);
            foreach($meta2vals as $meta2val) {
                if($meta2val == $tosearch->meta2) {
                    $filtered[$id] = $record;
                    break;
                }
            }
        }
    }else{
        $filtered = $records;
    }

    return $filtered;
}

function exist_courses_records($fromform) {
    $records = get_courses_records($fromform);

    return count($records) > 0;
}

function get_filtered_meta2_list($records) {
    global $DB;

    $meta2s = $DB->get_record('user_info_field', array('shortname' => 'isymeta_de_targetgroups')); // vorher: universities
    $meta2_list = explode("\n", $meta2s->param1);

    $filtered = array();

    foreach($meta2_list as $key => $meta2) {
        foreach($records as $record) {
            $meta2vals = explode(",", $record->meta2);
            foreach($meta2vals as $meta2val) {
                if($meta2val == $key) {
                    $filtered[$key+2] = ($key + 2)."=>$meta2";
                    break;
                }
            }
        }
    }

    array_unshift($filtered, '1=>'.get_string('filter_all', 'block_isymetaselect'));
    array_unshift($filtered, '0=>'.get_string('meta2_name', 'block_isymetaselect'));

    return $filtered;
}

function get_meta2_list($input) {
    global $DB;

    $meta2s = $DB->get_record('user_info_field', array('shortname' => 'isymeta_de_targetgroups')); // vorher: universities
    $meta2_list =  explode("\n", $meta2s->param1);
    
    $data = copy_to_data($input);
    $filtered = array();

    foreach($meta2_list as $key => $value) {
        $data->meta2 = ($key);
        if(exist_courses_records($data)) {
            $filtered[$key] = "$key=>$value";
        }
    }
    array_unshift($filtered, '1=>'.get_string('filter_all', 'block_isymetaselect'));
    array_unshift($filtered, '0=>'.get_string('meta2_name', 'block_isymetaselect'));

    return $filtered;
}

function get_filtered_meta6_list($records) {
    global $DB;

    $meta6s = $DB->get_record('user_info_field', array('shortname' => 'isymeta_de_formats')); // vorher: meta6s

    $meta6_list =  explode("\n", $meta6s->param1);

    $filtered = array();

    foreach($meta6_list as $key => $value) {
        foreach($records as $record) {
            if($record->meta6 == $key) {
                $filtered[$key+2] = ($key+2)."=>$value";
                break;
            }
        }
    }

    array_unshift($filtered, '1=>'.get_string('filter_all', 'block_isymetaselect'));
    array_unshift($filtered, '0=>'.get_string('meta6_name', 'block_isymetaselect'));

    return $filtered;
}


function get_meta6_list($input) {
    global $DB;
    $meta6s = $DB->get_record('user_info_field', array('shortname' => 'isymeta_de_formats')); // vorher: meta6s
    $meta6_list =  explode("\n", $meta6s->param1);
    
    $data = copy_to_data($input);
    $filtered = array();
    
    foreach($meta6_list as $key => $value) {
        $data->meta6 = $key;
        if(exist_courses_records($data)) {
            $filtered[$key+2] = ($key*2)."$k=>$value";
        }
    }

    array_unshift($filtered, '1=>'.get_string('filter_all', 'block_isymetaselect'));
    array_unshift($filtered, '0=>'.get_string('meta6', 'block_isymetaselect'));
    
    return $filtered;
}

function get_filtered_meta4_list($records) {
    global $DB;

    $meta4_list = [
        'upto15' => get_string('meta4_filter_upto15', 'block_isymetaselect'),
        'between16and20' => get_string('meta4_filter_between16and20', 'block_isymetaselect'),
        'between21and25' => get_string('meta4_filter_between21and25', 'block_isymetaselect'),
        'morethan25' => get_string('meta4_filter_morethan25', 'block_isymetaselect')
    ];

    $filtered = array();
    $filtered['-'] =  "-=>" . get_string('meta4_name', 'block_isymetaselect');
    $filtered['all'] = 'all=>' . get_string('meta4_filter_all', 'block_isymetaselect');

    foreach($meta4_list as $key => $value) {
        foreach($records as $record) {
            if(it_time_in_meta4($key, $record->meta4)) {
                $filtered[$key] = "$key=>$value";
            }
        }
    }

    return $filtered;
}

function get_meta4_list($input) {
    $meta4_list = [
        '-' => get_string('meta4_name', 'block_isymetaselect'),
        'all' => get_string('meta4_filter_all', 'block_isymetaselect'),
        'upto15' => get_string('meta4_filter_upto15', 'block_isymetaselect'),
        'between16and20' => get_string('meta4_filter_between16and20', 'block_isymetaselect'),
        'between21and25' => get_string('meta4_filter_between21and25', 'block_isymetaselect'),
        'morethan25' => get_string('meta4_filter_morethan25', 'block_isymetaselect')
    ];

    $data = copy_to_data($input);
    $filtered = array();
    
    foreach($meta4_list as $key => $value) {
        $data->meta4 = ($key);
        if(exist_courses_records($data)) {
            $filtered[$key] = "$key=>$value";
        }
    }

    return $filtered;
}

function get_filtered_meta5_list($records) {
    global $DB;

    $meta5_list = [
        'current' => get_string('meta5_filter_current', 'block_isymetaselect'),
        'less2weeks' => get_string('meta5_filter_less2weeks', 'block_isymetaselect'),
        'between3and4weeks' => get_string('meta5_filter_between3and4weeks', 'block_isymetaselect'),
        'between5and6weeks' => get_string('meta5_filter_between5and6weeks', 'block_isymetaselect'),
        'between7and8weeks' => get_string('meta5_filter_between7and8weeks', 'block_isymetaselect')
    ];

    $filtered = array();
    $filtered['-'] =  "-=>" . get_string('meta5_name', 'block_isymetaselect');
    $filtered['all'] = 'all=>' . get_string('filter_all', 'block_isymetaselect');

    foreach($meta5_list as $key => $value) {
        foreach($records as $record) {
            if(is_time_in_areas($key, $record->meta5)) {
                $filtered[$key] = "$key=>$value";
            }
        }
    }

    return $filtered;
}


function get_meta5_list($input) {
    $meta5_list = [
        '-' => get_string('meta5_name', 'block_isymetaselect'),
        'all' => get_string('filter_all', 'block_isymetaselect'),
        'current' => get_string('meta5_filter_current', 'block_isymetaselect'),
        'less2weeks' => get_string('meta5_filter_less2weeks', 'block_isymetaselect'),
        'between3and4weeks' => get_string('meta5_filter_between3and4weeks', 'block_isymetaselect'),
        'between5and6weeks' => get_string('meta5_filter_between5and6weeks', 'block_isymetaselect'),
        'between7and8weeks' => get_string('meta5_filter_between7and8weeks', 'block_isymetaselect')
    ];

    $data = copy_to_data($input);
    $filtered = array();
    
    foreach($meta5_list as $key => $value) {
        $data->meta5 = ($key);
        if(exist_courses_records($data)) {
            $filtered[$key] = "$key=>$value";
        }
    }

    return $filtered;
}

function get_filtered_lang_list($records) {
    global $DB;

    $lang_list = [
        get_string('filter_german', 'block_isymetaselect'),
        get_string('filter_english', 'block_isymetaselect')
    ];

    $filtered = array();
    $filtered[] = '0=>'.get_string('filter_courselanguage', 'block_isymetaselect');
    $filtered[] = '1=>'.get_string('filter_all', 'block_isymetaselect');

    foreach($lang_list as $key => $value) {
        foreach($records as $record) {
            if($key == $record->courselanguage) {
                $filtered[$key+2] = ($key+2)."=>$value";
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
    
    foreach($lang_list as $key => $value) {
        $data->courselanguage = ($key);
        if(exist_courses_records($data)) {
            $filtered[$key] = "$key=>$value";
        }
    }
    
    return $filtered;
}

function copy_to_data($curData) {
    $data = new stdClass();
    $data->courselanguage = $curData->courselanguage;
    $data->meta6 = $curData->meta6;
    $data->meta2 = $curData->meta2;
    $data->meta4 = $curData->meta4;
    $data->meta5 = $curData->meta5;
    return $data;
}

function get_all_request_data() {
    $data = new stdClass();
    $data->courselanguage = 1;
    $data->meta6 = 1;
    $data->meta2 = 1;
    $data->meta4 = 'all';
    $data->meta5 = 'all';
    return $data;
}