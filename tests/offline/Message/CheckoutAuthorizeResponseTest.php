<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\CheckoutAuthorizeResponse;
use Omnipay\Klarna\Message\CheckoutAuthorizeRequest;

class CheckoutAuthorizeResponseTest extends TestCase
{
    public function setUp()
    {
        $this->request = new CheckoutAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTestMode(true);
    }

    protected function getMockOrder(array $data = [])
    {
        $builder = $this->getMockBuilder('Klarna_Checkout_Order');
        $builder->disableOriginalConstructor();
        $builder->setMethods(['getLocation', 'offsetGet', 'offsetExists']);
        $order = $builder->getMock();
        $order->expects($this->any())->method('getLocation')->will($this->returnValue('SomeLocation'));
        $order->expects($this->any())->method('offsetGet')->will($this->returnCallback(
            function & ($key) use (&$data) { return $data[$key]; }
        ));
        $order->expects($this->any())->method('offsetExists')->will($this->returnCallback(
            function ($key) use ($data) { return array_key_exists($key, $data); }
        ));
        return $order;
    }


    public function testEmptyResponse()
    {
        $response = new CheckoutAuthorizeResponse($this->request, []);
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getWidget());
        $this->assertNull($response->getTransactionToken());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCheckoutOrderUri());
        $this->assertNull($response->getOrderStatus());
        $this->assertNull($response->getReservationNumber());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isWaiting());
        $this->assertFalse($response->isTransactionToken());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isTransparentRedirect());
        $this->assertFalse($response->haveWidget());
        $this->assertFalse($response->isStatusCheckoutIncomplete());
        $this->assertFalse($response->isStatusCheckoutComplete());
        $this->assertFalse($response->isStatusCreated());
        $this->assertSame([], $response->getData());
    }

    public function testSuccessfulResponse()
    {
        $data = [
            'status' => 'checkout_incomplete',
            'gui'   => ['snippet' => 'SomeWidget'],
        ];
        $order = $this->getMockOrder($data);
        $response = new CheckoutAuthorizeResponse($this->request, ['order' => $order]);
        $this->assertSame('SomeLocation', $response->getCheckoutOrderUri());
        $this->assertSame('checkout_incomplete', $response->getOrderStatus());
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getTransactionToken());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getReservationNumber());
        $this->assertFalse($response->isWaiting());
        $this->assertFalse($response->isTransactionToken());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isTransparentRedirect());
        $this->assertTrue($response->haveWidget());
        $this->assertTrue($response->isStatusCheckoutIncomplete());
        $this->assertFalse($response->isStatusCheckoutComplete());
        $this->assertFalse($response->isStatusCreated());
        $this->assertInternalType('array', $response->getData());
        $this->assertArrayHasKey('order', $response->getData());
        $widget = $response->getWidget();
        $this->assertNotEmpty($widget);
        $this->assertInstanceOf('Omnipay\\Klarna\\Widget\\CheckoutResponseWidget', $widget);
        $this->assertSame('<div>SomeWidget</div>', (string) $widget);
    }
}