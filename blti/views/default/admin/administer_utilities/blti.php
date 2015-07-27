<?php

// A little CSS to layout the table.
echo '<LINK rel="stylesheet" href="' . elgg_get_config('wwwltipath') . 'css/LTItable.css" type="text/css">';

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Called to display the registered LTI consumers tools screen for
 * admin
 ------------------------------------------------------------------*/

// Must be logged in to see this page
admin_gatekeeper();

elgg_set_context('admin');

$user = elgg_get_logged_in_user_entity();

// Get a list of all LTI consumers
$consumerApps = GetAllLTIConsumerInstancesGUID();

$area2 = elgg_view_title(elgg_echo('LTI:register:consumer'));

if ($consumerApps) {

    $tokList = '';
    foreach ($consumerApps as $consumerApp) {
        $tokList .= elgg_view('object/LTI',
                              array('consumer_guid' => $consumerApp)
                             );

    }

    $area2 .= $tokList;
    $text = elgg_view('lti/addbasicconsumer');
    $area2 .=  $text;
	
} else {

    $text = elgg_echo('LTI:register:consumer:none');
    $text .= elgg_view('lti/addbasicconsumer');

    $area2 .= $text;

}

// Format
//$body = elgg_view_layout("one_column",array('title' => '', 'content' => $area2));

// Draw page
//echo elgg_view_page(elgg_echo('LTI:registered'), $body);
echo elgg_view('page/elements/body',
                        array('body' => $area2)
                       );

/*-------------------------------------------------------------------
 * Get all Full LTI consumers
 *
 * Return values - array of Full LTI consumers
 ------------------------------------------------------------------*/
function GetAllLTIConsumerInstancesGUID() {

    // Initialise array
    $list_of_consumer_instance = array();

    // Select Basic LTI consumers from the DB
    $sql  = "SELECT consumer_instance_guid FROM " . elgg_get_config('dbprefix') . "lti_consumer_instance ";
    $results = mysql_query($sql);

    // None present
    if (!$results) {

        system_message('No result from DB');
        return null;

    }

    // Get the data
    while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {

        $list_of_consumer_instance[] = $row['consumer_instance_guid'];

    }

    return $list_of_consumer_instance;
}

?>