<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\InvoiceAuthorizeResponse;
use Omnipay\Klarna\Message\InvoiceAuthorizeRequest;

class InvoiceAuthorizeResponseTest extends TestCase
{
    public function setUp()
    {
        $this->request = new InvoiceAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTestMode(true);
    }


    public function testEmptyResponse()
    {
        $response = new InvoiceAuthorizeResponse($this->request, []);
        $this->assertNull($response->getReservationNumber());
        $this->assertNull($response->getInvoiceStatus());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getWidget());
        $this->assertNull($response->getTransactionToken());
        $this->assertNull($response->getTransactionReference());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isResolved());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isWaiting());
        $this->assertFalse($response->isTransactionToken());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isTransparentRedirect());
        $this->assertFalse($response->haveWidget());
        $this->assertSame([], $response->getData());
    }
}
