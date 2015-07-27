<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Display the share object
 ------------------------------------------------------------------*/

$share = $vars['shares'];
$group_guid = $vars['group'];

$info = ($share->approved) ? '<div class="plugin_details active">' : '<div class="plugin_details not-active">';

$url = elgg_add_action_tokens_to_url(elgg_get_config('wwwroot') . 'action/' . elgg_get_config('ltiname') . '/approve?guid=' . $share->consumer_instance_guid . '&id=' . $share->context_id . '&group=' . $group_guid);
$option = ($share->approved) ? 'Suspend' : 'Approve';
$info .= '<div class="admin_plugin_enable_disable"><a href="' . $url . '">' . $option . '</a></div>';

// Get consumer_name
$consumer = new LTI_Tool_Consumer($share->consumer_instance_guid, elgg_get_config('dbprefix'));
$info .= '<h3>' . $consumer->name . '</h3>';

$info .= '<div class="plugin_description"><p>';
$info .= $share->title;
$info .= '</p></div></div>';

echo $info;

?>