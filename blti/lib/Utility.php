<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Library file providing some additional functions
 ------------------------------------------------------------------*/

/*-------------------------------------------------------------------
 * Creates a Basic LTI consumer & customer_instance (in the database)
 *
 * Parameters
 *  $tool_guid - GUID for tool (usually the consumer domain for Basic
 *               LTI - customer key)
 *  $name - name of the consuming system
 *  $enable - enable the customer (yes/no)
 *  $secret - consumer shared secret
 -------------------------------------------------------------------*/
function BasicLTI_create_consumer($tool_guid, $name, $enable, $secret) {

    // Create consumer
    $consumer = new LTI_Tool_Consumer(NULL, elgg_get_config('dbprefix'));
    $consumer->guid = $tool_guid;
    $consumer->name = $name;
    $consumer->enabled = ($enable == 'yes') ? True : False;
    $consumer->save();

    // Create consumer instance
    $consumerInstance = new LTI_Tool_Consumer_Instance(NULL, elgg_get_config('dbprefix'));
    $consumerInstance->guid = $tool_guid;
    $consumerInstance->consumer_guid = $tool_guid;
    $consumerInstance->secret = $secret;
    $consumerInstance->state = 'BasicLTI';
    $consumerInstance->save();

    return;
}

/*-------------------------------------------------------------------
 * Update Basic LTI consumer & instance
 *
 * Paramters
 *  $tool_guid - GUID for tool (usually the consumer domain for Basic
 *               LTI - customer key)
 *  $name - name for the consumer
 *  $consumer_name - name of the consuming system
 *  $url - profile url
 *  $enable - enable the customer (yes/no)
 *  $instance_guid - unique ID of consumer
 *  $secret - consumer shared secret
 *  $state - state of this consumer (available, registered, etc)
 ------------------------------------------------------------------*/
function LTI_update_consumer($tool_guid, $name, $consumer_name, $url, $enable, $instance_guid, $secret, $state) {

    // Deleting the instance allows us to update the key (if required)
    $consumerInstance = new LTI_Tool_Consumer_Instance(elgg_get_config('dbprefix'), $tool_guid);

    // Update consumer
    $consumer = new LTI_Tool_Consumer($tool_guid, elgg_get_config('dbprefix'));
    $consumer->name = $name;
    $consumer->consumer_name = $consumer_name;
    $consumer->profile_url = $url;
    $consumer->enabled = ($enable == 'yes') ? true : false;
    $consumer->updated = time();
    $consumer->save();

    // Create new consumer instance
    $consumerInstance = new LTI_Tool_Consumer_Instance($instance_guid, elgg_get_config('dbprefix'));
    $consumerInstance->secret = $secret;
    $consumerInstance->state = $state;
    $consumerInstance->save();

    return;
}

/*-------------------------------------------------------------------
 * Various setting are selected by the Elgg administrator. This
 * function pops them into an array
 *
 * return value - array of settings
 -------------------------------------------------------------------*/
function GetPluginSettings () {

    // Get group provisioning setting
    $gp = elgg_get_plugin_setting('groupprovision', elgg_get_config('ltiname'));
    $groupprovision = true;
    if ($gp == 'no') $groupprovision = false;

    // Get user provisioning setting
    $up = elgg_get_plugin_setting('userprovision', elgg_get_config('ltiname'));
    $userprovision = true;
    if ($up == 'no') $userprovision = false;

    // Get whether instructors can update user details
    $in = elgg_get_plugin_setting('allowinstructor', elgg_get_config('ltiname'));
    $allowinstructor = true;
    if ($in == 'no') $allowinstructor = false;

    // Get whether we are running in testmode for group syncs
    $test = elgg_get_plugin_setting('testmode', elgg_get_config('ltiname'));
    $testmode = true;

    if ($test == 'no') $testmode = false;

    $return_values = array('groupprovision'  => $groupprovision,
                           'userprovision'   => $userprovision,
                           'allowinstructor' => $allowinstructor,
                           'testmode'        => $testmode
                          );

    return $return_values;
}

/*-------------------------------------------------------------------
 * Generate a random string of $length as the user password.
 *
 * Parameters
 *  $length - length of string
 *
 * Return value - string
 ------------------------------------------------------------------*/
function random_string($length) {

    $rand_str = '';

    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
    $chars_length = strlen($chars) - 1;

    for ($i = 0; $i < $length; $i++) {

        $rand_str = $rand_str . $chars{rand(0, $chars_length)};

    }

    return $rand_str;

}

/*-------------------------------------------------------------------
 * Create a new log file --- used for debugging purposes, not
 * designed to be used on a busy site where one file could still be
 * open when the next request starts!
 ------------------------------------------------------------------*/
function InitialiseLog() {

    $logfile = elgg_get_plugin_setting('logfile', 'lti');
    $fh = fopen($logfile, 'w');
    fwrite($fh, 'LTI Elgg Log' . "\n");
    fclose($fh);
}

/*-------------------------------------------------------------------
 * Write some data to log file
 *
 * Parameter
 *  $data - string to write into file
 ------------------------------------------------------------------*/
function WriteToLog($data) {

    $logfile = elgg_get_plugin_setting('logfile', 'lti');

    $data = $data . "\n";

    $fh = fopen($logfile, 'a+');
    fwrite($fh, $data);
    fclose($fh);

}

/*-------------------------------------------------------------------
 * Get image based on consumer name
 *
 * Parameters
 *  $consumer_instance_guid - unique id for custimer
 *  $type - the admin interface handles png but groups require jpg
 *          so allow calling function to define
 ------------------------------------------------------------------*/
function GetImage($consumer_instance_guid, $type) {

    $consumer_instance = new LTI_Tool_Consumer_Instance($consumer_instance_guid, elgg_get_config('dbprefix'));
    $consumer_tool = new LTI_Tool_Consumer($consumer_instance->consumer_guid, elgg_get_config('dbprefix'));

    // See if we have any named images to display
    $file_handle = fopen(elgg_get_config('ltipath') . "lib/images.map", "r");
    while (!feof($file_handle)) {

        $fields = preg_split('/=/', fgets($file_handle));
        $length = strlen($fields[0]);
        $consumer_name = strtolower(substr($consumer_tool->consumer_name, 0, $length)) == strtolower($fields[0]);
        $name          = strtolower(substr($consumer_tool->name, 0, $length)) == strtolower($fields[0]);
        if ($consumer_name || $name) return elgg_get_config('wwwltipath') . 'images/' . rtrim($fields[1]) . $type;

    }

    return  elgg_get_config('wwwltipath') . 'images/ims' . $type;
}

/*-------------------------------------------------------------------
 * When a group is deleted inform all the instructors using LTI
 * membership unofficial extension
 *
 * Parameters
 *  $group - group being deleted
 *  $context - context to obtain members from
 *  $consumer_key - identify the consumer
 ------------------------------------------------------------------*/
function InformInstructorsViaLTIMembership ($group, $context, $consumer_key) {

    // Get the consumer name
    $consumer = new LTI_Tool_Consumer($consumer_key, elgg_get_config('dbprefix'));
    $name = $consumer->name;

    // Get current members
    $members = $context->doMembershipsService();
    foreach ($members as $member) {

        // Find stadd
        if ($member->isStaff()) {
            $user = CheckLTIUser($member['user_id'], $consumer_key);
            // Ensure message goes via email (given that user may no
            // longer have access to Elgg to use the standard notifications systems)
            add_entity_relationship($user->guid, 'notifyemail', $group->guid);
            notify_user($user->guid, $group->guid, sprintf(elgg_echo('LTI:group:delete:subject'), $group->name), sprintf(elgg_echo('LTI:group:delete:message'), $group->name, $name));
        }
    }
}

/*-------------------------------------------------------------------
 * When a group is deleted inform all the instructors from the group
 *
 * Parameters
 *  $group - group being deleted
 ------------------------------------------------------------------*/
function InformInstructorsViaGroupMembership ($group) {

    // Get the consumer name
    $consumer = new LTI_Tool_Consumer($group->consumer_key, elgg_get_config('dbprefix'));
    $name = $consumer->name;

    // Get current members
    $members = $group->getMembers(0, 0, 0);
    foreach ($members as $member) {

        if (check_entity_relationship($member->guid, 'instructor', $group->guid) || ($group->getOwner() == $member->guid)) {
            add_entity_relationship($member->guid, 'notifyemail', $group->guid);
            notify_user($member->guid, $group->guid, sprintf(elgg_echo('LTI:group:delete:subject'), $group->name), sprintf(elgg_echo('LTI:group:delete:message'), $group->name, $name));
        }
    }
}

/*-------------------------------------------------------------------
 * Check that the tables necessary for class are present, if not
 * create.
 ------------------------------------------------------------------*/
function CheckDB() {

    $PREFIX = elgg_get_config('dbprefix');

    $dbnotefile = elgg_get_config('ltipath') . 'logs/DB.log';
    //if (file_exists($dbnotefile)) return true;

    $res = update_data('SET FOREIGN_KEY_CHECKS = 0');

    $sql  = 'CREATE TABLE IF NOT EXISTS ' . $PREFIX . 'lti_consumer (';
    $sql .= '  consumer_guid VARCHAR(255) NOT NULL,';
    $sql .= '  name VARCHAR(45) NOT NULL,';
    $sql .= '  profile_url VARCHAR(255),';
    $sql .= '  consumer_name VARCHAR(255),';
    $sql .= '  consumer_version VARCHAR(255),';
    $sql .= '  vendor_code VARCHAR(255),';
    $sql .= '  css_path VARCHAR(255),';
    $sql .= '  services TEXT,';
    $sql .= '  capabilities TEXT,';
    $sql .= '  enabled TINYINT(1) NOT NULL,';
    $sql .= '  created DATETIME NOT NULL,';
    $sql .= '  updated DATETIME NOT NULL,';
    $sql .= '  PRIMARY KEY (consumer_guid)';
    $sql .= ') ENGINE=InnoDB DEFAULT CHARSET=latin1';
    $res = update_data($sql);

    $sql  = 'CREATE TABLE IF NOT EXISTS ' . $PREFIX . 'lti_consumer_instance (';
    $sql .= '  consumer_instance_guid VARCHAR(255) NOT NULL,';
    $sql .= '  consumer_guid VARCHAR(255) NOT NULL,';
    $sql .= '  state VARCHAR(12) NOT NULL,';
    $sql .= '  secret VARCHAR(32) NOT NULL,';
    $sql .= '  created DATETIME NOT NULL,';
    $sql .= '  updated DATETIME NOT NULL,';
    $sql .= '  PRIMARY KEY (consumer_instance_guid),';
    $sql .= '  CONSTRAINT ' . $PREFIX . 'lti_consumer_consumer_instance_FK1 FOREIGN KEY (';
    $sql .= '    consumer_guid)';
    $sql .= '  REFERENCES ' . $PREFIX . 'lti_consumer (';
    $sql .= '    consumer_guid)';
    $sql .= '  ) ENGINE=InnoDB DEFAULT CHARSET=latin1';
    $res = update_data($sql);


    $sql  = 'CREATE TABLE IF NOT EXISTS ' . $PREFIX . 'lti_context (';
    $sql .= '  consumer_instance_guid VARCHAR(255) NOT NULL,';
    $sql .= '  context_id VARCHAR(255) NOT NULL,';
    $sql .= '  title VARCHAR(255) NOT NULL,';
    $sql .= '  lti_context_id VARCHAR(255),';
    $sql .= '  lti_resource_id VARCHAR(255),';
    $sql .= '  settings TEXT NULL,';
    $sql .= '  primary_consumer_instance_guid VARCHAR(255),';
    $sql .= '  primary_context_id VARCHAR(255),';
    $sql .= '  share_approved TINYINT(1),';
    $sql .= '  created DATETIME NOT NULL,';
    $sql .= '  updated DATETIME NOT NULL,';
    $sql .= '  PRIMARY KEY (consumer_instance_guid, context_id),';
    $sql .= '  CONSTRAINT ' . $PREFIX . 'lti_context_consumer_instance_FK1 FOREIGN KEY (';
    $sql .= '    consumer_instance_guid)';
    $sql .= '  REFERENCES ' . $PREFIX . 'lti_consumer_instance (';
    $sql .= '  consumer_instance_guid)';
    $sql .= '  ) ENGINE=InnoDB DEFAULT CHARSET=latin1';
    $res = update_data($sql);

    $sql  = 'CREATE TABLE IF NOT EXISTS lti_user (';
    $sql .= '  consumer_instance_guid VARCHAR(255) NOT NULL,';
    $sql .= '  context_id VARCHAR(255) NOT NULL,';
    $sql .= '  user_id VARCHAR(255),';
    $sql .= '  lti_result_sourcedid VARCHAR(255),';
    $sql .= '  created DATETIME NOT NULL,';
    $sql .= '  updated DATETIME NOT NULL,';
    $sql .= '  PRIMARY KEY (consumer_instance_guid, context_id, user_id),';
    $sql .= '  CONSTRAINT lti_user_context_FK1 FOREIGN KEY (';
    $sql .= '    consumer_instance_guid, context_id)';
    $sql .= '  REFERENCES lti_context (';
    $sql .= '  consumer_instance_guid, context_id)';
    $sql .= '  ) ENGINE=InnoDB DEFAULT CHARSET=latin1';
    $res = update_data($sql);

    $sql  = 'CREATE TABLE IF NOT EXISTS ' . $PREFIX . 'lti_context_share_key (';
    $sql .= ' share_key varchar(32) NOT NULL,';
    $sql .= ' primary_consumer_instance_guid varchar(255) NOT NULL,';
    $sql .= ' primary_context_id varchar(255) NOT NULL,';
    $sql .= ' auto_approve TINYINT(1) NOT NULL,';
    $sql .= ' expires int(10) NOT NULL,';
    $sql .= ' PRIMARY KEY (share_key)';
    $sql .= ' ) ENGINE=InnoDB DEFAULT CHARSET=latin1';
    $res = update_data($sql);

    $sql  = 'CREATE TABLE IF NOT EXISTS ' . $PREFIX . 'lti_nonce (';
    $sql .= '  consumer_instance_guid VARCHAR(255) NOT NULL,';
    $sql .= '  value VARCHAR(32) NOT NULL,';
    $sql .= '  timestamp INT UNSIGNED NOT NULL,';
    $sql .= '  PRIMARY KEY (consumer_instance_guid, value),';
    $sql .= '  CONSTRAINT ' . $PREFIX . 'lti_nonce_consumer_instance_FK1 FOREIGN KEY (';
    $sql .= '    consumer_instance_guid)';
    $sql .= '  REFERENCES ' . $PREFIX . 'lti_consumer_instance (';
    $sql .= '    consumer_instance_guid)';
    $sql .= ') ENGINE=InnoDB DEFAULT CHARSET=latin1';
    $res = update_data($sql);

    $res = update_data('SET FOREIGN_KEY_CHECKS = 1');

    // Note we have done this
    /*$fh = fopen($dbnotefile, 'w');
    fwrite($fh, 'DB tables/constraints created' . "\n");
    fclose($fh);*/

    return true;
}

/*-------------------------------------------------------------------
 * Obtain the members of an group from a given content (aka consumer)
 *
 * Parameters
 *  $context - context to obtain members from
 *  $groupMembers - array of group members
 ------------------------------------------------------------------*/
function GetFromContext($context, $groupMembers) {

    $thisContextMembers = array();

    foreach ($groupMembers as $members) {
        if ($context->consumer_instance->guid == $members->consumer_key) $thisContextMembers[] = $members;
    }

    return $thisContextMembers;
}

?>