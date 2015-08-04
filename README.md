# Omnipay: Klarna

**Klarna driver for the Omnipay PHP payment processing library**

Master branch: [![Build Status](https://travis-ci.org/Subscribo/omnipay-klarna.svg?branch=master)](https://travis-ci.org/Subscribo/omnipay-klarna)
Feature Checkout branch: [![Build Status](https://travis-ci.org/Subscribo/omnipay-klarna.svg?branch=feature%2Fcheckout)](https://travis-ci.org/Subscribo/omnipay-klarna)

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+. This package implements Klarna support for Omnipay.

## Important notes:

* This is a work-in-progress, unstable version.
  Stable version has not yet been released.
* [Error handling implementation](src/Message/AbstractInvoiceRequest.php) uses this heuristics:
  if code of `KlarnaException` thrown is below 0 or above 1100, it is assumed,
  that it contains [message to be displayed to customer](https://developers.klarna.com/en/at+php/kpm/error-codes),
  otherwise technical error is assumed and the exception is rethrown.

## Requirements

* PHP 5.4+
* [Klarna API credentials](https://developers.klarna.com/en/at+php/kpm/apply-for-test-account)

## Installation

Omnipay Klarna driver is installed via [Composer](http://getcomposer.org/). To install, add it
to your `composer.json` file (you might need to add also development version of egeloen/http-adapter).

For beta version use:
```json
{
    "require": {
        "subscribo/omnipay-klarna": "^0.1.2@beta"
    }
}
```

For development version use:
```json
{
    "require": {
        "subscribo/omnipay-klarna": "^0.1.*@dev"
    }
}
```

When you want to use feature branch Checkout, use:

```json
{
    "require": {
        "subscribo/omnipay-klarna": "dev-feature/checkout",
        "klarna/checkout": "1.2",
        "subscribo/psr-http-message-tools": ">=0.3.1 <0.5"
    }
}
```

After updating composer.json run composer update to update your dependencies:
```sh
    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update
```

If you want to run online tests, you also need to set environment variables `KLARNA_MERCHANT_ID`
and `KLARNA_SHARED_SECRET` with your Klarna API test credentials.
These are also needed for examples, provided in docs/example/invoice
(they are used usually around lines 12-13 of the examples, but you can modify them and provide credentials differently).

## Basic Usage

The following gateways are provided by this package:

* `Klarna\Invoice`
* `Klarna\Checkout` (only available in branch dev-feature/checkout)

Gateways in this package have following required options:

* `merchantId`
* `sharedSecret`

To get those please contact your Klarna representative.

Additionally these options could be specified:

* `testMode`
* `country`
* `language`
* `currency`

You can set up country, language and currency (for supported countries) at once using `setLocale()` method.

For meaning of `testMode` see general [Omnipay documentation](https://thephpleague.com/omnipay)

### Usage of gateway `Klarna\Invoice`

Gateway `Klarna\Invoice` supports these request-sending methods:

* `authorize()`
* `capture()`
* `checkOrderStatus()`

For use and expected parameters see unit tests and example code

#### authorize()

You may see [official documentation](https://developers.klarna.com/en/at+php/kpm/invoice-part-payment/3-create-order)
and related links for additional information.

Method `authorize()` may have an array with parameters as its optional argument,
or parameters could be specified via setters on returned `InvoiceAuthorizeRequest` object.

These are required parameters:

* `merchantId` *(may be inherited from gateway)*
* `sharedSecret` *(may be inherited from gateway)*
* `country` *(may be inherited from gateway)*
* `language` *(may be inherited from gateway)*
* `currency` *(may be inherited from gateway)*
* `card`
* `items`

These are optional parameters:

* `amount`
* `transactionId` alias for `orderId1`
* `orderId2`

If `amount` is not provided, it is set to `-1` and `items` are used to calculate the amount.
You may use method `calculateAmount()` to check this value.

##### Card

Customer personal and contact data could be sent via `card` parameter or `setCard()` setter method,
either in form of an array or a
[`Subscribo\Omnipay\Shared\CreditCard`](https://github.com/Subscribo/omnipay-subscribo-shared/blob/master/src/Shared/CreditCard.php) object
extending [`Omnipay\Common\CreditCard`](http://omnipay.thephpleague.com/api/cards)

Following card parameters might be used:

**Personal parameters:**

* `gender` for DE/AT/NL
* `birthday` for DE/AT/NL
* `nationalIdentificationNumber` - personal number for other countries; also may by used for company number when needed

**Address parameters:**

* `phone`
* `mobile`
* `firstName`
* `lastName`
* `postCode`
* `city`
* `country`
* `company`
* `address1`
* `address2`

For DE/AT/NL you can pass house number as `address2`,
for other countries is `address2` simply attached with space to `address1`.

You can pass also different shipping address using shipping variants of parameters, i.e. `shippingFirstName`...

##### Items

Shopping cart items should be sent via `items` parameter or `setItems()` setter method,
either in form of an array of arrays, or an array of
[`Subscribo\Omnipay\Shared\Item`](https://github.com/Subscribo/omnipay-subscribo-shared/blob/master/src/Shared/Item.php) objects
or a [`Subscribo\Omnipay\Shared\ItemBag`](https://github.com/Subscribo/omnipay-subscribo-shared/blob/master/src/Shared/ItemBag.php) object.

Following item parameters might be used:

* `name` ("title")
* `identifier` ("article number")
* `quantity`
* `price`
* `taxPercent` ("VAT")
* `discountPercent` ("discount")
* `flags`

##### Sending

When all required parameters are set on `InvoiceAuthorizeRequest` object,
you can call its method `send()`, to receive `InvoiceAuthorizeResponse` object.

`InvoiceAuthorizeResponse` object has following methods:

* `isSuccessful()` - alias for `isAccepted()`
* `isWaiting()` - alias for `isPending()`
* `getInvoiceStatus()`
* `getReservationNumber`
* `getMessage()`
* `getCode()`

In case authorization was successful, you can use `getReservationNumber()` for parameter of `capture()` gateway call.
In case authorization is waiting, you can use `getReservationNumber()`
for parameter of later `checkOrderStatus()` gateway call.

###### Errors and exceptions

If authorization was rejected, `getMessage()` should contain displayable message for customer
and `getCode()` exception code number.
In case of technical error a `KlarnaException` should be thrown.

Important note: in the background, `KlarnaException` is thrown for both technical errors
and for authorization rejections. However, if the code is below 0 or over 1100,
it is converted to `InvoiceAuthorizeResponse`.
See https://developers.klarna.com/en/at+php/kpm/error-codes for more details.

#### capture()

Method `capture()` may have an array with parameters as its optional argument,
or parameters could be specified via setters on returned `InvoiceAuthorizeRequest` object.

These are required parameters:

* `merchantId` *(may be inherited from gateway)*
* `sharedSecret` *(may be inherited from gateway)*
* `country` *(may be inherited from gateway)*
* `language` *(may be inherited from gateway)*
* `currency` *(may be inherited from gateway)*
* `reservationNumber`

These are optional parameters:

* `OCRNumber`
* `flags`
* `transactionId` alias for `orderId1`
* `orderId2`

##### Sending

When all required parameters are set on `InvoiceCaptureRequest` object,
you can call its method `send()`, to receive `InvoiceCaptureResponse` object.

`InvoiceCaptureResponse` object has following methods:

* `isSuccessful()` - alias for `isAccepted()`
* `getTransactionReference` alias for `getInvoiceNumber()`
* `getRiskStatus()`
* `getMessage()`
* `getCode()`

For error and exception handling see [Errors and Exceptions](#errors-and-exceptions) above.

#### checkOrderStatus()

Method `checkOrderStatus()` may have an array with parameters as its optional argument,
or parameters could be specified via setters on returned `InvoiceCheckOrderStatusRequest` object.

These are required parameters:

* `merchantId` *(may be inherited from gateway)*
* `sharedSecret` *(may be inherited from gateway)*
* `country` *(may be inherited from gateway)*
* `language` *(may be inherited from gateway)*
* `currency` *(may be inherited from gateway)*

One of these parameters is also required:

* `reservationNumber`
* `invoiceNumber`
* `orderId` or its alias `transactionId`

##### Sending

When all required parameters are set on `InvoiceCheckOrderStatusRequest` object,
you can call its method `send()`, to receive `InvoiceCheckOrderStatusResponse` object.

`InvoiceCaptureResponse` object has following methods:

* `isSuccessful()` - alias for `isAccepted()`
* `isWaiting()` - alias for `isPending()`
* `isDenied()`
* `getOrderStatus()`
* `getMessage()`
* `getCode()`

For error and exception handling see [Errors and Exceptions](#errors-and-exceptions) above.

### OrderIds

You can set up two custom reference identifiers on each invoice - `orderId1` and `orderId2`.
For `InvoiceAuthorizeRequest` and `InvoiceCaptureRequest` parameter `transactionId` is an alias for `orderId1`.

You can search for unique orderId (whether orderId1 or orderId2) setting parameter `orderId`
in `InvoiceCheckOrderStatusRequest`. In `InvoiceCheckOrderStatusRequest` is `transactionId` an alias for `orderId`.

### Example code

For example code see:

* GET [Prepare page](docs/example/invoice/prepare.php)
* POST [Authorize page](docs/example/invoice/authorize.php)
* GET [Check page](docs/example/invoice/check.php)
* GET [Capture page](docs/example/invoice/capture.php)

### InvoiceWidget

Both `InvoiceGateway` and `InvoiceAuthorizeRequest` have method `getWidget()`, which is returning `InvoiceWidget`,
with (among others) following methods:

* `getDefaultParameters()`
* `getRequiredParameters()`
* `isRenderable()`
* `render()`
* `renderPaymentMethodWidget()`
* `renderLogoUrl()`
* `renderTooltip()`
* `renderTermsInvoiceHtml()`
* `renderTermsConsentHtml()`
* `renderTermsAccountHtml()`

Class also contains following static methods:

* `assemblePaymentMethodWidgetHtml()`
* `assembleLogoUrl()`
* `assembleTooltipHtml()`
* `assembleTermsInvoiceObject()`
* `assembleTermsConsentObject()`
* `assembleTermsAccountObject()`
* `assembleTermsConsentText()`
* `assembleLoadJavascript()`
* `assembleLoadTermsJavascript()`

Parameters for rendering methods could be set via constructor, setter, or passed as keys of argument array.
Some parameters could be passed only as keys of argument array.

Default rendering method `render()` is an alias for `renderPaymentMethodWidget()` - rendering of
[part-payment / payment method widget](https://developers.klarna.com/en/at+php/kpm/payment-method-widget)

Required parameters:
* `merchantId` *(may be inherited from gateway or `InvoiceAuthorizeRequest`)*
* `country` *(may be inherited from gateway or `InvoiceAuthorizeRequest`)*
* `language` *(may be inherited from gateway or `InvoiceAuthorizeRequest`)*
* `price` *(may be inherited from `InvoiceAuthorizeRequest` - `amount` or calculated amount)*

Optional parameters:
* `charge` - "invoice-fee" - numeric string in format `'0.95'`
* `layout` - argument array key only; if not set the default may be affected by `color` parameter
* `width` - argument array key only
* `height` - argument array key only
* `style` - argument array key only; additional style setting of container `<div>`

For use and expected parameters of other rendering methods you may see the [code](src/Widget/InvoiceWidget.php),
[example code](docs/example/invoice/prepare.php) and unit tests as well as
[official documentation](https://developers.klarna.com/en/at+php/kpm/guidelines) and related links.

### Usage of gateway `Klarna\Checkout`

Gateway `Klarna\Checkout` supports these request-sending methods:

* `authorize()`
* `completeAuthorize()`
* `finalizeAuthorize()` (for PUSH request from Klarna)

For use and expected parameters see unit tests and example code

### General instructions

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

### Testing

For testing you need to install development dependencies:
```sh
    cd path/to/your/project
    cd vendor/subscribo/omnipay-klarna
    composer update
```

If you want to run both online and offline tests, run just `phpunit`.

If you want to run offline (not requiring internet connection) tests only, run `phpunit tests/offline`

If you want to run online tests, you also need to set environment variables `KLARNA_MERCHANT_ID`
and `KLARNA_SHARED_SECRET` with your Klarna API test credentials.

## Support

### General

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release announcements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

### Omnipay Klarna driver specific

If you believe you have found a bug, please send us an e-mail (packages@subscribo.io)
or report it using the [GitHub issue tracker](https://github.com/Subscribo/omnipay-klarna/issues),
or better yet, fork the library and submit a pull request.

### Links

* Klarna developers documentation: https://developers.klarna.com
* Omnipay Library web page: http://omnipay.thephpleague.com
* Omnipay Library Github Project: https://github.com/thephpleague/omnipay
