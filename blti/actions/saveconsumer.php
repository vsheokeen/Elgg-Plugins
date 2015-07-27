<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * This is called when administrator updates a consumer
 ------------------------------------------------------------------*/

// Must be logged in as admin to use this page
admin_gatekeeper();

// Get the data
$user            = elgg_get_logged_in_user_entity();
$enable[]        = get_input('enable');
$name            = get_input('name');
$consumer_name   = get_input('consumer_name');
$tool_guid       = get_input('tool_guid');
$secret          = get_input('secret');
$guid            = get_input('guid');
$state           = get_input('state');
$url             = get_input('url');

// Tidy up data
$enable        = trim($enable[0][0]);
$name          = trim($name);
$consumer_name = trim($consumer_name);
$tool_guid     = trim($tool_guid);
$secret        = trim($secret);
$guid          = trim($guid);
$state         = trim($state);
$url           = trim($url);

// If we have all we need for consumer
if ($guid && $tool_guid && $secret) {

    // Update Consumer
    LTI_update_consumer($tool_guid, $name, $consumer_name, $url, $enable, $guid, $secret, $state);

    // Tell administrator it has worked
    system_message(sprintf(elgg_echo('LTI:edit:consumer:success'), $name));
    
} else {

    // Opps! Something gone wrong
    register_error(elgg_echo('LTI:edit:consumer:error'));

}

forward('admin/administer_utilities/blti');
?>