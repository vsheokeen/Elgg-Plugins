<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Create new share key
 ------------------------------------------------------------------*/

$group = get_entity(get_input('group_guid'));
$life = get_input('life');
$preapprove = get_input('preapprove');
$approve = ($preapprove == 0 ? true : false);

$consumer_instance = new LTI_Tool_Consumer_Instance($group->consumer_key, elgg_get_config('dbprefix'));
$context = new LTI_Context($consumer_instance, $group->context_id);

forward('/' . elgg_get_config('ltiname') . '/shareinfo/' . group_guid . '/' . $context->getNewShareKey($life, $approve, 20));
?>