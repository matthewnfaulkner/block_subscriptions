<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/subscriptions/lib.php');

function block_subscriptions_get_user_subscriptions($userid, $courseid){
    global $DB;
    

    if(is_course_subscription($courseid)){

        $sql = "SELECT e.*, ue.status as userenrolled, ue.timestart, ue.timeend, ue.id as ueid
            FROM {enrol} e
            INNER JOIN {user_enrolments} ue on e.id = ue.enrolid
            AND ue.userid = :userid AND ue.status = :active
            AND e.courseid = :courseid
            ORDER BY ue.timeend ASC";


        $params = array('userid' => $userid,
                        'active' => ENROL_USER_ACTIVE,
                        'courseid' => $courseid);

        $subscriptions = $DB->get_records_sql($sql, $params);
        return $subscriptions;
    }
}

/**
 * End of the user's current active subscription in the course, like enrol_get_enrolment_end(),
 * but ignoring cohort sync enrolments (e.g. federation membership): those do not stop the
 * user buying a subscription.
 *
 * @param int $courseid
 * @param int $userid
 * @return int|bool timestamp when active enrolment ends, false means no active enrolment now, 0 means never
 */
function block_subscriptions_get_enrolment_end($courseid, $userid) {
    global $DB;

    $sql = "SELECT ue.id, ue.timestart, ue.timeend
              FROM {user_enrolments} ue
              JOIN {enrol} e ON e.id = ue.enrolid AND e.courseid = :courseid
              JOIN {user} u ON u.id = ue.userid
             WHERE ue.userid = :userid AND ue.status = :active AND e.status = :enabled AND u.deleted = 0
               AND e.enrol <> 'cohort'";
    $params = ['enabled' => ENROL_INSTANCE_ENABLED, 'active' => ENROL_USER_ACTIVE,
        'userid' => $userid, 'courseid' => $courseid];

    if (!$enrolments = $DB->get_records_sql($sql, $params)) {
        return false;
    }

    // As enrol_get_enrolment_end(): count overlapping enrolments over time and find when the
    // count, active now, next drops to zero.
    $changes = [];
    foreach ($enrolments as $ue) {
        $start = (int) $ue->timestart;
        $end = (int) $ue->timeend;
        if ($end != 0 && $end < $start) {
            continue;
        }
        $changes[$start] = ($changes[$start] ?? 0) + 1;
        if ($end !== 0) {
            $changes[$end] = ($changes[$end] ?? 0) - 1;
        }
    }
    ksort($changes);

    $now = time();
    $current = 0;
    $present = null;
    foreach ($changes as $time => $change) {
        if ($time > $now) {
            if ($present === null) {
                $present = $current;
                if ($present < 1) {
                    return false;
                }
            }
            if ($current + $change < 1) {
                return $time;
            }
        }
        $current += $change;
    }

    return $current > 0 ? 0 : false;
}

function block_subscriptions_course_get_subscriptions($courseid){
    global $DB;
    $enrolplugins = enrol_get_plugins(true);
    unset($enrolplugins['manual']);
    unset($enrolplugins['guest']);
    //unset($enrolplugins['self']);
    list($pluginssql, $params) = $DB->get_in_or_equal(array_keys($enrolplugins), SQL_PARAMS_NAMED);
    $sql = "SELECT e.* FROM {enrol} e WHERE e.courseid = :courseid AND e.status = :active AND e.enrol $pluginssql";
    $params['courseid'] = $courseid;
    $params['active'] = 0;
    $subscriptioninstances = $DB->get_records_sql($sql, $params);
    return $subscriptioninstances;
}

function block_subscriptions_course_get_subscriptions_for_user($courseid, $userid, $onlyactive = false){
    global $DB;
    $enrolplugins = enrol_get_plugins(true);
    unset($enrolplugins['manual']);
    unset($enrolplugins['guest']);
    //unset($enrolplugins['self']);
    list($pluginssql, $params) = $DB->get_in_or_equal(array_keys($enrolplugins), SQL_PARAMS_NAMED);
    if($onlyactive){
        $onlyactivewhere = "uee.userid = :userid AND uee.status = :active";
        $params['active'] = ENROL_USER_ACTIVE;
        $params['userid'] = $userid;
    }
    $sql = "SELECT e.*, ue.status as userenrolled, ue.timestart, ue.timeend, ue.id as ueid
    FROM {enrol} e
    LEFT JOIN 
    (SELECT uee.* FROM {user_enrolments} uee WHERE $onlyactivewhere ORDER BY uee.timeend DESC) AS ue ON e.id = ue.enrolid 
    WHERE e.courseid = :courseid 
    AND (e.enrol $pluginssql OR (e.enrol = :manualenrol AND ue.status IS NOT NULL))
    AND e.status = :enrolactive
    ORDER BY ue.status DESC";
    $params['courseid'] = $courseid;
    $params['enrolactive'] = 0;
    $params['manualenrol'] = 'manual';
    $subscriptioninstances = $DB->get_records_sql($sql, $params);
    return $subscriptioninstances;
}


function get_enrol_period($enrolperiod){

    if(!$enrolperiod){
        return get_string('enrolperiodlifetime', 'block_subscriptions');
    }
    if($enrolperiod < 31536000){
        $days = floor($enrolperiod / 86400);
        $formattedperiod = $days < 2 ? get_string('enrolperiodday', 'block_subscriptions', array('days' => $days)) 
                                    : get_string('enrolperioddays', 'block_subscriptions', array('days' => $days));
        return $formattedperiod;
    }
    else{
        $years = round($enrolperiod/31536000, 1);
        $formattedperiod = $years < 2 ? get_string('enrolperiodyear', 'block_subscriptions', array('years' => $years)) 
                                    : get_string('enrolperiodyears', 'block_subscriptions', array('years' => $years));
        return $formattedperiod;
    }
}

function block_subscriptions_output_fragment_new_cancel_form($args) {

    $serialiseddata = json_decode($args['jsonformdata'], true);
    $formdata = [];
    if (!empty($serialiseddata)) {
        parse_str($serialiseddata, $formdata);
    }
 
 
    $enrolid = $args['enrolid'];
    $ueid = $args['ueid'];
    $mform = new \block_subscriptions\output\cancel_form(
        null,
        array('enrolid' => $enrolid, 'ueid' => $ueid),
        'post', '', ['class' => 'ignoredirty'], true, $formdata);
 
    if (!empty($serialiseddata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
 
    ob_start();
    $mform->display();
    $o = ob_get_contents();
    ob_end_clean();
 
    return $o;
 }

 function block_subscriptions_output_fragment_new_enrol_form($args) {

    global $DB;

    $serialiseddata = json_decode($args['jsonformdata'], true);
    $formdata = [];
    if (!empty($serialiseddata)) {
        parse_str($serialiseddata, $formdata);
    }
 
    $enrolid = $args['enrolid'];
    $pluginname = $args['plugin'];

    if($enrol = $DB->get_record('enrol', array('id' => $enrolid))){
        $plugin = enrol_get_plugin($pluginname);
        $o = $plugin->enrol_page_hook($enrol);
    }

 
    return $o;
 }


