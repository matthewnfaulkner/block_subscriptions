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
 * Course copy form class.
 *
 * @package     core_backup
 * @copyright   2020 onward The Moodle Users Association <https://moodleassociation.org/>
 * @author      Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_subscriptions\output;


defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");


/**
 * Course copy form class.
 *
 * @package     core_backup
 * @copyright  2020 onward The Moodle Users Association <https://moodleassociation.org/>
 * @author     Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cancel_form extends \moodleform {


    protected string $confirm = "cancelsubscription";
    /**
     * Build form for the course copy settings.
     *
     * {@inheritDoc}
     * @see \moodleform::definition()
     */
    public function definition() {
 

        $mform = $this->_form;
        $enrolid = $this->_customdata['enrolid'];
        $ueid = $this->_customdata['ueid'];


        // Course ID.
        $mform->addElement('hidden', 'enrolid', $enrolid);
        $mform->setType('enrolid', PARAM_INT);
        $mform->setConstant('enrolid', $enrolid);

        $mform->addElement('hidden', 'ueid', $ueid);
        $mform->setType('ueid', PARAM_INT);
        $mform->setConstant('ueid', $ueid);

        $mform->addElement('hidden', 'confirmationphrase', $this->confirm);
        $mform->setType('confirmationphrase', PARAM_TEXT);
        $mform->setConstant('confirmationphrase', $this->confirm);

        // Form heading.
        $mform->addElement('html', \html_writer::div(
            get_string('cancelsubscriptionmessage', 'block_subscriptions'), 'form-description mb-3'));

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('cancel');
        $buttonarray[] = $mform->createElement('submit', 'submit', get_string('cancelsubscription', 'block_subscriptions'));
        $mform->addElement('text', 'confirm', get_string('confirmcancel', 'block_subscriptions', $this->confirm));
        $mform->setType('confirm', PARAM_ALPHA);
        $mform->addRule('confirm', 'type the confirmation phrase', 'required', null, 'server');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }

    /**
     * Validation of the form.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    public function validation($data, $files) {

        $errors = parent::validation($data, $files);

        if($data['confirm'] != $data['confirmationstring']){
            $errors['confirm'] = get_string('confirmerror', 'block_subscriptions');
        }

        
        // Add field validation check for duplicate shortname.

        // Add field validation check for duplicate idnumber.

        // Validate the dates (make sure end isn't greater than start).

        return $errors;
    }

}
