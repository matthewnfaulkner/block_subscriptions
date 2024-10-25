<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,membersh
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_subscriptions\output;

require_once($CFG->dirroot . '/local/subscriptions/lib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
use core\event\user_loggedin;
use core_cohort\reportbuilder\local\entities\cohort_member;
use stdClass;
use enrol_gwpayments\payment\service_provider as service_provider;
use moodle_url;

/**
 * Renderer for outputting the singleactivity course format.
 *
 * @package    format_singleactivity
 * @copyright  2013 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subscription_list_item implements \renderable, \templatable {

    private \stdClass $enrol;

    private bool $enrolledincourse;

    private bool $loggedin;

    private int $enrolmentend;

    protected \core_course_list_element $courselistelement;

    public function __construct(\stdClass $enrol, bool &$enrolledincourse, bool $loggedin, int $enrolmentend) {
        $this->enrol = $enrol;
        $this->enrolledincourse = &$enrolledincourse;
        $this->loggedin = $loggedin;
        $this->enrolmentend = $enrolmentend;
    }

 
    public function export_for_template(\renderer_base $output){

        global $USER;
        $data = new stdClass();

        $plugin = enrol_get_plugin($this->enrol->enrol);

        $canselfenrol = $plugin->show_enrolme_link($this->enrol);

        $data->enroltitle = $plugin->get_instance_name($this->enrol);
        $enrolurl = new \moodle_url('/enrol/index.php', array('id' => $this->enrol->courseid));

        if($this->enrol->enrol == 'cohort'){
            $cohortid = $this->enrol->customint1;
            preg_match('/\((.*?)\)/', $data->enroltitle, $match);
            $data->enroltitle = $match[1];

        }else if($this->enrol->enrol == 'gwpayments'){
            $cohortid = $this->enrol->customint5;
            $data->enrolid = $this->enrol->id;
            $data->successurl = service_provider::get_success_url('gwpayments', $this->enrol->id)->out(false);
            $data->gwpayments = true;
            $data->description = get_string('purchasedescription', 'enrol_gwpayments', $data->enroltitle);
        }else{
            $cohortid = null;
        }
        $formembershipcategories = get_membership_categories_from_cohort($cohortid);

        if($this->loggedin){
            if($cohortid){
                $iscohortmember = cohort_is_member($cohortid, $USER->id);
            }else{
                $iscohortmember = true;
            }
        }else{
            $iscohortmember = false;
        }

        $enrolfor = get_membership_category($this->enrol->customint5);
        $hascost = $this->enrol->cost && $this->enrol->currency;


        $enrolperiod = $this->enrol->timestart ?  $this->enrol->timeend - $this->enrol->timestart : $this->enrol->timeend -  time();
        if($enrolperiod > 0){
            $formattedperiod = get_enrol_period($enrolperiod);
        }
        else{
            $formattedperiod = get_enrol_period($this->enrol->enrolperiod);
        }
        
        
        $enrollink = new moodle_url('/enrol/index.php', array(  
                                            'enrolid' => $this->enrol->id, 
                                            'plugin' => $plugin->get_name(),
                                            'id' => $this->enrol->courseid
                                        )
                                    );
                                    
        if($unenrollink = $plugin->get_unenrolself_link($this->enrol)){
            $unenrollink->params(
                        array('ueid' => $this->enrol->ueid,
                              'courseid' => $this->enrol->courseid));
        }

        if($isenrolled = $this->enrol->userenrolled === (string)ENROL_USER_ACTIVE){
                $currentdate = new \DateTime();
                $now = $currentdate->getTimestamp();
                $isexpired = $this->enrol->timestart > $now || ( $this->enrol->timeend > 0 &&  $this->enrol->timeend < $now);

                if($this->enrol->timeend) {
                    $canresubscribe = true;
                }

                if(!$isexpired){
                    $this->enrolledincourse = true;
                }
                // If user enrolment status has not yet started/already ended or the enrolment instance is disabled.
        }

        if($this->enrolledincourse) {
            if($this->enrolmentend){
                if($this->enrol->timeend < $this->enrolmentend){
                    $canupgrade = true;
                }else if ($this->enrol->timeend < $this->enrolmentend){
                    $isenrolled = false;
                }
            }else{
                $isenrolled = $this->enrol->timeend == 0 && $isenrolled;
            }
        }
        
        
        $data->enrolcost = $this->enrol->cost ? \core_payment\helper::get_cost_as_string($this->enrol->cost ,$this->enrol->currency) : null;
        $data->isexpired = $isexpired;
        $data->enrolhascost = $hascost;
        $data->enrolperiod = $formattedperiod;
        $data->enrolurl = $enrolurl;
        $data->enrolfor = $enrolfor;
        $data->isenrolled = $isenrolled;
        $data->formembershipcategories = $formembershipcategories;
        $data->iscohortmember = $iscohortmember;
        $data->isloggedin = $this->loggedin;
        $data->enrolstartdate = $this->enrol->timestart;
        $data->enrolenddate = $this->enrol->timeend;
        $data->enrollink = $enrollink;
        $data->unenrollink = $unenrollink;
        $data->vat = (int)$this->enrol->customint3;
        $data->localisedcost = format_float($this->enrol->cost, 2, true);
        $data->locale = $USER->lang;
        $data->enablecoupon = (int)$this->enrol->customint2;
        $data->canresubscribe = $canresubscribe;
        $data->canselfenrol = $canselfenrol;
        $data->canupgrade = $canupgrade;
        $data->itemid = 1;
        $data->componentname = 'local_subscriptions';
        $data->area = 'main';
        $data->price = '100';
        $data->expirationdate = $this->enrol->timeend;
        $data->itemname = 'sakahjdadh';
        
        return $data;
    }
    
}