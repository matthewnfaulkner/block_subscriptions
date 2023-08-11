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
 * This file contains the course_enrolment_manager class which is used to interface
 * with the functions that exist in enrollib.php in relation to a single course.
 *
 * @package    core_enrol
 * @copyright  2010 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_user\fields;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/enrol/locallib.php');

/**
 * This class provides a targeted tied together means of interfacing the enrolment
 * tasks together with a course.
 *
 * It is provided as a convenience more than anything else.
 *
 * @copyright 2010 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subscription_enrolment_manager extends course_enrolment_manager {


            /**
     * Unenrols a user from the course given the users ue entry
     *
     * @global moodle_database $DB
     * @param stdClass $ue
     * @return bool
     */
    public function unenrol_self($ue) {
        global $DB, $USER;
        if($USER->id != $ue->userid){
            return false;
        }
        list ($instance, $plugin) = $this->get_user_enrolment_components($ue);
        if ($instance && $plugin && has_capability("enrol/$instance->enrol:unenrolself", $this->context)) {
            $plugin->unenrol_user($instance, $ue->userid);
            return true;
        }
        return false;
}
}