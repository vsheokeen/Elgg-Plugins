<?php
/**
 * Gain userpoints for hours worked - a skeletal plugin that might need to get customized according to your needs.
 *
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author iionly
 * @copyright iionly
 */

// get the form input
$params = get_input('params');
$hours_worked = $params['hours_worked'];
$description = $params['description'];

//get GUID and name of user who has entered the hours of revisions
$user_guid = elgg_get_logged_in_user_guid();
$username = elgg_get_logged_in_user_entity()->name;

if(function_exists('groupuserpoints_add')) {

    // Add userpoints
    $userpoints_balance = $hours_worked * GROUPPOINTS_PER_HOUR;
	
	$object = new ElggObject();
	$object->subtype = 'groupbalance';
	$object->container_guid = get_input('container_guid');
	$object->owner_guid = $user_guid;
	$object->meta_points = $userpoints_balance;
	$success = $object->save();
	
    if($description == '') {
        $description = elgg_echo('grouppoints_balance:no_description', array($username));
    }
   // $success = groupuserpoints_add($user_guid, $userpoints_balance, 'Group Point Balance: '.$description);

    if($success) {
        system_message(elgg_echo('grouppoints_balance:pointsuccess', array($userpoints_balance, $hours_worked)));
    } else {
        register_error(elgg_echo('grouppoints_balance:pointfail'));
    }

}

forward(REFERER);
