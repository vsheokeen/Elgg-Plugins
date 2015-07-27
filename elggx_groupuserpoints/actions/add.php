<?php

$params = get_input('params');

$user = get_user_by_username($params['username']);

groupuserpoints_add($user->guid, $params['points'], $params['description'], 'admin');

system_message(elgg_echo("elggx_groupuserpoints:add:success", array($params['points'], elgg_echo('elggx_groupuserpoints:lowerplural'), $params['username'])));
forward(REFERER);
