<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * This page receives the calls from the consumers
 ------------------------------------------------------------------*/

// The page handling mechanism in Elgg adds data to the $_SERVER['QUERY_STRING'],
// so set to '' before invoking the LTI_tool_provider.
$_SERVER['QUERY_STRING']='';

/*
ob_start();
$logger = "Request from {$_SERVER['REMOTE_ADDR']}: {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']}\n";
$logger .= '$_GET: ' . var_export($_GET, true) . "\n";
$logger .= '$_POST: ' . var_export($_POST, true) . "\n";
writeToLog($logger);
writeToLog('Output: ' . ob_get_contents());
ob_end_flush();
*/
$tool = new BasicLTI_Tool_Provider('doConnect', elgg_get_config('dbprefix'));
$tool->allowSharing = true;
$result = $tool->execute();

//if ($result) writeToLog("RE" . $tool->error);

return false;

/*-------------------------------------------------------------------
 * Invoked on LTI action connect (also default action). In this code
 * the Elgg menu item call this when working with Full LTI, and with
 * Basic this is called because it the default action.
------------------------------------------------------------------*/
function doConnect($tool_provider) {

    $result = LoginUser($tool_provider);
    if (!$result && !empty($tool_provider->return_url)) {
        $urlencode = urlencode(sprintf(elgg_echo('LTI:error:login'),  elgg_get_config('sitename')));
        forward($tool_provider->return_url . '&lti_msg=' . $urlencode);
        return false;
    }

    if (!$result && empty($tool_provider->return_url)) {
        system_message(sprintf(elgg_echo('LTI:error:login'),  elgg_get_config('sitename')));
        forward();
        return false;
    }

    // Send login time to consumer if has setting service and can handle freetext
    $freetext = strpos(strtolower($tool_provider->context->getSetting('ext_ims_lis_resultvalue_sourcedids')), 'freetext');
    if ($tool_provider->context->hasSettingService()  && $freetext) {

        $consumer_name_array = explode("-", $tool_provider->consumer->consumer_name, 2);
        $consumer_name = $consumer_name_array[0];
        $version = strtolower($consumer_name_array[1]);

        $outcome = new LTI_Outcome($tool_provider->context->getSetting('lis_result_sourcedid'), '');
        $outcome->type = 'freetext';
        $result = $tool_provider->context->doOutcomesService(LTI_Context::EXT_READ, $outcome);
        $count = 1;

        switch ($consumer_name) {
            case 'moodle':
                if (!empty($result)) {

                    system_message(sprintf('LTI:last:login'), $result);
                }

                $outcome = new LTI_Outcome($tool_provider->context->getSetting("lis_result_sourcedid"), date('d-M-Y'));
                $outcome->type = 'freetext';
                $outcome->status = 'final';
                break;

            default:
                if (!empty($result)) {
                    $pieces = explode(' ', $result);
                    $count = intval($pieces[0]);
                    $count++;
                    $last_login = $pieces[1] . ' ' . $pieces[2] . ' - Logins: ' . $count;

                    system_message(sprintf('LTI:last:login'), $last_login);
                }
                // Assume freetext available and send back date/count
                $outcome = new LTI_Outcome($tool_provider->context->getSetting('lis_result_sourcedid'), $count . ' ' . date('d-M-Y H:i'));
                $outcome->type = 'freetext';
                $outcome->status = 'interim';
                break;
        }

        $result = $tool_provider->context->doOutcomesService(LTI_Context::EXT_WRITE, $outcome);

    }

    // Store return URL for later use, if present
    if (!empty($tool_provider->return_url)) {
        $_SESSION['return_url'] = $tool_provider->return_url;
        $_SESSION['return_name'] = 'Return to ' . $tool_provider->consumer->name;
    }

    ProvisionLTIGroup($tool_provider);

    system_messages('Forwarded to Profile');
    forward();

    return false;

}

/*-------------------------------------------------------------------
 * Invoked by content handler and simply logs on to Elgg and
 * forwards to the group creation page
 ------------------------------------------------------------------*/
function doContentHandler($tool_provider) {

    LoginUser($tool_provider);
    system_messages('Forwarded to Group creation page');
    forward(elgg_get_config('wwwroot') . '/groups/new/');

    return;

}
?>