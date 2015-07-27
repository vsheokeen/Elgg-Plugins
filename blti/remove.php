<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * This is called when administrator removes a consumer
 ------------------------------------------------------------------*/

// Must be logged in as admin to use this page
admin_gatekeeper();

// Sort out what is to go
$consumer_instance = new LTI_Tool_Consumer_Instance(get_input('LTIconsumerguid'), elgg_get_config('dbprefix'));
$consumer_tool = new LTI_Tool_Consumer($consumer_instance->consumer_guid, elgg_get_config('dbprefix'));

// Check we have right sort of object
if ($consumer_instance) {

    $consumer_instance->state = 'removed';
    $consumer_instance->save();

    if ($consumer_instance->state == 'removed') {

        system_message(sprintf(elgg_echo('LTI:remove:success'), $consumer_tool->name));

    } else {

        system_message(sprintf(elgg_echo('LTI:restore:success'), $consumer_tool->name));

    }

} else {

    register_error(sprintf(elgg_echo('LTI:remove:fail'), $consumer_tool->name));

}

forward($_SERVER['HTTP_REFERER']);
?>