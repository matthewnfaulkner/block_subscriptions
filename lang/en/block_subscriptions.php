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
 * Plugin strings are defined here.
 *
 * @package     block_subscriptions
 * @category    string
 * @copyright   2022 Matthew<you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Subscriptions Block';
$string['cancelsubscription'] = 'Cancel Subscriptions';
$string['confirmcancel'] = 'To confirm cancellation type: {$a}';
$string['cancelsubscriptionmessage'] = 'Are you sure you want to cancel this subscription, the effects will be immediate 
    regardless of any time remaining.';
$string['subscriptioncancelled'] = 'your subscription to {$a} was cancelled.';
$string['cancelsubscription'] = 'Confirm';
$string['cancelsubscriptiontitle'] = 'Cancel Subscription: {$a}';
$string['cancelledsubscriptionsuccess'] = 'Successfully cancelled {$a}';
$string['subscriptions'] = 'Subscriptions';
$string['subscriptionoptions'] = 'Subscription options';
$string['subscriptiondescription'] = 'Subscription: {$a->name},
For membership categories: {$a->membershipcategory},
Duration: {$a->duration},
Enrol Method: {$a->enrol}';
$string['price'] = 'Subscription Fee:';
$string['cancelsubscription'] = 'Cancel Subscription';
$string['enrolperiod'] = 'Subscription Duration:';
$string['enrolperioddays'] = '{$a->days} days';
$string['enrolperiodday'] = '{$a->days} day';
$string['enrolperiodyears'] = '{$a->years} years';
$string['enrolperiodyear'] = '{$a->years} year';
$string['enrolperiodlifetime'] = 'Lifetime';
$string['enrol'] = 'Subscribe';
$string['missingsubhelp'] = "Should you already be subscribed? Click here for help.";
$string['subscriptionstitle'] = 'Subscription Options';
$string['nosubscriptions'] = 'No Subscription Options Available';
$string['allmembershipcategories'] = 'All Membership Catgories';
$string['membershipcategories'] = 'Available to:';
$string['alreadyenrolled'] = 'Active';
$string['alreadyenrolledother'] = 'Other Subscription Active';
$string['enrollmentunavailable'] = 'Unavailable';
$string['nocost'] = 'Free';
$string['notloggedin'] = 'You must be signed in to subscribe';
$string['startdate'] = 'Subscription Started:';
$string['enddate'] = 'Subscription Ends:';
$string['mysubscriptionstitle'] = 'My Active Subscriptions';
$string['subscriptionstitle'] = 'Subscriptions';
$string['subscribetitle'] = 'Subscribe to: {$a}';
$string['enrollmentunavailablenomain'] = 'You need to subscribe to APOA first.';
$string['expired'] = 'Expired on {$a}';
$string['renewsubscription'] = "Renew this Subscription";
$string['upgradesubscription'] = "Upgrade this Subscription";
$string['purchasesubscription'] = "Purchase this Suscription";