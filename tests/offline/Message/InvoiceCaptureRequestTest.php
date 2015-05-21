<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\InvoiceCaptureRequest;

class InvoiceCaptureRequestTest extends TestCase
{
    public function testEmptyRequest()
    {
        $request = new InvoiceCaptureRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->assertNull($request->getReservationNumber());
        $this->assertNull($request->getFlags());
        $this->assertNull($request->getOCRNumber());

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


    public function testSetters()
    {
        $request = new InvoiceCaptureRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->assertNull($request->getTestMode());
        $this->assertSame($request, $request->setTestMode(true));
        $this->assertTrue($request->getTestMode());

        $flags = mt_rand();
        $this->assertNull($request->getFlags());
        $this->assertSame($request, $request->setFlags($flags));
        $this->assertSame($flags, $request->getFlags());

        $reservationNumber = uniqid();
        $this->assertNull($request->getReservationNumber());
        $this->assertSame($request, $request->setReservationNumber($reservationNumber));
        $this->assertSame($reservationNumber, $request->getReservationNumber());

        $OCRNumber = uniqid();
        $this->assertNull($request->getOCRNumber());
        $this->assertSame($request, $request->setOCRNumber($OCRNumber));
        $this->assertSame($OCRNumber, $request->getOCRNumber());

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

        $merchantId = uniqid();
        $this->assertNull($request->getMerchantId());
        $this->assertSame($request, $request->setMerchantId($merchantId));
        $this->assertSame($merchantId, $request->getMerchantId());
        $this->assertSame($request, $request->setMerchantId(null));
        $this->assertNull($request->getMerchantId());

        $sharedSecret = uniqid();
        $this->assertNull($request->getSharedSecret());
        $this->assertSame($request, $request->setSharedSecret($sharedSecret));
        $this->assertSame($sharedSecret, $request->getSharedSecret());
        $this->assertSame($request, $request->setSharedSecret(null));
        $this->assertNull($request->getSharedSecret());

        $orderId1 = uniqid();
        $orderId2 = uniqid().'second';

        $this->assertSame($request, $request->setOrderId1($orderId1));
        $this->assertSame($request, $request->setOrderId2($orderId2));
        $this->assertSame($orderId1, $request->getOrderId1());
        $this->assertSame($orderId2, $request->getOrderId2());
        $this->assertSame($orderId1, $request->getTransactionId());

        $transactionId = uniqid().'transaction';

        $this->assertSame($request, $request->setTransactionId($transactionId));
        $this->assertSame($transactionId, $request->getOrderId1());
        $this->assertSame($orderId2, $request->getOrderId2());
        $this->assertSame($transactionId, $request->getTransactionId());
    }


    public function testGetData()
    {
        $merchantId = uniqid();
        $sharedSecret = uniqid();
        $reservationNumber = uniqid();
        $request = new InvoiceCaptureRequest($this->getHttpClient(), $this->getHttpRequest());
        $params = [
            'testMode' => true,
            'country' => 'AT',
            'language' => 'de',
            'currency' => 'EUR',
            'merchantId' => $merchantId,
            'sharedSecret' => $sharedSecret,
            'reservationNumber' => $reservationNumber,
        ];
        $this->assertSame($request, $request->initialize($params));
        $data = $request->getData();

        $this->assertTrue($request->getTestMode());
        $this->assertSame('de', $request->getLanguage());
        $this->assertSame('AT', $request->getCountry());
        $this->assertSame('EUR', $request->getCurrency());
        $this->assertSame($merchantId, $request->getMerchantId());
        $this->assertSame($sharedSecret, $request->getSharedSecret());
        $this->assertSame($reservationNumber, $request->getReservationNumber());
        $this->assertNull($request->getTransactionId());
        $this->assertNull($request->getFlags());
        $this->assertNull($request->getOCRNumber());
        $this->assertNull($request->getOrderId1());
        $this->assertNull($request->getOrderId2());
        $this->assertNull($request->getTransactionId());

        foreach($params as $key => $value) {
            $this->assertSame($value, $data[$key]);
        }
        $this->assertSame([], $data['articles']);
    }



    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The merchantId parameter is required
     */
    public function testGetDataEmpty()
    {
        $request = new InvoiceCaptureRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->setTestMode(true);
        $data = $request->getData();
    }
}
