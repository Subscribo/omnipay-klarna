<?php


namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\CheckoutAuthorizeRequest;

class CheckoutCompleteAuthorizeRequestTest extends TestCase
{
    public function setUp()
    {
        $this->merchantId = uniqid();
        $this->sharedSecret = uniqid();
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
        $request = new CheckoutCompleteAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->assertEmpty($request->getTestMode());
        $this->assertSame($request, $request->setTestMode(true));
        $this->assertTrue($request->getTestMode());

        $this->assertNull($request->getCheckoutOrderUri());
        $this->assertSame($request, $request->setCheckoutOrderUri('SomeUri'));
        $this->assertSame('SomeUri', $request->getCheckoutOrderUri());
    }
}
