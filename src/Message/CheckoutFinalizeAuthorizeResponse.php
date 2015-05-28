<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Klarna\Message\AbstractCheckoutResponse;

/**
 * Class CheckoutFinalizeAuthorizeResponse
 *
 * @package Omnipay\Klarna
 */
class CheckoutFinalizeAuthorizeResponse extends AbstractCheckoutResponse
{
    public function isSuccessful()
    {
        return $this->isStatusCreated();
    }
}
