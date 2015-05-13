<?php

namespace Omnipay\Klarna;

use KlarnaFlags;
use Omnipay\Tests\GatewayTestCase;
use Omnipay\Klarna\InvoiceGateway;

class InvoiceGatewayOnlineTest extends GatewayTestCase
{
    public function setUp()
    {
        $this->merchantId = getenv('KLARNA_MERCHANT_ID');
        $this->sharedSecret = getenv('KLARNA_SHARED_SECRET');
        $this->gateway = new InvoiceGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setTestMode(true)
            ->setLocale('de_at');

        $this->gateway->setMerchantId($this->merchantId);
        $this->gateway->setSharedSecret($this->sharedSecret);

        $this->card = $card = [
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

    public function testAuthorize()
    {
        $data = [
            'card' => $this->card,
        ];
        $request = $this->gateway->authorize($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceAuthorizeRequest', $request);
        $request->setItems($this->shoppingCart);

        if (empty($this->sharedSecret)) {
            $this->markTestSkipped('API credentials not provided, online test skipped.');
        }
        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceAuthorizeResponse', $response);
        $this->assertTrue($response->isSuccessful());
        $reservationNumber = $response->getReservationNumber();
        $this->assertNotEmpty($reservationNumber);
        return $reservationNumber;
    }

    /**
     * @depends testAuthorize
     */
    public function testPartialCapture($reservationNumber)
    {
        $this->assertNotEmpty($reservationNumber);
        $data = ['reservationNumber' => $reservationNumber];
        $request = $this->gateway->capture($data);
        $request->setItems([
            [
                'identifier' => 'A001',
                'quantity' => 2,
            ],
            [
                'identifier' => 'SHIPPING',
                'quantity' => 1,
            ]
        ]);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCaptureRequest', $request);
        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCaptureResponse', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertNotEmpty($response->getTransactionReference());
        $this->assertSame($response->getTransactionReference(), $response->getInvoiceNumber());

        $response2 = $request->send();


        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCaptureResponse', $response2);
        $this->assertTrue($response2->isSuccessful());
        $this->assertNotEmpty($response2->getTransactionReference());
        $this->assertSame($response2->getTransactionReference(), $response2->getInvoiceNumber());
        $this->assertNotSame($response->getTransactionReference(), $response2->getTransactionReference());

        return $reservationNumber;
    }

    /**
     * @depends testPartialCapture
     */
    public function testFinalCapture($reservationNumber)
    {
        $this->assertNotEmpty($reservationNumber);
        $data = ['reservationNumber' => $reservationNumber];
        $request = $this->gateway->capture($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCaptureRequest', $request);
        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCaptureResponse', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertNotEmpty($response->getTransactionReference());
        $this->assertSame($response->getTransactionReference(), $response->getInvoiceNumber());
    }


    public function testPurchaseParameters()
    {
        if ($this->gateway->supportsPurchase()) {
            parent::testPurchaseParameters();
        }
    }
}
