<?php

session_start();

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

// Get group details
$group_guid = (int) get_input('group_guid');
$group = get_entity($group_guid);
$filter = get_input('filter');
set_page_owner($group_guid);

// Get plugin settings
$values = GetPluginSettings();
$userprovision    = $values['userprovision'];
$instructorupdate = $values['allowinstructor'];
$testmode         = $values['testmode'];

// Title
if ($userprovision) {
    $title = elgg_echo('LTI:members:label');
    $area2 = elgg_view_title($title);
} else {
    $title = elgg_echo('LTI:members:label:off');
    $area2 = elgg_view_title($title);
}

// Hidden fields: delete
$formbody = elgg_view('input/hidden',
                       array('name' => 'delete',
                             'value' => 'yes'
                             )
                      );

// Hidden fields: delete and group_guid
$formbody .= elgg_view('input/hidden',
                       array('name' => 'group_guid',
                             'value' => $group_guid
                             )
                      );

$formbody .= elgg_view('input/submit',
                      array('value' => 'Update Membership'));

$form = elgg_view('input/form',
                 array('action' => elgg_get_config('wwwroot') . 'action/' . elgg_get_config('ltiname') . '/dosync',
                        'body' => $formbody
                      )
                 );

// Hidden fields: delete and group_guid
$formbody1 = elgg_view('input/hidden',
                       array('name' => 'delete',
                             'value' => 'no'
                             )
                      );

$formbody1 .= elgg_view('input/hidden',
                       array('name' => 'group_guid',
                             'value' => $group_guid
                             )
                      );

$formbody1 .= elgg_view('input/submit',
                      array('value' => 'Update Membership without deletions'));

$form1 = elgg_view('input/form',
                 array('action' => elgg_get_config('wwwroot') . 'action/' . elgg_get_config('ltiname') . '/dosync',
                        'body' => $formbody1
                      )
                 );

$area2 .= elgg_view('page/elements/wrapper', array('body' => '<table><tr><td>' . $form . '</td><td>&nbsp;</td><td>' . $form1 . '</td></tr></table>'));

switch ($filter) {
    case 'sync':
       // Clear session variables
        $_SESSION['added_members'] = array();
        $_SESSION['user_provision'] = array();
        $_SESSION['deleted_members'] = array();
        $_SESSION['changed_members'] = array();

        $_SESSION['role_changed_del'] = array();
        $_SESSION['role_changed_add'] = array();

        $all_added_members = array();
        $all_user_provision = array();
        $all_deleted_members = array();
        $all_changed_members = array();
        $all_role_changed_del = array();
        $all_role_changed_add = array();

        $contexts = array();

        // Get logged in user
        $cur_user = elgg_get_logged_in_user_entity();

        $consumer_instance = new LTI_Tool_Consumer_Instance($cur_user->consumer_key, elgg_get_config('dbprefix'));
        $context = new LTI_Context($consumer_instance, $cur_user->context_id);

        // Add this context as must be used
        $contexts[] = $context;

        // Check how synchronisation is to be done. If this instructor
        // is from primary context then synchronise across all contexts.
        // If the instructor is from a shared context than just
        // synchronise that context --- which is already set up
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

            $added_members = array();
            $user_provision = array();
            $membershipFromConsumer = array();

            // First check that we have a membership service. Strictly speaking we shouldn't end up here unless
            // there is a service as the Sync option shouldn't appear
            if ($context->hasMembershipsService()) {

                // Get array of membership from consumer
                $members = $context->doMembershipsService();

                // Failure and single context --- abandon with error message
                if (!$members && sizeof(contexts) == 1) {
                    register_error(sprintf(elgg_echo('LTI:members:error:noservice'), $context->consumer_instance->guid));
                    forward($group->getURL());
                }

                if (!$members && sizeof($contexts) > 1) {
                    register_error(sprintf(elgg_echo('LTI:members:error:noservice'), $context->consumer_instance->guid));
                    break;
                }

                // If there are members
                if (!empty($members)) {
                    foreach ($members as $member) {

                        $user = null;
                        $user = get_user_by_username($member->getID(BasicLTI_Tool_Provider::ID_SCOPE_GLOBAL));

                        if (empty($user)) {
                            if ($userprovison) {
                                // To be created
                                $user_provision[] = $member->getID(BasicLTI_Tool_Provider::ID_SCOPE_GLOBAL);
                            } else {
                                // Provisioning off --- would be added users!
                                $user_provision[] = $member;
                            }
                        } else {

                            $user_guid = $user->getGUID();

                            // Check Membership
                            if (!$group->isMember($user)) {
                                $added_members[] = $user;
                            } else {
                                // Check for changes here
                                $newname = '';
                                $newemail = '';

                                if ((!empty($member->email)    && ($member->email    != $user->email)) ||
                                    (!empty($member->fullname) && ($member->fullname != $user->name))) {
                                    // Store current name and email
                                    $userold = $user->name;
                                    $emailold = $user->email;
                                    $user->name  = $member->fullname . '(' . $member->email . ')<br />';
                                    $user->name .= '<i>' . $userold . '(' . $emailold . ')</i>';
                                    $changed_members[] = $user;
                                }
                            }

                            // Check that all users with instructor still have that privilege
                            if (check_entity_relationship($user_guid, 'instructor', $group_guid) && !$member->isStaff()) {
                                $role_changed_del[] = $user;
                            }

                            // Update role if need be
                            if (!check_entity_relationship($user_guid, 'instructor', $group_guid) && $member->isStaff()) {
                                $role_changed_add[] = $user;
                            }

                            // Some form of check if user has lost role

                            // List of members from consumer
                            $membershipFromConsumer[] = $user;

                        }
                    }
                }

                // Sort out deleted members
                $currentMembers = array();
                foreach (GetFromContext($context, $group->getMembers()) as $groupmember) {
                    $currentMembers[] = $groupmember->username;
                }

                $fromConsumer = array();
                foreach ($membershipFromConsumer as $consumermember) {
                    $fromConsumer[] = $consumermember->username;
                }

                $deleted_members_u = array_diff($currentMembers, $fromConsumer);

                foreach ($deleted_members_u as $key => $value) {
                    $user = get_user_by_username($value);
                    $deleted_members[] = $user;
                }

            } else {
                register_error(sprintf(elgg_echo('LTI:members:error:noservice'), $context->consumer_instance->guid));
            }

            foreach($added_members    as $added)    {$all_added_members[] = $added;}
            foreach($user_provision   as $pr)       {$all_user_provision[] = $pr;}
            foreach($changed_members  as $changed)  {$all_changed_members[] = $changed;}
            foreach($deleted_members  as $deleted)  {$all_deleted_members[] = $deleted;}
            foreach($role_changed_del as $role_del) {$all_role_changed_del[] = $role_del;}
            foreach($role_changed_add as $role_add) {$all_role_changed_add[] = $role_add;}
        }

        // Store added members
        $_SESSION['added_members'] = serialize($all_added_members);
        // Would be added
        $_SESSION['user_provision'] = serialize($all_user_provision);
        // Store changed members
        $_SESSION['changed_members'] = serialize($all_changed_members);
        // Store deleted members
        $_SESSION['deleted_members'] = serialize($all_deleted_members);
        // Store removed instructors
        $_SESSION['role_changed_del'] = serialize($role_changed_del);
        // Store added instructors
        $_SESSION['role_changed_add'] = serialize($role_changed_add);

        // Work out which set to show against the appropriate tab
        if (sizeof($all_role_changed_add)   > 0) $filter_content = elgg_view_entity_list($all_role_changed_add, $all_role_changed_add.sizeof(), 0, 10, false, true, true);
        if (sizeof($all_user_provision)     > 0) $filter_content = elgg_view_entity_list($all_user_provision, $all_user_provision.sizeof(), 0, 10, false, true, true);
        if (sizeof($all_role_changed_del)   > 0) $filter_content = elgg_view_entity_list($all_role_changed_del, $all_role_changed_del.sizeof(), 0, 10, false, true, true);
        if (sizeof($all_changed_members)    > 0) $filter_content = elgg_view_entity_list($all_changed_members, $all_changed_members.sizeof(), 0, 10, false, true, true);
        if (sizeof($all_deleted_members)    > 0) $filter_content = elgg_view_entity_list($all_deleted_members, $all_deleted_members.sizeof(), 0, 10, false, true, true);
        if (sizeof($all_added_members)      > 0) $filter_content = elgg_view_entity_list($all_added_members, $all_added_members.sizeof(), 0, 10, false, true, true);
    break;
    // Display the various lists
    case 'added':
        $added_members = array();
        $added_members = $_SESSION['added_members'];
        $filter_content = elgg_view_entity_list($added_members, $added_members.sizeof(), 0, 10, false, true, true);
    break;
    //case 'pr_off' || 'pr_on':
    //    $user_provision = array();
    //    $user_provision = $_SESSION['user_provision'];
    //    $filter_content = elgg_view_entity_list($user_provision, $user_provision.sizeof(), 0, 10, false, true, true);
    //break;
    case 'deleted':
        $deleted_members = array();
        $deleted_members = $_SESSION['deleted_members'];
        $filter_content = elgg_view_entity_list($deleted_members, $deleted_members.sizeof(), 0, 10, false, true, true);
    break;
    case 'changed':
        $changed_members = array();
        $changed_members = $_SESSION['changed_members'];
        $filter_content = elgg_view_entity_list($changed_members, $changed_members.sizeof(), 0, 10, false, true, true);
    break;
    case 'role_del':
        $role_changed_del = array();
        $role_changed_del = $_SESSION['role_changed_del'];
        $filter_content = elgg_view_entity_list($role_changed_del, $role_changed_del.sizeof(), 0, 10, false, true, true);
    break;
    case 'role_add':
        $role_chnaged_add = array();
        $role_changed_add = $_SESSION['role_changed_add'];
        $filter_content = elgg_view_entity_list($role_changed_add, $role_changed_add.sizeof(), 0, 10, false, true, true);
}

$members = $group->getMembers();
$members_nav = elgg_view('members/members_list_menu', array('count' => sizeof($members), 'filter' => $filter));

$content = $members_nav . $filter_content;

$area2 .= elgg_view('page/elements/wrapper', array('body' => $content, 'subclass' => 'members'));

$body = elgg_view_layout('two_column_left_sidebar', $area1, $area2);

$title = elgg_echo('Members...');

// Finally draw the page
echo elgg_view_page($title, $body);

?>