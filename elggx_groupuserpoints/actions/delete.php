<?php

$guid = (int)get_input('guid');

groupuserpoints_delete_by_userpoint($guid);

system_message(elgg_echo("elggx_groupuserpoints:delete_success"));
forward(REFERER);
