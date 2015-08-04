<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\InvoiceAuthorizeRequest;
use Subscribo\Omnipay\Shared\CreditCard;

class InvoiceAuthorizeRequestTest extends TestCase
{
    public function setUp()
    {
        $this->merchantId = uniqid();
        $this->sharedSecret = uniqid();
        $this->card = [
            'gender' => CreditCard::GENDER_MALE,
            'birthday' => '1960-04-14',
            'firstName' => 'Testperson-at',
            'lastName' => 'Approved',
            'address1' => 'Klarna-Straße 1/2/3',
            'address2' => null,
            'postCode' => '8071',
            'city'     => 'Hausmannstätten',
            'country'  => 'at',
            'phone'    => '0676 2600000',
            'email'    => 'youremail@email.com',
        ];
        $this->deniedCard = [
            'gender' => 'Female',
            'birthday' => '1980-04-14',
            'firstName' => 'Testperson-at',
            'lastName' => 'Denied',
            'address1' => 'Klarna-Straße 1/2/3',
            'address2' => null,
            'postCode' => '8070',
            'city'     => 'Hausmannstätten',
            'country'  => 'at',
            'phone'    => '0676 2800000',
            'email'    => 'youremail@email.com',
        ];
        $this->shoppingCart = [
            [
                'name' => 'Some Article',
                'identifier' => 'A001',
                'price' => '2.00',
                'description' => 'Just article for testing',
                'quantity' => 9,
                'taxPercent' => '20',
            ],
            [
                'name' => 'Another Article',
                'identifier' => 'A002',
                'price' => '10.00',
                'quantity' => 1,
                'taxPercent' => '20',
                'description' => 'An article with different VAT set up',
                'flags' => 0,
            ],
            [
                'name' => 'Discounted Article',
                'identifier' => 'A003',
                'price' => '10.00',
                'description' => 'Some discounted article for testing',
                'quantity' => 1,
                'discountPercent' => '10',
                'taxPercent' => '20',
            ],
            [
                'name' => 'Shipping Fee',
                'identifier' => 'SHIPPING',
                'price' => '5.00',
                'quantity' => 3,
                'description' => 'Testing shipping fee',
                'flags' => 8,
            ]
        ];
    }


    public function testGetData()
    {
        $request = new InvoiceAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->assertSame($request, $request->setMerchantId($this->merchantId));
        $this->assertSame($request, $request->setSharedSecret($this->sharedSecret));
        $this->assertSame($request, $request->setLocale('de_at'));
        $this->assertSame($request, $request->setTestMode(true));
        $this->assertSame($request, $request->setClientIp('192.0.2.1'));
        $this->assertSame($request, $request->setCard($this->card));
        $this->assertSame($request, $request->setItems($this->shoppingCart));

        $data = $request->getData();

        $this->assertSame('de', $request->getLanguage());
        $this->assertSame('AT', $request->getCountry());
        $this->assertSame('EUR', $request->getCurrency());
        $this->assertSame('192.0.2.1', $request->getClientIp());
        $this->assertTrue($request->getTestMode());
        $this->assertEmpty($request->getAmount());
        $this->assertSame(0, $request->getAmountInteger());
        $this->assertSame('54.00', $request->calculateAmount());
        $this->assertNotEmpty($request->getItems());
        $this->assertInstanceOf('Subscribo\\Omnipay\\Shared\\CreditCard', $request->getCard());
        $this->assertSame($this->merchantId, $request->getMerchantId());
        $this->assertSame($this->sharedSecret, $request->getSharedSecret());

        $this->assertTrue($data['testMode']);
        $this->assertNotEmpty($data['articles']);
        $this->assertInstanceOf('Subscribo\\Omnipay\\Shared\\CreditCard', $data['card']);
        $this->assertSame('m', $data['gender']);
        $this->assertSame(-1, $data['amount']);
        $this->assertSame('de', $data['language']);
        $this->assertSame('AT', $data['country']);
        $this->assertSame('EUR', $data['currency']);
        $this->assertSame('192.0.2.1', $data['clientIp']);
        $this->assertSame('14041960', $data['pno']);
        $this->assertSame($this->merchantId, $data['merchantId']);
        $this->assertSame($this->sharedSecret, $data['sharedSecret']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSendWrongData()
    {
        $request = new InvoiceAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->setTestMode(true);
        $request->sendData(null);
    }


    public function testGetWidget()
    {
        $request = new InvoiceAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->setMerchantId($this->merchantId);
        $request->setSharedSecret($this->sharedSecret);
        $request->setLocale('de_at');
        $request->setTestMode(true);
        $request->setItems($this->shoppingCart);

        $widget = $request->getWidget();

        $this->assertInstanceOf('Omnipay\\Klarna\\Widget\\InvoiceWidget', $widget);
        $this->assertSame('54.00', $widget->getPrice());
        $this->assertSame($this->merchantId, $widget->getMerchantId());
        $this->assertSame('de', $widget->getLanguage());
        $this->assertSame('AT', $widget->getCountry());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     */
    public function testMissingNationalIdentificationNumber()
    {
        $request = new InvoiceAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->setMerchantId($this->merchantId);
        $request->setSharedSecret($this->sharedSecret);
        $request->setLocale('fi_fi');
        $request->setTestMode(true);
        $request->setClientIp('192.0.2.1');
        $request->setCard($this->card);
        $data = $request->getData();
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage Gender
     */
    public function testMissingGender()
    {
        $request = new InvoiceAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->setMerchantId($this->merchantId);
        $request->setSharedSecret($this->sharedSecret);
        $request->setLocale('de_at');
        $request->setTestMode(true);
        $request->setClientIp('192.0.2.1');
        $card = $this->card;
        unset($card['gender']);
        $request->setCard($card);
        $data = $request->getData();
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage Birthday
     */
    public function testMissingBirthDate()
    {
        $request = new InvoiceAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->setMerchantId($this->merchantId);
        $request->setSharedSecret($this->sharedSecret);
        $request->setLocale('de_at');
        $request->setTestMode(true);
        $request->setClientIp('192.0.2.1');
        $card = $this->card;
        unset($card['birthday']);
        $request->setCard($card);
        $data = $request->getData();
    }


    public function testPno()
    {
        $request = new InvoiceAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->setMerchantId($this->merchantId);
        $request->setSharedSecret($this->sharedSecret);
        $request->setLocale('sv_se');
        $request->setTestMode(true);
        $request->setClientIp('192.0.2.1');
        $card = [
            'gender' => 'Male',
            'birthday' => '1960-04-14',
            'firstName' => 'Testperson-se',
            'lastName' => 'Approved',
            'address1' => 'Stårgatan 1',
            'address2' => null,
            'postCode' => '12345',
            'city'     => 'Ankeborg',
            'country'  => 'se',
            'phone'    => '0765260000',
            'email'    => 'youremail@email.com',
            'nationalIdentificationNumber' => '410321-9202'
        ];
        $request->setCard($card);
        $data = $request->getData();
        $this->assertSame('410321-9202', $data['pno']);
        $this->assertNull($data['gender']);
    }

    public function testSetters()
    {
        $request = new InvoiceAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->assertNull($request->getOrderId1());
        $this->assertNull($request->getOrderId2());
        $this->assertNull($request->getTransactionId());
        $this->assertNull($request->getLanguage());
        $this->assertNull($request->getCountry());
        $this->assertNull($request->getCurrency());
        $this->assertNull($request->getClientIp());
        $this->assertNull($request->getAmount());
        $this->assertSame(0, $request->getAmountInteger());
        $this->assertNull($request->getCard());
        $this->assertNull($request->getItems());
        $this->assertNull($request->getMerchantId());
        $this->assertNull($request->getSharedSecret());

        $this->assertNull($request->getDescription());
        $this->assertNull($request->getReturnUrl());
        $this->assertNull($request->getCancelUrl());
        $this->assertNull($request->getCardReference());
        $this->assertNull($request->getTransactionReference());
        $this->assertNull($request->getToken());

        $this->assertNull($request->getTestMode());
        $this->assertSame($request, $request->setTestMode(false));
        $this->assertFalse($request->getTestMode());
        $this->assertSame($request, $request->setTestMode(true));
        $this->assertTrue($request->getTestMode());

        $orderId1 = uniqid();
        $orderId2 = uniqid().'second';

        $this->assertSame($request, $request->setOrderId1($orderId1));
        $this->assertSame($request, $request->setOrderId2($orderId2));
        $this->assertSame($request, $request->setLanguage('de'));
        $this->assertSame($request, $request->setCountry('AT'));
        $this->assertSame($request, $request->setCurrency('EUR'));
        $this->assertSame($orderId1, $request->getOrderId1());
        $this->assertSame($orderId2, $request->getOrderId2());
        $this->assertSame($orderId1, $request->getTransactionId());
        $this->assertSame('de', $request->getLanguage());
        $this->assertSame('AT', $request->getCountry());
        $this->assertSame('EUR', $request->getCurrency());

        $transactionId = uniqid().'transaction';

        $this->assertSame($request, $request->setTransactionId($transactionId));
        $this->assertSame($transactionId, $request->getOrderId1());
        $this->assertSame($orderId2, $request->getOrderId2());
        $this->assertSame($transactionId, $request->getTransactionId());
    }

    public function testInitialize()
    {
        $request = new InvoiceAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $orderId1 = uniqid();
        $orderId2 = uniqid();
        $data = [
            'testMode' => true,
            'merchantId' => $this->merchantId,
            'sharedSecret' => $this->sharedSecret,
            'locale' => 'sv_se',
            'orderId1' => $orderId1,
            'orderId2' => $orderId2,
            'clientIp' => '192.0.2.1',
            'card' => [
                'nationalIdentificationNumber' => '410321-9202'
            ]
        ];
        $this->assertSame($request, $request->initialize($data));

        $this->assertSame('sv', $request->getLanguage());
        $this->assertSame('SE', $request->getCountry());
        $this->assertSame('SEK', $request->getCurrency());
        $this->assertSame('192.0.2.1', $request->getClientIp());
        $this->assertTrue($request->getTestMode());
        $this->assertEmpty($request->getAmount());
        $this->assertSame(0, $request->getAmountInteger());
        $this->assertSame('0.00', $request->calculateAmount());
        $this->assertInstanceOf('Subscribo\\Omnipay\\Shared\\CreditCard', $request->getCard());
        $this->assertSame($this->merchantId, $request->getMerchantId());
        $this->assertSame($this->sharedSecret, $request->getSharedSecret());

        $data = $request->getData();

        $this->assertTrue($data['testMode']);
        $this->assertSame($this->merchantId, $data['merchantId']);
        $this->assertSame($this->sharedSecret, $data['sharedSecret']);
        $this->assertNull($data['gender']);
        $this->assertSame(-1, $data['amount']);
        $this->assertSame('sv', $data['language']);
        $this->assertSame('SE', $data['country']);
        $this->assertSame('SEK', $data['currency']);
        $this->assertSame('192.0.2.1', $data['clientIp']);
        $this->assertSame('410321-9202', $data['pno']);
        $this->assertSame('0.00', $request->calculateAmount());
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
}
