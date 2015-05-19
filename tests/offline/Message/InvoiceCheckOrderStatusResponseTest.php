<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\InvoiceCheckOrderStatusResponse;
use Omnipay\Klarna\Message\InvoiceCheckOrderStatusRequest;

class InvoiceCheckOrderStatusResponseTest extends TestCase
{
    public function setUp()
    {
        $this->request = new InvoiceCheckOrderStatusRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTestMode(true);
    }


    public function testEmptyResponse()
    {
        $response = new InvoiceCheckOrderStatusResponse($this->request, []);
        $this->assertNull($response->getOrderStatus());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getWidget());
        $this->assertNull($response->getTransactionToken());
        $this->assertNull($response->getTransactionReference());
        $this->assertFalse($response->isAccepted());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isDenied());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isWaiting());
        $this->assertFalse($response->isTransactionToken());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isTransparentRedirect());
        $this->assertFalse($response->haveWidget());
        $this->assertSame([], $response->getData());
    }
}
