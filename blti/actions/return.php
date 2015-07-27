<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Return to whence we came!
 ------------------------------------------------------------------*/

	$site = elgg_get_site_entity(); 
	$access = elgg_set_ignore_access(true);	
	$user = elgg_get_logged_in_user_entity();
	$user_id = elgg_get_logged_in_user_guid();
	$LTI_group_id = $_SESSION['lti_group_id'];
	
	
	
		$result=elgg_get_entities(array('type' => 'object','subtype' => 'poins_scaling'));
		$guid = $result[0]->guid;
		$Values = elgg_get_metadata(array('guid' => $guid));
		//$num = $entity->groupuserpoints_points;
		$class= "";
		$grade= "";
		$newArray= array();

		foreach($Values as $data)	
		{
			if($data->name == 'classA1' || $data->name == 'classA2')
			{
				$newArray['classA'][] = $data->value;
			}
			elseif($data->name == 'classB1' || $data->name == 'classB2')
			{
				$newArray['classB'][] = $data->value;
			}
			elseif($data->name == 'classC1' || $data->name == 'classC2')
			{
				$newArray['classC'][] = $data->value;
			}
			elseif($data->name == 'classD1' || $data->name == 'classD2')
			{
				$newArray['classD'][] = $data->value;
			}
			elseif($data->name == 'classE1' || $data->name == 'classE2')
			{
				$newArray['classE'][] = $data->value;
			}	
		}
	
	$all_activities = elgg_get_entities_from_metadata(array(
				'metadata_name' => 'meta_moderate',
				'metadata_value' => 'approved',
				'type' => 'object',
				'subtype' => 'groupuserpoint',
				'owner_guid' => $user_id,
		));
	
	$points = 0;
		
		
		$ubalance = elgg_get_entities(array('type' => 'object','subtype' => 'groupbalance' , 'container_guid' => $LTI_group_id ,'owner_guid' => $user_id,'limit' => false));

		if(!empty($ubalance))
			{
				foreach($ubalance as $balance)
				{
					$bguid = $balance->guid;
					$bmetadata = elgg_get_metadata(array('guid' => $bguid,'owner_guid' => $user_id));

					if(!empty($bmetadata))
					{
						$points += $bmetadata[0]->value;
					}
				}
			}	
		
		
		foreach($all_activities as $activity)
		{
	
		$ElggEntity = elgg_get_entities(array('guid' => $activity->meta_guid));
		$group_meta_guid = $ElggEntity[0]->container_guid;
		
		if($activity->description == 'comment' || $activity->description == 'discussion_reply')
		{
			$meta_container_id = $activity->meta_guid;
			$meta_entities = elgg_get_entities(array('guid' => $meta_container_id));
		
			$meta_container_secid = $meta_entities[0]->container_guid;
			$meta_entities_sec = elgg_get_entities(array('guid' => $meta_container_secid));
			$group_meta_guid = $meta_entities_sec[0]->container_guid;
			
		}	
		
			if($group_meta_guid == $LTI_group_id)
			{
				$points += $activity->meta_points;
			}
		}

	foreach($newArray as $key => $Array)	
		{
			$min = $Array[0];
			$max = $Array[1];
			$range = range($min, $max);
			if(in_array($points, $range)){
				$class = $key;
			}	
		}

		switch($class) {
                case 'classA':
                    $grade="A";
                    break;
                case 'classB':
                    $grade="B";
                    break;
                case 'classC':
                    $grade="C";
                    break;
                case 'classD':
                    $grade="D";
                    break;
                case 'classE':
                    $grade="E";
                    break;		
        }

		
if (!empty($_SESSION['return_url'])) {
    $return_url = $_SESSION['return_url'];
    $result = logout();

    if ($result) {
        $urlencode = urlencode('You have been logged out of ' . $site->name);
        $url = $return_url . '&lti_msg=' . $urlencode .'&points=' .$points.'&grade='.$grade;
		//echo '<pre>';print_r($url);die;	
        forward($url);

    } else {

        register_error('Failed to logout --- please use Log Off');

    }

}
?>