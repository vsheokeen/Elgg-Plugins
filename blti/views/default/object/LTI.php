<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Display the LTI object
 ------------------------------------------------------------------*/

$consumer_instance_guid = $vars['consumer_guid'];
$consumer_instance = new LTI_Tool_Consumer_Instance($consumer_instance_guid, elgg_get_config('dbprefix'));
$consumer_tool = new LTI_Tool_Consumer($consumer_instance->consumer_guid, elgg_get_config('dbprefix'));

$url = elgg_get_site_entity()->url;

if ($consumer_instance->isEnabled()) {
    $enabled = 'yes';
    $info = '<div class="plugin_details active">';
} else {
    $enabled = 'no';
    $info = '<div class="plugin_details not-active">';
}

$icon = '<img src="' . GetImage($consumer_instance->guid, '.png') . '" title="Image of Consumer" />';

$info .= '<table>';
$info .= '<tr><th class = "column1"></th><th class = "column2"></th><th class = "column3">Name</th><th class = "column4">Consumer Name</th><th class = "column5"></th></tr>';
$info .= '<tr>';
$info .= '<td class = "column1">' . $icon . '</td>';
$info .= '<td class = "column2">';
$info .= '<a href="editconsumer?LTIconsumerguid=' . $consumer_instance->guid . '" alt="Edit consumer" title="Edit Consumer"><img src="' . elgg_get_config('wwwroot') . 'mod/blti/images/edit.gif'   . '"></a>';
$info .= '<a href="'.$url.'blti/delete/'       . $consumer_instance->guid . '" alt="Delete consumer" title="Delete Consumer"><img src="' . elgg_get_config('wwwroot') . 'mod/blti/images/delete.gif' . '"></a>';
$info .= '</td>';
$info .= '<td class = "column3">' . $consumer_tool->name          . '</td>';
$info .= '<td class = "column4">' . $consumer_tool->consumer_name . '</td>';

$url = elgg_add_action_tokens_to_url(elgg_get_config('wwwroot') . 'action/' . elgg_get_config('ltiname') . '/enable?guid=' . $consumer_instance->guid );
$option = ($consumer_instance->isEnabled()) ? 'Disable' : 'Enable';
$info .= '<td class = "column5"><div class="admin_plugin_enable_disable"><a href="' . $url . '">' . $option . '</a></div></td>';

$info .= '</tr>';
$info .= '</table>';
$info .= '</div>';

echo $info;
?>