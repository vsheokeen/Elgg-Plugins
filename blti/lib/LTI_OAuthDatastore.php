<?php
/*
 *  LTI_OAuthDataStore - PHP class to support LTI_Tool_Producer class and handle SOAP connections to tool consumer
 *  Copyright (C) 2011  Stephen P Vickers
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
 *    1.0.00   8-Feb-11  Initial release
 *    1.1.00  26-Feb-11
 *    1.1.01  17-Apr-11
*/

if (!class_exists("OAuthConsumer")) require_once('OAuth.php');

class LTI_OAuthDataStore extends OAuthDataStore {

  private $consumer_instance = null;

  public function __construct($consumer_instance) {

    $this->consumer_instance = $consumer_instance;

  }

  function lookup_consumer($consumer_key) {

    $consumer = new OAuthConsumer($this->consumer_instance->guid, $this->consumer_instance->secret);

    return $consumer;

  }


  function lookup_token($consumer, $token_type, $token) {

    return new OAuthToken($consumer, "");

  }


  function lookup_nonce($consumer, $token, $nonce, $timestamp) {

    $ok = $this->consumer_instance->saveNonce($nonce);

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