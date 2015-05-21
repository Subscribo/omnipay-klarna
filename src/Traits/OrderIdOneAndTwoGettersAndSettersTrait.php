<?php

namespace Omnipay\Klarna\Traits;

/**
 * Trait OrderIdOneAndTwoGettersAndSettersTrait
 *
 * @package Omnipay\Klarna
 */
trait OrderIdOneAndTwoGettersAndSettersTrait
{
    /**
     * Parameter transactionId is for this request class an alias for parameter orderId1
     *
     * @return string|null
     */
    public function getTransactionId()
    {
        return $this->getOrderId1();
    }

    /**
     * Parameter transactionId is for this request class an alias for parameter orderId1
     *
     * @param string|null $value
     * @return $this
     */
    public function setTransactionId($value)
    {
        return $this->setOrderId1($value);
    }

    /**
     * @return string|null
     */
    public function getOrderId1()
    {
        return $this->getParameter('orderId1');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setOrderId1($value)
    {
        return $this->setParameter('orderId1', $value);
    }

    /**
     * @return string|null
     */
    public function getOrderId2()
    {
        return $this->getParameter('orderId2');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setOrderId2($value)
    {
        return $this->setParameter('orderId2', $value);
    }
}
