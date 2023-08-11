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
    var enrol;
    var modalObj;
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
        if (typeof enrolId !== "undefined"){
            var params = {
                'jsonformdata': JSON.stringify(formdata),
                'plugin' : plugin,
                'enrolId' : enrolId
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

        // Form data.
        var cancelform = modalObj.getRoot().find('form').serialize();
        //var formjson = JSON.stringify(cancelform);

        var ueid = modalObj.getRoot().find('input[name="ueid"]').val();
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
            methodname: 'core_enrol_enrol_user_enrolment',
            args: {
                ueid: ueid
            },
        }])[0].done(function() {
            // For submission succeeded.
            modalObj.setBody(spinner);
            modalObj.hide();
            console.log(course.shortname);
            ajax.call([{
                methodname: 'block_subscriptions_notify',
                args:{
                    cancelled: true,
                    coursename: course.shortname
                }
            }])[0].done(function(){
                window.location.reload();
            }).fail(function(fail){
                console.log(fail);
            });
        }).fail(function(){
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
        $('.action-cancel-' + subscriptionId).on('click', function(e) {
            e.preventDefault(); // Stop. Hammer time.
            let url = new URL(this.getAttribute('href'));
            let params = new URLSearchParams(url.search);
            enrolId = params.get('enrolid');
            plugin = params.get('plugin');
            let courseId = params.get('courseid');
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
