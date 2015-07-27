<?php
/**
 * Activate Elggx Userpoints plugin
 *
 */

// Register the Userpoint class for the object/userpoint subtype
if (get_subtype_id('object', 'groupuserpoint')) {
	update_subtype('object', 'groupuserpoint', 'Groupuserpoint');
} else {
	add_subtype('object', 'groupuserpoint', 'Groupuserpoint');
}


// Upgrade settings
$oldversion = elgg_get_plugin_setting('version', 'groupuserpoints');
$current_version = elgg_get_plugin_setting('version', 'elggx_groupuserpoints');

// Check if we need to run an upgrade
if ($oldversion && !$current_version) {

	// Update plugin settings
	$plugin = elgg_get_plugin_from_id('groupuserpoints');

	elgg_set_plugin_setting('moderate', $plugin->moderate, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('subtract', $plugin->subtract, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('displaymessage', $plugin->displaymessage, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('profile_display', $plugin->profile_display, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('delete', $plugin->delete, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('expire_after', $plugin->expire_after, 'elggx_groupuserpoints');

	// Update point settings
	$pointssettings = elgg_get_plugin_from_id('groupuserpoints_standard');

	elgg_set_plugin_setting('blog', $pointssettings->blog, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('group', $pointssettings->group, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('page_top', $pointssettings->page_top, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('comment', $pointssettings->generic_comment, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('riverpost', $pointssettings->riverpost, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('thewire', $pointssettings->thewire, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('image', $pointssettings->upload_photo, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('poll', $pointssettings->poll, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('pollvote', $pointssettings->pollvote, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('phototag', $pointssettings->phototag, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('discussion_reply', $pointssettings->group_topic_post, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('delete', $pointssettings->delete, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('invite', $pointssettings->invite, 'elggx_groupuserpoints');


	// Set new version
	elgg_set_plugin_setting('version', '1.9.11', 'elggx_groupuserpoints');
} else if ($current_version < '1.9.7') {
	$pointssettings = elgg_get_plugin_from_id('elggx_groupuserpoints');
	elgg_set_plugin_setting('discussion_reply', $pointssettings->group_topic_post, 'elggx_groupuserpoints');
	elgg_set_plugin_setting('comment', $pointssettings->generic_comment, 'elggx_groupuserpoints');
	// Set new version
	elgg_set_plugin_setting('version', '1.9.11', 'elggx_groupuserpoints');
} else if ($current_version < '1.9.8') {
	$pointssettings = elgg_get_plugin_from_id('elggx_groupuserpoints');
	elgg_set_plugin_setting('comment', $pointssettings->generic_comment, 'elggx_groupuserpoints');
	// Set new version
	elgg_set_plugin_setting('version', '1.9.11', 'elggx_groupuserpoints');
}
$current_version = elgg_get_plugin_setting('version', 'elggx_groupuserpoints');
if ($current_version != '1.9.11') {
	// Set new version
	elgg_set_plugin_setting('version', '1.9.11', 'elggx_groupuserpoints');
}
