<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Klarna\Message\AbstractCheckoutResponse;

/**
 * Class CheckoutAuthorizeResponse
 *
 * @package Omnipay\Klarna
 */
class CheckoutAuthorizeResponse extends AbstractCheckoutResponse
{
    public function isSuccessful()
    {
        return $this->isStatusCheckoutIncomplete();
    }
}
