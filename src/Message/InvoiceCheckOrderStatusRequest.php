<?php

namespace Omnipay\Klarna\Message;

use Klarna;
use InvalidArgumentException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Klarna\Message\AbstractInvoiceRequest;
use Omnipay\Klarna\Message\InvoiceCheckOrderStatusResponse;
use Omnipay\Klarna\Traits\InvoiceGatewayDefaultParametersGettersAndSettersTrait;

/**
 * Class InvoiceCheckOrderStatusRequest
 *
 * @package Omnipay\Klarna
 *
 * @method \Omnipay\Klarna\Message\InvoiceCheckOrderStatusResponse send() send()
 */
class InvoiceCheckOrderStatusRequest extends AbstractInvoiceRequest
{
    use InvoiceGatewayDefaultParametersGettersAndSettersTrait;

    /**
     * @return string|int
     */
    public function getReservationNumber()
    {
        return $this->getParameter('reservationNumber');
    }

    /**
     * @param string|int $value
     * @return $this;
     */
    public function setReservationNumber($value)
    {
        return $this->setParameter('reservationNumber', $value);
    }

    /**
     * @return string|int
     */
    public function getInvoiceNumber()
    {
        return $this->getParameter('invoiceNumber');
    }

    /**
     * @param string|int $value
     * @return $this
     */
    public function setInvoiceNumber($value)
    {
        return $this->setParameter('invoiceNumber', $value);
    }

    /**
     * @return string|int
     */
    public function getOrderId()
    {
        return $this->getParameter('orderId');
    }

    /**
     * @param string|int $value
     * @return $this
     */
    public function setOrderId($value)
    {
        return $this->setParameter('orderId', $value);
    }

    /**
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('merchantId', 'sharedSecret', 'country', 'language', 'currency');
        $data = $this->getParameters();
        if (empty($data['reservationNumber'])
            and empty($data['invoiceNumber'])
            and empty($data['orderId'])
            and empty($data['transactionId'])
        ) {
            throw new InvalidRequestException('One of parameters: reservationNumber, invoiceNumber, orderId, transactionId need to be specified and non empty.');
        }
        return $data;
    }

    /**
     * @param array $data
     * @return InvoiceCheckOrderStatusResponse
     * @throws \InvalidArgumentException
     */
    public function sendData($data)
    {
        $k = $this->createKlarnaConnector($data);
        $type = 0;
        if (( ! empty($data['reservationNumber']))) {
            $id = $data['reservationNumber'];
        } elseif (( ! empty($data['invoiceNumber']))) {
            $id = $data['invoiceNumber'];
        } elseif (( ! empty($data['orderId']))) {
            $id = $data['orderId'];
            $type = 1;
        } elseif (( ! empty($data['transactionId']))) {
            $id = $data['transactionId'];
            $type = 1;
        } else {
            throw new InvalidArgumentException('One of reservationNumber, invoiceNumber, orderId or transactionId need to be provided');
        }

        $result = $k->checkOrderStatus($id, $type);

        $this->response = $this->createResponse([$result]);

        return $this->response;
    }

    /**
     * @param array $data
     * @return InvoiceCheckOrderStatusResponse
     */
    protected function createResponse(array $data)
    {
        return new InvoiceCheckOrderStatusResponse($this, $data);
    }
}
