<?php
/**
 * Groups profile page Userpoints widget for Widget Manager plugin
 *
 */

// get widget settings
$count = sanitise_int($vars["entity"]->grouppoints_widget_count, false);
if(empty($count)){
        $count = 5;
}

$prev_context = elgg_get_context();
elgg_set_context('groups');

$options = array('type' => 'user',
                 'limit' => $limit,
                 'relationship' => 'member',
                 'relationship_guid' => elgg_get_page_owner_guid(),
                 'inverse_relationship' => true,
                 'order_by_metadata' =>  array('name' => 'groupuserpoints_points', 'direction' => DESC, 'as' => integer));
$options['metadata_name_value_pairs'] = array(array('name' => 'groupuserpoints_points', 'value' => 0,  'operand' => '>'));
$entities = elgg_get_entities_from_relationship($options);

elgg_set_context($prev_context);

$content = '';

foreach ($entities as $entity) {
                $icon = elgg_view_entity_icon($entity, 'small');
                $branding = (abs($entity->groupuserpoints_points) > 1) ? elgg_echo('elggx_groupuserpoints:lowerplural') : elgg_echo('elggx_groupuserpoints:lowersingular');
                $info = "<a href=\"{$entity->getURL()}\">{$entity->name}</a><br><b>{$entity->groupuserpoints_points} $branding</b>";
                $content .= elgg_view('page/components/image_block', array('image' => $icon, 'body' => $info));
            }

echo $content;
