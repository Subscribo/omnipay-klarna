<?php

namespace Omnipay\Klarna\Message;

use Klarna_Checkout_Connector;
use Klarna_Checkout_Order;
use InvalidArgumentException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Klarna\Message\AbstractCheckoutRequest;
use Omnipay\Klarna\Message\CheckoutFinalizeAuthorizeResponse;

/**
 * Class CheckoutFinalizeAuthorizeRequest
 *
 * @package Omnipay\Klarna
 */
class CheckoutFinalizeAuthorizeRequest extends AbstractCheckoutRequest
{
    public function getData()
    {
        $this->validate('sharedSecret');

        $checkoutOrderUri = $this->getCheckoutOrderUri();
        if (empty($checkoutOrderUri)) {
            $checkoutOrderUri = $this->httpRequest->query->get('klarna_order');
        }
        if (empty($checkoutOrderUri)) {
            throw new InvalidRequestException("Required parameter 'checkoutOrderUri' was not provided explicitly, corresponding value was not found in URL.");
        }
        $data = [
            'sharedSecret'  => $this->getSharedSecret(),
            'checkoutOrderUri' => $checkoutOrderUri,
        ];

        return $data;
    }

    public function sendData($data)
    {
        if (( ! is_array($data))) {
            throw new InvalidArgumentException('Data parameter should be an array');
        }
        Klarna_Checkout_Order::$baseUri = $this->getEndpointUrl();
        Klarna_Checkout_Order::$contentType = 'application/vnd.klarna.checkout.aggregated-order-v2+json';
        $connector = Klarna_Checkout_Connector::create($data['sharedSecret']);
        $order = new Klarna_Checkout_Order($connector, $data['checkoutOrderUri']);
        $order->fetch();
        if ('checkout_complete' === $order['status']) {
            $callback = $this->getProcessOrderCallback();
            $toProcess = empty($callback) ? true : $callback($order);
        } else {
            $toProcess = false;
        }
        if ($toProcess) {
            $update = ['status' => 'created'];
            $order->update($update);
        }
        $this->response = $this->createResponse(['order' => $order]);

        return $this->response;
    }

    /**
     * @return string
     */
    public function getCheckoutOrderUri()
    {
        return $this->getParameter('checkoutOrderUri');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCheckoutOrderUri($value)
    {
        return $this->setParameter('checkoutOrderUri', $value);
    }

    /**
     * @return callable|null
     */
    public function getProcessOrderCallback()
    {
        return $this->getParameter('processOrderCallback');
    }

    /**
     * @param callable $value
     * @return $this
     */
    public function setProcessOrderCallback(callable $value)
    {
        return $this->setParameter('processOrderCallback', $value);
    }

    /**
     * @param array $data
     * @return CheckoutFinalizeAuthorizeResponse
     */
    protected function createResponse($data)
    {
        return new CheckoutFinalizeAuthorizeResponse($this, $data);
    }
}
