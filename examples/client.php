<?php
/**
 * Sendega MMS/SSM Wrapper library -- Client Examples
 *
 * @author Anders Evenrud <andersevenrud@gmail.com>
 */

define("SENDEGA_USERNAME", "your_username");
define("SENDEGA_PASSWORD", "your_password");
define("SENDEGA_SENDER",   "your_number");

require realpath(__DIR__ . "/../src/Sendega.php");

$destination = "0012345678"; // Country code + number

// Get recipient network information
var_dump(Sendega::SubscriberEnquiry($destination));

// Get recipient location information
var_dump(Sendega::HomeLocationEnquiry($destination));

// Send SMS
var_dump(Sendega::SendSMS($destination, "This is an SMS message"));

// Send MMS (blob/string file attachments)
var_dump(Sendega::SendMMS($destination, "MMS Message title", Array(
  "test.jpg" => file_get_contents("/tmp/test.jpg")
)));

// Send MMS (file attachments)
var_dump(Sendega::SendMMS($destination, "MMS Message title", Array(
  "/tmp/test.jpg"
)));

?>
