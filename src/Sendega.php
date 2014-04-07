<?php
/**
 * Sendega MMS/SSM Wrapper library
 *
 * Sendega.php - Copyright (c) 2014, Anders Evenrud <andersevenrud@gmail.com>
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met: 
 * 
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer. 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution. 
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Anders Evenrud <andersevenrud@gmail.com>
 */

///////////////////////////////////////////////////////////////////////////////
// DEFAULT CONFIGURATION
///////////////////////////////////////////////////////////////////////////////

/**
 * @constant [Sendega] Gateway URL
 */
if ( !defined("SENDEGA_GW") )
      define("SENDEGA_GW",       "https://smsc.sendega.com");

/**
 * @constant [Sendega] Gateway Username (REQUIRED)
 */
if ( !defined("SENDEGA_USERNAME") )
      define("SENDEGA_USERNAME", "");

/**
 * @constant [Sendega] Gateway Password (REQUIRED)
 */
if ( !defined("SENDEGA_PASSWORD") )
      define("SENDEGA_PASSWORD", "");

/**
 * @constant [Sendega] Default Sender in Message (REQUIRED)
 */
if ( !defined("SENDEGA_SENDER") )
      define("SENDEGA_SENDER",   "");

/**
 * @constant [Sendega] Message Delivery Report URL -- Your server
 */
if ( !defined("SENDEGA_DLR") )
      define("SENDEGA_DLR",      "");

/**
 * @constant [Sendega] Gateway Mode (POST or GET) -- Remote server
 */
if ( !defined("SENDEGA_MODE") )
      define("SENDEGA_MODE",     "POST");

/**
 * @constant [Sendega] Host Mode (POST or GET) -- Your server
 */
if ( !defined("SENDEGA_HOST_MODE") )
      define("SENDEGA_HOST_MODE", "POST");

/**
 * @constant [Sendega] Development Mode (Allow "mock messages", i.e. no whitelisting)
 */
if ( !defined("SENDEGA_DEVMODE") )
      define("SENDEGA_DEVMODE",  false);

///////////////////////////////////////////////////////////////////////////////
// LIBRARIES
///////////////////////////////////////////////////////////////////////////////

/**
 * Sendega Exception Wrapper
 *
 * This Exception will be thrown for all fatal errors
 * in Sendega class
 *
 * @package Sendega
 * @author Anders Evenrud <andersevenrud@gmail.com>
 */
class SendegaException extends Exception
{
}


/**
 * Sendega Methods Wrapper
 *
 * All public functions are a part of the Sendega API
 *
 * @package Sendega
 * @author Anders Evenrud <andersevenrud@gmail.com>
 */
class Sendega
{
  /*
   * Content types
   */
  const CONTENT_TYPE_WAP      = 0;    // WAP Link
  const CONTENT_TYPE_SMS      = 1;    // SMS Bulk
  const CONTENT_TYPE_SMS_CPA  = 2;    // SMS Premium (CPA)
  const CONTENT_TYPE_MMS      = 3;    // MMS Bulk
  const CONTENT_TYPE_MMS_CPA  = 4;    // MMS Premium (CPA)
  const CONTENT_TYPE_SMS_GAS  = 5;    // SMS Premium (GAS)

  /**
   * @var List of Sendega Servers (Whitelisting)
   */
  public static $SendegaServers = Array(
    '91.189.121.80',
    '91.189.121.81',
    '91.189.121.100',
    '91.189.121.104',
    '91.189.121.105',
    '91.189.121.106'
  );

  /**
   * @var Default Arguments for Delivery Report
   */
  protected static $DeliveryArguments = Array(
    "msgid"             => "", // [String]  Unique message id provided by Sendega while sending message
    "extID"             => "", // [String]  Transparent ID reference included when sending message
    "msisdn"            => "", // [Double]  The subscribers MSISDN starting with country code
    "errorcode"         => "", // [Integer] Short error code from operator – ref. table Error codes.
    "errormessage"      => "", // [String]  Long error description if error.
    "status"            => "", // [Integer] 4 = Delivered, 5 = Failed
    "statustext"        => "", // [String]  Either “delivered” or “failed”
    "operatorerrorcode" => "", // [String]  Actual error code received from operator/network
    "registered"        => "", // [String]  Date and time when message delivered to Sendega
    "sent"              => "", // [String]  Date and time when delivered to operator/ network
    "delivered"         => ""  // [String]  Date and time when message was delivered to handset
  );

  /**
   * @var Default Arguments for Recieved Message
   */
  protected static $ReceiveArguments = Array(
    "msgid"         => "", // [String]  Sendega unique message id
    "msisdn"        => "", // [Double]  The subscribers MSISDN starting with country code
    "msg"           => "", // [String]  Message content
    "mms"           => "", // [Boolean] Set to 1 if the message is MMS
    "mmsdata"       => "", // [String]  Contains Base64 encoded string of mms content as a zip file
    "shortcode"     => "", // [Integer] The short code the message was sent to.
    "mcc"           => "", // [Integer] Mobile country code
    "mnc"           => "", // [Integer] Mobile network code
    "pricegroup"    => "", // [Integer] Tariff used for MO content.
    "keyword"       => "", // [String]  The keyword used
    "keywordid"     => "", // [Integer] Sendega keyword id.
    "firstname"     => "", // [String]  For use with Number enquiry service
    "surname"       => "", // [String]  For use with Number enquiry service
    "address1"      => "", // [String]  For use with Number enquiry service
    "address2"      => "", // [String]  For use with Number enquiry service
    "zip"           => "", // [String]  For use with Number enquiry service
    "city"          => "", // [String]  For use with Number enquiry service
    "errorcode"     => "", // [Integer] Used when receiving premium MO messages
    "errormessage"  => "", // [String]  Used when receiving premium MO messages
    "registered"    => ""  // [String]  Date when Sendega received MO msg. Format: 2013-04-11T15:55:18
  );

  /**
   * @var Default Arguments for Sending Message
   */
  protected static $SendArguments = Array(
    "username"      => "",  // [String]  Your API username
    "password"      => "",  // [String]  Your API password
    "sender"        => "",  // [String]  Originating numeric or alpha numeric address for the outgoing message. Behavior may vary with Operator  integrations. Maximum 16 numbers or 11 characters using alphanumeric sender
    "destination"   => "",  // [String]  The MSISDN that the message should be sent to, starting with country code: Example 479232592. Multiple recipients are comma separated. Maximum number of destinations per request: 100. 
    "pricegroup"    => 0,   // [Integer] Tariff class for sending premium messaging (premium rate)
    "contentTypeID" => 1,   // [Integer] Type of message to send. Default is 1 for bulk sms. Ses own table with content type values description.
    "contentHeader" => "",  // [String]  Message header. Must be hex-encoded. Only used when sending binary sms, wap or mms messages. See “valid parameter values” table for content header values and description. 
    "content"       => "",  // [String]  The message content. The system automatically splits the message up into more messages if the text length exceeds 160 characters.
    "dlrUrl"        => "",  // [String]  URL used to receive delivery reports. Ignored when sending multiple recipients. See own chapter for delivery reports
    "ageLimit"      => 0,   // [Integer] Used to indicate end-user age limit for premium or adult services.
    "extID"         => "",  // [String]  Your local unique ID reference. Will be returned if dlrUrl is used.
    "sendDate"      => "",  // [String]  The message can be delivered with delayed delivery time. Format: YYYY-MM-DD HH:MI:SS
    "refID"         => "",  // [String]  Only to be used when sending premium SMS/MMS to some countries
    "priority"      => 0,   // [Integer] -1 " => " Low | 0 " => " Normal | 1 " => " High
    "gwid"          => 0,   // [Integer] Method for sending message to a specific gateway/supplier at Sendega
    "pid"           => 0,   // [Integer] Protocol ID of message
    "dcs"           => 0    // [Integer] Data Coding Scheme. Always use 0 or 16 for flash sms.
  );

  /**
   * @var Sendega Error Codes
   */
  public static $ErrorCodes = Array(
    // Delivery reports
    100 => "Successfully executed",
    101 => "Invalid MSISDN",
    102 => "Invalid fromAlpha",
    103 => "Invalid fromNumber",
    104 => "Invalid deliverytime",
    105 => "Invalid pricegroup or pricegroup not supported",
    106 => "Unknown subscriber – Remove from database",
    107 => "Absent subscriber – Remove from database",
    108 => "Subscriber busy for MT-SMS or SIM card full",
    109 => "Invalid reference ID",
    110 => "Invalid content or content length. (Message to long or illegal characters in message)",
    111 => "Subscriber barred for CPA content - Remove from database",
    112 => "Subscriber is too young – Remove from database",
    113 => "Subscriber reached monthly turnover limit",
    114 => "Subscriber is temporarily barred",
    115 => "Subscriber is permanently barred",
    116 => "Subscriber account balance to low",
    117 => "Invalid MSISDN or operator/network not covered",
    118 => "Operator returned internal error – contact Sendega for more information",
    119 => "Network timeout",
    120 => "Communication error with operator",
    121 => "Operator not supported or not reachable",
    122 => "Message queued at operator/network SMSC",
    123 => "Billing failed. Content delivered to subscriber",
    124 => "Billing successful. Content not delivered to subscriber",
    125 => "Subscriber not registered for this service at operator.",
    126 => "Message acked by carrier – awaiting new status",
    127 => "Message not delivered. Carrier returned unknown errorcode/errormessage",
    128 => "Rejected by operator",
    129 => "Message filtered by Sendega.",
    130 => "Subscriber is already registered for this service at operator",
    197 => "Prepaid account at Sendega does not have sufficient founds",
    198 => "Other/unknown operator error",
    199 => "Other/unknown Sendega error",

    // Messages
    1001 => "Not validated Wrong CID ",
    1003 => "Wrong format: pid/dcs Wrong pid or dcs values. ",
    1004 => "Erroneous typeid Parameter contentTypeID incorrect. ",
    1020 => "Fromalpha too long Alphanumeric sender value to long. Max 11 chars ",
    1021 => "Fromnumber too long Numeric sender value to long. Max 16 numbers. ",
    1022 => "Erroneous recipient, integer overflow Integer overflow value used as recipient. ",
    1023 => "No message content submitted Content parameter has no value. ",
    1024 => "Premium sms must have abbreviated number as sender Please include short code such as 2440 for Norway, 72721 for Sweden ",
    1025 => "The message sender is not allowed The sender value is barred. ",
    1026 => "Balance to low Only for prepaid customers where the balance is to low to send messages. ",
    1027 => "Message too long Content value is too long. Bulk sms max 459 characters. ",
    1028 => "Alphanumeric sender is not valid Invalid alphanumeric value or barred. ",
    1029 => "Unknown MSISDN. Remember to add country prefix ",
    1099 => "Internal error ",
    9001 => "Username and password does not match Incorrect username or password. ",
    9002 => "Account is closed Contact Sendega for more information. ",
    9004 => "Http not enabled No access or API interface not available ",
    9005 => "Smpp not enabled No access or SMPP service not available. ",
    9006 => "Ip not allowed IP address not valid for this account. IP addresses can be managed using Sendega controlpanel ",
    9007 => "Demo account empty Only used for de",
  );

  /**
   * Convert SimpleXML Object to Array
   *
   * @param  Object   $xmlObject      Source object
   * @return Array
   */
  public static function xml2array($xmlObject) {
    return json_decode(json_encode((array) $xmlObject), 1);
  }

  /**
   * Unpack MMS Data
   *
   * @param   String    $data     MMS Data (base64 encoded zip file raw-data)
   *
   * @return  Array               Attachments as assiociative array
   */
  protected static function _UnpackMMS($data) {
    if ( !class_exists('ZipArchive') ) {
      throw new SendegaException("Cannot unpack MMS: Missing library ZipArchive");
    }

    $zip_name = tempnam("mms_tmp", "zip");

    $fdata = base64_decode($data);
    if ( file_put_contents($zip_name, $fdata) ) {
      $zip = new ZipArchive();
      if ( $zip->open($zip_name) === true ) {
        for ( $i = 0; $i < $zip->numFiles; $i++ ) {
          if ( $stat = $zip->statIndex($i) ) {
            if ( $fp = $zip->getStream($stat['name']) ) {
              $idata = '';
              while ( !feof($fp) ) {
                $idata .= fread($fp, 2);
              }
              fclose($fp);

              $attachments[$stat['name']] = $idata;
            }

          }
        }
        $zip->close();
      }
    }

    @unlink($zip_name);

    return $attachments;
  }

  /**
   * Pack MMS Data
   *
   * @param   Array     $attachments      MMS Attachments ([filename => raw-data, ...] or [filename, ...])
   *
   * @return  String                      Zip-file raw-data encoded with base64 (null on error)
   */
  protected static function _PackMMS(Array $attachments) {
    if ( !class_exists('ZipArchive') ) {
      throw new SendegaException("Cannot pack MMS: Missing library ZipArchive");
    }

    $data     = null;
    $zip_name = tempnam("mms_tmp", "zip");

    $zip = new ZipArchive();
    if ( $zip->open($zip_name, ZipArchive::OVERWRITE) === true ) {
      foreach ( $attachments as $k => $v ) {
        if ( is_int($k) ) {
          $zip->addFile($v);
        } else {
          $zip->addFromString($k, $v);
        }
      }
      $zip->close();
      $data = base64_encode(file_get_contents($zip_name));
    }

    @unlink($zip_name);

    return $data;
  }

  /**
   * Wrapper for calling Sendega gateway (Gets data)
   *
   * @param   String    $url            URL
   * @param   Array     $parameters     POST/GET arguments
   * @param   String    $method         Call method
   *
   * @throws  SendegaException          On Parsing error
   * @return  String                    XML Data string
   */
  protected static function _Call($url, $parameters, $method = 'GET') {
    $result = false;

    if ( $ch = curl_init() ) {
      $query = http_build_query($parameters);

      if ( $method == 'POST' ) {
        curl_setopt($ch, CURLOPT_URL,         $url);
        curl_setopt($ch, CURLOPT_POST,        1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  $query);
      } else {
        curl_setopt($ch, CURLOPT_URL, sprintf("%s?%s", $url, $query));
      }

      curl_setopt($ch, CURLOPT_RETURNTRANSFER,  1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,  60);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);

      if ( $data = curl_exec($ch) ) {
        $result = $data;
      }

      curl_close($ch);

      if ( !preg_match("/^\</", $result) ) {
        throw new SendegaException("Not a valid XML Document: {$result}");
      }
    }

    return $result;
  }

  /**
   * Wrapper for sending messages
   *
   * @param String    $destination      Destination number (recipient)
   * @param String    $content          Message contents
   * @param Array     $args             Combine these arguments (overrides any defaults)
   *
   * @throws SendegaException
   * @return String                     Message ID on success (or 'false' on no result)
   */
  protected static function _Send($destination, $content, Array $args = Array()) {
    $defaults = Array(
      'username'    => SENDEGA_USERNAME,
      'password'    => SENDEGA_PASSWORD,
      'sender'      => SENDEGA_SENDER,
      'destination' => $destination,
      'content'     => $content,
      'dlrUrl'      => SENDEGA_DLR
    );

    $defaults  = array_merge(self::$SendArguments, $defaults);
    $arguments = array_merge($defaults, $args);

    if ( $arguments['contentTypeID'] == self::CONTENT_TYPE_SMS ) {
      $arguments['content'] = mb_convert_encoding($arguments['content'], 'ISO-8859-1', 'UTF-8');
    } else {
      $arguments['contentHeader'] = mb_convert_encoding($arguments['contentHeader'], 'ISO-8859-1', 'UTF-8');
    }

    // Validate destination
    if ( !($arguments['destination'] = preg_replace("/^\+/", "", $arguments['destination'])) ) {
      throw new SendegaException("You need to set a destination");
    }
    if ( ctype_digit($arguments['destination']) ) {
      if ( strlen($arguments['destination']) > 16 ) {
        throw new SendegaException("Invalid destination: {$arguments['destination']} -- Number too long");
      }
    } else {
      if ( strlen($arguments['destination']) > 11 ) {
        throw new SendegaException("Invalid destination: {$arguments['destination']} -- Alphanumeric Number too long");
      }

      $arguments['destination'] = mb_convert_encoding($arguments['destination'], 'ISO-8859-1', 'UTF-8');
    }

    // Validate sender
    if ( !($arguments['sender'] = preg_replace("/^\+/", "", $arguments['sender'])) ) {
      throw new SendegaException("You need to set a sender");
    }
    if ( ctype_digit($arguments['sender']) ) {
      if ( strlen($arguments['sender']) > 16 ) {
        throw new SendegaException("Invalid sender: {$arguments['sender']} -- Number too long");
      }
    } else {
      if ( !preg_match("/[A-Za-z0-9\.\-\ ]{1,11}/", $arguments['sender']) ) {
        throw new SendegaException("Invalid sender: {$arguments['sender']} -- Invalid alphanumeric combination");
      }
      $arguments['sender'] = mb_convert_encoding($arguments['sender'], 'ISO-8859-1', 'UTF-8');
    }


    // Call Sendega
    $url = sprintf("%s/Content.asmx/Send", SENDEGA_GW);
    if ( $result = self::_Call($url, $arguments, SENDEGA_MODE) ) {
      if ( $xmlDocument = simplexml_load_string($result) ) {
        if ( strtolower($xmlDocument->Success) == 'true' ) {
          return (string)$xmlDocument->MessageID;
        } else {
          if ( (int)$xmlDocument->ErrorNumber ) {
            throw new SendegaException($xmlDocument->ErrorMessage);
          }
        }
      }
    }

    return false;
  }

  /**
   * Method for checking if host is whitelisted
   *
   * @param   String      $host     Hostname or IP to check
   *
   * @return  bool                  If is whitelisted
   */
  public static function CheckWhitelist($host) {
    return SENDEGA_DEVMODE || in_array($host, self::$SendegaServers);
  }

  /**
   * Subscriber Information Inquiry (Recipient Network Information)
   *
   * This service is only available in a selected number of countries. It returns the full name and address of the
   * owner of an MSISDN Please contact Sendega support for country availability.
   *
   * @param   Double    $msisdn     The subscribers MSISDN starting with country code
   * @param   String    $lang       Output language
   *
   * @return  Array                 Result data as array (or 'false' on no result)
   */
  public static function SubscriberEnquiry($msisdn, $lang = 'no') {
    $arguments = Array(
      'username'        => SENDEGA_USERNAME,
      'password'        => SENDEGA_PASSWORD,
      'msisdn'          => $msisdn,
      'outputLanguage'  => $lang
    );

    // Call Sendega
    $url = sprintf("%s/ExtraServices/NumberEnquiry.asmx/GetSubscriberInformation", SENDEGA_GW);
    if ( $result = self::_Call($url, $arguments, SENDEGA_MODE) ) {
      if ( $xmlDocument = simplexml_load_string($result) ) {
        if ( strtolower($xmlDocument->Success) == 'true' ) {
          if ( !empty($xmlDocument->Persons) ) {
            return self::xml2array($xmlDocument->Persons[0]);
          }
        } else {
          if ( ($code = (int)$xmlDocument->ErrorCode) ) {
            $error = isset(self::$ErrorCodes[$code]) ? self::$ErrorCodes[$code] : "Unknown Error ({$code})";
            throw new SendegaException($error);
          }
        }
      }
    }

    return false;
  }

  /**
   * Subscription Service Check
   *
   * Setting up subscription service check is mandatory if you are running subscription services and is used by
   * Sendega customer service.
   *
   * @param   Double    $msisdn     The subscribers MSISDN starting with country code
   *
   * @return  Array                 Result data as array (or 'false' on no result)
   */
  public static function SubscriptionCheckService($msisdn) {
    $arguments = Array(
      'username'        => SENDEGA_USERNAME,
      'password'        => SENDEGA_PASSWORD,
      'msisdn'          => $msisdn
    );

    // Call Sendega
    $url = sprintf("%s/content.asmx", SENDEGA_GW); // FIXME
    if ( $result = self::_Call($url, $arguments, SENDEGA_MODE) ) {
      if ( $xmlDocument = simplexml_load_string($result) ) {
        return self::xml2array($xmlDocument);
      }
    }

    return false;
  }

  /**
   * Home Location Inquiry (Recipient Location Information)
   *
   * This service is available for all mobile numbers, and will return information about MCC/MNC for a given
   * number. Sendega HLR service is directly connected with the number portability databases in Norway, Sweden
   * and Denmark. For other destinations several other HLR services are implemented for best results.
   *
   * @param   Double    $msisdn     The subscribers MSISDN starting with country code
   * @param   String    $lang       Output language
   *
   * @return  Array                 Result data as array (or 'false' on no result)
   */
  public static function HomeLocationEnquiry($msisdn) {
    $arguments = Array(
      'username'        => SENDEGA_USERNAME,
      'password'        => SENDEGA_PASSWORD,
      'msisdn'          => $msisdn
    );

    // Call Sendega
    $url = sprintf("%s/ExtraServices/NumberEnquiry.asmx/HomeLocationRegistry", SENDEGA_GW);
    if ( $result = self::_Call($url, $arguments, SENDEGA_MODE) ) {
      if ( $xmlDocument = simplexml_load_string($result) ) {
        if ( strtolower($xmlDocument->Success) == 'true' ) {
          if ( !empty($xmlDocument->MsisdnInformation) ) {
            return self::xml2array($xmlDocument->MsisdnInformation);
          }
        } else {
          if ( ($code = (int)$xmlDocument->ErrorCode) != 1000 ) {
            $error = isset(self::$ErrorCodes[$code]) ? self::$ErrorCodes[$code] : "Unknown Error ({$code})";
            throw new SendegaException($error);
          }
        }
      }
    }

    return false;
  }

  /**
   * Method for parsing a Delivery Report
   *
   * @return  Array       Delivery Data as array
   */
  public static function ParseDeliveryReport() {
    $arguments = self::$DeliveryArguments;
    $params    = SENDEGA_HOST_MODE === 'POST' ? $_POST : $_GET;
    foreach ( $params as $k => $v ) {
      $arguments[$k] = $v;
    }

    // 4 = Delivered
    // 5 = Failed

    return $arguments;
  }


  /**
   * Method for parsing a Received Message
   *
   * @see     _UnpackMMS()
   *
   * @return  Array           Message data as array
   */
  public static function ParseMessage() {
    $arguments = self::$ReceiveArguments;
    $params    = SENDEGA_HOST_MODE === 'POST' ? $_POST : $_GET;
    foreach ( $params as $k => $v ) {
      $arguments[$k] = $v;
    }

    // MMS Needs to be parsed
    if ( ((int)$arguments['mms'] == 1) ) {
      $arguments['mmsdata'] = self::_UnpackMMS($arguments['mmsdata']);
    }

    return $arguments;
  }

  /**
   * Send SMS
   *
   * @param String    $destination      Destination number (recipient)
   * @param String    $content          SMS Content
   * @param Array     $_args            Arguments for _Send() (Optional)
   *
   * @throws SendegaException
   * @return String                     Message ID on success (or 'false' on no result)
   *
   * @see   _Send()
   */
  public static function SendSMS($destination, $content, Array $_args = Array()) {
    return self::_Send($destination, $content, $_args);
  }

  /**
   * Send MMS
   *
   * @param String    $destination      Destination number (recipient)
   * @param String    $content          MMS Content (Visible text)
   * @param Array     $attachments      MMS Attachments ([filename => raw-data, ...] or [filename, ...]) (Optional)
   * @param Array     $_args            Arguments for _Send() (Optional)
   *
   * @throws SendegaException
   * @return String                     Message ID on success (or 'false' on no result)
   *
   * @see   _Send()
   * @see   _PackMMS()
   */
  public static function SendMMS($destination, $content, Array $attachments = Array(), Array $_args = Array()) {
    $data = self::_PackMMS($attachments);
    return self::_Send($destination, $data, array_merge($_args, Array(
      'contentTypeID' => self::CONTENT_TYPE_MMS,
      'contentHeader' => $content
    )));
  }
}

?>
