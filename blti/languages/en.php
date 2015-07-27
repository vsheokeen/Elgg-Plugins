<?php

/*-------------------------------------------------------------------
* Elgg LTI
*
* Language file
---------------------------------------------------------------------*/

global $CONFIG;

$en = array(

    'item:object:LTI' => 'LTI consumers',

    'FullLTI' => 'Full LTI',

    'LTI:Last:logion' => 'Last login: %s',

    'LTI:delete:fail' => 'Failed to delete consumer --- %s',
    'LTI:delete:success' => 'Consumer %s deleted',
    'LTI:disable' => 'Disable',
    'LTI:disabled' => '%s disabled',

    'LTI:edit:consumer:create' => 'Consumer %s has been successfully created',
    'LTI:edit:consumer:error' => 'You must fill out all the fields',
    'LTI:edit:consumer:success' => 'Consumer %s has been successfully updated',
    'LTI:edit:consumer:title' => 'Edit Consumer',
    'LTI:edit:submit' => 'Save Changes',
    'LTI:enable' => 'Enable',
    'LTI:enabled' => '%s enabled',

    'LTI:provision:group' => 'Provisioned by LTIv1.1',
    'LTI:groupprovisioning' =>'Group provisioning',
    'LTI:groupprovisioningoff' =>'Group provisioning: The groups plugin is disabled or not present',

    'LTI:userdetails' => 'Allow instructors to create/update users',

    'LTI:test' => 'Run in test mode --- this means that group syncs simply list and do not add, delete, change',

    'LTI:info:newgroup' => 'Group successfully created.',
    'LTI:info:nogroup' => 'No group information, forwarded to dashboard',
    'LTI:info:noid' => 'No user information!',
    'LTI:info:noprovision' => 'User provisioning off, please create account',

    'LTI:change:upgrade' => 'Forwarded to Group and made instructor',
    'LTI:change:downgrade' => 'Forwarded to Group and made learner',

    'LTI:plugin:notenabled' => 'The Groups plugin must be enabled for group provisioning to work',
    'LTI:provisionoff' => 'No',
    'LTI:provisionon' => 'Yes',

    'LTI:register:consumer' => 'LTI Consumers',
    'LTI:register:consumer:title' => 'Add LTI v1.1 consumer',
    'LTI:register:consumer:label' => 'Consumer Details',
    'LTI:register:consumer:none' => 'No LTI consumers defined',
    'LTI:register:consumerinstance:label' => 'Consumer Instance Details',
    'LTI:register:created:label' => 'Created',
    'LTI:register:enable:label' => 'Enabled',
    'LTI:register:key:label' => 'Customer Key',
    'LTI:register:name:GUID' => 'GUID',
    'LTI:register:conname:label' => 'Customer Name',
    'LTI:register:name:label' => 'Name',
    'LTI:register:secret:label' => 'Secret',
    'LTI:register:services:desc' => 'List of services provided by %s',
    'LTI:register:services:label' => 'Services',
    'LTI:register:show' => 'Add LTI v1.1 consumer',
    'LTI:register:state:label' => 'State',
    'LTI:register:submit' => 'Add',
    'LTI:register:system:label' => 'System',
    'LTI:register:updated:label' => 'Updated',
    'LTI:register:URL:label' => 'Profile URL',
    'LTI:registered' => 'LTI Consumers',
    'LTI:remove:confirm' => 'Are you sure you wish to remove this consumer',
    'LTI:remove:fail' => 'Consumer %s has NOT been removed',
    'LTI:remove:success' => 'Consumer %s has been removed',
    'LTI:restore:confirm' => 'Are you sure you wish to restore this consumer',
    'LTI:restore:fail' => 'Consumer %s has NOT been restored',
    'LTI:restore:success' => 'Consumer %s has been restored',

    'LTI:userprovisioning' =>'User provisioning',

    // Group deletion notifications
    'LTI:group:delete:subject' => 'Group Deletion: %s',
    'LTI:group:delete:message' => 'Group %s has been deleted, please remember to remove the link in the consumer (%s)',

    // LTI Extensions
    'LTI:sync' => '<i>Group Sync</i>',
    'LTI:members:label' => 'Membership Synchronisation',
    'LTI:members:label:done'  => 'Membership Synchronisation: Completed',
    'LTI:members:testmode' => 'Test mode is on. Changes cannot be applied',
    'LTI:members:explain' => '<p>This page allows you to update this group with any changes to the enrolments ' .
                             'in the course which is the source for this group. These updates may include:' .
                             '<ul><li>new members</li>' .
                             '    <li>changes to the names and/or email addresses of existing members</li>' .
                             '    <li>changes to the type (instructor or student) of an exsiting member</li>' .
                             '    <li>deletion of members which no longer exist in the course course</li>' .
                             '</ul>Click on the <i>Continue</i> to obtain a list of the changes to be processed. ' .
                             'The updates will not be made until you confirm them</p>',
    'LTI:members:explain:lastsync' => '<p>The last sychronisation was performed on %s</p>',
    'LTI:members:explain:lastsync:share' => '<p>The last sychronisations were performed for contexts:</p>',
    'LTI:members:label:off' => 'Members --- User provisioning off',
    'LTI:members:label:added' => 'Members to be added',
    'LTI:members:label:pr_off' => 'Provisioning Off',
    'LTI:members:label:pr_on' => 'Provisioning On',
    'LTI:members:label:deleted' => 'Members to be deleted',
    'LTI:members:label:changed' => 'Members to be changed',
    'LTI:members:label:role_del' => 'Instructors to be changed',
    'LTI:members:label:role_add' => 'Instructors to be added',
    'LTI:members:active' => 'Group Members',
    'LTI:members:sync:noadded' => 'No added members',
    'LTI:members:sync:nousers' => 'No added users',
    'LTI:members:sync:nousers:pr_off' => 'No added users --- user provisioning off',
    'LTI:members:error:noservice' => 'Membership synchronisation failure: %s',
    'LTI:member:wouldbe' => '%d new user(s) not added as user provisioning is off',
    'LTI:member:added' => '%d new user(s) to be added',

    'LTI:members:deleted:no' => 'Deleted members: synchronisation without deletion',
    'LTI:members:deleted:none' => 'No deleted members',

    'LTI:memners:changed:none' => 'No changed members',

    // ceLTIc Extensions
    'LTI:share:key' => '<i>Create Share Key</i>',
    'LTI:share:manage' => '<i>Manage Sharing</i>',
    'LTI:share:explain' => 'You may share this module with users from other sources. These might be:<br /><br />' .
                           '<ul><li>other links from within the same course/module</li>' .
                           '    <li>links from other course/modules in the same VLE/LMS</li>' .
                           '    <li>links from a different VLE/LMS within your institution or outside' .
                           '</ul>',
    'LTI:share:contexts' => 'Shared source contexts',
    'LTI:share:workflow' => 'To invite another source to share this course/module:' .
                            '<ol><li>use the button below to generate a new share key (you may choose to ' .
                            '        pre-approve the share or leave it to be approved once the key has been ' .
                            '        initialised, see below</li>' .
                            '    <li>send the share key to an instructor for the other source</li>' .
                            '</ol>',
    'LTI:share:life' => 'Life',
    'LTI:share:preapprove' => 'Auto-approve',
    'LTI:share:emailaddress' => 'Enter the email address for the sharing recipient:',
    'LTI:share:email' => 'Email Address to send sharing key to ',
    'LTI:share:subject' => 'Sharing Key',
    'LTI:share:emailmsg:pre' => '<div class="search_listing"><p>Place this key (%s) in the custom fields of the LTI connection as<br/><br/>share_key=%s</p>' .
                                '<p>The share key is valid for %d hours and is pre-approved.</p></div>',
    'LTI:share:emailmsg' => '<div class="search_listing"><p>Place this key (%s) in the custom fields of the LTI connection as<br/><br/>share_key=%s</p>' .
                            '<p>The share key is valid for %d hours and needs approving.</p></div>' ,
    'LTI:share:email' => '<p>The text below has been emailed to %s:</p>',
    'LTI:share:noemail' => '<p>The email was not sent to %s. Please cut and paste the message below ' .
                           'and send manually</p>',
    'LTI:share:submit' => 'Generate a new share key',
    'LTI:share:key:title' => 'Share this context using the key below',
    'LTI:share:send' => 'Send',
    'LTI:share:shared' => 'This context is shared',
    'LTI:share:notshared' => 'This context is not shared',
    'LTI:share:noshared' => 'There are no shares for this context',

    // LTI errors
    'LTI:error:login' => 'Failed to login to %s',
    'LTI:error:sharing' => 'Sharing has not been enabled for this context',
    'admin:administer_utilities:blti' => 'LTI Consumers',
    'admin:administer_utilities:editconsumer' => 'Edit LTI Consumer',

    );

add_translation('en', $en);

?>