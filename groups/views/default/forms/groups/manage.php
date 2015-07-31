<?php

$container_guid = elgg_extract("container_guid", $vars, false);
$guid = '';
$result=elgg_get_entities(array('type' => 'object', 'subtype' => 'points_manage', 'container_guid' => $container_guid));
$array = array();

if(!empty($result))
{
	$guid = $result[0]->guid;
	$Values = elgg_get_metadata(array('guid' => $guid, 'limit' => false));
		if(!empty($Values))
		{
			foreach($Values as $data)	
			{
				$array[$data->name] = $data->value;
			}
		}
}

// echo '<pre>';print_r($array);die;

$blog = isset($array['blog']) ? $array['blog'] : '';
$file = isset($array['file']) ? $array['file'] : '';
$bookmarks = isset($array['bookmarks']) ? $array['bookmarks'] : '';
$comment = isset($array['comment']) ? $array['comment'] : '';
$riverpost = isset($array['riverpost']) ? $array['riverpost'] : '';
$thewire = isset($array['thewire']) ? $array['thewire'] : '';
//$group = isset($array['group']) ? $array['group'] : '';
$groupforumtopic = isset($array['groupforumtopic']) ? $array['groupforumtopic'] : '';
$discussion_reply = isset($array['discussion_reply']) ? $array['discussion_reply'] : '';
$page_top = isset($array['page_top']) ? $array['page_top'] : '';
$likes = isset($array['likes']) ? $array['likes'] : '';
$poll = isset($array['poll']) ? $array['poll'] : '';
$vote = isset($array['vote']) ? $array['vote'] : '';
$ad = isset($array['ad']) ? $array['ad'] : '';
$izap_videos = isset($array['izap_videos']) ? $array['izap_videos'] : '';
$album = isset($array['album']) ? $array['album'] : '';
$image = isset($array['image']) ? $array['image'] : '';
$fivestar = isset($array['fivestar']) ? $array['fivestar'] : '';
$recommendation = isset($array['recommendation']) ? $array['recommendation'] : '';
$recommendations_approve = isset($array['recommendations_approve']) ? $array['recommendations_approve'] : '';
$invite = isset($array['invite']) ? $array['invite'] : '';
$verify_email = isset($array['verify_email']) ? $array['verify_email'] : '';
$require_registration = isset($array['require_registration']) ? $array['require_registration'] : '';
$expire_invite = isset($array['expire_invite']) ? $array['expire_invite'] : '';
$delete = isset($array['delete']) ? $array['delete'] : '';
$phototag = isset($array['phototag']) ? $array['phototag'] : '';

?>


<div class="elggx_groupuserpoints_actions">
	<form method="POST" action="<?php echo elgg_add_action_tokens_to_url(elgg_get_site_url() . 'action/groups/manage'); ?>">

	<table>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr><td><h3><?php echo elgg_echo('groupuserpoints_standard:activities'); ?></h3></td><td>&nbsp;</td></tr>
	<tr><td colspan="2"><hr /><br /></td></tr>

	<tr>
		<td width="40%"><label><?php echo elgg_echo('groupuserpoints_standard:blog'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[blog]", 'value' => $blog)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td width="40%"><label><?php echo elgg_echo('groupuserpoints_standard:file'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[file]", 'value' => $file)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td width="40%"><label><?php echo elgg_echo('groupuserpoints_standard:bookmarks'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[bookmarks]", 'value' => $bookmarks)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:comment'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[comment]", 'value' => $comment)); ?></td>
	</tr>

	<!--<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:riverpost'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[riverpost]", 'value' => $riverpost)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:thewire'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[thewire]", 'value' => $thewire)); ?></td>
	</tr>-->

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<!--<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:group'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[group]", 'value' => $group)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>-->

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:groupforumtopic'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[groupforumtopic]", 'value' => $groupforumtopic)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:discussion_reply'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[discussion_reply]", 'value' => $discussion_reply)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:page_top'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[page_top]", 'value' => $page_top)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:likes'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[likes]", 'value' => $likes)); ?></td>
	</tr>

	<tr><td></td><td>&nbsp;</td></tr>
	
	<tr><td><h3><?php echo elgg_echo('groupuserpoints_standard:polls'); ?></h3></td><td>&nbsp;</td></tr>
	<tr><td colspan="2"><hr /><br /></td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:poll'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[poll]", 'value' => $poll)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:pollvote'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[vote]", 'value' => $vote)); ?></td>
	</tr>
	<!--
	<tr><td></td><td>&nbsp;</td></tr>
	<tr><td><h3><?php echo elgg_echo('groupuserpoints_standard:classifieds'); ?></h3></td><td>&nbsp;</td></tr>
	<tr><td colspan="2"><hr /><br /></td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:add_classified'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[ad]", 'value' => $ad)); ?></td>
	</tr>

	<tr><td></td><td>&nbsp;</td></tr>
	<tr><td><h3><?php echo elgg_echo('groupuserpoints_standard:izap_videos'); ?></h3></td><td>&nbsp;</td></tr>
	<tr><td colspan="2"><hr /><br /></td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:add_video'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[izap_videos]", 'value' => $izap_videos)); ?></td>
	</tr>

	<tr><td></td><td>&nbsp;</td></tr>
	<tr><td><h3><?php echo elgg_echo('groupuserpoints_standard:tidypics'); ?></h3></td><td>&nbsp;</td></tr>
	<tr><td colspan="2"><hr /><br /></td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:create_album'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[album]", 'value' => $album)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:upload_photo'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[image]", 'value' => $image)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:phototag'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[phototag]", 'value' => $phototag)); ?></td>
	</tr>

	<tr><td></td><td>&nbsp;</td></tr>
	<tr><td><h3><?php echo elgg_echo('groupuserpoints_standard:fivestar'); ?></h3></td><td>&nbsp;</td></tr>
	<tr><td colspan="2"><hr /><br /></td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:fivestar_vote'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[fivestar]", 'value' => $fivestar)); ?></td>
	</tr>

	<tr><td></td><td>&nbsp;</td></tr>
	<tr><td><h3><?php echo elgg_echo('groupuserpoints_standard:recommendations'); ?></h3></td><td>&nbsp;</td></tr>
	<tr><td colspan="2"><hr /><br /></td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:recommendations:points'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[recommendation]", 'value' => $recommendation)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:recommendations:approve'); ?></label></td>
		<td><?php echo elgg_view('input/dropdown', array(
							'name' => 'params[recommendations_approve]',
							'options_values' => array('1' => elgg_echo('elggx_groupuserpoints:settings:yes'), '0' => elgg_echo('elggx_groupuserpoints:settings:no')),
							'value' => $recommendations_approve
						));
			?>
		</td>
	</tr>

	<tr><td></td><td>&nbsp;</td></tr>
	<tr><td><h3><?php echo elgg_echo('groupuserpoints_standard:invitesettings'); ?></h3></td><td>&nbsp;</td></tr>
	<tr><td colspan="2"><hr /><br /></td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:invite'); ?></label></td>
		<td><?php echo elgg_view('input/text', array('name' => "params[invite]", 'value' => $invite)); ?></td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:verify_email'); ?></label></td>
		<td><?php echo elgg_view('input/dropdown', array(
							'name' => 'params[verify_email]',
							'options_values' => array('1' => elgg_echo('elggx_groupuserpoints:settings:yes'), '0' => elgg_echo('elggx_groupuserpoints:settings:no')),
							'value' => $verify_email
						));
			?>
		</td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:require_registration'); ?></label></td>
		<td><?php echo elgg_view('input/dropdown', array(
							'name' => 'params[require_registration]',
							'options_values' => array('1' => elgg_echo('elggx_groupuserpoints:settings:yes'), '0' => elgg_echo('elggx_groupuserpoints:settings:no')),
							'value' => $require_registration
						));
			?>
		</td>
	</tr>

	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:expire_invite'); ?></label></td>
		<td><?php echo elgg_view('input/dropdown', array(
						'name' => 'params[expire_invite]',
						'options_values' => array(
							'0'        => elgg_echo('elggx_groupuserpoints:settings:never'),
							'3600'     => elgg_echo('elggx_groupuserpoints:settings:1_hour'),
							'86400'    => elgg_echo('elggx_groupuserpoints:settings:1_day'),
							'604800'   => elgg_echo('elggx_groupuserpoints:settings:1_week'),
							'1209600'  => elgg_echo('elggx_groupuserpoints:settings:2_weeks'),
							'2419200'  => elgg_echo('elggx_groupuserpoints:settings:4_weeks'),
							'31536000' => elgg_echo('elggx_groupuserpoints:settings:365_days')
						),
						'value' => $expire_invite
					));
			?>
		</td>
	</tr>-->

	<tr><td></td><td>&nbsp;</td></tr>
	<tr><td><h3><?php echo elgg_echo('groupuserpoints_standard:misc'); ?></h3></td><td>&nbsp;</td></tr>
	<tr><td colspan="2"><hr /><br /></td></tr>

	<tr>
		<td><label><?php echo elgg_echo('groupuserpoints_standard:delete'); ?></label></td>
		<td><?php echo elgg_view('input/dropdown', array(
							'name' => 'params[delete]',
							'options_values' => array('1' => elgg_echo('elggx_groupuserpoints:settings:yes'), '0' => elgg_echo('elggx_groupuserpoints:settings:no')),
							'value' => $delete
						));
			?>
		</td>
	</tr>

	</table>

	<br>

	<?php 
	echo elgg_view('input/hidden', array(
							'name' => 'container_guid',
							'value' => $container_guid
						));
	
	echo elgg_view('input/hidden', array(
							'name' => 'guid',
							'value' => $guid
						));	
						
	echo elgg_view('input/submit', array('value' => elgg_echo("save"))); 
	?>

	</form>
</div>
