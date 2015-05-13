# Omnipay: Klarna

**Klarna driver for the Omnipay PHP payment processing library**

[![Build Status](https://travis-ci.org/Subscribo/omnipay-klarna.svg?branch=master)](https://travis-ci.org/Subscribo/omnipay-klarna)

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+. This package implements Klarna support for Omnipay.

## Important note:

This is a work-in-progress, unstable version.
Stable version has not yet been released.

## Requirements

* PHP 5.4+
* [Klarna API credentials](https://developers.klarna.com/en/at+php/kpm/apply-for-test-account)

## Installation

Omnipay Klarna driver is installed via [Composer](http://getcomposer.org/). To install, add it
to your `composer.json` file (you might need to add also development version of egeloen/http-adapter):

```json
{
    "require": {
        "subscribo/omnipay-klarna": "^0.1.0@alpha",
        "egeloen/http-adapter": "^0.8@dev"
    }
}
```

And run composer to update your dependencies:
```sh
    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update
```

If you want to run online tests, you also need to set environment variables KLARNA_MERCHANT_ID and KLARNA_SHARED_SECRET with your Klarna API test credentials.
These are also needed for examples, provided in docs/example/invoice (they are used usually around lines 14-15 of the examples, but you can modify it and provide the credentials differently).

## Basic Usage

The following gateways are provided by this package:

* Klarna\Invoice

Gateways in this package have following required options:

* merchantId
* sharedSecret

To get those please contact your Klarna representative.

Additionally these options could be specified:

* testMode
* country
* language
* currency

You can set up country, language and currency (for supported countries) at once using setLocale() method

For meaning of testMode see general [Omnipay documentation](https://thephpleague.com/omnipay)

### Usage of gateway Klarna\Invoice

Gateway Klarna\Checkout supports these request-sending methods:

* authorize()
* capture()
* checkOrderStatus()

For use and expected parameters see unit tests and example code

### Example code

For example code see:

* GET [Prepare page](docs/example/invoice/prepare.php)
* POST [Authorize page](docs/example/invoice/authorize.php)
* GET [Check page](docs/example/invoice/check.php)
* GET [Capture page](docs/example/invoice/capture.php)

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

If you want to run both online and offline tests, run just phpunit.

If you want to run offline (not requiring internet connection) tests only, run:
```sh
    phpunit tests/offline
```

If you want to run online tests, you also need to set environment variables KLARNA_MERCHANT_ID and KLARNA_SHARED_SECRET with your Klarna API test credentials.

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

* Omnipay Library web page: http://omnipay.thephpleague.com
* Omnipay Library Github Project: https://github.com/thephpleague/omnipay
