# Sendega.php

Sendega SSM/MMS API Library for PHP

## About

Sendega is a Norwegian SMS/MMS solution provider.

This library uses the API to send and recieve messages (and other extra API methods)

## Requirements

Requires PHP 5.2+ with mbstring, ctype, curl, xml, json and zip (for MMS)

This library expects your encoding to be **UTF-8**


## Installation

Clone this repository directly to your project or use *Composer*:

```
     "require": {
         "andersevenrud/sendega.php": "dev-master"
     }
```

## Documentation

See `examples/server.php` for recieving messages and delivery reports

See `examples/client.php` for sending of messages

### Methods

Simplified overview, full code documentation in `Sendega.php`

```php
Sendega::SubscriptionCheckService($number)      // Check subscription service
Sendega::SubscriberEnquiry($number, $language)  // Subscriber Information Inquiry (Recipient Network Information)
Sendega::HomeLocationEnquiry($number)           // Home Location Inquiry (Recipient Location Information)
Sendega::ParseDeliveryReport()                  // Parses incoming delivery report
Sendega::ParseMessage()                         // Parses incoming message (SMS/MMS)
Sendega::SendSMS($number, $text)                // Sends SMS message
Sendega::SendMMS($number, $text, $attachments)  // Sends MMS message
```

# Links

* [Sendega](http://www.sendega.no/)
* [Sendega v2.3 API](http://controlpanel.sendega.com/Content/Sendega%20-%20API%20documentation%20v2.3.pdf)
* [Author Homepage](http://andersevenrud.github.io/)
* [Composer Package Homepage](https://packagist.org/packages/andersevenrud/sendega.php)

# License

```

Sendega.php - Copyright (c) 2014, Anders Evenrud <andersevenrud@gmail.com>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met: 

1. Redistributions of source code must retain the above copyright notice, this
   list of conditions and the following disclaimer. 
2. Redistributions in binary form must reproduce the above copyright notice,
   this list of conditions and the following disclaimer in the documentation
   and/or other materials provided with the distribution. 

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

```
