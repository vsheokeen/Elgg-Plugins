<?php

/**
 * Userpoints for for manually adding points
 */

$action = elgg_get_site_url() . 'action/elggx_groupuserpoints/add';

$form = "<br><b>" . elgg_echo('elggx_groupuserpoints:add:user') . "</b>";
$form .= elgg_view('input/text', array('name' => "params[username]", 'value' => ''));
$form .= "<br><br>";

$form .= "<b>" . elgg_echo('elggx_groupuserpoints:upperplural') . ":</b>";
$form .= elgg_view('input/text', array('name' => "params[points]", 'value' => ''));
$form .= "<br><br>";

$form .= "<b>" . elgg_echo('elggx_groupuserpoints:add:description') . "</b>";
$form .= elgg_view('input/text', array('name' => "params[description]", 'value' => ''));
$form .= "<br><br>";

$form .= elgg_view("input/securitytoken");

$form .= elgg_view('input/submit', array('value' => elgg_echo("save")));
echo elgg_view('input/form', array('action' => $action, 'body' => $form));
