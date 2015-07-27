<?php
/**
 * Gain userpoints for hours worked - a skeletal plugin that might need to get customized according to your needs.
 *
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author iionly
 * @copyright iionly
 */

    $action = elgg_get_site_url() . 'action/groups/groupbalance';
	$container_guid = elgg_extract('container_guid', $vars, null);
    $hours_input = array('1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8,  '9' => 9,  '10' =>10);

    $form = "<br>" . elgg_echo("grouppoints_balance:overview", array(GROUPPOINTS_PER_HOUR));
    $form .= "<br><br>";

    $form .= "<b>" . elgg_echo("grouppoints_balance:hours_worked") . "</b>";
    $form .= elgg_view('input/dropdown', array(
                                               'name' => "params[hours_worked]",
                                               'value' => $hours_worked,
                                               'options_values' => $hours_input,
                                              ));
    $form .= "<br><br>";

    $form .= "<b>" . elgg_echo('grouppoints_balance:description') . "</b>";
    $form .= elgg_view('input/text', array('name' => "params[description]", 'value' => ''));
    $form .= "<br><br>";

    $form .= elgg_view("input/securitytoken");
    $form .= elgg_view("input/hidden", array('name' => "container_guid",'value' => $container_guid ));

    $form .= elgg_view('input/submit', array('value' => elgg_echo('grouppoints_balance:proceed')));
    echo elgg_view('input/form', array('action' => $action, 'body' => $form));
