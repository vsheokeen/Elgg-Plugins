<?php

/**
 * Save Userpoints settings
 *
 */

// Params array (text boxes and drop downs)
$params = get_input('params');
$result = false;
foreach ($params as $k => $v) {
    if (!elgg_set_plugin_setting($k, $v, 'elggx_groupuserpoints')) {
        register_error(elgg_echo('plugins:settings:save:fail', array('elggx_groupuserpoints')));
        forward(REFERER);
    }
}

system_message(elgg_echo('elggx_groupuserpoints:settings:save:ok'));
forward(REFERER);
