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
 * @package    ildmetaselect
 * @author     Markus Strehling <markus.strehling@oncampus.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;

//require_once($CFG->libdir . "/externallib.php");
//require_once($CFG->dirroot . '/blocks/ildmetaselect/locallib.php');

// New import try
require_once('externallib.php');
require_once('locallib.php');

//global $CFG;

class blocks_ildmetaselect_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_filter_parameters() {
        return new external_function_parameters(
            array(
                'subjectarea' => new external_value(PARAM_INT, 'rating value'),
                'provider' => new external_value(PARAM_INT, 'rating value'),
                'courselanguage' => new external_value(PARAM_INT, 'rating value'),
                'processingtime' => new external_value(PARAM_TEXT, 'rating value'),
                'starttime' => new external_value(PARAM_TEXT, 'rating value'),
            )
        );
    }

    public static function get_filter($subjectarea, $provider, $courselanguage, $processingtime, $starttime) {

        global $DB, $CFG;

        $params = self::validate_parameters(
            self::get_filter_parameters(),
            array(
                'subjectarea' => $subjectarea,
                'provider' => $provider,
                'courselanguage' => $courselanguage,
                'processingtime' => $processingtime,
                'starttime' => $starttime,
            )
        );

        $data = new stdClass();
        $data->subjectarea = $subjectarea;
        $data->provider = $provider;
        $data->courselanguage = $courselanguage;
        $data->processingtime = $processingtime;
        $data->starttime = $starttime;

        $return = array();

        $records = get_courses_records($data);

        $return['subjectarea'] = json_encode(get_filtered_subjectarea_list($records));
        $return['provider'] = json_encode(get_filtered_provider_list($records));
        $return['courselanguage'] = json_encode(get_filtered_lang_list($records));
        $return['processingtime'] = json_encode(get_filtered_processingtime_list($records));
        $return['starttime'] = json_encode(get_filtered_starttime_list($records));
        $return['debug'] = json_encode($data);

        return $return;
    }

    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function get_filter_returns() {
        return new external_function_parameters(
            array(
                'subjectarea' => new external_value(PARAM_TEXT, 'rating value'),
                'provider' => new external_value(PARAM_TEXT, 'rating value'),
                'courselanguage' => new external_value(PARAM_TEXT, 'rating value'),
                'processingtime' => new external_value(PARAM_TEXT, 'rating value'),
                'starttime' => new external_value(PARAM_TEXT, 'rating value'),
                'debug' => new external_value(PARAM_RAW, 'rating value'),
            )
        );
    }
}
