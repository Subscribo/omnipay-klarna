<?php

namespace Omnipay\Klarna;

use Omnipay\Klarna\AbstractGateway;
use Omnipay\Klarna\Traits\CheckoutGatewayDefaultParametersGettersAndSettersTrait;
use Omnipay\Klarna\Widget\CheckoutWidget;

/**
 * Class CheckoutGateway
 *
 * @package Omnipay\Klarna
 */
class CheckoutGateway extends AbstractGateway
{
    use CheckoutGatewayDefaultParametersGettersAndSettersTrait;

    public function getName()
    {
        return 'Klarna Checkout';
    }

    public function getDefaultParameters()
    {
        $result = parent::getDefaultParameters();
        return $result;
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Klarna\Message\CheckoutAuthorizeRequest
     */
    public function authorize(array $parameters = [])
    {
        return $this->createRequest('Omnipay\\Klarna\\Message\\CheckoutAuthorizeRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Klarna\Message\CheckoutCompleteAuthorizeRequest
     */
    public function completeAuthorize(array $parameters = [])
    {
        return $this->createRequest('Omnipay\\Klarna\\Message\\CheckoutCompleteAuthorizeRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Klarna\Message\CheckoutFinalizeAuthorizeRequest
     */
    public function finalizeAuthorize(array $parameters = [])
    {
        return $this->createRequest('Omnipay\\Klarna\\Message\\CheckoutFinalizeAuthorizeRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return CheckoutWidget
     */
    public function getWidget(array $parameters = [])
    {
        $parameters = array_replace($this->getParameters(), $parameters);
        $widget = new CheckoutWidget($parameters);
        return $widget;
    }
}
