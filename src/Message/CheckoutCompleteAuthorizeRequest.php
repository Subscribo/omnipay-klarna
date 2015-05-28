<?php

namespace Omnipay\Klarna\Message;

use Klarna_Checkout_Connector;
use Klarna_Checkout_Order;
use InvalidArgumentException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Klarna\Message\AbstractCheckoutRequest;
use Omnipay\Klarna\Message\CheckoutCompleteAuthorizeResponse;

/**
 * Class CheckoutCompleteAuthorizeRequest
 *
 * @package Omnipay\Klarna
 */
class CheckoutCompleteAuthorizeRequest extends AbstractCheckoutRequest
{
    public function getData()
    {
        session_start();
        $this->validate('sharedSecret');

        $checkoutOrderUri = $this->getCheckoutOrderUri();
        if (empty($checkoutOrderUri)) {
            $checkoutOrderUri = $this->httpRequest->query->get('klarna_order');
        }
        if (empty($checkoutOrderUri)) {
            if (empty($_SESSION['klarna_checkout'])) {
                throw new InvalidRequestException("Required parameter 'checkoutOrderUri' was not provided explicitly, corresponding value was not found in URL nor in session.");
            }
            $checkoutOrderUri = $_SESSION['klarna_checkout'];
        }
        $data = [
            'sharedSecret'  => $this->getSharedSecret(),
            'checkoutOrderUri'    => $checkoutOrderUri,
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
        $this->response = $this->createResponse(['order' => $order]);
        if (( ! $this->response->isStatusCheckoutIncomplete())) {
            unset($_SESSION['klarna_checkout']);
        }
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
     * @param array $data
     * @return CheckoutCompleteAuthorizeResponse
     */
    protected function createResponse($data)
    {
        return new CheckoutCompleteAuthorizeResponse($this, $data);
    }
}
