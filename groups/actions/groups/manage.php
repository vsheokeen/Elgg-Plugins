<?php

$params = get_input('params');

$guid = get_input('guid') ? get_input('guid') : '';

if(!empty(get_input('container_guid')))
{ $_SESSION['lti_group_id'] = get_input('container_guid'); }

if(!empty($guid))
{
	$points = new ElggObject($guid);
	$points->guid = $guid;
	
	$message = "Fields updated successfully !!!";
	
}
else
{
	$points = new ElggObject();	
	$message = "Fields added successfully !!!";
}

foreach($params as $k => $v) { 
   $points->$k = $v;  
}

$points->subtype = 'points_manage';
$points->container_guid = get_input('container_guid');

$result = $points->save();

if(!empty($result))
{
system_message($message);
}

forward(REFERER);
