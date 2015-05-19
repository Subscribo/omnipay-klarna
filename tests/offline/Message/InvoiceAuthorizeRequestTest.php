<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\InvoiceAuthorizeRequest;

class InvoiceAuthorizeRequestTest extends TestCase
{
    public function setUp()
    {
        $this->merchantId = uniqid();
        $this->sharedSecret = uniqid();
        $this->card = [
            'gender' => 'Male',
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
        $request->setMerchantId($this->merchantId);
        $request->setSharedSecret($this->sharedSecret);
        $request->setLocale('de_at');
        $request->setTestMode(true);
        $request->setClientIp('192.0.2.1');
        $request->setCard($this->card);
        $request->setItems($this->shoppingCart);

        $data = $request->getData();

        $this->assertSame('de', $request->getLanguage());
        $this->assertSame('AT', $request->getCountry());
        $this->assertSame('EUR', $request->getCurrency());
        $this->assertSame('192.0.2.1', $request->getClientIp());
        $this->assertTrue($request->getTestMode());
        $this->assertEmpty($request->getAmount());
        $this->assertSame(0, $request->getAmountInteger());
        $this->assertSame('54.00', $request->calculateAmount());
        $this->assertInstanceOf('Subscribo\\Omnipay\\Shared\\CreditCard', $request->getCard());
        $this->assertSame($this->merchantId, $request->getMerchantId());
        $this->assertSame($this->sharedSecret, $request->getSharedSecret());

        $this->assertTrue($data['testMode']);
        $this->assertNotEmpty($data['merchantId']);
        $this->assertNotEmpty($data['sharedSecret']);
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
    public function testMissingSocialSecurityNumber()
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
            'socialSecurityNumber' => '410321-9202'
        ];
        $request->setCard($card);
        $data = $request->getData();
        $this->assertSame('410321-9202', $data['pno']);
        $this->assertNull($data['gender']);
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
