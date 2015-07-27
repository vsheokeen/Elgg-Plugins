<?php

elgg_set_context('groups');

// Must be logged in to use this page
gatekeeper();

// Get group details
$group_guid = (int) get_input('group_guid');
$group = get_entity($group_guid);
elgg_set_page_owner_guid($group_guid);

// Get logged in user
$cur_user = elgg_get_logged_in_user_entity();

$consumer_instance = new LTI_Tool_Consumer_Instance($cur_user->consumer_key, elgg_get_config('dbprefix'));
$context = new LTI_Context($consumer_instance, $cur_user->context_id);
$contexts[] = $context;

if ($cur_user->consumer_key == $group->consumer_key) {
    // add the shared contexts --- get all the contexts
    $shares = $context->getShares();
    foreach($shares as $share) {
        $consumer_instance = new LTI_Tool_Consumer_Instance($share->consumer_instance_guid, elgg_get_config('dbprefix'));
        $context = new LTI_Context($consumer_instance, $share->context_id);
        $contexts[] = $context;
    }
}

// Title
$area2 = elgg_view_title(elgg_echo('LTI:members:label'));

// Main text
$formbody = elgg_echo('LTI:members:explain');

// Read back the last sync time
if (sizeof($contexts) == 1 && $context->hasSettingService()) {

    $last_sync = $context->doSettingService(LTI_Context::EXT_READ, NULL);
    if (!empty($last_sync)) $formbody .= sprintf(elgg_echo('LTI:members:explain:lastsync'), $last_sync);
} else {
    $formbody .= sprintf(elgg_echo('LTI:members:explain:lastsync:share'));
    foreach ($contexts as $context) {
        if ($context->hasSettingService()) {
            $last_sync = $context->doSettingService(LTI_Context::EXT_READ, NULL);
            if (!empty($last_sync)) $formbody .= $context->title . ': ' . $last_sync . '<br />';
        }
    }
}

// Submit button
$formbody .= elgg_view('input/submit',
                      array('value' => 'Continue'));

// Hidden fields: group_guid and filter
$formbody .= elgg_view('input/hidden',
                       array('name' => 'group_guid',
                             'value' => $group_guid
                             )
                      );

$formbody .= elgg_view('input/hidden',
                       array('name' => 'filter',
                             'value' => 'sync'
                            )
                      );

$form = elgg_view('input/form',
                 array('action' => elgg_get_config('wwwroot') . 'action/blti/sync',
                        'body' => $formbody
                      )
                 );

$area2 .= elgg_view('page/elements/body', array('body' => $form));

$body = elgg_view_layout('two_column_left_sidebar', array('title' => '', 'content' => $area2));

// Finally draw the page
echo elgg_view_page($title, $body);

?>