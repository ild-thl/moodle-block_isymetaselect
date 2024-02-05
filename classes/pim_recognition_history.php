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

namespace block_ildmetaselect;

/**
 * Class PIM_Recognition_History
 *
 * This class provides jelper functions to retrieve the PIMs history of recognition. This depends on the local_emp plugin.
 *
 * @package     block_ildmetaselect
 * @copyright   2024 Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pim_recognition_history {
    /**
     * Checks if a course has recognition.
     *
     * @param object $DB The database object.
     * @param int $courseid The ID of the course to check.
     * @return bool Returns true if the course has recognition, false otherwise.
     */
    public static function check_course_recognition($DB, int $courseid): bool {
        return $DB->record_exists('local_emp_course', array('courseid' => $courseid)) ? true : false;
    }

    /**
     * Retrieves the recognition history for a given course.
     *
     * @param object $DB The database object.
     * @param int $courseid The ID of the course.
     * @return array An array containing the recognition history.
     */
    public static function get_recognition_history($DB, int $courseid): array {
        return $DB->get_records('local_emp_recognitions', array('courseid' => $courseid));
    }

    /**
     * Process the history array.
     *
     * @param array $history The history array to be processed.
     * @return array The processed history array.
     */
    public static function process_history(array $history): array {
        $historyrender = array();
        $positivestatuscount = 0;
        $totalstatuscount = 0;
        $overallstatus = 0;

        if (empty($history)) {
            $overallstatus = 4; // No history data available.
        } else {
            foreach ($history as $item) {
                $historyrender[] = array(
                    'hei' => $item->hei,
                    'studyprogramme' => $item->studyprogramme,
                    'learningopportunity' => $item->learningopportunity,
                    'statustext' => $item->status == 1
                        ? get_string('recognition_status_approved', 'block_ildmetaselect')
                        : get_string('recognition_status_rejected', 'block_ildmetaselect'),
                    'credits' => $item->credits . ' ECTS',
                    'year' => $item->year
                );
                if ($item->status == 1) {
                    $positivestatuscount++;
                }
                $totalstatuscount++;
            }

            $overallstatus = self::calculate_overall_status($positivestatuscount, $totalstatuscount);
        }

        return array($historyrender, $overallstatus);
    }

    /**
     * Calculates the overall status based on the positive status count and total status count.
     *
     * @param int $positivestatuscount The count of positive statuses.
     * @param int $totalstatuscount The total count of statuses.
     * @return int The overall status.
     */
    public static function calculate_overall_status(int $positivestatuscount, int $totalstatuscount): int {
        if ($positivestatuscount == $totalstatuscount) {
            return 1; // All histories are positive.
        } else if ($positivestatuscount > 0) {
            return 2; // There are histories with both positive and negative statuses.
        } else {
            return 3; // All histories are negative.
        }
    }

    /**
     * Returns the description of the status based on the overall status value.
     *
     * @param int $overallstatus The overall status value.
     * @return string The description of the status.
     */
    public static function get_status_description(int $overallstatus): string {
        return $overallstatus == 1
            ? get_string('recognition_status_approved_description', 'block_ildmetaselect')
            : (
                $overallstatus == 2 ? get_string('recognition_status_mixed_description', 'block_ildmetaselect')
                : (
                    $overallstatus == 3 ? get_string('recognition_status_rejected_description', 'block_ildmetaselect')
                    : get_string('recognition_status_no_data_description', 'block_ildmetaselect')
                )
            );
    }

    /**
     * Returns the color associated with the given overall status.
     *
     * @param int $overallstatus The overall status value.
     * @return string The color associated with the overall status.
     */
    public static function get_status_color(int $overallstatus): string {
        return $overallstatus == 1 ? 'green' : ($overallstatus == 2 ? 'yellow' : ($overallstatus == 3 ? 'red' : 'grey'));
    }
}
