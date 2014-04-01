<?php
/**
 * Sendega MMS/SSM Wrapper library -- Server Examples
 *
 * You have to configure your subscription and set the
 * SMS service to call this script via a URL (ex: http://myserver.no/server.example.php)
 * By default this library uses POST data, so you should configure for this
 *
 * @author Anders Evenrud <andersevenrud@gmail.com>
 */

///////////////////////////////////////////////////////////////////////////////
// 1: Incoming Messages
///////////////////////////////////////////////////////////////////////////////

define("SENDEGA_DEVMODE",  true); // If you are using "mock messages"

require realpath(__DIR__ . "/../src/Sendega.php");

if ( !Sendega::CheckWhitelist($_SERVER['REMOTE_ADDR']) ) {
  header("HTTP/1.1 401 Unauthorized");
  exit;
}

if ( $data = Sendega::ParseMessage() ) {

  // Store your message

  header("HTTP/1.1 200 OK");
  exit;
}

header("HTTP/1.1 400 Bad Request");

///////////////////////////////////////////////////////////////////////////////
// 2: Delivery Reports
///////////////////////////////////////////////////////////////////////////////

define("SENDEGA_DEVMODE",  true);                               // If you are using "mock messages"
define("SENDEGA_DLR",     "http://myserver.com/sms_gateway");   // Set this in your send message script

require realpath(__DIR__ . "/../src/Sendega.php");

if ( !Sendega::CheckWhitelist($_SERVER['REMOTE_ADDR']) ) {
  header("HTTP/1.1 401 Unauthorized");
  exit;
}

if ( $data = Sendega::ParseDeliveryReport() ) {

  // Store your delivery report

  header("HTTP/1.1 200 OK");
  exit;
}

header("HTTP/1.1 400 Bad Request");

?>
