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

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/blocks/isymetaselect/locallib.php');

class blocks_isymetaselect_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_filter_parameters(){
        return new external_function_parameters(
            array(
                'meta6' => new external_value(PARAM_INT, 'rating value'),
                'meta2' => new external_value(PARAM_INT, 'rating value'),
                'courselanguage' => new external_value(PARAM_INT, 'rating value'),
                'meta4' => new external_value(PARAM_TEXT, 'rating value'),
                'meta5' => new external_value(PARAM_TEXT, 'rating value'),
            )
        );
    }

    public static function get_filter($meta6, $meta2, $courselanguage, $meta4, $meta5) {

        global $DB, $CFG;

        $params = self::validate_parameters(
            self::get_filter_parameters(), 
            [
                'meta6' => $meta6,
                'meta2' => $meta2,
                'courselanguage' => $courselanguage,
                'meta4' => $meta4,
                'meta5' => $meta5,
            ]
        );

        $data = new stdClass();
        $data->meta6 = $meta6;
        $data->meta2 = $meta2;
        $data->courselanguage = $courselanguage;
        $data->meta4 = $meta4;
        $data->meta5 = $meta5;

        $return = array();
        
        $records = get_courses_records($data);

        $return['meta6'] = json_encode(get_filtered_meta6_list($records));
        $return['meta2'] = json_encode(get_filtered_meta2_list($records));
        $return['courselanguage'] = json_encode(get_filtered_lang_list($records));
        $return['meta4'] = json_encode(get_filtered_meta4_list($records));
        $return['meta5'] = json_encode(get_filtered_meta5_list($records));
        $return['debug'] = json_encode($data);

        return $return;
    }

    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function get_filter_returns(){
        return new external_function_parameters(
            array(
                'meta6' => new external_value(PARAM_TEXT, 'rating value'),
                'meta2' => new external_value(PARAM_TEXT, 'rating value'),
                'courselanguage' => new external_value(PARAM_TEXT, 'rating value'),
                'meta4' => new external_value(PARAM_TEXT, 'rating value'),
                'meta5' => new external_value(PARAM_TEXT, 'rating value'),
                'debug' => new external_value(PARAM_RAW, 'rating value'),
            )
        );
    }

}