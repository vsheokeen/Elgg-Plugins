<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * List the sharing contexts
 ------------------------------------------------------------------*/

elgg_set_context('groups');

// Must be logged in to use this page
gatekeeper();

// Get group details
$group_guid = (int) get_input('group_guid');
$group = get_entity($group_guid);
elgg_set_page_owner_guid($group_guid);

// Sort out which consumer to get membership from
$consumer_instance = new LTI_Tool_Consumer_Instance($group->consumer_key, elgg_get_config('dbprefix'));
$context = new LTI_Context($consumer_instance, $group->context_id);
$list_of_shares = $context->getShares();

$area2 = elgg_view_title(elgg_echo('LTI:share:manage'));

if (!empty($list_of_shares)) {
    $text = '';
    foreach($list_of_shares as $share) {
        $text .= elgg_view('object/shares', array('shares' => $share, 'group' => $group_guid));
    }
    $area2 .= $text;
} else {
    $area2 .= elgg_view('page/elements/body', array('body' => elgg_echo('LTI:share:noshared')));
}

$body = elgg_view_layout('two_column_left_sidebar', array('title' => '', 'content' => $area2));

// Finally draw the page
echo elgg_view_page($title, $body);

?>