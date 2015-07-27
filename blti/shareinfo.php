<?php

elgg_set_context('groups');

// Must be logged in to use this page
gatekeeper();

// Get group details
//$group_guid = (int) get_input('group_guid');
$group_guid = (int) get_input('group_guid');
$group = get_entity($group_guid);
elgg_set_page_owner_guid($group_guid);
$share_key = get_input('sharekey');

$area2 = elgg_view_title(elgg_echo('LTI:share'));
$area2 .= elgg_view('page/elements/body', array('body' => $share_key));

$body = elgg_view_layout('two_column_left_sidebar', array('title' => '', 'content' => $area2));

// Finally draw the page
echo elgg_view_page($title, $body);

?>