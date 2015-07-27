<?php

/*------------------------------------------------------------------
 * Elgg LTI
 *
 * This is called when a sync (membership) operation is performed.
 * This works out the added, deleted and changed when called. The
 * results are stored in session variables to avoid recaluating when
 * the added, deleted and changed tabs are clicked.
 ------------------------------------------------------------------*/

elgg_set_context('groups');

// Must be logged in to use this page
gatekeeper();

// Get plugin settings
$values = GetPluginSettings();
$userprovision    = $values['userprovision'];
$instructorupdate = $values['allowinstructor'];
$testmode         = $values['testmode'];

// Get group details
$group_guid = (int) get_input('group_guid');
$group = get_entity($group_guid);
set_page_owner($group_guid);

// Update synchronisation time
$contexts = array();

// Get logged in user
$cur_user = elgg_get_logged_in_user_entity();

$consumer_instance = new LTI_Tool_Consumer_Instance($cur_user->consumer_key, elgg_get_config('dbprefix'));
$context = new LTI_Context($consumer_instance, $cur_user->context_id);

// Add this context as must be used
$contexts[] = $context;

if ($cur_user->consumer_key == $group->consumer_key) {
    // add the shared contexts --- get all the contexts
    $shares = $context->getShares();
    foreach($shares as $share) {
        $consumer_instance = new LTI_Tool_Consumer_Instance($share->consumer_instance_guid, elgg_get_config('dbprefix'));
        $context = new LTI_Context($consumer_instance, $share->context_id);
        $contexts[] = $context;
    }
}

foreach ($contexts as $context) {
    // First check that we have a membership service. Strictly speaking we shouldn't end up here unless
    // there is a service as the Sync option shouldn't appear
    if ($context->hasMembershipsService() && $context->hasSettingService()) {
        // Lets try and write back the current time
        $context->doSettingService(LTI_Context::EXT_WRITE, date('d-M-Y H:i'));
    }
}

$deletions = get_input('delete');
$delete = ($deletions == 'yes') ? true : false;

$added_members    = unserialize($_SESSION['added_members']);
if ($delete) $deleted_members  = unserialize($_SESSION['deleted_members']);
$changed_members  = unserialize($_SESSION['changed_members']);
$role_changed_del = unserialize($_SESSION['role_changed_del']);
$role_changed_add = unserialize($_SESSION['role_changed_add']);
$user_provision   = unserialize($_SESSION['user_provision']);

/*------------------------------------------------------------------
 * Added users
 ------------------------------------------------------------------*/
// If there are users to be added and user provision is on then do it!
if (sizeof($user_provision) > 0 && $userprovision) {
    $new_text = 'New users: ';
    foreach($user_provision as $member) {
        $new_text .= $member->fullname . ' ';
        // Create account, if needed
        if (!$testmode) {
            $user = CreateFromLTIMembership($member);
            $group->join($user);
        }
    }
} else {
    if (sizeof($user_provision) == 0 && $userprovision) $new_text = elgg_echo('LTI:members:sync:nousers');
    if (sizeof($user_provision) >  0 && !$userprovision) $new_text = elgg_echo('LTI:members:sync:nousers:pr_off');
}

/*------------------------------------------------------------------
 * Added members
 ------------------------------------------------------------------*/
if (sizeof($added_members) > 0) {
    // Added members --- join to group
    $added_text = 'Added members: ';
    foreach ($added_members as $member) {
        $added_text .= $member->name . ' ';
        if (!$group->isMember($member)) {
            if (!$testmode) $group->join($member);
        }
    }
} else {
    $added_text = elgg_echo('LTI:members:sync:noadded');
}

/*------------------------------------------------------------------
 * Delete members
 ------------------------------------------------------------------*/
if (sizeof($deleted_members) > 0 && $delete) {
    // Deleted members
    $deleted_text = 'Deleted members: ';
    foreach ($deleted_members as $member) {
        $deleted_text .= $member->name . ' ';
        if ($group->isMember($member)) {
            if (!$testmode)  $group->leave($member);
        }
    }
} else {
    $deleted_text = elgg_echo('LTI:members:deleted:no');
    if (sizeof($deleted_members) == 0 && $delete) $deleted_text = elgg_echo('LTI:members:deleted:none');
}

/*------------------------------------------------------------------
 * Changed members
 ------------------------------------------------------------------*/
if (sizeof($changed_members) > 0 && $instructorupdate) {
    $changed_text = 'Changed members: ';
    foreach ($changed_members as $changed) {
        $changed_text .= $changed->name . ' ';
        if (!$testmode) {
            //slice and dice
            $changed->save();
        }
    }
} else {
    if (sizeof($changed_members) == 0) $changed_text = elgg_echo('LTI:memners:changed:none');
}

/*------------------------------------------------------------------
 * Changed role --- upgraded
 ------------------------------------------------------------------*/
if (sizeof($role_changed_add) > 0) {
    $changed_role_add_text = 'Upgraded to instructor: ';
    foreach ($role_changed_add as $new_instructor) {
        $changed_role_add_text .= $new_instructor->name . ' ';
        if (!$testmode) add_entity_relationship($new_instructor->getGUID(), 'instructor', $group_guid);
    }
}

/*------------------------------------------------------------------
 * Changed role --- downgraded
 ------------------------------------------------------------------*/
if (sizeof($role_changed_del) > 0) {
    $changed_role_del_text = 'Upgraded to instructor: ';
    foreach ($role_changed_del as $new_student) {
        $changed_role_del_text .= $new_student->name . ' ';
        if (!$testmode) remove_entity_relationship($new_student->getGUID(), 'instructor', $group_guid);
    }
}

// Title
$area2 = elgg_view_title(elgg_echo('LTI:members:label:done'));

$area2 .= elgg_view('page/elements/wrapper', array('body' => $new_text));
$area2 .= elgg_view('page/elements/wrapper', array('body' => $added_text));
$area2 .= elgg_view('page/elements/wrapper', array('body' => $deleted_text));
$area2 .= elgg_view('page/elements/wrapper', array('body' => $changed_text));
if (!empty($changed_role_add_text)) $area2 .= elgg_view('page/elements/wrapper', array('body' => $changed_role_add_text));
if (!empty($changed_role_del_text)) $area2 .= elgg_view('page/elements/wrapper', array('body' => $changed_role_del_text));

$body = elgg_view_layout('two_column_left_sidebar', $area1, $area2);

// Finally draw the page
elgg_view_page($title, $body);

?>