<?php

/*-------------------------------------------------------------------
 * Elgg LTI topbar extender
 *
 ------------------------------------------------------------------*/

// Need to be logged in to have return to consumer option
gatekeeper();

$user = elgg_get_logged_in_user_entity();

if (!empty($_SESSION['return_url'])) {

    $url = elgg_add_action_tokens_to_url(elgg_get_config('wwwroot') . 'action/' . elgg_get_config('ltiname') . '/return');
    echo '<a class="usersettings" href="' . $url . '">' . $_SESSION['return_name'] . '</a>';

}
?>