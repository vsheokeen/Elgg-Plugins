<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * @author Simon Booth
 * @copyright JISC ceLTIc
 * @link http://www.celtic-project.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Contact: s.p.booth@stir.ac.uk
 *
 * Version history:
 *   1.0 First public release -
 ------------------------------------------------------------------*/
$CONFIG->ltiname = 'blti';
$CONFIG->ltipath = elgg_get_config('pluginspath') . elgg_get_config('ltiname') . '/';
$CONFIG->wwwltipath = elgg_get_config('wwwroot') . 'mod/' . elgg_get_config('ltiname') . '/';

/*-------------------------------------------------------------------
 * Elgg plugin initialisation function called by:
 *
 * register_elgg_event_handler('init', 'system', 'lti_init');
 *
 ------------------------------------------------------------------*/
function lti_init() {

	elgg_register_library('BasicLTI_Tool_Provider', elgg_get_plugins_path() . 'blti/lib/BasicLTI_Tool_Provider.php');
	elgg_register_library('Utility', elgg_get_plugins_path() . 'blti/lib/Utility.php');
	elgg_register_library('LTIGroup', elgg_get_plugins_path() . 'blti/lib/LTIGroup.php');
	elgg_register_library('LTIUser', elgg_get_plugins_path() . 'blti/lib/LTIUser.php');
	
	elgg_load_library('BasicLTI_Tool_Provider');
	elgg_load_library('Utility');
	elgg_load_library('LTIGroup');
	elgg_load_library('LTIUser');
	
    // Check DB
    CheckDB();

    // Set log file
    $logfile = elgg_get_config('ltipath') . 'logs/LTI.log';
    elgg_set_plugin_setting('logfile', $logfile, 'blti');

	$action_path = elgg_get_plugins_path() . 'blti/actions';
	
    // Mechanisms to register and unregister LTI actions
    elgg_register_action('blti/return', $action_path . '/return.php');
    elgg_register_action('blti/createconsumer', $action_path . '/createconsumer.php');
    elgg_register_action('blti/saveconsumer', $action_path . '/saveconsumer.php');
    elgg_register_action('blti/enable', $action_path . '/enable.php');
    elgg_register_action('blti/createshare', $action_path . '/createshare.php');
    elgg_register_action('blti/sync', $action_path . '/sync.php');
    elgg_register_action('blti/dosync', $action_path . '/dosync.php');
    elgg_register_action('blti/approve', $action_path . '/approve.php');

    // Page handler
    elgg_register_page_handler(elgg_get_config('ltiname'), 'LTI_page_handler');

    elgg_register_plugin_hook_handler('plugin:setting', 'all', 'LTI_ValidateGroupProvision');

    // Allow instructors to have owner access
    elgg_register_plugin_hook_handler('permissions_check', 'group', 'LTIgroup_operators_permissions_hook');
    // Allow instructors to update user details but within context of group
    elgg_register_plugin_hook_handler('permissions_check', 'user', 'LTIgroup_operators_permissions_hook');
    elgg_register_css('blti_css', '/mod/blti/css/style.css');

	if (!empty($_SESSION['return_url'])) {

    $url = elgg_add_action_tokens_to_url(elgg_get_config('wwwroot') . 'action/' . elgg_get_config('ltiname') . '/return');

	elgg_register_menu_item('topbar', array(
            'href' => $url,
            'name' => 'moodle_return',
            'priority' => 2,
            'section' => 'alt',
            'text' => $_SESSION['return_name'],
        ));
        
	elgg_load_css('blti_css');
	
	}
	
	elgg_register_admin_menu_item('administer', 'blti', 'administer_utilities');
	
    // Extend the elgg topbar. See views/default/lti/topbar.php for the code run by this function
   // elgg_extend_view('topbar','lti/topbar');

}
/*------------------------------------------------------------------
 * Page setup called before pages are drawn by Elgg. This adds the
 * LTI options to the administrator menu and Sync to groups. Called
 * by
 *
 * register_elgg_event_handler('pagesetup','system','LTI_pagesetup');
 ------------------------------------------------------------------*/
function LTI_pagesetup() {

    $page_owner = elgg_get_page_owner_entity();

    // Add administrator's menu items
    if (elgg_get_context() == 'admin' && elgg_is_admin_logged_in()) {

		/* elgg_register_menu_item('page', array(
					'name' => elgg_echo('LTI:registered'),
					'text' => elgg_echo('LTI:registered'),
					'href' => "blti/displayconsumers",
					'title' => elgg_echo('LTI:registered'),
		)); */

    }

    // Group submenu: add the option to Sync Users
    $user = elgg_get_logged_in_user_entity();
    if ($page_owner instanceof ElggGroup && elgg_get_context() == 'groups') {
        // LTI plugin must be enabled, Group must be LTI and current user must be instructor from module
        // Also need to add check that extensions are enabled for this consumer. Finally check that instructor
        // can create/update

        // Get status of instructors for create/update users
        $values = GetPluginSettings();
        $allowinstructor = $values['allowinstructor'];

        // Get context to allow check that membership servce is available before putting up Sync option.
        // Use the user context to ensure we check against consumer they came from
        $consumer_instance = new LTI_Tool_Consumer_Instance($user->consumer_key, elgg_get_config('dbprefix'));
        $context = new LTI_Context($consumer_instance, $user->context_id);

        if(elgg_is_active_plugin(elgg_get_config('ltiname')) &&
           !empty($page_owner->consumer_key) &&
           ($context->hasMembershipsService()) &&
           (check_entity_relationship($user->getGUID(), 'instructor', $page_owner->getGUID())) && $allowinstructor) {

            //$page_owner->consumer_key;
            add_submenu_item(sprintf(elgg_echo('LTI:sync')), elgg_get_config('wwwroot') . elgg_get_config('ltiname') . '/synctext/' . $page_owner->getGUID());
        }

        // Only display sharing options to instructors from primary context
        if(elgg_is_active_plugin(elgg_get_config('ltiname')) &&
           !empty($page_owner->consumer_key) &&
           ($page_owner->consumer_key == $user->consumer_key) &&
           (check_entity_relationship($user->getGUID(), 'instructor', $page_owner->getGUID()))) {
            add_submenu_item(sprintf(elgg_echo('LTI:share:key')), elgg_get_config('wwwroot') . elgg_get_config('ltiname') . '/sharekey/' . $page_owner->consumer_key . '/' . $page_owner->getGUID() . '/');
            add_submenu_item(sprintf(elgg_echo('LTI:share:manage')), elgg_get_config('wwwroot') . elgg_get_config('ltiname') . '/sharemanage/' . $page_owner->consumer_key . '/' . $page_owner->getGUID() . '/');
        }
    }
}

/*------------------------------------------------------------------
 * Check that the groups plugin is enabled when group provisioning
 * is requested. If not do  not allow group provisioning to be
 * enabled.
 ------------------------------------------------------------------*/
function LTI_ValidateGroupProvision($hook, $entity_type, $returnvalue, $params) {

    $plugin = $params['plugin'];
    $name = $params['name'];
    $value = $params['value'];

    if ($plugin == 'lti' && $name == 'groupprovision' && $value == 'yes') {
        if (!elgg_is_active_plugin('groups')) {
            register_error(elgg_echo('LTI:plugin:notenabled'));
            $returnvalue = false;
        }
    }

    return $returnvalue;
}


/*------------------------------------------------------------------
 * Set up various Elgg page handlers. We can use this to expose URLs
 * outside of Elgg that 'disappear' when the plugin is disabled. The
 * URLs are of the form:
 *
 * http://your_elgg_site/pg/plugin_name
 *
 * These main purpose of the page handler is to 'knit together output
 * from different views to form the page that the user sees'. Hence
 * the code that display the Full or Basic LTI consumers is handled
 * here.
 ------------------------------------------------------------------*/
function LTI_page_handler($page) {

    switch ($page[0]) {
    case '':
        require_once(elgg_get_config('ltipath') . '/pages/register.php');
        break;
    case 'displayconsumers':
        require_once(elgg_get_config('ltipath') . '/pages/' . $page[0] . '.php');
        break;
    // These pages exploit the parameter passing mechanism avaiable via
    // the page handler
    case 'editconsumer':
        set_input('LTIconsumerguid', $page[1]);
        require_once(elgg_get_config('ltipath') . '/editconsumer.php');
        break;
    case 'remove':
        set_input('LTIconsumerguid', $page[1]);
        require_once(elgg_get_config('ltipath') . '/remove.php');
        break;
    case 'delete':
        set_input('LTIconsumerguid', $page[1]);
        require_once(elgg_get_config('ltipath') . '/delete.php');
        break;
    case 'synctext':
        set_input('group_guid', $page[1]);
        require_once(elgg_get_config('ltipath') . '/synctext.php');
        break;
    case 'sync':
        set_input('group_guid', $page[1]);
        set_input('filter', $page[2]);
        require_once(elgg_get_config('ltipath') . 'actions/sync.php');
        break;
    case 'sharekey':
        set_input('LTIconsumerguid', $page[1]);
        set_input('group_guid', $page[2]);
        require_once(elgg_get_config('ltipath') . '/sharekey.php');
        break;
    case 'sharemanage':
        set_input('LTIconsumerguid', $page[1]);
        set_input('group_guid', $page[2]);
        require_once(elgg_get_config('ltipath') . '/sharemanage.php');
        break;
    }

    return true;

}

/*-------------------------------------------------------------------
 * Based on group operators plugin. Allows users with relationship
 *'instructor' to edit a group just like the owner
 ------------------------------------------------------------------*/
function LTIgroup_operators_permissions_hook($hook, $entity_type, $returnvalue, $params) {

    return LTIgroup_operators_container_permissions_hook($hook,
                                                         $entity_type,
                                                         $returnvalue,
                                                         array('container'=>$params['entity'],
                                                               'user'=>$params['user']
                                                              )
                                                        );
}

function LTIgroup_operators_container_permissions_hook($hook, $entity_type, $returnvalue, $params) {

    if ($params['user'] && $params['container']->type == 'user') {
        $page_owner = elgg_get_page_owner_entity();
        $user_guid = $params['user']->getGUID();

        if (!empty($page_owner)) {
            // Any instructor can update user
            if (check_entity_relationship($user_guid, 'instructor', $page_owner->getGUID())) return true;
            // Group owner can update user
            if ($user_guid == $page_owner->getOwnerGUID()) return true;
        }
    }

    if ($params['user'] && $params['container']->type == 'group') {

        $container_guid = $params['container']->getGUID();
        $user_guid = $params['user']->getGUID();

        if (check_entity_relationship($user_guid, 'instructor', $container_guid)) return true;
    }

    return $returnvalue;
}

/*-------------------------------------------------------------------
 * Called when a group is deleted, if the group has been provisioned
 * by LTI we need to clean up some of the database tables.
 ------------------------------------------------------------------*/
function LTI_Group_Delete($event, $object_type, $group) {

    // Check we have LTI group (i.e. it has a context and consumer_key) and the delete
    // event is occurring
    if (!empty($group->context_id) && !empty($group->consumer_key) && $event == 'delete') {
        $consumer_instance = new LTI_Tool_Consumer_Instance($group->consumer_key, elgg_get_config('dbprefix'));
        $context = new LTI_Context($consumer_instance, $group->context_id);

        if (elgg_is_active_plugin('notifications')) {

            if ($context->hasMembershipsService()) {
                // Inform all instructors that group is being deleted using
                // membership list from consumer
                InformInstructorsViaLTIMembership($group, $context, $consumer_instance->guid);
            } else {
                // No Membership service, so use group members
                InformInstructorsViaGroupMembership($group);
            }
        }

        $context->delete();
    }

    return true;

}

elgg_register_event_handler('init', 'system', 'lti_init');
elgg_register_event_handler('pagesetup','system','LTI_pagesetup');
elgg_register_event_handler('delete', 'group', 'LTI_Group_Delete');

?>
