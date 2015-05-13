<?php

namespace Omnipay\Klarna;

use Omnipay\Tests\GatewayTestCase;
use Omnipay\Klarna\AbstractGateway;
use Omnipay\Klarna\Traits\AbstractGatewayDefaultParametersGettersAndSettersTrait;

class AbstractGatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        $this->gateway = new ExtendedAbstractGatewayForTesting($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setTestMode(true);
    }

    public function testPurchaseParameters()
    {
        if ($this->gateway->supportsPurchase()) {
            parent::testPurchaseParameters();
        }
    }

    public function testSetLocale()
    {
        $this->assertSame('', $this->gateway->getLanguage());
        $this->assertSame('', $this->gateway->getCountry());
        $this->assertSame('', $this->gateway->getCurrency());
        $this->assertSame($this->gateway, $this->gateway->setLocale('de_AT'));
        $this->assertSame('de', $this->gateway->getLanguage());
        $this->assertSame('AT', $this->gateway->getCountry());
        $this->assertSame('EUR', $this->gateway->getCurrency());
        $this->assertSame($this->gateway, $this->gateway->setLocale('no-NO'));
        $this->assertSame('no', $this->gateway->getLanguage());
        $this->assertSame('NO', $this->gateway->getCountry());
        $this->assertSame('NOK', $this->gateway->getCurrency());
        $this->assertSame($this->gateway, $this->gateway->setLocale('da_dk'));
        $this->assertSame('da', $this->gateway->getLanguage());
        $this->assertSame('DK', $this->gateway->getCountry());
        $this->assertSame('DKK', $this->gateway->getCurrency());
        $this->assertSame($this->gateway, $this->gateway->setLocale('SV_SE'));
        $this->assertSame('sv', $this->gateway->getLanguage());
        $this->assertSame('SE', $this->gateway->getCountry());
        $this->assertSame('SEK', $this->gateway->getCurrency());
        $this->assertSame($this->gateway, $this->gateway->setLocale('en_GB'));
        $this->assertSame('en', $this->gateway->getLanguage());
        $this->assertSame('GB', $this->gateway->getCountry());
        $this->assertSame('', $this->gateway->getCurrency());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetLocaleInvalidArgument()
    {
        $this->gateway->setLocale('en');
    }
}


class ExtendedAbstractGatewayForTesting extends AbstractGateway
{
    use AbstractGatewayDefaultParametersGettersAndSettersTrait;
}
