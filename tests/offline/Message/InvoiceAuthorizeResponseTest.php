<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\InvoiceAuthorizeResponse;
use Omnipay\Klarna\Message\InvoiceAuthorizeRequest;
use KlarnaFlags;

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
        $this->assertFalse($response->isAccepted());
        $this->assertFalse($response->isPending());
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
        $reservationNumber = uniqid();
        $data = [$reservationNumber, KlarnaFlags::ACCEPTED];
        $response = new InvoiceAuthorizeResponse($this->request, $data);

        $this->assertSame($reservationNumber, $response->getReservationNumber());
        $this->assertSame(KlarnaFlags::ACCEPTED, $response->getInvoiceStatus());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getWidget());
        $this->assertNull($response->getTransactionToken());
        $this->assertNull($response->getTransactionReference());
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isAccepted());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isWaiting());
        $this->assertFalse($response->isTransactionToken());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isTransparentRedirect());
        $this->assertFalse($response->haveWidget());
        $this->assertSame($data, $response->getData());
    }

    public function testPendingResponse()
    {
        $reservationNumber = uniqid();
        $data = [$reservationNumber, KlarnaFlags::PENDING];
        $response = new InvoiceAuthorizeResponse($this->request, $data);

        $this->assertSame($reservationNumber, $response->getReservationNumber());
        $this->assertSame(KlarnaFlags::PENDING, $response->getInvoiceStatus());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getWidget());
        $this->assertNull($response->getTransactionToken());
        $this->assertNull($response->getTransactionReference());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isAccepted());
        $this->assertTrue($response->isPending());
        $this->assertTrue($response->isWaiting());
        $this->assertFalse($response->isTransactionToken());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isTransparentRedirect());
        $this->assertFalse($response->haveWidget());
        $this->assertSame($data, $response->getData());
    }

    public function testDeniedResponse()
    {
        $reservationNumber = uniqid();
        $data = [$reservationNumber, KlarnaFlags::DENIED];
        $response = new InvoiceAuthorizeResponse($this->request, $data);

        $this->assertSame($reservationNumber, $response->getReservationNumber());
        $this->assertSame(KlarnaFlags::DENIED, $response->getInvoiceStatus());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getWidget());
        $this->assertNull($response->getTransactionToken());
        $this->assertNull($response->getTransactionReference());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isAccepted());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isWaiting());
        $this->assertFalse($response->isTransactionToken());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isTransparentRedirect());
        $this->assertFalse($response->haveWidget());
        $this->assertSame($data, $response->getData());
    }
}
