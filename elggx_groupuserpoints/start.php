<?php

elgg_register_event_handler('init','system','groupuserpoints_init');

function groupuserpoints_init() {

	// Register library
	elgg_register_library('groupuserpoints_library', elgg_get_plugins_path() . 'elggx_groupuserpoints/lib/groupuserpoint.php');
	elgg_load_library('groupuserpoints_library');

	elgg_register_plugin_hook_handler('expirationdate:expire_entity', 'all', 'elggx_groupuserpoints_expire');

	elgg_extend_view('css/elgg', 'elggx_groupuserpoints/css');
	elgg_extend_view('icon/user/default','elggx_groupuserpoints/icon');

	elgg_register_widget_type('toppoints', elgg_echo('elggx_groupuserpoints:toppoints'), elgg_echo('elggx_groupuserpoints:widget:toppoints:info'));
	elgg_register_widget_type('index_toppoints', elgg_echo('elggx_groupuserpoints:toppoints'), elgg_echo('elggx_groupuserpoints:toppoints'), array('index'));

	// Hooks for awarding points

	//elgg_register_plugin_hook_handler('action', 'invitefriends/invite', 'elggx_groupuserpoints_invite');
	//elgg_register_plugin_hook_handler('action', 'groups/invite', 'elggx_groupuserpoints_invite');
	elgg_register_plugin_hook_handler('action', 'likes/add', 'elggx_groupuserpoints_like');
	elgg_register_plugin_hook_handler('action', 'poll/vote', 'elggx_groupuserpoints_vote');
	elgg_register_plugin_hook_handler('action', 'recommendations/new', 'elggx_groupuserpoints_recommendations');
	elgg_register_plugin_hook_handler('action', 'recommendations/approve', 'elggx_groupuserpoints_recommendations');
	elgg_register_event_handler('create','object', 'elggx_groupuserpoints_object');
	elgg_register_event_handler('delete','object', 'elggx_groupuserpoints_object');
	elgg_register_event_handler('delete','entity', 'elggx_groupuserpoints_object');
	elgg_register_event_handler('create','annotation','elggx_groupuserpoints_annotate_create');
	elgg_register_event_handler('create','group','elggx_groupuserpoints_group');
	elgg_register_event_handler('delete','group','elggx_groupuserpoints_group');

	elgg_register_admin_menu_item('administer', 'elggx_groupuserpoints', 'administer_utilities');

	// Register actions
	
	$base_dir = elgg_get_plugins_path() . 'elggx_groupuserpoints/actions';
	elgg_register_action("elggx_groupuserpoints/settings", "$base_dir/settings.php", 'admin');
	elgg_register_action("elggx_groupuserpoints/delete", "$base_dir/delete.php", 'admin');
	elgg_register_action("elggx_groupuserpoints/moderate", "$base_dir/moderate.php", 'admin');
	elgg_register_action("elggx_groupuserpoints/add", "$base_dir/add.php", 'admin');
	elgg_register_action("elggx_groupuserpoints/scaling", "$base_dir/scaling.php", 'admin');
	elgg_register_action("elggx_groupuserpoints/reset", "$base_dir/reset.php", 'admin');
	elgg_register_action("elggx_groupuserpoints/restore", "$base_dir/restore.php", 'admin');
	elgg_register_action("elggx_groupuserpoints/restore_all", "$base_dir/restore_all.php", 'admin');
}
