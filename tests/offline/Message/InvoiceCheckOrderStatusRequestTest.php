<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\InvoiceCheckOrderStatusRequest;

class InvoiceCheckOrderStatusRequestTest extends TestCase
{
    public function setUp()
    {
        $this->merchantId = uniqid();
        $this->sharedSecret = uniqid();
    }

    public function testEmptyRequest()
    {
        $request = new InvoiceCheckOrderStatusRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->assertNull($request->getReservationNumber());
        $this->assertNull($request->getInvoiceNumber());
        $this->assertNull($request->getOrderId());

        $this->assertNull($request->getCountry());
        $this->assertNull($request->getLanguage());
        $this->assertNull($request->getCurrency());
        $this->assertNull($request->getMerchantId());
        $this->assertNull($request->getSharedSecret());

        $this->assertNull($request->getCard());
        $this->assertNull($request->getItems());
        $this->assertSame(0, $request->getAmountInteger());
        $this->assertNull($request->getAmount());
        $this->assertNull($request->getTransactionReference());
        $this->assertNull($request->getTransactionId());
        $this->assertNull($request->getTestMode());
        $this->assertNull($request->getDescription());
        $this->assertNull($request->getReturnUrl());
        $this->assertNull($request->getCancelUrl());
        $this->assertNull($request->getCardReference());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The merchantId parameter is required
     */
    public function testGetDataEmpty()
    {
        $request = new InvoiceAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->setTestMode(true);
        $data = $request->getData();
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage One of parameters
     */
    public function testIncompleteRequestException()
    {
        $request = new InvoiceCheckOrderStatusRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->setTestMode(true);
        $request->setMerchantId($this->merchantId);
        $request->setSharedSecret($this->sharedSecret);
        $request->setLocale('de_at');
        $data = $request->getData();
    }


    public function testGetData()
    {
        $transactionId = uniqid();
        $request = new InvoiceCheckOrderStatusRequest($this->getHttpClient(), $this->getHttpRequest());
        $params = [
            'testMode' => true,
            'country' => 'AT',
            'language' => 'de',
            'currency' => 'EUR',
            'merchantId' => $this->merchantId,
            'sharedSecret' => $this->sharedSecret,
            'transactionId' => $transactionId,
        ];
        $this->assertSame($request, $request->initialize($params));
        $data = $request->getData();

        $this->assertTrue($request->getTestMode());
        $this->assertSame('de', $request->getLanguage());
        $this->assertSame('AT', $request->getCountry());
        $this->assertSame('EUR', $request->getCurrency());
        $this->assertSame($this->merchantId, $request->getMerchantId());
        $this->assertSame($this->sharedSecret, $request->getSharedSecret());
        $this->assertNull($request->getReservationNumber());
        $this->assertNull($request->getInvoiceNumber());
        $this->assertNull($request->getOrderId());
        $this->assertSame($transactionId, $request->getTransactionId());

        foreach($params as $key => $value) {
            $this->assertSame($value, $data[$key]);
        }
    }


    public function testSetters()
    {
        $request = new InvoiceCheckOrderStatusRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->assertNull($request->getTestMode());
        $this->assertSame($request, $request->setTestMode(true));
        $this->assertTrue($request->getTestMode());

        $reservationNumber = uniqid();
        $this->assertNull($request->getReservationNumber());
        $this->assertSame($request, $request->setReservationNumber($reservationNumber));
        $this->assertSame($reservationNumber, $request->getReservationNumber());
        $this->assertSame($request, $request->setReservationNumber(null));
        $this->assertNull($request->getReservationNumber());

        $invoiceNumber = uniqid();
        $this->assertNull($request->getInvoiceNumber());
        $this->assertSame($request, $request->setInvoiceNumber($invoiceNumber));
        $this->assertSame($invoiceNumber, $request->getInvoiceNumber());
        $this->assertSame($request, $request->setInvoiceNumber(null));
        $this->assertNull($request->getInvoiceNumber());

        $orderId = uniqid();
        $this->assertNull($request->getOrderId());
        $this->assertSame($request, $request->setOrderId($orderId));
        $this->assertSame($orderId, $request->getOrderId());
        $this->assertSame($request, $request->setOrderId(null));
        $this->assertNull($request->getOrderId());

        $transactionId = uniqid();
        $this->assertNull($request->getTransactionId());
        $this->assertSame($request, $request->setTransactionId($transactionId));
        $this->assertSame($transactionId, $request->getTransactionId());
        $this->assertSame($request, $request->setTransactionId(null));
        $this->assertNull($request->getTransactionId());

        $this->assertNull($request->getCountry());
        $this->assertNull($request->getLanguage());
        $this->assertNull($request->getCurrency());
        $this->assertSame($request, $request->setLocale('de_at'));
        $this->assertSame('de', $request->getLanguage());
        $this->assertSame('AT', $request->getCountry());
        $this->assertSame('EUR', $request->getCurrency());

        $this->assertSame($request, $request->setCountry('SE'));
        $this->assertSame('SE', $request->getCountry());
        $this->assertSame($request, $request->setLanguage('sv'));
        $this->assertSame('sv', $request->getLanguage());
        $this->assertSame($request, $request->setCurrency('sek'));
        $this->assertSame('SEK', $request->getCurrency());

        $this->assertNull($request->getMerchantId());
        $this->assertSame($request, $request->setMerchantId($this->merchantId));
        $this->assertSame($this->merchantId, $request->getMerchantId());
        $this->assertSame($request, $request->setMerchantId(null));
        $this->assertNull($request->getMerchantId());

        $this->assertNull($request->getSharedSecret());
        $this->assertSame($request, $request->setSharedSecret($this->sharedSecret));
        $this->assertSame($this->sharedSecret, $request->getSharedSecret());
        $this->assertSame($request, $request->setSharedSecret(null));
        $this->assertNull($request->getSharedSecret());
    }
}
