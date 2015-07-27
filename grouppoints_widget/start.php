<?php

elgg_register_event_handler('init','system','grouppoints_widget_init');

function grouppoints_widget_init() {

    // uncommenting the following line would list the top users in the sidebar of groups
    //elgg_extend_view('groups/sidebar/members', 'userpoints_group_widget/sidebar');

    // Add group option
    add_group_tool_option('grouppoints_widget', elgg_echo('grouppoints_widget:enable_grouppoints_widget'), true);
    elgg_extend_view('groups/tool_latest', 'grouppoints_widget/group_module');

    if (elgg_is_active_plugin('widget_manager')) {
        //add groups widget
        elgg_register_widget_type('grouppoints_widget', elgg_echo("grouppoints_widget:top_group_members"), elgg_echo('grouppoints_widget:top_group_members'), array("groups"));
    }
}