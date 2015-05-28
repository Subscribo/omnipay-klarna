<?php

namespace Omnipay\Klarna\Message;

use Subscribo\Omnipay\Shared\Message\AbstractResponse;
use Omnipay\Klarna\Widget\CheckoutResponseWidget;

/**
 * Abstract class AbstractCheckoutResponse
 *
 * @package Omnipay\Klarna
 */
abstract class AbstractCheckoutResponse extends AbstractResponse
{
    /**
     * @return bool
     */
    public function isStatusCheckoutIncomplete()
    {
        return 'checkout_incomplete' === $this->getOrderStatus();
    }

    /**
     * @return bool
     */
    public function isStatusCheckoutComplete()
    {
        return 'checkout_complete' === $this->getOrderStatus();
    }

    /**
     * @return bool
     */
    public function isStatusCreated()
    {
        return 'created' === $this->getOrderStatus();
    }

    public function haveWidget()
    {
        return ! empty($this->data['order']['gui']['snippet']);
    }


    public function getWidget()
    {
        if (empty($this->data['order']['gui']['snippet'])) {
            return null;
        }
        return new CheckoutResponseWidget(['content' => $this->data['order']['gui']['snippet']]);
    }

    public function getOrderStatus()
    {
        if (empty($this->data['order']['status'])) {
            return null;
        }
        return $this->data['order']['status'];
    }

    public function getReservationNumber()
    {
        if (empty($this->data['order']['reservation'])) {
            return null;
        }
        return $this->data['order']['reservation'];
    }

    /**
     * @return string|null
     */
    public function getCheckoutOrderUri()
    {
        if (empty($this->data['order'])) {
            return null;
        }
        return $this->data['order']->getLocation();
    }
}
