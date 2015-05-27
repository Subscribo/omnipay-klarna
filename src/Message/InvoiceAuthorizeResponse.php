<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Klarna\Message\AbstractInvoiceResponse;
use KlarnaFlags;

/**
 * Class InvoiceAuthorizeResponse
 *
 * @package Omnipay\Klarna
 */
class InvoiceAuthorizeResponse extends AbstractInvoiceResponse
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
        return (strval(KlarnaFlags::ACCEPTED) === strval($this->getInvoiceStatus()));
    }


    public function isPending()
    {
        return (strval(KlarnaFlags::PENDING) === strval($this->getInvoiceStatus()));
    }

    public function getInvoiceStatus()
    {
        if (is_array($this->data) and isset($this->data[1])) {
            return $this->data[1];
        }
        return null;
    }


    public function getReservationNumber()
    {
        if (is_array($this->data) and isset($this->data[0])) {
            return $this->data[0];
        }
        return null;
    }
}
