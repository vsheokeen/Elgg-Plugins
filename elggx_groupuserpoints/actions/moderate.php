<?php

$guid = (int)get_input('guid');
$status = get_input('status');

groupuserpoints_moderate($guid, $status);

system_message(elgg_echo("elggx_groupuserpoints:".$status."_message", array(elgg_echo('elggx_groupuserpoints:lowerplural'))));
forward(REFERER);
