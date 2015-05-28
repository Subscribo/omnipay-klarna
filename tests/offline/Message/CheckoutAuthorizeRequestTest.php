<?php


namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\CheckoutAuthorizeRequest;

class CheckoutAuthorizeRequestTest extends TestCase
{
    public function setUp()
    {
        $this->merchantId = uniqid();
        $this->sharedSecret = uniqid();
    }

    public function testGetData()
    {
        $this->markTestSkipped("Skipped until implementation of Session handling callbacks");

        $urlBase = 'https://your.web.site.example';
        $request = new CheckoutAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->assertSame($request, $request->setTestMode(true));
        $this->assertSame($request, $request->setMerchantId($this->merchantId));
        $this->assertSame($request, $request->setSharedSecret($this->sharedSecret));
        $this->assertSame($request, $request->setLocale('de_AT'));
        $this->assertSame($request, $request->setTermsUrl($urlBase.'/about/terms'));
        $this->assertSame($request, $request->setAuthorizeUrl($urlBase.'/path/to/example/checkout/authorize'));
        $this->assertSame($request, $request->setReturnUrl($urlBase.'/path/to/example/checkout/complete_authorize'));
        $this->assertSame($request, $request->setPushUrl($urlBase.'/scripts/checkout/push'));

        $data = $request->getData();
        $this->assertSame('AT', $data['create']['purchase_country']);
        $this->assertSame('EUR', $data['create']['purchase_currency']);
        $this->assertSame('de-at', $data['create']['locale']);
        $this->assertSame($this->merchantId, $data['create']['merchant']['id']);
        $this->assertSame($urlBase.'/about/terms', $data['create']['merchant']['terms_uri']);
        $this->assertSame($urlBase.'/path/to/example/checkout/authorize', $data['create']['merchant']['checkout_uri']);
        $this->assertSame(
            $urlBase.'/path/to/example/checkout/complete_authorize',
            $data['create']['merchant']['confirmation_uri']
        );
        $this->assertSame($urlBase.'/scripts/checkout/push', $data['create']['merchant']['push_uri']);


        $this->assertSame('de', $request->getLanguage());
        $this->assertSame('AT', $request->getCountry());
        $this->assertSame('EUR', $request->getCurrency());

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSendWrongData()
    {
        $request = new CheckoutAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->setTestMode(true);
        $request->sendData(null);
    }

    public function testSetters()
    {
        $urlBase = 'https://your.web.site.example';

        $request = new CheckoutAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->assertEmpty($request->getTestMode());
        $this->assertSame($request, $request->setTestMode(true));
        $this->assertTrue($request->getTestMode());

        $this->assertSame([], $request->getAdditionalQueryParameters());
        $this->assertNull($request->getAuthorizeUrl());
        $this->assertNull($request->getPushUrl());
        $this->assertNull($request->getTermsUrl());

        $this->assertSame($request, $request->setAdditionalQueryParameters(['some' => 'value']));
        $this->assertSame(['some' => 'value'], $request->getAdditionalQueryParameters());
        $this->assertSame($request, $request->setAuthorizeUrl($urlBase.'/path/to/example/checkout/authorize'));
        $this->assertSame($urlBase.'/path/to/example/checkout/authorize', $request->getAuthorizeUrl());
        $this->assertSame($request, $request->setPushUrl($urlBase.'/scripts/checkout/push'));
        $this->assertSame($urlBase.'/scripts/checkout/push', $request->getPushUrl());
        $this->assertSame($request, $request->setTermsUrl($urlBase.'/about/terms'));
        $this->assertSame($urlBase.'/about/terms', $request->getTermsUrl());
    }
}
