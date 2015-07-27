<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/engine/start.php");
require_once('BasicLTI_Tool_Provider.php');

$consumer_guid = $_GET['consumer_guid'];
$context_id = $_GET['context_id'];
$email = $_GET['email'];
$fromemail = $_GET['fromemail'];

$consumer_instance = new LTI_Tool_Consumer_Instance($consumer_guid, elgg_get_config('dbprefix'));
$context = new LTI_Context($consumer_instance, $context_id);

$life = $_GET['life'];
$param = $_GET['auto_approve'];

$auto_approve = !empty($param);

$key = $context->getNewShareKey($life, $auto_approve, SHARE_KEY_LENGTH);

if ($auto_approve) {
    $message = sprintf(elgg_echo('LTI:share:emailmsg:pre'), $key, $key, $life);
} else {
    $message = sprintf(elgg_echo('LTI:share:emailmsg'), $key, $key, $life);
}

$headers  = 'From:' . $fromemail . "\r\n";
$headers .= "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
$result = mail($email, elgg_echo('LTI:share:subject'), $message, $headers);

if ($result) {
    $premessage = sprintf(elgg_echo('LTI:share:email'), $email);
} else {
    $premessage = sprintf(elgg_echo('LTI:share:noemail'), $email);
}

echo $premessage  . $message;

?>