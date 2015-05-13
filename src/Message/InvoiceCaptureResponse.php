<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Klarna\Message\AbstractInvoiceResponse;

/**
 * Class InvoiceCaptureResponse
 *
 * @package Omnipay\Klarna
 */
class InvoiceCaptureResponse extends AbstractInvoiceResponse
{
    public function isSuccessful()
    {
        return $this->isAccepted();
    }


    public function getTransactionReference()
    {
        return $this->getInvoiceNumber();
    }


    public function isAccepted()
    {
        return ('ok' === strtolower($this->getRiskStatus()));
    }


    public function getRiskStatus()
    {
        if (is_array($this->data) and isset($this->data[0])) {
            return $this->data[0];
        }
        return null;
    }


    public function getInvoiceNumber()
    {
        if (is_array($this->data) and isset($this->data[1])) {
            return $this->data[1];
        }
        return null;
    }
}
