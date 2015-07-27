<?php
/*
 *  BasicLTI_Tool_Provider - PHP class to include in an external tool to handle connections with a Basic LTI-compliant tool consumer
 *  Copyright (C) 2012  Stephen P Vickers
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 *  Contact: stephen@spvsoftwareproducts.com
 *
 *  Version history:
 *    1.0.00  25-Sep-11  Initial release
 *    1.0.01  17-Oct-11  Added support for unofficial extension services
 *                       Fixed bug with setting the consumer property of the BasicLTI_Tool_Provider class
 *    1.0.02  21-Oct-11  Bug fix when passing an array of callback methods to BasicLTI_Tool_Provider class
 *                       Changed definition of profile_url field in lti_consumer table to allow NULLs
 *    1.1.00  15-Feb-12  Changed default values of LTI_Context (ID and title)
 *                       Reversed order of parameters in constructor for LTI_Tool_Consumer_Instance class
 *                       Changed dbTableNamePrefix properties to public in LTI_Tool_Consumer and LTI_Tool_Consumer_Instance classes
 *                       EXT_WRITE option for doSettingService will also update the saved setting value
 *                       Added setValue() method to LTI_Outcome class and made value parameter optional for the constructor
 *                       Added context to LTI_User class
 *                       Added defaultEmail and debugMode options
 *                       Added LTI_User::getId() method
 *                       Added debug mode
 *                       Returns reason for launch failure as lti_errorlog parameter
 *                       Save custom settings with LTI settings for each context
 *                       Added optional default value parameter to LTI_Context::getSetting() method
 *                       Added check on data type of outcome with an attempt to convert the value to a type supported by the tool consumer
 *                       Fixed issue with apostrophes in data
 *                       Changed doMembershipsService() to return an array of LTI_User objects
 *                       New lti_user table used to record result sourcedid values for users
 *                       Added support for LTI 1.1
*/

require_once('OAuth.php');

###
###  Class to represent a Basic LTI Tool Provider
###

class BasicLTI_Tool_Provider {

  const CONNECTION_ERROR_MESSAGE = 'Sorry, there was an error connecting you to the application.';

  const CONSUMER_TABLE_NAME = 'lti_consumer';
  const CONSUMER_INSTANCE_TABLE_NAME = 'lti_consumer_instance';
  const CONTEXT_TABLE_NAME = 'lti_context';
  const USER_TABLE_NAME = 'lti_user';
  const CONTEXT_SHARE_KEY_TABLE_NAME = 'lti_context_share_key';
  const NONCE_TABLE_NAME = 'lti_nonce';
  const LTI_VERSION = 'LTI-1p0';
  const ID_SCOPE_ID_ONLY = 0;
  const ID_SCOPE_GLOBAL = 1;
  const ID_SCOPE_CONTEXT = 2;
  const ID_SCOPE_RESOURCE = 3;
  const ID_SCOPE_SEPARATOR = ':';
  const MAX_SHARE_KEY_AGE = 168;  // in hours (1 week)

  public $isOK = TRUE;

  public $consumer = NULL;
  public $consumer_instance = NULL;
  public $return_url = NULL;
  public $user = NULL;
  public $context = NULL;
  public $autoEnable = FALSE;
  public $dbTableNamePrefix = '';
  public $defaultEmail = '';
  public $id_scope = self::ID_SCOPE_ID_ONLY;
  public $allowSharing = FALSE;
  public $reason = NULL;

  private $redirectURL = NULL;
  private $callbackHandler = NULL;
  private $output = NULL;
  private $error = NULL;
  private $debugMode = FALSE;
  private $lti_settings_names = array('ext_resource_link_content', 'ext_resource_link_content_signature',
                                      'lis_result_sourcedid', 'lis_outcome_service_url',
                                      'ext_ims_lis_basic_outcome_url', 'ext_ims_lis_resultvalue_sourcedids',
                                      'ext_ims_lis_memberships_id', 'ext_ims_lis_memberships_url',
                                      'ext_ims_lti_tool_setting', 'ext_ims_lti_tool_setting_id', 'ext_ims_lti_tool_setting_url');

###
#    Class constructor
###
  function __construct($callbackHandler, $dbTableNamePrefix = '', $autoEnable = FALSE) {

    if (!is_array($callbackHandler)) {
      $this->callbackHandler = $callbackHandler;
    } else if (isset($callbackHandler['connect'])) {
      $this->callbackHandler = $callbackHandler['connect'];
    } else if (count($callbackHandler) > 0) {
      $callbackHandlers = array_values($callbackHandler);
      $this->callbackHandler = $callbackHandlers[0];
    }
    $this->dbTableNamePrefix = $dbTableNamePrefix;
    $this->autoEnable = $autoEnable;

  }

  public function execute() {

#
### Set return URL if available
#
    if (isset($_REQUEST['launch_presentation_return_url'])) {
      $this->return_url = $_REQUEST['launch_presentation_return_url'];
    }
#
### Perform action
#
    if ($this->authenticate()) {
      $this->doCallback();
    }
    $this->result();

  }

###
###  PRIVATE METHODS
###

###
#    Call any callback function for the requested action
###
  private function doCallback() {

    if (isset($this->callbackHandler)) {
      $result = call_user_func($this->callbackHandler, $this);

#
### Callback function may return HTML, a redirect URL, or a boolean value
#
      if (is_string($result)) {
        if ((substr($result, 0, 7) == 'http://') || (substr($result, 0, 8) == 'https://')) {
          $this->redirectURL = $result;
        } else {
          if (is_null($this->output)) {
            $this->output = '';
          }
          $this->output .= $result;
        }
      } else if (is_bool($result)) {
        $this->isOK = $result;
      }
    }

    return $this->isOK;

  }

###
#    Perform the result of an action (boolean result, redirection or display HTML
###
  private function result() {

    if (!$this->isOK) {
#
### If not valid, return an error message to the tool consumer if a return URL is provided
#
      if (!empty($this->return_url)) {
        $this->error = $this->return_url;
        if (strpos($this->error, '?') === FALSE) {
          $this->error .= '?';
        } else {
          $this->error .= '&';
        }
        if ($this->debugMode && !is_null($this->reason)) {
          $this->error .= 'lti_errormsg=' . urlencode("Debug error: $this->reason");
        } else {
          $this->error .= 'lti_errormsg=' . urlencode(self::CONNECTION_ERROR_MESSAGE);
          if (!is_null($this->reason)) {
            $this->error .= '&lti_errorlog=' . urlencode("Debug error: $this->reason");
          }
        }
      } else if ($this->debugMode && !is_null($this->reason)) {
        $this->error = $this->reason;
      } else {
        $this->error = self::CONNECTION_ERROR_MESSAGE;
      }
      if (is_null($this->error)) {
        echo '<result>ERROR<result>';
      } else if ((substr($this->error, 0, 7) == 'http://') || (substr($this->error, 0, 8) == 'https://')) {
        header("Location: {$this->error}");
      } else {
        echo "Error: {$this->error}";
      }
    } else if (!is_null($this->redirectURL)) {
      header("Location: {$this->redirectURL}");
    } else if (!empty($this->return_url)) {
      header("Location: {$this->return_url}");
    } else if (!is_null($this->output)) {
      echo $this->output;
    } else {
      echo '<result>SUCCESS<result>';
    }

  }

###
#    Check the authenticity of the LTI launch request
###
  private function authenticate() {

#
### Set debug mode
#
    $this->debugMode = isset($_REQUEST['custom_debug']);
#
### Get the consumer instance
#
    $this->isOK = isset($_REQUEST['oauth_consumer_key']);

    if ($this->isOK) {
      $this->consumer_instance = new LTI_Tool_Consumer_Instance($_REQUEST['oauth_consumer_key'], $this->dbTableNamePrefix);
      $this->isOK = $this->consumer_instance->isEnabled();
      if ($this->debugMode && !$this->isOK) {
        $this->reason = 'Tool consumer instance has not been enabled by the tool provider.';
      }
    }

    if ($this->isOK) {

      try {

        $store = new LTI_OAuthDataStore($this);
        $server = new OAuthServer($store);

        $method = new OAuthSignatureMethod_HMAC_SHA1();
        $server->add_signature_method($method);
        $request = OAuthRequest::from_request();
        $res = $server->verify_request($request);

      } catch (Exception $e) {

        $this->isOK = FALSE;
        if (empty($this->reason)) {
          $this->reason = 'OAuth signature check failed - perhaps an incorrect secret.';
        }

      }

    }

    if ($this->isOK) {
      $this->consumer_instance->defaultEmail = $this->defaultEmail;
#
### Set the request context
#
      if (isset($_REQUEST['resource_link_id'])) {
        $id = trim($_REQUEST['resource_link_id']);
      } else {
        $id = trim($_REQUEST['context_id']);
      }
      $this->context = new LTI_Context($this->consumer_instance, $id);
      if (isset($_REQUEST['context_id'])) {
        $this->context->lti_context_id = trim($_REQUEST['context_id']);
      }
      if (isset($_REQUEST['resource_link_id'])) {
        $this->context->lti_resource_id = trim($_REQUEST['resource_link_id']);
      }
      $title = '';
      if (isset($_REQUEST['context_title'])) {
        $title = trim($_REQUEST['context_title']);
      }
      if (isset($_REQUEST['resource_link_title']) && (strlen(trim($_REQUEST['resource_link_title'])) > 0)) {
        if (!empty($title)) {
          $title .= ': ';
        }
        $title .= trim($_REQUEST['resource_link_title']);
      }
      if (empty($title)) {
        $title = "Course {$this->context->id}";
      }
      $this->context->title = $title;
// Save LTI parameters
      foreach ($this->lti_settings_names as $name) {
        if (isset($_REQUEST[$name])) {
          $this->context->setSetting($name, $_REQUEST[$name]);
        } else {
          $this->context->setSetting($name, NULL);
        }
      }
// Delete any existing custom parameters
      foreach ($this->context->getSettings() as $name => $value) {
        if (strpos($name, 'custom_') === 0) {
          $this->context->setSetting($name);
        }
      }
// Save custom parameters
      foreach ($_REQUEST as $name => $value) {
        if (strpos($name, 'custom_') === 0) {
          $this->context->setSetting($name, $value);
        }
      }
      $this->context->save();
    }

    if ($this->isOK) {
#
### Set the user instance
#
      $this->user = new LTI_User($this->context, trim($_REQUEST['user_id']));
#
### Set the user name
#
      $firstname = (isset($_REQUEST['lis_person_name_given'])) ? $_REQUEST['lis_person_name_given'] : '';
      $lastname = (isset($_REQUEST['lis_person_name_family'])) ? $_REQUEST['lis_person_name_family'] : '';
      $fullname = (isset($_REQUEST['lis_person_name_full'])) ? $_REQUEST['lis_person_name_full'] : '';
      $this->user->setNames($firstname, $lastname, $fullname);
#
### Set the user email
#
      $email = (isset($_REQUEST['lis_person_contact_email_primary'])) ? $_REQUEST['lis_person_contact_email_primary'] : '';
      $this->user->setEmail($email, $this->defaultEmail);
#
### Set the user roles
#
      if (isset($_REQUEST['roles'])) {
        $this->user->roles = explode(',', $_REQUEST['roles']);
      }
#
### Save the user instance
#
      if (isset($_REQUEST['lis_result_sourcedid'])) {
        $this->user->lti_result_sourcedid = $_REQUEST['lis_result_sourcedid'];
        $this->user->save();
      }
#
### Update the consumer instance
#
      if ($this->consumer_instance->state != $_REQUEST['lti_version']) {
        $this->consumer_instance->state = $_REQUEST['lti_version'];
        $this->consumer_instance->save();
      }
#
### Initialise the consumer and check for changes
#
      $this->consumer = new LTI_Tool_Consumer($_REQUEST['oauth_consumer_key'], $this->dbTableNamePrefix);
      $doSave = FALSE;
// do not delete any existing consumer name if none is passed
      if (isset($_REQUEST['tool_consumer_info_product_family_code'])) {
        $name = $_REQUEST['tool_consumer_info_product_family_code'];
        if (isset($_REQUEST['tool_consumer_info_version'])) {
          $name .= "-{$_REQUEST['tool_consumer_info_version']}";
        }
        if ($this->consumer->consumer_name != $name) {
          $this->consumer->consumer_name = $name;
          $doSave = TRUE;
        }
      } else if (isset($_REQUEST['ext_lms']) && ($this->consumer->consumer_name != $_REQUEST['ext_lms'])) {
        $this->consumer->consumer_name = $_REQUEST['ext_lms'];
        $doSave = TRUE;
      }
      if (isset($_REQUEST['launch_presentation_css_url'])) {
        if ($this->consumer->css_path != $_REQUEST['launch_presentation_css_url']) {
          $this->consumer->css_path = $_REQUEST['launch_presentation_css_url'];
          $doSave = TRUE;
        }
      } else if (isset($_REQUEST['ext_launch_presentation_css_url']) &&
         ($this->consumer->css_path != $_REQUEST['ext_launch_presentation_css_url'])) {
        $this->consumer->css_path = $_REQUEST['ext_launch_presentation_css_url'];
        $doSave = TRUE;
      } else if (!empty($this->consumer->css_path)) {
        $this->consumer->css_path = NULL;
        $doSave = TRUE;
      }
      if ($doSave) {
        $this->consumer->save();
      }
#
### Check if a share arrangement is in place for this context
#
      $this->isOK = $this->checkForShare();

    }

    return $this->isOK;

  }

  private function checkForShare() {

    $ok = TRUE;

    $guid = $this->context->primary_consumer_instance_guid;
    $id = $this->context->primary_context_id;

    $shareRequest = isset($_REQUEST['custom_share_key']);
    if ($shareRequest) {
      if (!$this->allowSharing) {
        $ok = FALSE;
        $this->reason = 'Your sharing request has been refused because sharing is not being permitted.';
      } else {
// Clear expired share keys
        $this->clearExpiredShareKeys();
// Check if this is a new share key
        $key = mysql_real_escape_string($_REQUEST['custom_share_key']);
        $sql = 'SELECT k.* ' .
               "FROM {$this->dbTableNamePrefix}" . self::CONTEXT_SHARE_KEY_TABLE_NAME . ' AS k ' .
               "WHERE k.share_key = '{$key}'";
        $rs_key = mysql_query($sql);
        if ($rs_key) {
          $row = mysql_fetch_object($rs_key);
          if ($row) {
// Update context with sharing primary context details
            $time = time();
            $now = date("Y-m-d H:i:s", $time);
            $guid = $row->primary_consumer_instance_guid;
            $id = $row->primary_context_id;
            $ok = ($guid != $this->consumer_instance->guid) || ($id != $this->context->id);
            if ($ok) {
              $approve = $row->auto_approve;
              if (is_null($approve)) {
                $approved = 'NULL';
              } else if ($approve) {
                $approved = 1;
              } else {
                $approved = 0;
              }
              $sql = "UPDATE {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME . ' SET ';
              if (is_null($guid)) {  // Should never be null
                $sql .= 'primary_consumer_instance_guid = NULL, primary_context_id = NULL, ';
              } else {
                $sql .= "primary_consumer_instance_guid = '{$guid}', primary_context_id = '{$id}', ";
              }
              $sql .= "share_approved = {$approved}, updated = '{$now}' " .
                      "WHERE consumer_instance_guid = '{$this->consumer_instance->guid}' AND context_id = '{$this->context->id}'";
              $ok = mysql_query($sql);
              if ($ok) {
                $this->user->context->primary_consumer_instance_guid = $guid;
                $this->user->context->primary_context_id = $id;
                $this->user->context->share_approved = $approve;
                $this->user->context->updated = $time;
// Remove share key
                $sql = "DELETE FROM {$this->dbTableNamePrefix}" . self::CONTEXT_SHARE_KEY_TABLE_NAME . " WHERE share_key = '{$key}'";
                mysql_query($sql);
              } else {
                $this->reason = 'An error occurred initialising your share arrangement.';
              }
            } else {
              $this->reason = 'It is not possible to share your context with yourself.';
            }
          }
        }
        if ($ok) {
          $ok = !is_null($guid);
          if (!$ok) {
            $this->reason = 'You have requested to share a context but none is available.';
          } else {
            $ok = (!is_null($this->user->context->share_approved) && $this->user->context->share_approved);
            if (!$ok) {
              $this->reason = 'Your share request is waiting to be approved.';
            }
          }
        }
      }
    } else {
// Check no share is in place
      $ok = is_null($guid);
      if (!$ok) {
        $this->reason = 'You have not requested to share a context but an arrangement is currently in place.';
      }
    }

// Look up primary context
    if ($ok && !is_null($guid)) {
      $consumer_instance = new LTI_Tool_Consumer_Instance($guid, $this->dbTableNamePrefix);
      $ok = !is_null($consumer_instance->created);
      if ($ok) {
        $context = new LTI_Context($consumer_instance, $id);
        $ok = !is_null($context->created);
      }
      if ($ok) {
        $this->context = $context;
      } else {
        $this->reason = 'Unable to load context being shared.';
      }
    }

    return $ok;

  }

###
#    Delete out-of-date share keys
###
  private function clearExpiredShareKeys() {

    $time = time();
    $sql = "DELETE FROM {$this->dbTableNamePrefix}" . self::CONTEXT_SHARE_KEY_TABLE_NAME . " WHERE expires <= $time";

    $ok = mysql_query($sql);

    return $ok;

  }

###
#    Quote a string for use in a database query
###
  public function quoted($value, $addQuotes = TRUE) {

    if (is_null($value)) {
      $value = 'NULL';
    } else {
      $value = str_replace('\'', '\'\'', $value);
      if ($addQuotes) {
        $value = "'{$value}'";
      }
    }

    return $value;

  }

}


###
###  Class to represent a tool consumer
###

class LTI_Tool_Consumer {

  public $guid = NULL;
  public $dbTableNamePrefix = NULL;
  public $name = NULL;
  public $consumer_name = NULL;
  public $css_path = NULL;
  public $enabled = FALSE;
  public $created = NULL;
  public $updated = NULL;

  private $key = NULL;

###
#    Class constructor
###
  public function __construct($guid = NULL, $dbTableNamePrefix = '', $autoEnable = FALSE) {

    $this->dbTableNamePrefix = $dbTableNamePrefix;
    if (!is_null($guid)) {
      $this->load($guid, $autoEnable);
    } else {
      $this->enabled = $autoEnable;
    }

  }

###
#    Save the tool consumer to the database
###
  public function save() {

    if ($this->enabled) {
      $enabled = 1;
    } else {
      $enabled = 0;
    }
    $time = time();
    $now = date("Y-m-d H:i:s", $time);
    if (is_null($this->key)) {
      $sql = sprintf("INSERT INTO {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONSUMER_TABLE_NAME . ' (consumer_guid, name, ' .
             'consumer_name, css_path, enabled, created, updated) ' .
             "VALUES (%s, %s, %s, %s, %d, '{$now}', '{$now}')",
             BasicLTI_Tool_Provider::quoted($this->guid), BasicLTI_Tool_Provider::quoted($this->name),
             BasicLTI_Tool_Provider::quoted($this->consumer_name), BasicLTI_Tool_Provider::quoted($this->css_path), $enabled);
    } else {
      $sql = sprintf("UPDATE {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONSUMER_TABLE_NAME . " SET consumer_guid = %s, " .
               "name = %s, consumer_name = %s, " .
               "css_path = %s, enabled = %d, updated = '{$now}' " .
             "WHERE consumer_guid = %s",
             BasicLTI_Tool_Provider::quoted($this->guid), BasicLTI_Tool_Provider::quoted($this->name),
             BasicLTI_Tool_Provider::quoted($this->consumer_name), BasicLTI_Tool_Provider::quoted($this->css_path),
             $enabled, BasicLTI_Tool_Provider::quoted($this->key));
    }
    $ok = mysql_query($sql);
    if ($ok) {
      if (is_null($this->created)) {
        $this->created = $time;
      }
      $this->updated = $time;
    }

    return $ok;

  }

###
#    Delete the tool consumer from the database
###
  public function delete() {

    $sql = sprintf('SELECT ci.consumer_instance_guid ' .
                   "FROM {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONSUMER_INSTANCE_TABLE_NAME . ' AS ci ' .
                   "INNER JOIN {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONSUMER_TABLE_NAME . ' AS c ON ci.consumer_guid = c.consumer_guid ' .
                   "WHERE c.consumer_guid = %s",
       BasicLTI_Tool_Provider::quoted($this->guid));
    $rs_consumer_instances = mysql_query($sql);
    if ($rs_consumer_instances === FALSE) {
      $ok = FALSE;
    } else if (mysql_num_rows($rs_consumer_instances) <= 0) {
      $sql = sprintf("DELETE FROM {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONSUMER_TABLE_NAME . " WHERE consumer_guid = %s",
         BasicLTI_Tool_Provider::quoted($this->guid));
      $ok = mysql_query($sql);
      $this->guid = NULL;
    } else {
      $ok = TRUE;
    }

    return $ok;

  }

###
###  PRIVATE METHOD
###

###
#    Load the tool consumer from the database
###
  private function load($guid, $autoEnable = FALSE) {

    $this->key = NULL;
    $this->guid = $guid;
    $this->name = NULL;
    $this->consumer_name = NULL;
    $this->css_path = NULL;
    $this->enabled = $autoEnable;
    $this->created = NULL;
    $this->updated = NULL;

    $sql = sprintf('SELECT * ' .
                   "FROM {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONSUMER_TABLE_NAME . ' ' .
                   "WHERE consumer_guid = %s",
       BasicLTI_Tool_Provider::quoted($guid));
    $rs_consumer = mysql_query($sql);
    if ($rs_consumer) {
      $row = mysql_fetch_object($rs_consumer);
      if ($row) {
        $this->key = $row->consumer_guid;
        $this->name = $row->name;
        $this->consumer_name = $row->consumer_name;
        $this->css_path = $row->css_path;
        $this->enabled = ($row->enabled == 1);
        $this->created = strtotime($row->created);
        $this->updated = strtotime($row->updated);
      }
    }
  }

}


###
###  Class to represent a specific tool instance in a tool consumer
###

class LTI_Tool_Consumer_Instance {

  const MAX_NONCE_AGE = 24;  // in hours

  public $guid = NULL;
  public $dbTableNamePrefix = NULL;
  public $consumer_guid = NULL;
  public $state = NULL;
  public $secret = NULL;
  public $id_scope = BasicLTI_Tool_Provider::ID_SCOPE_ID_ONLY;
  public $defaultEmail = '';
  public $created = NULL;
  public $updated = NULL;

  private $key = NULL;
  private $enabled = FALSE;

###
#    Class constructor
###
  public function __construct($guid = NULL, $dbTableNamePrefix = '') {

    $this->dbTableNamePrefix = $dbTableNamePrefix;
    if (!is_null($guid)) {
      $this->load($guid);
    } else {
      $this->secret = $this->getRandomString(32);
    }

  }

###
#    Save the tool consumer to the database
###
  public function save() {

    $time = time();
    $now = date("Y-m-d H:i:s", $time);
    if (is_null($this->key)) {
      $sql = sprintf("INSERT INTO {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONSUMER_INSTANCE_TABLE_NAME . ' (consumer_instance_guid, consumer_guid, ' .
                     'state, secret, created, updated) ' .
                     "VALUES (%s, %s, %s, %s, '{$now}', '{$now}')",
             BasicLTI_Tool_Provider::quoted($this->guid), BasicLTI_Tool_Provider::quoted($this->consumer_guid),
             BasicLTI_Tool_Provider::quoted($this->state), BasicLTI_Tool_Provider::quoted($this->secret));
    } else {
      $sql = sprintf("UPDATE {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONSUMER_INSTANCE_TABLE_NAME . " SET consumer_instance_guid = %s, " .
                     "consumer_guid = %s, state = %s, secret = %s, " .
                     "updated = '{$now}' WHERE consumer_instance_guid = %s",
             BasicLTI_Tool_Provider::quoted($this->guid), BasicLTI_Tool_Provider::quoted($this->consumer_guid),
             BasicLTI_Tool_Provider::quoted($this->state), BasicLTI_Tool_Provider::quoted($this->secret),
             BasicLTI_Tool_Provider::quoted($this->key));
    }
    $ok = mysql_query($sql);
    if ($ok) {
      if (is_null($this->created)) {
        $this->created = $time;
      }
      $this->updated = $time;
    }

    return $ok;

  }

###
#    Delete the tool consumer from the database
###
  public function delete($deleteConsumer = FALSE) {

// Delete any nonce values for this consumer instance
    $sql = sprintf("DELETE FROM {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::NONCE_TABLE_NAME . " WHERE consumer_instance_guid = %s",
       BasicLTI_Tool_Provider::quoted($this->guid));
    mysql_query($sql);

// Update any contexts for which this is a primary context
    $sql = sprintf("UPDATE {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME .
                   ' SET primary_consumer_instance_guid = NULL AND primary_context_id = NULL' .
                   " WHERE primary_consumer_instance_guid = %s",
       BasicLTI_Tool_Provider::quoted($this->guid));
    $ok = mysql_query($sql);

// Delete any contexts for this consumer instance
    $sql = sprintf("DELETE FROM {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME . " WHERE consumer_instance_guid = %s",
       BasicLTI_Tool_Provider::quoted($this->guid));
    mysql_query($sql);

// Delete consumer instance
    $sql = sprintf("DELETE FROM {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONSUMER_INSTANCE_TABLE_NAME . " WHERE consumer_instance_guid = %s",
       BasicLTI_Tool_Provider::quoted($this->guid));
    $ok = mysql_query($sql);

    if ($ok) {
      $this->guid = NULL;
      if ($deleteConsumer) {
        $consumer = new LTI_Tool_Consumer($this->consumer_guid, $this->dbTableNamePrefix);
        $consumer->delete();
      }

    }

    return $ok;

  }

###
#    Check if the tool consumer instance is enabled
###
  public function isEnabled() {

    return $this->enabled;

  }

###
#    Update the state of the tool consumer instance
###
  public function updateState($state) {

    $this->state = $state;
    $now = date("Y-m-d H:i:s", time());
    $sql = sprintf("UPDATE {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONSUMER_INSTANCE_TABLE_NAME . " SET state = %s, updated = '{$now}' WHERE consumer_instance_guid = %s",
       BasicLTI_Tool_Provider::quoted($state), BasicLTI_Tool_Provider::quoted($this->guid));

    $ok = mysql_query($sql);

    return $ok;

  }

###
#    Delete out-of-date (more than a day old) nonce values
###
  public function clearOldNonces() {

    $old = time() - (self::MAX_NONCE_AGE * 60 * 60);
    $sql = "DELETE FROM {$this->dbTableNamePrefix}" . self::NONCE_TABLE_NAME . " WHERE timestamp < $old";
    $ok = mysql_query($sql);

    return $ok;

  }

###
#    Save a nonce value in the database
###
  public function saveNonce($nonce) {

    $ok = TRUE;

#
### Delete nonce values more than one day old
#
    $old = time() - (self::MAX_NONCE_AGE * 60 * 60);
    $sql = "DELETE FROM {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::NONCE_TABLE_NAME . " WHERE timestamp < $old";
    mysql_query($sql);

#
### check if nonce already exists
#
    $sql = sprintf("SELECT value AS T FROM {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::NONCE_TABLE_NAME . " WHERE consumer_instance_guid = %s AND value = %s",
       BasicLTI_Tool_Provider::quoted($this->guid), BasicLTI_Tool_Provider::quoted($nonce));
    $rs_nonce = mysql_query($sql);
    if ($rs_nonce) {
      $row = mysql_fetch_object($rs_nonce);
      if ($row !== FALSE) {
        $ok = FALSE;
      }
    }

    if ($ok) {

#
### Save new nonce
#
      $timestamp = time();
      $sql = sprintf("INSERT INTO {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::NONCE_TABLE_NAME . " (consumer_instance_guid, value, timestamp) VALUES(%s, %s, $timestamp)",
         BasicLTI_Tool_Provider::quoted($this->guid), BasicLTI_Tool_Provider::quoted($nonce));
      $ok = mysql_query($sql);
    }

    return $ok;

  }

###
#    Generate a random string
###
  public function getRandomString($length = 8) {

    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    $value = '';
    $charsLength = strlen($chars) - 1;

    for ($i = 1 ; $i <= $length; $i++) {
      $value .= $chars[rand(0, $charsLength)];
    }

    return $value;

  }

###
###  PRIVATE METHOD
###

###
#    Load the tool consumer instance from the database
###
  private function load($guid) {

    $this->key = NULL;
    $this->guid = $guid;
    $this->consumer_guid = NULL;
    $this->state = NULL;
    $this->secret = NULL;
    $this->enabled = FALSE;
    $this->created = NULL;
    $this->updated = NULL;

    $sql = sprintf('SELECT ci.*, c.enabled ' .
                   "FROM {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONSUMER_INSTANCE_TABLE_NAME . ' AS ci ' .
                   "INNER JOIN {$this->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONSUMER_TABLE_NAME . ' AS c ON ci.consumer_guid = c.consumer_guid ' .
                   "WHERE consumer_instance_guid = %s",
       BasicLTI_Tool_Provider::quoted($guid));
    $rs_consumer = mysql_query($sql);
    if ($rs_consumer) {
      $row = mysql_fetch_object($rs_consumer);
      if ($row) {
        $this->key = $row->consumer_instance_guid;
        $this->consumer_guid = $row->consumer_guid;
        $this->enabled = ($row->enabled == 1);
        $this->state = $row->state;
        $this->secret = $row->secret;
        $this->created = strtotime($row->created);
        $this->updated = strtotime($row->updated);
      }
    }
  }

}


###
###  Class to represent a tool consumer context
###

class LTI_Context {

  const EXT_READ = 1;
  const EXT_WRITE = 2;
  const EXT_DELETE = 3;

  const EXT_TYPE_DECIMAL = 'decimal';
  const EXT_TYPE_PERCENTAGE = 'percentage';
  const EXT_TYPE_RATIO = 'ratio';
  const EXT_TYPE_LETTER_AF = 'letteraf';
  const EXT_TYPE_LETTER_AF_PLUS = 'letterafplus';
  const EXT_TYPE_PASS_FAIL = 'passfail';
  const EXT_TYPE_TEXT = 'freetext';

  const MIN_SHARE_KEY_LENGTH = 5;
  const MAX_SHARE_KEY_LENGTH = 32;

  public $consumer_instance = NULL;
  public $id = NULL;
  public $lti_context_id = NULL;
  public $lti_resource_id = NULL;
  public $title = NULL;
  public $ext_response = NULL;
  public $primary_consumer_instance_guid = NULL;
  public $primary_context_id = NULL;
  public $share_approved = NULL;
  public $created = NULL;
  public $updated = NULL;

  private $settings_changed = FALSE;
  private $settings = NULL;
  private $ext_doc = NULL;
  private $ext_nodes = NULL;

###
#    Class constructor
###
  public function __construct(&$consumer_instance, $id) {

    $this->consumer_instance = $consumer_instance;
    $this->id = $id;
    $this->settings = array();
    $this->load();

  }

###
#    Save the context to the database
###
  public function save() {

    if (is_null($this->share_approved)) {
      $approved = 'NULL';
    } else if ($this->share_approved) {
      $approved = 1;
    } else {
      $approved = 0;
    }
    $time = time();
    $now = date("Y-m-d H:i:s", $time);
    $settingsValue = serialize($this->settings);
    if (is_null($this->created)) {
      $sql = sprintf("INSERT INTO {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME . ' (consumer_instance_guid, context_id, ' .
                     'lti_context_id, lti_resource_id, title, settings, primary_consumer_instance_guid, primary_context_id, share_approved, created, updated) ' .
                     "VALUES (%s, %s, %s, %s, %s, '{$settingsValue}', ",
         BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id),
         BasicLTI_Tool_Provider::quoted($this->lti_context_id), BasicLTI_Tool_Provider::quoted($this->lti_resource_id),
         BasicLTI_Tool_Provider::quoted($this->title));
      if (is_null($this->primary_consumer_instance_guid)) {
        $sql .= 'NULL, NULL, ';
      } else {
        $sql .= sprintf("%s, %s, ", BasicLTI_Tool_Provider::quoted($this->primary_consumer_instance_guid), BasicLTI_Tool_Provider::quoted($this->primary_context_id));
      }
      $sql .= "{$approved}, '{$now}', '{$now}')";
    } else {
      $sql = sprintf("UPDATE {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME . ' SET ' .
                     "lti_context_id = %s, lti_resource_id = %s, title = %s, settings = '{$settingsValue}', ".
                     "primary_consumer_instance_guid = %s, primary_context_id = %s, updated = '{$now}' " .
                     "WHERE consumer_instance_guid = %s AND context_id = %s",
         BasicLTI_Tool_Provider::quoted($this->lti_context_id), BasicLTI_Tool_Provider::quoted($this->lti_resource_id),
         BasicLTI_Tool_Provider::quoted($this->title),
         BasicLTI_Tool_Provider::quoted($this->primary_consumer_instance_guid), BasicLTI_Tool_Provider::quoted($this->primary_context_id),
         BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
    }
    $ok = mysql_query($sql);
    if ($ok) {
      if (is_null($this->created)) {
        $this->created = $time;
      }
      $this->updated = $time;
      $this->settings_changed = FALSE;
    }

    return $ok;

  }

###
#    Delete the context from the database
###
  public function delete() {

// Update any contexts for which this is the primary context
    $sql = sprintf("UPDATE {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME .
                   ' SET primary_consumer_instance_guid = NULL AND primary_context_id = NULL' .
                   " WHERE primary_consumer_instance_guid = %s AND primary_context_id = %s",
       BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
    $ok = mysql_query($sql);

// Delete users
    if ($ok) {
      $sql = sprintf("DELETE FROM {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::USER_TABLE_NAME .
                     " WHERE consumer_instance_guid = %s AND context_id = %s",
         BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
      $ok = mysql_query($sql);
    }

// Delete context
    if ($ok) {
      $sql = sprintf("DELETE FROM {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME .
                     " WHERE consumer_instance_guid = %s AND context_id = %s",
         BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
      $ok = mysql_query($sql);
    }

    return $ok;

  }

###
#    Get a setting value
###
  public function getSetting($name, $default = '') {

    if (array_key_exists($name, $this->settings)) {
      $value = $this->settings[$name];
    } else {
      $value = $default;
    }

    return $value;

  }

###
#    Set a setting value
###
  public function setSetting($name, $value = NULL) {

    $old_value = $this->getSetting($name);
    if ($value != $old_value) {
      if (!empty($value)) {
        $this->settings[$name] = $value;
      } else {
        unset($this->settings[$name]);
      }
      $this->settings_changed = TRUE;
    }

  }

###
#    Get an array of all setting values
###
  public function getSettings() {

    return $this->settings;

  }

###
#    Save setting values
###
  public function saveSettings() {

    if ($this->settings_changed) {
      $ok = $this->save();
    } else {
      $ok = TRUE;
    }

    return $ok;

  }

###
#    Check if the Outcomes service is supported
###
  public function hasOutcomesService() {

    $url = $this->getSetting('ext_ims_lis_basic_outcome_url') . $this->getSetting('lis_outcome_service_url');

    return !empty($url);

  }

###
#    Check if the Memberships service is supported
###
  public function hasMembershipsService() {

    $url = $this->getSetting('ext_ims_lis_memberships_url');

    return !empty($url);

  }

###
#    Check if the Setting service is supported
###
  public function hasSettingService() {

    $url = $this->getSetting('ext_ims_lti_tool_setting_url');

    return !empty($url);

  }

###
#    Perform an Outcomes service request
###
  public function doOutcomesService($action, $lti_outcome) {

    $response = FALSE;
    $this->ext_response = NULL;
#
### Use LTI 1.1 service in preference to extension service if it is available
#
    $urlLTI11 = $this->getSetting('lis_outcome_service_url');
    $urlExt = $this->getSetting('ext_ims_lis_basic_outcome_url');
    if ($urlExt || $urlLTI11) {
      switch ($action) {
        case self::EXT_READ:
          if ($urlLTI11) {
            $do = 'readResult';
          } else {
            $do = 'basic-lis-readresult';
          }
          break;
        case self::EXT_WRITE:
          if ($urlLTI11 && $this->checkValueType($lti_outcome, array(self::EXT_TYPE_DECIMAL))) {
            $do = 'replaceResult';
          } else if ($this->checkValueType($lti_outcome)) {
            $urlLTI11 = NULL;
            $do = 'basic-lis-updateresult';
          }
          break;
        case self::EXT_DELETE:
          if ($urlLTI11) {
            $do = 'deleteResult';
          } else {
            $do = 'basic-lis-deleteresult';
          }
          break;
      }
    }
    if (isset($do)) {
      if ($urlLTI11) {
        $xml = <<<EOF
      <resultRecord>
        <sourcedGUID>
          <sourcedId>{$lti_outcome->getSourcedid()}</sourcedId>
        </sourcedGUID>
        <result>
          <resultScore>
            <language>{$lti_outcome->language}</language>
            <textString>{$lti_outcome->getValue()}</textString>
          </resultScore>
        </result>
      </resultRecord>
EOF;
        if ($this->doLTI11Service($do, $urlLTI11, $xml)) {
          switch ($action) {
            case self::EXT_READ:
              if (isset($this->ext_nodes['imsx_POXBody']["{$do}Response"]['result']['resultScore']['textString'])) {
                $response = $this->ext_nodes['imsx_POXBody']["{$do}Response"]['result']['resultScore']['textString'];
              }
              break;
            case self::EXT_WRITE:
            case self::EXT_DELETE:
              $response = TRUE;
              break;
          }
        }
      } else {
        $params = array();
        $params['sourcedid'] = $lti_outcome->getSourcedid();
        $params['result_resultscore_textstring'] = $lti_outcome->getValue();
        if (!empty($lti_outcome->language)) {
          $params['result_resultscore_language'] = $lti_outcome->language;
        }
        if (!empty($lti_outcome->status)) {
          $params['result_statusofresult'] = $lti_outcome->status;
        }
        if (!empty($lti_outcome->date)) {
          $params['result_date'] = $lti_outcome->date;
        }
        if (!empty($lti_outcome->type)) {
          $params['result_resultvaluesourcedid'] = $lti_outcome->type;
        }
        if (!empty($lti_outcome->data_source)) {
          $params['result_datasource'] = $lti_outcome->data_source;
        }
        if ($this->doService($do, $urlExt, $params)) {
          switch ($action) {
            case self::EXT_READ:
              if (isset($this->ext_nodes['result']['resultscore']['textstring'])) {
                $response = $this->ext_nodes['result']['resultscore']['textstring'];
              }
              break;
            case self::EXT_WRITE:
            case self::EXT_DELETE:
              $response = TRUE;
              break;
          }
        }
      }
    }

    return $response;

  }

###
#    Perform a Memberships service request
###
  public function doMembershipsService() {

    $users = array();
    $old_users = $this->getUserResultSourcedIDs(TRUE, BasicLTI_Tool_Provider::ID_SCOPE_RESOURCE);
    $this->ext_response = NULL;
    $url = $this->getSetting('ext_ims_lis_memberships_url');
    $params = array();
    $params['id'] = $this->getSetting('ext_ims_lis_memberships_id');

    if ($this->doService('basic-lis-readmembershipsforcontext', $url, $params)) {
      if (!isset($this->ext_nodes['memberships']['member'])) {
        $members = array();
      } else if (!isset($this->ext_nodes['memberships']['member'][0])) {
        $members = array();
        $members[0] = $this->ext_nodes['memberships']['member'];
      } else {
        $members = $this->ext_nodes['memberships']['member'];
      }

      for ($i = 0; $i < count($members); $i++) {

        $user = new LTI_User($this, $members[$i]['user_id']);
#
### Set the user name
#
        $firstname = (isset($members[$i]['person_name_given'])) ? $members[$i]['person_name_given'] : '';
        $lastname = (isset($members[$i]['person_name_family'])) ? $members[$i]['person_name_family'] : '';
        $fullname = (isset($members[$i]['person_name_full'])) ? $members[$i]['person_name_full'] : '';
        $user->setNames($firstname, $lastname, $fullname);
#
### Set the user email
#
        $email = (isset($members[$i]['person_contact_email_primary'])) ? $members[$i]['person_contact_email_primary'] : '';
        $user->setEmail($email, $this->consumer_instance->defaultEmail);
#
### Set the user roles
#
        if (isset($members[$i]['roles'])) {
          $user->roles = explode(',', $members[$i]['roles']);
        }
#
### If a result sourcedid is provided save the user
#
        if (isset($members[$i]['lis_result_sourcedid'])) {
          $user->lti_result_sourcedid = $members[$i]['lis_result_sourcedid'];
          $user->save();
        }
        $users[] = $user;
#
### Remove old user (if it exists)
#
        unset($old_users[$user->getId(BasicLTI_Tool_Provider::ID_SCOPE_RESOURCE)]);
      }
#
### Delete any old users which were not in the latest list from the tool consumer
#
      foreach ($old_users as $id => $user) {
        $user->delete();
      }
    } else {
      $users = array_values($old_users);
    }

    return $users;

  }

###
#    Perform a Setting service request
###
  public function doSettingService($action, $value = NULL) {

    $response = FALSE;
    $this->ext_response = NULL;
    switch ($action) {
      case self::EXT_READ:
        $do = 'basic-lti-loadsetting';
        break;
      case self::EXT_WRITE:
        $do = 'basic-lti-savesetting';
        break;
      case self::EXT_DELETE:
        $do = 'basic-lti-deletesetting';
        break;
    }
    if (isset($do)) {

      $url = $this->getSetting('ext_ims_lti_tool_setting_url');
      $params = array();
      $params['id'] = $this->getSetting('ext_ims_lti_tool_setting_id');
      if (is_null($value)) {
        $value = '';
      }
      $params['setting'] = $value;

      if ($this->doService($do, $url, $params)) {
        switch ($action) {
          case self::EXT_READ:
            if (isset($this->ext_nodes['setting']['value'])) {
              $response = $this->ext_nodes['setting']['value'];
              if (is_array($response)) {
                $response = '';
              }
            }
            break;
          case self::EXT_WRITE:
            $this->setSetting('ext_ims_lti_tool_setting', $value);
            $this->saveSettings();
            $response = TRUE;
            break;
          case self::EXT_DELETE:
            $response = TRUE;
            break;
        }
      }

    }

    return $response;

  }

###
#    Obtain an array of LTI_User objects for users with a result sourcedId.  The array may include users from other
#    contexts which are sharing this context.  It may also be optionally indexed by the user ID of a specified scope.
###
  public function getUserResultSourcedIDs($context_only = FALSE, $id_scope = NULL) {

    $users = array();

    if ($context_only) {
      $sql = sprintf('SELECT u.consumer_instance_guid, u.context_id, u.user_id, u.lti_result_sourcedid ' .
                     "FROM {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::USER_TABLE_NAME . ' AS u '  .
                     "INNER JOIN {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME . ' AS c '  .
                     'ON u.consumer_instance_guid = c.consumer_instance_guid AND u.context_id = c.context_id ' .
                     "WHERE (c.consumer_instance_guid = %s AND c.context_id = %s AND c.primary_consumer_instance_guid IS NULL AND c.primary_context_id IS NULL)",
         BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
    } else {
      $sql = sprintf('SELECT u.consumer_instance_guid, u.context_id, u.user_id, u.lti_result_sourcedid ' .
                     "FROM {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::USER_TABLE_NAME . ' AS u '  .
                     "INNER JOIN {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME . ' AS c '  .
                     'ON u.consumer_instance_guid = c.consumer_instance_guid AND u.context_id = c.context_id ' .
                     "WHERE (c.consumer_instance_guid = %s AND c.context_id = %s AND c.primary_consumer_instance_guid IS NULL AND c.primary_context_id IS NULL) OR " .
                     "(c.primary_consumer_instance_guid = %s AND c.primary_context_id = %s AND share_approved = 1)",
         BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id),
         BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
    }
    $rs_user = mysql_query($sql);
    if ($rs_user) {
      while ($row = mysql_fetch_object($rs_user)) {
        $user = new LTI_User($this, $row->user_id);
        $user->consumer_instance_guid = $row->consumer_instance_guid;
        $user->context_id = $row->context_id;
        $user->lti_result_sourcedid = $row->lti_result_sourcedid;
        if (is_null($id_scope)) {
          $users[] = $user;
        } else {
          $users[$user->getId($id_scope)] = $user;
        }
      }
    }

    return $users;

  }

###
#    Generate a new share key
###
  public function getNewShareKey($life, $auto_approve = FALSE, $length = NULL) {

    $expires = time() + ($life * 60 * 60);
    if ($auto_approve) {
      $approve = 1;
    } else {
      $approve = 0;
    }
    if (empty($length) || !is_numeric($length)) {
      $length = self::MAX_SHARE_KEY_LENGTH;
    } else {
      $length = max(min($length, self::MAX_SHARE_KEY_LENGTH), self::MIN_SHARE_KEY_LENGTH);
    }
    $key = LTI_Tool_Consumer_Instance::getRandomString($length);

    $sql = sprintf("INSERT INTO {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_SHARE_KEY_TABLE_NAME .
                   ' (share_key, primary_consumer_instance_guid, primary_context_id, auto_approve, expires) ' .
                   "VALUES (%s, %s, %s, {$approve}, {$expires})",
       BasicLTI_Tool_Provider::quoted($key), BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
    $ok = mysql_query($sql);
    if (!$ok) {
      $key = '';
    }

    return $key;

  }

###
#    Get an array of LTI_Context_Share objects for each context which is sharing this context
###
  public function getShares() {

    $shares = array();

    $sql = sprintf('SELECT consumer_instance_guid, context_id, title, share_approved ' .
                   "FROM {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME .
                   " WHERE primary_consumer_instance_guid = %s AND primary_context_id = %s" .
                   ' ORDER BY consumer_instance_guid',
       BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
    $rs_share = mysql_query($sql);
    if ($rs_share) {
      while ($row = mysql_fetch_object($rs_share)) {
        $share = new LTI_Context_Share();
        $share->consumer_instance_guid = $row->consumer_instance_guid;
        $share->context_id = $row->context_id;
        $share->title = $row->title;
        $share->approved = $row->share_approved;
        $shares[] = $share;
      }
    }

    return $shares;

  }

###
#    Set the approval status of a share
###
  public function doApproveShare($consumer_instance_guid, $context_id, $approve) {

    if ($approve) {
      $approved = 1;
    } else {
      $approved = 0;
    }
    $sql = sprintf("UPDATE {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME .
                   " SET share_approved = {$approved} " .
                   "WHERE consumer_instance_guid = %s AND context_id = %s",
       BasicLTI_Tool_Provider::quoted($consumer_instance_guid), BasicLTI_Tool_Provider::quoted($context_id));

    $ok = mysql_query($sql);

    return $ok;

  }

###
#    Cancel a context share
###
  public function doCancelShare($consumer_instance_guid, $context_id) {

    $sql = sprintf("UPDATE {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME .
                   " SET primary_consumer_instance_guid = NULL, primary_context_id = NULL, share_approved = NULL " .
                   "WHERE consumer_instance_guid = %s AND context_id = %s",
       BasicLTI_Tool_Provider::quoted($consumer_instance_guid), BasicLTI_Tool_Provider::quoted($context_id));

    $ok = mysql_query($sql);

    return $ok;

  }

###
###  PRIVATE METHODS
###

###
#    Load the context from the database
###
  private function load() {

    $this->lti_context_id = NULL;
    $this->lti_resource_id = NULL;
    $this->title = '';
    $this->settings = array();
    $this->primary_consumer_instance_guid = NULL;
    $this->primary_context_id = NULL;
    $this->share_approved = NULL;
    $this->created = NULL;
    $this->updated = NULL;

    $sql = sprintf('SELECT c.* ' .
                   "FROM {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME . ' AS c ' .
                   "WHERE consumer_instance_guid = %s AND context_id = %s",
       BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
    $rs_context = mysql_query($sql);
    if ($rs_context) {
      $row = mysql_fetch_object($rs_context);
      if ($row) {
        $this->lti_context_id = $row->lti_context_id;
        $this->lti_resource_id = $row->lti_resource_id;
        $this->title = $row->title;
        $this->settings = unserialize($row->settings);
        if (!is_array($this->settings)) {
          $this->settings = array();
        }
        if (!is_null($row->primary_consumer_instance_guid)) {
          $this->primary_consumer_instance_guid = $row->primary_consumer_instance_guid;
        }
        if (!is_null($row->primary_context_id)) {
          $this->primary_context_id = $row->primary_context_id;
        }
        $this->share_approved = (is_null($row->share_approved)) ? NULL : ($row->share_approved == 1);
        $this->created = strtotime($row->created);
        $this->updated = strtotime($row->updated);
      }
    }
  }

###
#    Convert data type of value to a supported type if possible
###
  public function checkValueType(&$lti_outcome, $supported_types = NULL) {

    if (empty($supported_types)) {
      $supported_types = explode(',', str_replace(' ', '', strtolower($this->getSetting('ext_ims_lis_resultvalue_sourcedids', self::EXT_TYPE_DECIMAL))));
    }
    $type = $lti_outcome->type;
    $value = $lti_outcome->getValue();
// Check whether the type is supported or there is no value
    $ok = in_array($type, $supported_types) || (strlen($value) <= 0);
    if (!$ok) {
// Convert numeric values to decimal
      if ($type == self::EXT_TYPE_PERCENTAGE) {
        if (substr($value, -1) == '%') {
          $value = substr($value, 0, -1);
        }
        $ok = is_numeric($value) && ($value >= 0) && ($value <= 100);
        if ($ok) {
          $lti_outcome->setValue($value / 100);
          $lti_outcome->type = self::EXT_TYPE_DECIMAL;
        }
      } else if ($type == self::EXT_TYPE_RATIO) {
        $parts = explode('/', $value, 2);
        $ok = (count($parts) == 2) && is_numeric($parts[0]) && is_numeric($parts[1]) && ($parts[0] >= 0) && ($parts[1] > 0);
        if ($ok) {
          $lti_outcome->setValue($parts[0] / $parts[1]);
          $lti_outcome->type = self::EXT_TYPE_DECIMAL;
        }
// Convert letter_af to letter_af_plus or text
      } else if ($type == self::EXT_TYPE_LETTER_AF) {
        if (in_array(self::EXT_TYPE_LETTER_AF_PLUS, $supported_types)) {
          $ok = TRUE;
          $lti_outcome->type = self::EXT_TYPE_LETTER_AF_PLUS;
        } else if (in_array(self::EXT_TYPE_TEXT, $supported_types)) {
          $ok = TRUE;
          $lti_outcome->type = self::EXT_TYPE_TEXT;
        }
// Convert letter_af_plus to letter_af or text
      } else if ($type == self::EXT_TYPE_LETTER_AF_PLUS) {
        if (in_array(self::EXT_TYPE_LETTER_AF, $supported_types) && (strlen($value) == 1)) {
          $ok = TRUE;
          $lti_outcome->type = self::EXT_TYPE_LETTER_AF;
        } else if (in_array(self::EXT_TYPE_TEXT, $supported_types)) {
          $ok = TRUE;
          $lti_outcome->type = self::EXT_TYPE_TEXT;
        }
// Convert text to decimal
      } else if ($type == self::EXT_TYPE_TEXT) {
        $ok = is_numeric($value) && ($value >= 0) && ($value <=1);
        if ($ok) {
          $lti_outcome->type = self::EXT_TYPE_DECIMAL;
        } else if (substr($value, -1) == '%') {
          $value = substr($value, 0, -1);
          $ok = is_numeric($value) && ($value >= 0) && ($value <=100);
          if ($ok) {
            if (in_array(self::EXT_TYPE_PERCENTAGE, $supported_types)) {
              $lti_outcome->type = self::EXT_TYPE_PERCENTAGE;
            } else {
              $lti_outcome->setValue($value / 100);
              $lti_outcome->type = self::EXT_TYPE_DECIMAL;
            }
          }
        }
      }
    }

    return $ok;

  }

###
#    Send a service request to the tool consumer
###
  private function doService($type, $url, $params) {

    $this->ext_response = NULL;
    if (!empty($url)) {
// Check for query parameters which need to be included in the signature
      $query_params = array();
      $query_string = parse_url($url, PHP_URL_QUERY);
      if (!is_null($query_string)) {
        $query_items = explode('&', $query_string);
        foreach ($query_items as $item) {
          if (strpos($item, '=') !== FALSE) {
            list($name, $value) = explode('=', $item);
            $query_params[$name] = $value;
          } else {
            $query_params[$name] = '';
          }
        }
      }
      $params = $params + $query_params;
// Add standard parameters
      $params['oauth_consumer_key'] = $this->consumer_instance->guid;
      $params['lti_version'] = BasicLTI_Tool_Provider::LTI_VERSION;
      $params['lti_message_type'] = $type;
// Add OAuth signature
      $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
      $consumer = new OAuthConsumer($this->consumer_instance->guid, $this->consumer_instance->secret, NULL);
      $req = OAuthRequest::from_consumer_and_token($consumer, NULL, 'POST', $url, $params);
      $req->sign_request($hmac_method, $consumer, NULL);
      $params = $req->get_parameters();
// Remove parameters being passed on the query string
      foreach (array_keys($query_params) as $name) {
        unset($params[$name]);
      }
// Connect to tool consumer
      $this->ext_response = $this->do_post_request($url, $params);
// Parse XML response
      $this->ext_doc = new DOMDocument();
      $this->ext_doc->loadXML($this->ext_response);
      $this->ext_nodes = $this->domnode_to_array($this->ext_doc->documentElement);
      if (!isset($this->ext_nodes['statusinfo']['codemajor']) || ($this->ext_nodes['statusinfo']['codemajor'] != 'Success')) {
        $this->ext_response = NULL;
      }
    }

    return !is_null($this->ext_response);

  }

###
#    Send a service request to the tool consumer
###
  private function doLTI11Service($type, $url, $xml) {

    $this->ext_response = NULL;
    if (!empty($url)) {
      $id = uniqid();
      $xmlRequest = <<<EOF
<?xml version = "1.0" encoding = "UTF-8"?>
<imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/lis/oms1p0/pox">
  <imsx_POXHeader>
    <imsx_POXRequestHeaderInfo>
      <imsx_version>V1.0</imsx_version>
      <imsx_messageIdentifier>{$id}</imsx_messageIdentifier>
    </imsx_POXRequestHeaderInfo>
  </imsx_POXHeader>
  <imsx_POXBody>
    <{$type}Request>
{$xml}
    </{$type}Request>
  </imsx_POXBody>
</imsx_POXEnvelopeRequest>
EOF;
// Calculate body hash
      $hash = base64_encode(sha1($xmlRequest, TRUE));
      $params = array('oauth_body_hash' => $hash);

// Add OAuth signature
      $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
      $consumer = new OAuthConsumer($this->consumer_instance->guid, $this->consumer_instance->secret, NULL);
      $req = OAuthRequest::from_consumer_and_token($consumer, NULL, 'POST', $url, $params);
      $req->sign_request($hmac_method, $consumer, NULL);
      $params = $req->get_parameters();
      $header = $req->to_header();
      $header = $header . "\nContent-Type: application/xml";

// Connect to tool consumer
      $this->ext_response = $this->do_post_request($url, $xmlRequest, $header);
// Parse XML response
      $this->ext_doc = new DOMDocument();
      $this->ext_doc->loadXML($this->ext_response);
      $this->ext_nodes = $this->domnode_to_array($this->ext_doc->documentElement);
      if (!isset($this->ext_nodes['imsx_POXHeader']['imsx_POXResponseHeaderInfo']['imsx_statusInfo']['imsx_codeMajor']) ||
          ($this->ext_nodes['imsx_POXHeader']['imsx_POXResponseHeaderInfo']['imsx_statusInfo']['imsx_codeMajor'] != 'success')) {
        $this->ext_response = NULL;
      }
    }

    return !is_null($this->ext_response);

  }

###
#    Get the response from an HTTP POST request
###
  private function do_post_request($url, $params, $header = NULL) {

    $response = '';
    if (is_array($params)) {
      $data = http_build_query($params);
    } else {
      $data = $params;
    }
    $opts = array('method' => 'POST',
                  'content' => $data
                 );
    if (!empty($header)) {
      $opts['header'] = $header;
    }
    $ctx = stream_context_create(array('http' => $opts));
    $fp = @fopen($url, 'rb', false, $ctx);
    if ($fp) {
      $resp = @stream_get_contents($fp);
      if ($resp !== FALSE) {
        $response = $resp;
      }
    }

    return $response;

  }

###
#    Convert DOM nodes to array
###
  private function domnode_to_array($node) {

    $output = array();
    switch ($node->nodeType) {
      case XML_CDATA_SECTION_NODE:
      case XML_TEXT_NODE:
        $output = trim($node->textContent);
        break;
      case XML_ELEMENT_NODE:
        for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
          $child = $node->childNodes->item($i);
          $v = $this->domnode_to_array($child);
          if (isset($child->tagName)) {
            $t = $child->tagName;
            if (!isset($output[$t])) {
              $output[$t] = array();
            }
            $output[$t][] = $v;
          } else if($v) {
            $output = (string) $v;
          }
        }
        if (is_array($output)) {
          if ($node->attributes->length) {
            $a = array();
            foreach ($node->attributes as $attrName => $attrNode) {
              $a[$attrName] = (string) $attrNode->value;
            }
            $output['@attributes'] = $a;
          }
          foreach ($output as $t => $v) {
            if (is_array($v) && count($v)==1 && $t!='@attributes') {
              $output[$t] = $v[0];
            }
          }
        }
        break;
    }

    return $output;

  }

}


###
###  Class to represent an outcome
###

class LTI_Outcome {

  public $language = NULL;
  public $status = NULL;
  public $date = NULL;
  public $type = NULL;
  public $data_source = NULL;

  private $sourcedid = NULL;
  private $value = NULL;

  public function __construct($sourcedid, $value = NULL) {

    $this->sourcedid = $sourcedid;
    $this->value = $value;
    $this->language = 'en-US';
    $this->date = gmdate('Y-m-d\TH:i:s\Z', time());
    $this->type = 'decimal';

  }

  public function getSourcedid() {

    return $this->sourcedid;

  }

  public function getValue() {

    return $this->value;

  }

  public function setValue($value) {

    $this->value = $value;

  }

}


###
###  Class to represent a context share
###

class LTI_Context_Share {

  public $consumer_instance_guid = NULL;
  public $context_id = NULL;
  public $title = NULL;
  public $approved = NULL;

  public function __construct() {

  }

}


###
###  Class to represent a tool consumer user
###

class LTI_User {

  public $context = NULL;
  public $id = NULL;
  public $firstname = '';
  public $lastname = '';
  public $fullname = '';
  public $email = '';
  public $roles = array();
  public $lti_result_sourcedid = NULL;
  public $created = NULL;
  public $updated = NULL;

###
#    Class constructor
###
  public function __construct(&$context, $id) {

    $this->context = $context;
    $this->id = $id;
    $this->load();

  }

###
#    Load the user instance from the database
###
  public function load() {

    $this->lti_result_sourcedid = NULL;
    $this->created = NULL;
    $this->updated = NULL;

    $sql = sprintf('SELECT u.* ' .
                   "FROM {$this->context->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::USER_TABLE_NAME . ' AS u ' .
                   "WHERE consumer_instance_guid = %s AND context_id = %s AND user_id = %s",
       BasicLTI_Tool_Provider::quoted($this->context->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->context->id),
       BasicLTI_Tool_Provider::quoted($this->id));
    $rs_user = mysql_query($sql);
    if ($rs_user) {
      $row = mysql_fetch_object($rs_user);
      if ($row) {
        $this->lti_result_sourcedid = $row->lti_result_sourcedid;
        $this->created = strtotime($row->created);
        $this->updated = strtotime($row->updated);
      }
    }
  }

###
#    Save the user to the database
###
  public function save() {

    if (!empty($this->lti_result_sourcedid)) {
      $time = time();
      $now = date("Y-m-d H:i:s", $time);
      if (is_null($this->created)) {
        $sql = sprintf("INSERT INTO {$this->context->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::USER_TABLE_NAME . ' (consumer_instance_guid, context_id, ' .
                       'user_id, lti_result_sourcedid, created, updated) ' .
                       "VALUES (%s, %s, %s, %s, '{$now}', '{$now}')",
           BasicLTI_Tool_Provider::quoted($this->context->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->context->id),
           BasicLTI_Tool_Provider::quoted($this->id), BasicLTI_Tool_Provider::quoted($this->lti_result_sourcedid));
      } else {
        $sql = sprintf("UPDATE {$this->context->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::USER_TABLE_NAME .
                       " SET lti_result_sourcedid = %s, updated = '{$now}' " .
                       "WHERE consumer_instance_guid = %s AND context_id = %s AND user_id = %s",
           BasicLTI_Tool_Provider::quoted($this->lti_result_sourcedid),
           BasicLTI_Tool_Provider::quoted($this->context->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->context->id),
           BasicLTI_Tool_Provider::quoted($this->id));
      }
      $ok = mysql_query($sql);
      if ($ok) {
        if (is_null($this->created)) {
          $this->created = $time;
        }
        $this->updated = $time;
      }
    } else {
      $ok = TRUE;
    }

    return $ok;

  }

###
#    Delete the user from the database
###
  public function delete() {

    $sql = sprintf("DELETE FROM {$this->context->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::USER_TABLE_NAME .
                   " WHERE consumer_instance_guid = %s AND context_id = %s AND user_id = %s",
       BasicLTI_Tool_Provider::quoted($this->context->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->context->id),
       BasicLTI_Tool_Provider::quoted($this->id));
    $ok = mysql_query($sql);

    return $ok;

  }

###
#    Get the user ID (which may be a compound of the tool consumer and context IDs)
###
  public function getId($id_scope = NULL) {

    if (empty($id_scope)) {
      $id_scope = $this->context->consumer_instance->id_scope;
    }
    switch ($id_scope) {
      case BasicLTI_Tool_Provider::ID_SCOPE_GLOBAL:
        $id = $this->context->consumer_instance->guid . BasicLTI_Tool_Provider::ID_SCOPE_SEPARATOR . $this->id;
        break;
      case BasicLTI_Tool_Provider::ID_SCOPE_CONTEXT:
        $id = $this->context->consumer_instance->guid;
        if ($this->context->lti_context_id) {
          $id .= BasicLTI_Tool_Provider::ID_SCOPE_SEPARATOR . $this->context->lti_context_id;
        }
        $id .= BasicLTI_Tool_Provider::ID_SCOPE_SEPARATOR . $this->id;
        break;
      case BasicLTI_Tool_Provider::ID_SCOPE_RESOURCE:
        $id = $this->context->consumer_instance->guid;
        if ($this->context->lti_resource_id) {
          $id .= BasicLTI_Tool_Provider::ID_SCOPE_SEPARATOR . $this->context->lti_resource_id;
        }
        $id .= BasicLTI_Tool_Provider::ID_SCOPE_SEPARATOR . $this->id;
        break;
      default:
        $id = $this->id;
        break;
    }

    return $id;

  }

###
#    Set the user name fields
###
  public function setNames($firstname, $lastname, $fullname) {

    $names = array(0 => '', 1 => '');
    if (!empty($fullname)) {
      $this->fullname = trim($fullname);
      $names = preg_split("/[\s]+/", $this->fullname, 2);
    }
    if (!empty($firstname)) {
      $this->firstname = trim($firstname);
      $names[0] = $this->firstname;
    } else if (!empty($names[0])) {
      $this->firstname = $names[0];
    } else {
      $this->firstname = 'User';
    }
    if (!empty($lastname)) {
      $this->lastname = trim($lastname);
      $names[1] = $this->lastname;
    } else if (!empty($names[1])) {
      $this->lastname = $names[1];
    } else {
      $this->lastname = $this->id;
    }
    if (empty($this->fullname)) {
      $this->fullname = "{$this->firstname} {$this->lastname}";
    }

  }

###
#    Set the email field
###
  public function setEmail($email, $defaultEmail = NULL) {

    if (!empty($email)) {
      $this->email = $email;
    } else if (!empty($defaultEmail)) {
      $this->email = $defaultEmail;
      if (substr($this->email, 0, 1) == '@') {
        $this->email = $this->getId() . $this->email;
      }
    } else {
      $this->email = '';
    }

  }

###
#    Check if the user is an administrator
###
  public function isAdmin() {

    return $this->hasRole('admin');

  }

###
#    Check if the user is staff
###
  public function isStaff() {

    return ($this->hasRole('instructor') || $this->hasRole('contentdeveloper') || $this->hasRole('teachingassistant'));

  }

###
#    Check if the user is a learner
###
  public function isLearner() {

    return $this->hasRole('learner');

  }

###
###  PRIVATE METHODS
###

###
#    Check whether the user has a specified role name
###
  private function hasRole($role) {

    $roles = strtolower(implode(',', $this->roles));

    return (strpos($roles, $role) !== FALSE);

  }

}


###
###  Class to provide an OAuth datastore
###

class LTI_OAuthDataStore extends OAuthDataStore {

  private $consumer_instance = NULL;

  public function __construct($tool_provider) {

    $this->tool_provider = $tool_provider;

  }

  function lookup_consumer($consumer_key) {

    $consumer = new OAuthConsumer($this->tool_provider->consumer_instance->guid, $this->tool_provider->consumer_instance->secret);

    return $consumer;

  }


  function lookup_token($consumer, $token_type, $token) {

    return new OAuthToken($consumer, "");

  }


  function lookup_nonce($consumer, $token, $nonce, $timestamp) {

    $ok = $this->tool_provider->consumer_instance->saveNonce($nonce);
    if (!$ok) {
      $this->tool_provider->reason = 'Invalid nonce.';
    }

    return !$ok;

  }


  function new_request_token($consumer) {

    return NULL;

  }


  function new_access_token($token, $consumer) {

    return NULL;

  }

}

?>
