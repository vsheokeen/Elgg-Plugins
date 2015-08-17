<?php

$limit = $vars['entity']->num_display;
$limit = $limit ? $limit : 5;

$options = array('type' => 'user', 'limit' => $limit, 'order_by_metadata' =>  array('name' => 'groupuserpoints_points', 'direction' => DESC, 'as' => integer));
$options['metadata_name_value_pairs'] = array(array('name' => 'groupuserpoints_points', 'value' => 0,  'operand' => '>'));
$entities = elgg_get_entities_from_metadata($options);

$html = '';

foreach ($entities as $entity) {

    $icon = elgg_view_entity_icon($entity, 'small');
    $branding = (abs($entity->groupuserpoints_points) > 1) ? elgg_echo('elggx_groupuserpoints:lowerplural') : elgg_echo('elggx_groupuserpoints:lowersingular');
    $info = "<a href=\"{$entity->getURL()}\">{$entity->name}</a><br><b>{$entity->groupuserpoints_points} $branding</b>";
    $html .= elgg_view('page/components/image_block', array('image' => $icon, 'body' => $info));
}

echo $html;
