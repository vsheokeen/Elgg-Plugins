<?php

$params = $_REQUEST['params'];

$guid = isset( $_REQUEST['guid'] ) ? $_REQUEST['guid'] : '';

if(!empty($_REQUEST['container_guid']))
{ $_SESSION['lti_group_id'] = $_REQUEST['container_guid']; }

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
$points->container_guid = $_REQUEST['container_guid'];

$result = $points->save();

if(!empty($result))
{
system_message($message);
}

forward(REFERER);
