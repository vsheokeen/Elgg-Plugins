<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * This is called when administrator creates a Basic LTI consumer
 ------------------------------------------------------------------*/

// Must be logged in as admin to use this page
admin_gatekeeper();

// Get the data
$user            = elgg_get_logged_in_user_entity();
$tool_guid       = get_input('tool_guid');
$consumer_name   = get_input('consumer_name');
$enable[]        = get_input('enable');
$secret          = get_input('secret');

// Tidy up data
$tool_guid     = trim($tool_guid);
$consumer_name = trim($consumer_name);
$enable        = trim($enable[0][0]);
$secret        = trim($secret);

// If we have all we need for consumer
if ($tool_guid && $consumer_name && $secret) {

    // Create Consumer
    BasicLTI_create_consumer($tool_guid, $consumer_name, $enable, $secret);

    // Tell administrator it has worked
    system_message(sprintf(elgg_echo('LTI:edit:consumer:create'), $consumer_name));

} else {

    // Opps! Something gone wrong
    register_error(elgg_echo('LTI:edit:consumer:error'));

}

forward(REFERRER);
?>