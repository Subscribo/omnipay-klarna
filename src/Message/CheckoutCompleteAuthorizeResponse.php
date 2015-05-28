<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Klarna\Message\AbstractCheckoutResponse;

/**
 * Class CheckoutCompleteAuthorizeResponse
 *
 * @package Omnipay\Klarna
 */
class CheckoutCompleteAuthorizeResponse extends AbstractCheckoutResponse
{
    public function isSuccessful()
    {
        return $this->isStatusCheckoutComplete();
    }
}
