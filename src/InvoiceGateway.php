<?php

namespace Omnipay\Klarna;

use Omnipay\Klarna\AbstractGateway;
use Omnipay\Klarna\Traits\InvoiceGatewayDefaultParametersGettersAndSettersTrait;

class InvoiceGateway extends AbstractGateway
{
    use InvoiceGatewayDefaultParametersGettersAndSettersTrait;

    public function getName()
    {
        return 'Klarna Invoice';
    }

    public function getDefaultParameters()
    {
        $result = parent::getDefaultParameters();
        return $result;
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Klarna\Message\InvoiceAuthorizeRequest
     */
    public function authorize(array $parameters = [])
    {
        return $this->createRequest('Omnipay\\Klarna\\Message\\InvoiceAuthorizeRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Klarna\Message\InvoiceCaptureRequest
     */
    public function capture(array $parameters = [])
    {
        return $this->createRequest('Omnipay\\Klarna\\Message\\InvoiceCaptureRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Klarna\Message\InvoiceCheckOrderStatusRequest
     */
    public function checkOrderStatus(array $parameters = [])
    {
        return $this->createRequest('Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $parameters);
    }

}
