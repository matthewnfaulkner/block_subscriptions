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
 * Class containing data for my overview block.
 *
 * @package    block_subscriptions
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_subscriptions\output;
defined('MOODLE_INTERNAL') || die();


use block_myoverview;
use context_system;
use core\event\user_loggedin;
use core_course_category;
use renderable;
use renderer_base;
use templatable;
use stdClass;
use block_subscriptions\output\enrol_list_item as enrol_list_item;
use moodle_url;

require_once($CFG->dirroot . '/blocks/subscriptions/lib.php');

/**
 * Class containing data for my overview block.
 *
 * @copyright  2018 Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable{


    private string $options;

    private \block_subscriptions $block;


    private int $course;
    
    private int $userid;

    private bool $loggedin = false;

    private bool $enrolledincourse = false;

    public function __construct($block, $options = []) {
        global $USER;
        $this->block = $block;
        $this->course = $block->get_courseid();
        if(!isguestuser() && isloggedin()){
            $this->userid = $USER->id;
            $this->loggedin = true;
        }   
        else{
            $this->userid = 0;
        }

    }

       /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     * @throws \coding_exception
     *
     */
    public function export_for_template(renderer_base $output) {
        
        global $PAGE, $CFG;

        $template['enrollist'] = [];
        $template['subscriptionid'] = $this->block->instance->id;
        
        $template['subsupportlink'] = new moodle_url($CFG->wwwroot . '/local/subscriptions/contactsitesupport.php', array('courseid' => $this->course));
        $subpage = $PAGE->subpage;
        if($subpage == "my"){
            if($enrollist = block_subscriptions_get_user_subscriptions($this->userid, $this->course)){
                $this->enrolledincourse = true;
                $template['memberdetails'] = local_subscriptions_get_membership_number_from_course($this->course, $this->userid);
            };
        }
        else if($this->course && $this->userid){
            if($enrollist = block_subscriptions_course_get_subscriptions_for_user($this->course, $this->userid, true)){
                if(reset($enrollist)->userenrolled !== NULL){
                    $this->enrolledincourse = true;
                    $template['memberdetails'] = local_subscriptions_get_membership_number_from_course($this->course, $this->userid);
                }
            };
            
        }
        else if ($this->course){
            $enrollist = block_subscriptions_course_get_subscriptions($this->course);
        }
        foreach ($enrollist as $enrol){
            $enrollistitem = new subscription_list_item($enrol, $this->enrolledincourse, $this->loggedin);
            $template['enrollist'][] = $enrollistitem->export_for_template($output);
        }
        return $template;
    }
}
