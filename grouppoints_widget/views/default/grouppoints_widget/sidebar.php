<?php

$limit = 5;

$options = array('type' => 'user',
                 'limit' => $limit,
                 'relationship' => 'member',
                 'relationship_guid' => $vars['entity']->guid,
                 'inverse_relationship' => true,
                 'order_by_metadata' =>  array('name' => 'groupuserpoints_points', 'direction' => DESC, 'as' => integer));
$options['metadata_name_value_pairs'] = array(array('name' => 'groupuserpoints_points', 'value' => 0,  'operand' => '>'));
$entities = elgg_get_entities_from_relationship($options);

$html = '';

?>

<div class="elgg-module elgg-module-aside">
    <div class="elgg-head">
        <h3><?php echo elgg_echo('grouppoints_widget:top_group_members'); ?></h3>
    </div>
    <div>
        <?php
            foreach ($entities as $entity) {
                $icon = elgg_view_entity_icon($entity, 'tiny');
                $branding = (abs($entity->groupuserpoints_points) > 1) ? elgg_echo('elggx_groupuserpoints:lowerplural') : elgg_echo('elggx_groupuserpoints:lowersingular');
                $info = "<a href=\"{$entity->getURL()}\">{$entity->name}</a><br><b>{$entity->groupuserpoints_points} $branding</b>";
                $html .= elgg_view('page/components/image_block', array('image' => $icon, 'body' => $info));
            }
            echo $html;
        ?>
    </div>
</div>
