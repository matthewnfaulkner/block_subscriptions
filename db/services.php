<?php


$functions = array(

    'block_subscriptions_notify' => array(
        'classname' => 'block_subscriptions_external',
        'methodname' => 'notify',
        'classpath' => 'blocks/subscriptions/externallib.php',
        'description' => 'redirects with notification',
        'type' => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'block_subscriptions_submit_user_enrolment_form' => array(
        'classname' => 'block_subscriptions_external',
        'methodname' => 'submit_user_enrolment_form',
        'classpath' => 'blocks/subscriptions/externallib.php',
        'description' => 'submits enrolment form if plugin allows',
        'type' => 'write',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'block_subscriptions_unenrol_self' => array(
        'classname' => 'block_subscriptions_external',
        'methodname' => 'unenrol_self',
        'classpath' => 'blocks/subscriptions/externallib.php',
        'description' => 'user unenrols self',
        'type' => 'write',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);