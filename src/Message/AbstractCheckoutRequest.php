<?php

namespace Omnipay\Klarna\Message;

use Subscribo\Omnipay\Shared\Message\AbstractRequest;
use Omnipay\Klarna\Traits\CheckoutGatewayDefaultParametersGettersAndSettersTrait;

/**
 * Abstract class AbstractCheckoutRequest
 *
 * @package Omnipay\Klarna
 */
abstract class AbstractCheckoutRequest extends AbstractRequest
{
    use CheckoutGatewayDefaultParametersGettersAndSettersTrait;

    protected function getEndpointUrl()
    {
        if ($this->getTestMode()) {
            return 'https://checkout.testdrive.klarna.com/checkout/orders';
        }
        throw new \Exception('Live mode not implemented yet.'); //todo obtain live URL and implement
    }
}
