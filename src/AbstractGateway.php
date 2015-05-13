<?php

namespace Omnipay\Klarna;

use Subscribo\Omnipay\Shared\AbstractGateway as Base;

/**
 * Abstract Class AbstractGateway
 *
 * @package Omnipay\Klarna
 */
abstract class AbstractGateway extends Base
{

    public function getName()
    {
        return 'Klarna';
    }

    public function getDefaultParameters()
    {
        return [
            'merchantId' => '',
            'sharedSecret' => '',
            'language' => '',
            'country' => '',
            'testMode' => false,
        ];
    }
}
