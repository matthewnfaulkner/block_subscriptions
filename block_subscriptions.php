<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block subscriptions is defined here.
 *
 * @package     block_subscriptions
 * @copyright   2022 Matthew<you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_subscriptions extends block_base {


    protected int $courseid = 0;
    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_subscriptions');
    }


    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {

        global $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $text = 'Please define the content text in /blocks/subscriptions/block_subscriptions.php.';
            $this->content->text = $text;
        }
        $renderable = new block_subscriptions\output\main($this);
        $renderer = $this->page->get_renderer('block_subscriptions');


        $this->content = new stdClass();
        $this->content->text = $renderer->render($renderable);

        $this->page->requires->js_call_amd('block_subscriptions/cancel_modal', 'init', array(1, $this->instance->id));

        return $this->content;
    }


    public function get_courseid(){
        return $this->courseid;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {
        global $DB;

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_subscriptions');
        } else {
            $this->title = $this->config->title;
        }
        $this->title = get_string('subscriptionoptions', 'block_subscriptions');

        if($this->instance->parentcontextid == context_system::instance()->id){

            $sql = "SELECT ctx.instanceid, be.target, be.source FROM {block_subscriptions} be 
            INNER JOIN {block_instances} bi ON be.source = bi.id
            INNER JOIN {context} ctx ON bi.parentcontextid = ctx.id
            WHERE be.target = :targetid";
            $params = array('targetid' => $this->instance->id);
            if($linkedblock = $DB->get_record_sql($sql, $params)){
                $this->courseid = $linkedblock->instanceid;
            }
        }
        else if($parentcontext = $DB->get_record('context', array('id' => $this->instance->parentcontextid))){
            $this->courseid = $parentcontext->instanceid;
        }
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function instance_create(){
        global $DB;
        $context = context_system::instance();
        $sourceid = $this->instance->id;
        $instance = $this->instance;
        $instance->parentcontextid = $context->id;
        $instance->pagetypepattern = "subscriptions";
        unset($instance->id);
        if($linkedid = $DB->insert_record('block_instances', $instance)){
            $subscriptions = new stdClass();
            $subscriptions->source = $sourceid;
            $subscriptions->target = $linkedid;
            $DB->insert_record('block_subscriptions', $subscriptions);
        };
    }

    function instance_delete(){
        global $DB;
        $instance = $this->instance;
        $sql = "id IN (SELECT be.id FROM {block_subscriptions} be WHERE be.source = :sourceid)";
        $params = array('sourceid' => $instance->id);
        if($DB->delete_records_select('block_instances', $sql, $params)){
            $DB->delete_records('block_subscriptions', array('source' => $instance->id));
        };

    }
}
