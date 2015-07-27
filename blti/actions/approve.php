<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Approve/Suspend share
 ------------------------------------------------------------------*/

elgg_set_context('groups');

// Must be logged in to use this page
gatekeeper();

$group_guid = (int) get_input('group');
$group = get_entity($group_guid);
set_page_owner($group_guid);

$consumer_instance = new LTI_Tool_Consumer_Instance(get_input('guid'), elgg_get_config('dbprefix'));
$context = new LTI_Context($consumer_instance, get_input('id'));

$approve = ($context->share_approved) ? false : true;

$primary_guid = $context->primary_consumer_instance_guid;
$consumer_instance = new LTI_Tool_Consumer_Instance($primary_guid, elgg_get_config('dbprefix'));
$context = new LTI_Context($consumer_instance, $context->primary_context_id);

$context->doApproveShare(get_input('guid'), get_input('id'), $approve);

forward(elgg_get_config('wwwroot') .  elgg_get_config('ltiname') . '/sharemanage/' . $primary_guid . '/' . $group_guid);
?>