# QR code payment by Hungarian MNB standard

Library to generate QR payment codes for Hungarian banks (standard by Hungarian national bank (MNB)).
This library was copied and modified from the [rikudou's EU QR Payment](https://github.com/RikudouSage/QrPaymentEU)

> See [the standard](https://www.mnb.hu/letoltes/qr-kod-utmutato-20190712-en.pdf)

## Installation

Via composer: `composer require hubipe/huqrpayment`

## Usage

In the constructor you must supply IBAN which may be a string
or an instance of `hubipe\HuQrPayment\Iban\IbanInterface`.

Example with string:

```php
<?php

use hubipe\HuQrPayment\QrPayment;

$payment = new QrPayment('HU42117730161111101800000000');

```

Example with base IBAN class:

```php
<?php

use hubipe\HuQrPayment\QrPayment;
use hubipe\HuQrPayment\Iban\IBAN;

$payment = new QrPayment(new IBAN('HU42117730161111101800000000'));

```

The `IbanInterface` is useful in case you want to create an
adapter that transforms your local format (*BBAN*) to IBAN.

This package already contains Adapter for Hungarian account numbers:

```php
<?php

use hubipe\HuQrPayment\Iban\HungarianBbanAdapter;use hubipe\HuQrPayment\QrPayment;

$payment = new QrPayment(new HungarianBbanAdapter('11773016-11111018-00000000'));

```

### Setting payment details

All payment details can be set via setters:

```php
<?php

use hubipe\HuQrPayment\Enums\CharacterSet;
use hubipe\HuQrPayment\Enums\IdCode;
use hubipe\HuQrPayment\Enums\Purpose;
use hubipe\HuQrPayment\QrPayment;

$payment = new QrPayment('HU42117730161111101800000000');
$payment
    ->setIdCode(IdCode::TRANSFER_ORDER)
    ->setCharacterSet(CharacterSet::UTF_8)
    ->setBic('OTPVHUHB')
    ->setName('My company name')
    ->setAmount(53250)
    ->setCurrency('HUF')
    ->setExpiration(new DateTimeImmutable('+3 days'))
    ->setPaymentSituationIdentifier(Purpose::PURCHASE_SALE_OF_GOODS)
   ->setRemittance('Payment for goods')
   ->setShopId('SHOP1')
   ->setMerchantDeviceId('Terminal 1')
   ->setReceiptId('1234984657S')
   ->setPayeeInternalId('Payee internal identification')
   ->setLoyaltyId('GOLDEN_CUSOMER')
   ->setNavVerificationCode('FXC4');

```

## QR Code image

This library provides many implementations of QR code image using its sister library
[rikudou/qr-payment-qr-code-provider](https://github.com/RikudouSage/QrPaymentQrCodeProvider). If any supported
QR code generating library is installed, the method `getQrCode()` will return an instance of
`\Rikudou\QrPaymentQrCodeProvider\QrCode` which can be used to get an image containing the generated QR payment data.

```php
<?php

use hubipe\HuQrPayment\QrPayment;
use Endroid\QrCode\QrCode;

$payment = new QrPayment(...);

$qrCode = $payment->getQrCode();

// get the raw image data and display them in the browser
header('Content-Type: image/png');
echo $qrCode->getRawString();

// use in an img html tag
echo "<img src='{$qrCode->getDataUri()}'>";

// write to a file
$qrCode->writeToFile('/tmp/some-file.png');

// get the raw object from the underlying system
$raw = $qrCode->getRawObject();
// let's assume we're using endroid/qr-code v4
assert($raw instanceof QrCode);
// do some custom transformations
$raw->setLabelFontSize(15);
// the object is still referenced by the adapter, meaning we can now render it the same way as before
echo "<img src='{$qrCode->getDataUri()}'>";
```
