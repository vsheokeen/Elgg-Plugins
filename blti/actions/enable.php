<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Enable/Disable consumer
 ------------------------------------------------------------------*/

// Must be logged in as admin to use this page
admin_gatekeeper();

$consumer_instance = new LTI_Tool_Consumer_Instance(get_input('guid'), elgg_get_config('dbprefix'));
$consumer_tool = new LTI_Tool_Consumer($consumer_instance->consumer_guid, elgg_get_config('dbprefix'));

$consumer_tool->enabled = ($consumer_instance->isEnabled()) ? False : True;
$consumer_tool->save();

forward(REFERRER);
?>