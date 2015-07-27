<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Library file providing group functions
 -------------------------------------------------------------------*/

/*-------------------------------------------------------------------
 * Create a group with two additional attributes that allows us to
 * track this group back to the consumer that created it and the
 * context (in VLE terms course) that it is linked from.
 *
 * Parameters
 *  $user - the owner of the group
 *  $name - group name
 *  $context_id - the contaxt from the consumer (e.g., VLE = course)
 *  $consumer_key - identify the tool consumer for this group
 *
 * return value - group object
 ------------------------------------------------------------------*/
function CreateLTIGroup($user, $name, $context_id, $consumer_key) {

    $group_guid = 0;
    $group = new ElggGroup($group_guid);

    // Set the group properties that we can!
    $group->name              = $name;
    $group->context_id        = $context_id;   // This is a unique identifier from the consumer for this context
    $group->consumer_key      = $consumer_key; // Which consumer is creating this group
    $group->membership        = ACCESS_PRIVATE;
    $group->access_id         = ACCESS_PUBLIC;
    $group->briefdescription  = elgg_echo('LTI:provision:group');

    $consumer_instance = new LTI_Tool_Consumer_Instance($group->consumer_key, elgg_get_config('dbprefix'));
    $context = new LTI_Context($consumer_instance, $group->context_id);
    $group->description = $context->title;

    $group->save();
    $group->join($user);

     // Add images
    $prefix = 'groups/' . $group->guid;
    $filename = GetImage($consumer_key, '.jpg');

    $thumbtiny   = get_resized_image_from_existing_file($filename,25,25, true);
    $thumbsmall  = get_resized_image_from_existing_file($filename,40,40, true);
    $thumbmedium = get_resized_image_from_existing_file($filename,100,100, true);
    $thumblarge  = get_resized_image_from_existing_file($filename,200,200, false);

    if ($thumbtiny) {

        $thumb = new ElggFile();
        $thumb->owner_guid = $group->owner_guid;
        $thumb->setMimeType('image/jpeg');

        $thumb->setFilename($prefix."tiny.jpg");
        $thumb->open("write");
        $thumb->write($thumbtiny);
        $thumb->close();

        $thumb->setFilename($prefix."small.jpg");
        $thumb->open("write");
        $thumb->write($thumbsmall);
        $thumb->close();

        $thumb->setFilename($prefix."medium.jpg");
        $thumb->open("write");
        $thumb->write($thumbmedium);
        $thumb->close();

        $thumb->setFilename($prefix."large.jpg");
        $thumb->open("write");
        $thumb->write($thumblarge);
        $thumb->close();

        $group->icontime = time();

    }

     // return the URL
     return $group;

}

/*-------------------------------------------------------------------
 * Check whether a particular group exists. For our LTI created
 * groups this requires that the name, context_id and consumer_key
 * are all the same
 *
 * Parameters
 *  $name - group name
 *  $context_id - the context from the consumer (e.g., VLE = course)
 *  $consumer_key - identify the tool consumer for this group
 *
 * return value - group object if it exits, otherwise null
 -------------------------------------------------------------------*/
function CheckLTIgroup($name, $context_id, $consumer_key) {

    // Does this group exist. This means that $context_id and
    // $consumer_key must all match
    $allgroups = get_entities('group', '', 0, '', 0, 0, false, 0, null);

    foreach ($allgroups as $group) {
        if($group->consumer_key == $consumer_key &&
           $group->context_id == $context_id) {

            if($group->name != $name ) {
                $group->name = $name;
                $group->save();
            }

            // Group exists, return URL
            return $group;

        }
    }

    return null;
}

/*-------------------------------------------------------------------
 * Provision the group as long as the group plugin is present and
 * provisioning is switched on
 *
 * Parameters
 *  $tool_provider
 -------------------------------------------------------------------*/
function ProvisionLTIGroup ($tool_provider) {

    // Get status of group provisining
    $values = GetPluginSettings();
    $groupprovision = $values['groupprovision'];

    $groupname = $tool_provider->context->title;

    // Get context_id and consumer_key for primary context
    $context_id = $tool_provider->context->id;
    $consumer_key = $tool_provider->context->consumer_instance->consumer_guid;
    $user_id = $tool_provider->user->getID(BasicLTI_Tool_Provider::ID_SCOPE_GLOBAL);

    // Check if we are dealing with an unapproved share. If so return
    // to consumer with suitable message
    if (!empty($tool_provider->user->context->share_approved) && !$tool_provider->user->context->share_approved) {
        $urlencode = urlencode(sprintf(elgg_echo('LTI:error:sharing'),  elgg_get_config('sitename')));
        forward($tool_provider->return_url . '&lti_msg=' . $urlencode);
        return false;
    }

    // Get user
    $user = CheckLTIUser($user_id);
    $user_guid = $user->getGUID();
    $_SESSION['lti_logger_id'] = $user->getGUID();
    $staff = $tool_provider->user->isStaff();

    // Check that groups are present and provisioning is on
    if (elgg_is_active_plugin('groups') && $groupprovision) {

        $group = CheckLTIGroup($groupname, $context_id, $consumer_key);
        // Only staff can create groups.
        if ($staff && is_null($group)) {
            $group = CreateLTIGroup($user, $groupname,  $context_id, $consumer_key);
            // Ensure that the owner is instructor as simpifies matters
            add_entity_relationship($user_guid, 'instructor', $group->getGUID());
            system_message(elgg_echo('LTI:info:newgroup'));
            forward($group->getURL());
        }

        if (is_null($group)) {
            system_messages(elgg_echo('LTI:info:nogroup'));
            forward();
        }

        $group_guid = $group->getGUID();
		$_SESSION['lti_group_id'] = $group->getGUID();
        // Is this user a member of the group
        if ($group->isMember($user)) {

            // If user used to be instructor but is now student remove 'instructor' relationship
            if ($tool_provider->user->isLearner() && check_entity_relationship($user_guid, 'instructor', $group_guid)) {
                remove_entity_relationship($user_guid, 'instructor', $group->getGUID());
                system_messages(elgg_echo('LTI:change:downgrade'));
                forward($group->getURL());
            }

            // If user is staff add instructor relationship unless they are group owner
            if ($staff && (!check_entity_relationship($user_guid, 'instructor', $group_guid)) && ($user_guid != $group_guid)) {
                add_entity_relationship($user_guid, 'instructor', $group_guid);
                system_messages(elgg_echo('LTI:change:upgrade'));
                forward($group->getURL());
            }

            system_messages('Forwarded to Group');
            forward($group->getURL());
        }

        // If not member join
        $group->join($user);

        // If instructor but not owner give instructor relationship to group. This makes
        // other consumer instructors able to edit the group in Elgg
        if ($staff && (!check_entity_relationship($user_guid, 'instructor', $group_guid))) {
             add_entity_relationship($user_guid, 'instructor', $group_guid);
             system_messages(elgg_echo('LTI:change:upgrade'));
             forward($group->getURL());
        }

        system_messages('Forwarded to Group');
        forward($group->getURL());
    }
}
?>