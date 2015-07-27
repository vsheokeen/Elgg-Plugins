<?php
/**
 * Edit/create a group wrapper
 *
 * @uses $vars['entity'] ElggGroup object
 */

$container_guid = elgg_extract('container_guid', $vars, null);
$array = array();
$array['container_guid'] = $container_guid;

$form_vars = array(
	'enctype' => 'multipart/form-data',
	'class' => 'elgg-form-alt',
);

echo elgg_view_form('groups/manage', $form_vars, $array);
