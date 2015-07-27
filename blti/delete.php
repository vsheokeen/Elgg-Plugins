<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * This is called when administrator delete a consumer
 ------------------------------------------------------------------*/

// Must be logged in as admin to use this page
admin_gatekeeper();

// Sort out what is to go
$consumer_instance = new LTI_Tool_Consumer_Instance(get_input('LTIconsumerguid'), elgg_get_config('dbprefix'));
$consumer_tool = new LTI_Tool_Consumer($consumer_instance->consumer_guid, elgg_get_config('dbprefix'));

// Check we have right sort of object
if ($consumer_instance) {

    if ($consumer_instance->delete() && DeleteConsumerTool($consumer_tool)) {

        system_message(sprintf(elgg_echo('LTI:delete:success'), $consumer_tool->name));

    } else {

        system_message(sprintf(elgg_echo('LTI:delete:fail'),    $consumer_tool->name));

    }

} else {

    register_error(sprintf(elgg_echo('LTI:remove:fail'), $consumer_tool->name));

}

forward($_SERVER['HTTP_REFERER']);

/*-------------------------------------------------------------------
 * Delete the consumer tool. The instance will already be deleted
 *
 * Parameters
 * consumer_tool - Consumer to be deleted. Done via direct DB access
 *
 * return value - true, if row deleted
 *
 * Experimental Feature
-------------------------------------------------------------------*/
function DeleteConsumerTool($consumer_tool) {

    // Delete Tool from the DB
    $sql  = "DELETE FROM " . elgg_get_config('dbprefix') . "lti_consumer ";
    $sql .= "WHERE consumer_guid = '" . $consumer_tool->guid . "'";

    return mysql_query($sql);
}
?>