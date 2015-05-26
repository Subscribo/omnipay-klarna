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
        $this->merchantId = uniqid();
    }


    public function testPurchaseParameters()
    {
        if ($this->gateway->supportsPurchase()) {
            parent::testPurchaseParameters();
        }
    }


    public function testGetWidget()
    {
        $this->gateway->setMerchantId($this->merchantId);
        $this->gateway->setLocale('de_at');

        $widget = $this->gateway->getWidget();

        $this->assertInstanceOf('Omnipay\\Klarna\\Widget\\InvoiceWidget', $widget);
        $this->assertSame('', $widget->getPrice());
        $this->assertSame($this->merchantId, $widget->getMerchantId());
        $this->assertSame('de', $widget->getLanguage());
        $this->assertSame('AT', $widget->getCountry());
    }
}
