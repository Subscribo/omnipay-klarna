<?php

namespace Omnipay\Klarna;

use Omnipay\Tests\GatewayTestCase;

use Omnipay\Klarna\InvoiceGateway;

class InvoiceGatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        $this->gateway = new InvoiceGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setTestMode(true);
    }

    public function testPurchaseParameters()
    {
        if ($this->gateway->supportsPurchase()) {
            parent::testPurchaseParameters();
        }
    }
}
