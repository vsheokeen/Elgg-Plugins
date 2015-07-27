<?php

if (elgg_get_context() == 'profile' && $vars['size'] == 'large') {
	if (elgg_get_plugin_setting('profile_display', 'elggx_groupuserpoints')) {
?>

		<div class="groupuserpoints_profile mtm">
			<div><span><?php echo elgg_echo('elggx_groupuserpoints:upperplural') . ': ' . $vars['entity']->groupuserpoints_points;?></span></div>
		</div>

	<?php } ?>
<?php } ?>
