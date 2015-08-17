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

$access = elgg_set_ignore_access(true);
$group_guid = elgg_get_page_owner_guid();

$prev_context = elgg_get_context();
elgg_set_context('groups');

$options = array('type' => 'user',
                 'limit' => false,
                 'relationship' => 'member',
                 'relationship_guid' => elgg_get_page_owner_guid(),
                 'inverse_relationship' => true,
                 'order_by_metadata' =>  array('name' => 'groupuserpoints_points', 'direction' => DESC, 'as' => integer));
$options['metadata_name_value_pairs'] = array(array('name' => 'groupuserpoints_points', 'value' => 0,  'operand' => '>'));
$entities = elgg_get_entities_from_relationship($options);

elgg_set_context($prev_context);

$contents = array();
$content = '';

foreach ($entities as $entity) {
	
		$user_id = $entity->guid;
		$points = 0;
			
		$ubalance = elgg_get_entities(array('type' => 'object','subtype' => 'groupbalance' , 'container_guid' => $group_guid ,'owner_guid' => $user_id,'limit' => false));

		if(!empty($ubalance))
			{
				foreach($ubalance as $balance)
				{
					$bguid = $balance->guid;
					$bmetadata = elgg_get_metadata(array('guid' => $bguid,'owner_guid' => $user_id,'limit' => false));

					if(!empty($bmetadata))
					{
						$points += $bmetadata[0]->value;
					}
				}
			}	
			
		$all_activities = elgg_get_entities_from_metadata(array(
			'metadata_name' => 'meta_moderate',
			'metadata_value' => 'approved',
			'type' => 'object',
			'limit' => false,
			'subtype' => 'groupuserpoint',
			'owner_guid' => $user_id,
		));
		
		
		if(!empty($all_activities))
		{
			
		foreach($all_activities as $activity)
		{
	
		$ElggEntity = elgg_get_entities(array('guid' => $activity->meta_guid));
		$group_meta_guid = $activity->meta_guid;
		
		if($activity->description == 'comment' || $activity->description == 'discussion_reply')
		{
			$meta_container_id = $activity->meta_guid;
			$meta_entities = elgg_get_entities(array('guid' => $meta_container_id));
				if(!empty($meta_entities))
				{
					$meta_container_secid = $meta_entities[0]->container_guid;
					$meta_entities_sec = elgg_get_entities(array('guid' => $meta_container_secid));
					if(!empty($meta_entities_sec))
					{
						$group_meta_guid = $meta_entities_sec[0]->container_guid;
					}
				}
			
		}	

			if($group_meta_guid == $group_guid || $ElggEntity[0]->container_guid == $group_guid)
			{
				$points += $activity->meta_points;
			}
		}
}

                $icon = elgg_view_entity_icon($entity, 'small');
				
				if(abs($points) > 1){
                $branding = (abs($points) > 1) ? elgg_echo('elggx_groupuserpoints:lowerplural') : elgg_echo('elggx_groupuserpoints:lowersingular');
                $info = "<a href=\"{$entity->getURL()}\">{$entity->name}</a><br><b>{$points} $branding</b>";
				
                $contents[$i]['data'] = elgg_view('page/components/image_block', array('image' => $icon, 'body' => $info));
                $contents[$i]['points'] = $points;
				$i++;
				}
			}
		
	usort($contents, function($a,$b)
	{
		return $b['points'] - $a['points'];
	});
	
$i=0;
foreach($contents as $con)
{
	if($i<$count)
	$content .= $con['data'];

	$i++;
}	

echo $content;
