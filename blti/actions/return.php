<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Return to whence we came!
 ------------------------------------------------------------------*/

	$site = elgg_get_site_entity(); 
	$access = elgg_set_ignore_access(true);	
	$user = elgg_get_logged_in_user_entity();
	$u_id = elgg_get_logged_in_user_guid();
	$group_guid = $LTI_group_id = $_SESSION['lti_group_id'];
	
	$points=0;
	
	$options = array('type' => 'user',
                 'limit' => false,
                 'relationship' => 'member',
                 'relationship_guid' => elgg_get_page_owner_guid(),
                 'inverse_relationship' => true,
                 'order_by_metadata' =>  array('name' => 'groupuserpoints_points', 'direction' => DESC, 'as' => integer));
$options['metadata_name_value_pairs'] = array(array('name' => 'groupuserpoints_points', 'value' => 0,  'operand' => '>'));
$entities = elgg_get_entities_from_relationship($options);

foreach ($entities as $entity) {
	
		if($entity->guid == $u_id){
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
}
}
		
if (!empty($_SESSION['return_url'])) {
    $return_url = $_SESSION['return_url'];
    $result = logout();

    if ($result) {
        $urlencode = urlencode('You have been logged out of ' . $site->name);
        $url = $return_url . '&lti_msg=' . $urlencode .'&points=' .$points;
		//echo '<pre>';print_r($url);die;	
        forward($url);

    } else {

        register_error('Failed to logout --- please use Log Off');

    }

}
?>
