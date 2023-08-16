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
 * External forum API
 *
 * @package    mod_forum
 * @copyright  2012 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use format_subscription\external\exporters\subscription;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_warnings;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");


class block_subscriptions_external extends external_api {

   /**
     * Toggle the favouriting value for the discussion provided
     *
     * @param int $discussionid The discussion we need to favourite
     * @param bool $targetstate The state of the favourite value
     * @return array The exported discussion
     */
    public static function notify($cancelled, $coursename) {

        $params = self::validate_parameters(self::notify_parameters(), [
            'cancelled' => $cancelled,
            'coursename' => $coursename
        ]);

        if(!$params['cancelled']){
            return false;
        }
        
        \core\notification::success(get_string('cancelledsubscriptionsuccess', 'block_subscriptions', $coursename));
        return true;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function notify_returns() {
        return new external_value(PARAM_BOOL, 'success or not');
    }

     /**
     * Defines the parameters for the notify method
     *
     * @return external_function_parameters
     */
    public static function notify_parameters() {
        return new external_function_parameters(
            [
                'cancelled' => new external_value(PARAM_BOOL, 'the redirect url'),
                'coursename' => new external_value(PARAM_TEXT, 'name of the subscription course')
            ]
        );
    }

        /**
     * Returns description of submit_user_enrolment_form parameters.
     *
     * @return external_function_parameters.
     */
    public static function submit_user_enrolment_form_parameters() {
        return new external_function_parameters([
            'formdata' => new external_value(PARAM_RAW, 'The data from the event form'),
        ]);
    }

    /**
     * External function that handles the user enrolment form submission.
     *
     * @param string $formdata The user enrolment form data in s URI encoded param string
     * @return array An array consisting of the processing result and error flag, if available
     */
    public static function submit_user_enrolment_form($formdata) {
        global $CFG, $DB, $PAGE, $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::submit_user_enrolment_form_parameters(), ['formdata' => $formdata]);

        $data = [];
        parse_str($params['formdata'], $data);

        $instance = $DB->get_record('enrol', ['id' => $data['instance']], '*', MUST_EXIST);
        $plugin = enrol_get_plugin($instance->enrol);
        $course = get_course($data['id']);
        $context = context_course::instance($course->id);
        self::validate_context($context);

        $enrolstatus = $plugin->can_self_enrol($instance);
        
        if (true === $enrolstatus && confirm_sesskey()) {
            // This user can self enrol using this instance.
            $result = $plugin->enrol_self($instance, $data);
            return ['result' => is_enrolled($context, $USER)];
        } else {
            return ['result' => false, 'validationerror' => true];
        }
    }

    /**
     * Returns description of submit_user_enrolment_form() result value
     *
     * @return external_description
     */
    public static function submit_user_enrolment_form_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'True if the user\'s enrolment was successfully updated'),
            'validationerror' => new external_value(PARAM_BOOL, 'Indicates invalid form data', VALUE_DEFAULT, false),
        ]);
    }

     /**
     * Returns description of unenrol_user_enrolment() parameters
     *
     * @return external_function_parameters
     */
    public static function unenrol_self_parameters() {
        return new external_function_parameters(
            array(
                'ueid' => new external_value(PARAM_INT, 'User enrolment ID')
            )
        );
    }

    /**
     * External function that unenrols a given user enrolment.
     *
     * @param int $ueid The user enrolment ID.
     * @return array An array consisting of the processing result, errors.
     */
    public static function unenrol_self($ueid) {
        global $CFG, $DB, $PAGE;

        $params = self::validate_parameters(self::unenrol_self_parameters(), [
            'ueid' => $ueid
        ]);

        $result = false;
        $errors = [];

        $userenrolment = $DB->get_record('user_enrolments', ['id' => $params['ueid']], '*');
        if ($userenrolment) {
            $userid = $userenrolment->userid;
            $enrolid = $userenrolment->enrolid;
            $enrol = $DB->get_record('enrol', ['id' => $enrolid], '*', MUST_EXIST);
            $courseid = $enrol->courseid;
            $course = get_course($courseid);
            $context = context_course::instance($course->id);
            self::validate_context($context);
        } else {
            $validationerrors['invalidrequest'] = get_string('invalidrequest', 'enrol');
        }

        // If the userenrolment exists, unenrol the user.
        if (!isset($validationerrors)) {
            require_once('locallib.php');
            $manager = new subscription_enrolment_manager($PAGE, $course);
            $result = $manager->unenrol_self($userenrolment);
        } else {
            foreach ($validationerrors as $key => $errormessage) {
                $errors[] = (object)[
                    'key' => $key,
                    'message' => $errormessage
                ];
            }
        }

        return [
            'result' => $result,
            'errors' => $errors,
        ];
    }

    /**
     * Returns description of unenrol_user_enrolment() result value
     *
     * @return external_description
     */
    public static function  unenrol_self_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'True if the user\'s enrolment was successfully updated'),
                'errors' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'key' => new external_value(PARAM_TEXT, 'The data that failed the validation'),
                            'message' => new external_value(PARAM_TEXT, 'The error message'),
                        )
                    ), 'List of validation errors'
                ),
            )
        );
    }
}

