<?php

namespace Omnipay\Klarna\Message;

use KlarnaFlags;
use Omnipay\Klarna\Message\AbstractInvoiceResponse;

/**
 * Class InvoiceCheckOrderStatusResponse
 *
 * @package Omnipay\Klarna
 */
class InvoiceCheckOrderStatusResponse extends AbstractInvoiceResponse
{
    public function isSuccessful()
    {
        return $this->isAccepted();
    }


    public function isWaiting()
    {
        return $this->isPending();
    }


    public function isAccepted()
    {
        return (strval(KlarnaFlags::ACCEPTED) === strval($this->getOrderStatus()));
    }


    public function isPending()
    {
        return (strval(KlarnaFlags::PENDING) === strval($this->getOrderStatus()));
    }


    public function isDenied()
    {
        return (strval(KlarnaFlags::DENIED) === strval($this->getOrderStatus()));
    }


    public function getOrderStatus()
    {
        if (is_array($this->data) and isset($this->data[0])) {
            return $this->data[0];
        }
        return null;
    }
}
