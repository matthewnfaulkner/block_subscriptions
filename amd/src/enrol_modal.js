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
 * This module provides the course copy modal from the course and
 * category management screen.
 *
 * @module     format_apoapage/copy_modal
 * @copyright  2020 onward The Moodle Users Association <https://moodleassociation.org/>
 * @author     Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.9
 */

define(['jquery', 'core/str', 'core/modal_factory',
        'core/ajax', 'core/fragment', 'core/notification'],
        function($, Str, ModalFactory, ajax, Fragment, Notification) {

    /**
     * Module level variables.
     */
    var CancelModal = {};
    var contextid;
    var enrolId;
    var plugin;
    var subscriptionId;
    var course;
    var modalObj;
    var plugin;
    var spinner = '<p class="text-center">'
        + '<i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>'
        + '</p>';

    /**
     * Creates the modal for the course copy form
     *
     * @private
     */
    function createModal() {
       // Get the Title String.
       Str.get_string('loading').then(function(title) {
        // Create the Modal.
        ModalFactory.create({
            type: ModalFactory.types.DEFAULT,
            title: title,
            body: spinner,
            large: true
        })
        .done(function(modal) {
            modalObj = modal;
            // Explicitly handle form click events.
            modalObj.getRoot().on('click', '#id_submitbutton', function(e) {
                //e.preventDefault();
                e.formredirect = true;
                processModalForm(e);
            });
            modalObj.getRoot().on('click', '#id_cancel', function(e) {
                e.preventDefault();
                modalObj.setBody(spinner);
                modalObj.hide();
            });
        });
        return;
    }).catch(function() {
        Notification.exception(new Error('Failed to load string: loading'));
    });
    }

    /**
     * Updates the body of the modal window.
     *
     * @param {Object} formdata
     * @private
     */
    function updateModalBody(formdata) {
        if (typeof formdata === "undefined") {
            formdata = {};
        }
        if (typeof enrolId !== "undefined" && typeof enrolId !== "undefined") {
            var params = {
                'jsonformdata': JSON.stringify(formdata),
                'plugin' : plugin,
                'enrolid' : enrolId
            };
            modalObj.setBody(spinner);
            Str.get_string('subscribetitle', 'block_subscriptions', course.shortname).then(function(title) {
                modalObj.setTitle(title);
                modalObj.setBody(Fragment.loadFragment('block_subscriptions', 'new_enrol_form', contextid, params));
                return;
            }).catch(function() {
                Notification.exception(new Error('Failed to load string: copycoursetitle'));
            });
        }

    }

   /**
     * Updates Moodle form with selected information.
     *
     * @param {Object} e
     * @private
     */
   function processModalForm(e) {
    e.preventDefault(); // Stop modal from closing.
    console.log('herer');
    // Form data.
    var cancelform = modalObj.getRoot().find('form').serialize();
    //var formjson = JSON.stringify(cancelform);

    // Handle invalid form fields for better UX.
    var invalid = $.merge(
            modalObj.getRoot().find('[aria-invalid="true"]'),
            modalObj.getRoot().find('.error')
    );

    if (invalid.length) {
        invalid.first().focus();
        return;
    }

    // Submit form via ajax.
    ajax.call([{
        methodname: 'block_subscriptions_submit_user_enrolment_form',
        args: {
            formdata: cancelform
        },
    }])[0].done(function(response) {
        // For submission succeeded.
        var result = response;
        console.log(response.result);
        if(result.result){
            modalObj.setBody(spinner);
            modalObj.hide();
            window.location.reload();
        }
        else{
            updateModalBody(cancelform);
        }
    }).fail(function(fail){
        console.log(fail);
        // Form submission failed server side, redisplay with errors.
        updateModalBody(cancelform);
    });
}
    /**
     * Initialise the class.
     *
     * @method
     * @param {Object} context
     * @param {Int} subscription
     * @public
     */
    CancelModal.init = function(context, subscription) {
        contextid = context;
        subscriptionId = subscription;
        // Setup the initial Modal.
        createModal();
        // Setup the click handlers on the copy buttons.
        $('.action-subscribe-' + subscriptionId).on('click', function(e) {
            modalObj.setBody(spinner);
            e.preventDefault(); // Stop. Hammer time.
            let url = new URL(this.getAttribute('href'));
            let params = new URLSearchParams(url.search);
            enrolId = params.get('enrolid');
            plugin = params.get('plugin');
            let courseId = params.get('id');
            if(courseId !== null){
                ajax.call([{ // Get the course information.
                    methodname: 'core_course_get_courses',
                    args: {
                        'options': {'ids': [courseId]},
                    },
                }])[0].done(function(response) {
                    // We have the course info get the modal content.
                    course = response[0];
                    updateModalBody();
                }).fail(function() {
                    Notification.exception(new Error('Failed to load course'));
                });
            }
            else{
                updateModalBody();
            }
            modalObj.show();
        });

    };

    return CancelModal;
});
