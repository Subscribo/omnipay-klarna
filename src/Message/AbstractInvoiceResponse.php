<?php

namespace Omnipay\Klarna\Message;

use KlarnaException;
use Subscribo\Omnipay\Shared\Message\AbstractResponse;

/**
 * Class AbstractInvoiceResponse
 *
 * @package Omnipay\Klarna
 */
abstract class AbstractInvoiceResponse extends AbstractResponse
{
    public function getMessage()
    {
        if ($this->data instanceof KlarnaException) {
            return $this->data->getMessage();
        }
        return null;
    }


    public function getCode()
    {
        if ($this->data instanceof KlarnaException) {
            return $this->data->getCode();
        }
        return null;
    }
}
