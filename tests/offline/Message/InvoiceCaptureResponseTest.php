<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\InvoiceCaptureResponse;
use Omnipay\Klarna\Message\InvoiceCaptureRequest;

class InvoiceCaptureResponseTest extends TestCase
{
    public function setUp()
    {
        $this->request = new InvoiceCaptureRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTestMode(true);
    }


    public function testEmptyResponse()
    {
        $response = new InvoiceCaptureResponse($this->request, []);
        $this->assertNull($response->getInvoiceNumber());
        $this->assertNull($response->getRiskStatus());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getWidget());
        $this->assertNull($response->getTransactionToken());
        $this->assertNull($response->getTransactionReference());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isAccepted());
        $this->assertFalse($response->isWaiting());
        $this->assertFalse($response->isTransactionToken());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isTransparentRedirect());
        $this->assertFalse($response->haveWidget());
        $this->assertSame([], $response->getData());
    }

    public function testSuccessfulResponse()
    {
        $invoiceNumber = uniqid();
        $data = ['ok', $invoiceNumber];
        $response = new InvoiceCaptureResponse($this->request, $data);

        $this->assertSame($invoiceNumber, $response->getInvoiceNumber());
        $this->assertSame('ok', $response->getRiskStatus());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getWidget());
        $this->assertNull($response->getTransactionToken());
        $this->assertSame($invoiceNumber, $response->getTransactionReference());
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isAccepted());
        $this->assertFalse($response->isWaiting());
        $this->assertFalse($response->isTransactionToken());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isTransparentRedirect());
        $this->assertFalse($response->haveWidget());
        $this->assertSame($data, $response->getData());
    }

    public function testUnsuccessfulResponse()
    {
        $invoiceNumber = uniqid();
        $data = ['no_risk', $invoiceNumber];
        $response = new InvoiceCaptureResponse($this->request, $data);

        $this->assertSame($invoiceNumber, $response->getInvoiceNumber());
        $this->assertSame('no_risk', $response->getRiskStatus());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getWidget());
        $this->assertNull($response->getTransactionToken());
        $this->assertSame($invoiceNumber, $response->getTransactionReference());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isAccepted());
        $this->assertFalse($response->isWaiting());
        $this->assertFalse($response->isTransactionToken());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isTransparentRedirect());
        $this->assertFalse($response->haveWidget());
        $this->assertSame($data, $response->getData());
    }
}
