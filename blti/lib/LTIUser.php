<?php

/*------------------------------------------------------------------
 * Elgg LTI
 *
 * Library file providing User functions
 ------------------------------------------------------------------*/

/*-------------------------------------------------------------------
 * Login the current user having logged out any existing sessions. We
 * note the return url for later use in the topbar.
 *
 * Parameters
 *  $tool_provider - the tool provider for this session
 *
 * return value - boolean, successful login or not
 ------------------------------------------------------------------*/
function LoginUser ($tool_provider) {

    // Clear any existing sessions
    if (elgg_is_logged_in()) logout();

    $values = GetPluginSettings();
    $userprovision = $values['userprovision'];

    $user_id = $tool_provider->user->getID(BasicLTI_Tool_Provider::ID_SCOPE_GLOBAL);

    $consumer_key = $tool_provider->consumer->guid;
    $context_id = $tool_provider->user->context->id;

    // Does user exist
    $user = CheckLTIUser($user_id);

    // Provision user, if on and needed
    if (empty($user)) {

        if ($userprovision) {
            $user = CreateLTIUser($consumer_key, $context_id, $tool_provider->user);
            if (empty($user)) forward();

        } else {

            system_message(elgg_echo('LTI:info:noprovision'));
            forward();
            exit;

        }
    }

    // Set up current context id
    $user->context_id = $context_id;
    $user->email = $tool_provider->user->email;
    $user->name = $tool_provider->user->fullname;
    $user->save();

    // Login
    $result = login($user, false);

    return $result;

}

/*-------------------------------------------------------------------
 * Check whether this context_id from consumer_key has already been
 * provisioned.
 *
 * Parameters
 *  $user_id - unique identifer for each user provided by consumer
 *             application. This is not the username
 *  $consumer_key - identify the tool consumer for this group
 *
 *  return value - user object if exits, otherwise null
 ------------------------------------------------------------------*/
function CheckLTIUser($user_id) {

    // Check that user_id is present; if not all we can do is forward
    // to login page
    if (empty($user_id)) {

        system_messages(elgg_echo('LTI:info:noid'));
        forward();

    }

    // Is there a user with username $user_id
    $user = get_user_by_username($user_id);

    if (!empty($user->username)) return $user;

    // No such user
    return null;

}

/*-------------------------------------------------------------------
 * Check whether this context_id from consumer_key has already been
 * provisioned.
 *
 * Parameters
 *  $consumer_key - identify the tool consumer for this group
 *  $context_id - id of the course
 *  $LTI_User - user details from consumer
 *
 * return value - user object
 ------------------------------------------------------------------*/
function CreateLTIUser($consumer_key, $context_id, $LTI_User) {

   $user_id = $LTI_User->getID(BasicLTI_Tool_Provider::ID_SCOPE_GLOBAL);

   $fullname = $LTI_User->fullname;
   if(empty($fullname)) $fullname = 'LTI Provisioned username';
   $email = $LTI_User->email;
   if (empty($email)) $email = 'lti@noreply.lti';

   $password = random_string(10);

   $guid = register_user($user_id                                ,                 // Elgg username
                         $password,                                                // Password
                         $fullname,                                                // Full name + [user_id]
                         $email,                                                   // Email address
                         True,                                                     // Allow multiple emails
                         0,                                                        // GUID of user this user will friend once registered
                         ''
                        );                                                         // Invite code

   if (!$guid) return null;

   $user = get_entity($guid);
   $user->enable();
   $user->consumer_key = $consumer_key;
   $user->context_id;

   // If we have image then create Elgg version

   return $user;

}

function CreateFromLTIMembership($member) {

    $user_id = $member->getID(BasicLTI_Tool_Provider::ID_SCOPE_GLOBAL);

    $values = GetPluginSettings();
    $userprovision = $values['userprovision'];

    // Does user exist
    $user = CheckLTIUser($user_id);

    if ((is_null($user) || empty($user)) && $userprovision) {

        $fullname = $member->fullname;
        $email = $member->email;
        if (empty($email)) $email = 'lti@noreply.lti';

        $password = random_string(10);

        $guid = register_user($user_id,                                                 // Elgg username
                              $password,                                                // Password
                              $fullname,                                                // Full name
                              $email,                                                   // Email address
                              True,                                                     // Allow multiple emails
                              0,                                                        // GUID of user this user will friend once registered
                              ''
                             );

        if (!$guid) return null;

        $user = get_entity($guid);
        $user->enable();
        $user->consumer_key = $member->context->consumer_instance->guid;
        $user->context_id = $member->context->id;
        $user->save();
    }

    return $user;
}
?>