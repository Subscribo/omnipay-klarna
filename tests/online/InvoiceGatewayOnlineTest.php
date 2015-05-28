<?php

namespace Omnipay\Klarna;

use KlarnaFlags;
use Omnipay\Tests\GatewayTestCase;
use Omnipay\Klarna\InvoiceGateway;
use Omnipay\Klarna\Message\InvoiceCheckOrderStatusRequest;

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
        $this->deniedCard = $deniedCard = [
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


    public function testAuthorizeAccepted()
    {
        $orderId1 = uniqid();
        $orderId2 = uniqid();
        $data = [
            'card' => $this->card,
            'orderId1' => $orderId1,
            'orderId2' => $orderId2,
        ];
        $request = $this->gateway->authorize($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceAuthorizeRequest', $request);
        $request->setItems($this->shoppingCart);

        if (empty($this->sharedSecret)) {
            $this->markTestSkipped('API credentials not provided, online test skipped.');
        }
        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceAuthorizeResponse', $response);

        $responseCode = $response->getCode();
        if (9120 === $responseCode) {
            $this->markTestSkipped('API credentials provided does not allow testing for this country.');
        }
        $this->assertNull($responseCode);

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isAccepted());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isWaiting());
        $this->assertNull($response->getMessage());
        $reservationNumber = $response->getReservationNumber();
        $this->assertNotEmpty($reservationNumber);

        return [$reservationNumber, $orderId1, $orderId2];
    }

    /**
     * @depends testAuthorizeAccepted
     */
    public function testCheckOrderStatusAccepted(array $numbers)
    {
        list($reservationNumber, $orderId1, $orderId2) = $numbers;
        $this->assertNotEmpty($reservationNumber);
        $data = ['reservationNumber' => $reservationNumber];
        $request = $this->gateway->checkOrderStatus($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $request);

        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusResponse', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isAccepted());
        $this->assertFalse($response->isDenied());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isWaiting());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNotEmpty($response->getOrderStatus());
        
        $data2 = ['orderId' => $orderId1];
        $request2 = $this->gateway->checkOrderStatus($data2);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $request2);

        $response2 = $request2->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusResponse', $response2);
        $this->assertTrue($response2->isSuccessful());
        $this->assertTrue($response2->isAccepted());
        $this->assertFalse($response2->isDenied());
        $this->assertFalse($response2->isPending());
        $this->assertFalse($response2->isWaiting());
        $this->assertNull($response2->getCode());
        $this->assertNull($response2->getMessage());
        $this->assertNotEmpty($response2->getOrderStatus());

        $data3 = ['orderId' => $orderId2];
        $request3 = $this->gateway->checkOrderStatus($data3);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $request3);

        $response3 = $request3->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusResponse', $response3);
        $this->assertTrue($response3->isSuccessful());
        $this->assertTrue($response3->isAccepted());
        $this->assertFalse($response3->isDenied());
        $this->assertFalse($response3->isPending());
        $this->assertFalse($response3->isWaiting());
        $this->assertNull($response3->getCode());
        $this->assertNull($response3->getMessage());
        $this->assertNotEmpty($response3->getOrderStatus());

        $data4 = ['transactionId' => $orderId1];
        $request4 = $this->gateway->checkOrderStatus($data4);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $request4);

        $response4 = $request4->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusResponse', $response4);
        $this->assertTrue($response4->isSuccessful());
        $this->assertTrue($response4->isAccepted());
        $this->assertFalse($response4->isDenied());
        $this->assertFalse($response4->isPending());
        $this->assertFalse($response4->isWaiting());
        $this->assertNull($response4->getCode());
        $this->assertNull($response4->getMessage());
        $this->assertNotEmpty($response4->getOrderStatus());

        $data5 = ['transactionId' => $orderId2];
        $request5 = $this->gateway->checkOrderStatus($data5);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $request5);

        $response5 = $request5->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusResponse', $response5);
        $this->assertTrue($response5->isSuccessful());
        $this->assertTrue($response5->isAccepted());
        $this->assertFalse($response5->isDenied());
        $this->assertFalse($response5->isPending());
        $this->assertFalse($response5->isWaiting());
        $this->assertNull($response5->getCode());
        $this->assertNull($response5->getMessage());
        $this->assertNotEmpty($response5->getOrderStatus());
        
        return $numbers;
    }

    /**
     * @depends testCheckOrderStatusAccepted
     */
    public function testPartialCapture(array $numbers)
    {
        list($reservationNumber, $orderId1, $orderId2) = $numbers;
        $items = [
            [
                'identifier' => 'A001',
                'quantity' => 2,
            ],
            [
                'identifier' => 'SHIPPING',
                'quantity' => 1,
            ]
        ];

        $this->assertNotEmpty($reservationNumber);
        $data = ['reservationNumber' => $reservationNumber];
        $request = $this->gateway->capture($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCaptureRequest', $request);
        $this->assertSame($request, $request->setItems($items));

        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCaptureResponse', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNotEmpty($response->getTransactionReference());
        $this->assertSame($response->getTransactionReference(), $response->getInvoiceNumber());

        $orderId2A = '  may contain spaces  '.uniqid().'   ';
        $numbers[] = $orderId2A;
        $data2 = [
            'reservationNumber' => $reservationNumber,
            'orderId1'          => '-', // With current API version it is not possible to unset orderId
            'orderId2'          => $orderId2A,
            'items'             => $items,
        ];
        $request2 = $this->gateway->capture($data2);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCaptureRequest', $request2);

        $response2 = $request2->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCaptureResponse', $response2);
        $this->assertTrue($response2->isSuccessful());
        $this->assertNull($response2->getCode());
        $this->assertNull($response2->getMessage());
        $this->assertNotEmpty($response2->getTransactionReference());
        $this->assertSame($response2->getTransactionReference(), $response2->getInvoiceNumber());
        $this->assertNotSame($response->getTransactionReference(), $response2->getTransactionReference());

        return $numbers;
    }

    /**
     * @depends testPartialCapture
     */
    public function testFinalCapture(array $numbers)
    {
        list($reservationNumber, $orderId1, $orderId2, $orderId2A) = $numbers;
        $orderId2B = uniqid();
        $this->assertNotEmpty($reservationNumber);
        $request = $this->gateway->capture();
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCaptureRequest', $request);
        $this->assertSame($request, $request->setReservationNumber($reservationNumber));
        $this->assertSame($request, $request->setOrderId2($orderId2B));

        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCaptureResponse', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $invoiceNumber = $response->getInvoiceNumber();
        $this->assertNotEmpty($invoiceNumber);
        $this->assertSame($invoiceNumber, $response->getTransactionReference());
        return [$reservationNumber, $invoiceNumber, $orderId1, $orderId2, $orderId2A, $orderId2B];
    }

    /**
     * @depends testFinalCapture
     */
    public function testCheckOrderStatusAfterCaptureByInvoiceNumber(array $numbers)
    {
        list($reservationNumber, $invoiceNumber, $orderId1, $orderId2, $orderId2A, $orderId2B) = $numbers;
        $this->assertNotEmpty($reservationNumber);
        $this->assertNotEmpty($invoiceNumber);
        $data = ['invoiceNumber' => $invoiceNumber];
        $request = $this->gateway->checkOrderStatus($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $request);

        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusResponse', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isAccepted());
        $this->assertFalse($response->isDenied());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isWaiting());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNotEmpty($response->getOrderStatus());

        $data2 = ['orderId' => $orderId2];
        $request2 = $this->gateway->checkOrderStatus($data2);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $request2);

        $response2 = $request2->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusResponse', $response2);
        $this->assertTrue($response2->isSuccessful());
        $this->assertTrue($response2->isAccepted());
        $this->assertFalse($response2->isDenied());
        $this->assertFalse($response2->isPending());
        $this->assertFalse($response2->isWaiting());
        $this->assertNull($response2->getCode());
        $this->assertNull($response2->getMessage());
        $this->assertNotEmpty($response2->getOrderStatus());

        $data3 = ['orderId' => $orderId2A];
        $request3 = $this->gateway->checkOrderStatus($data3);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $request3);

        $response3 = $request3->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusResponse', $response3);
        $this->assertTrue($response3->isSuccessful());
        $this->assertTrue($response3->isAccepted());
        $this->assertFalse($response3->isDenied());
        $this->assertFalse($response3->isPending());
        $this->assertFalse($response3->isWaiting());
        $this->assertNull($response3->getCode());
        $this->assertNull($response3->getMessage());
        $this->assertNotEmpty($response3->getOrderStatus());

        $data4 = ['orderId' => $orderId2B];
        $request4 = $this->gateway->checkOrderStatus($data4);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $request4);

        $response4 = $request4->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusResponse', $response4);
        $this->assertTrue($response4->isSuccessful());
        $this->assertTrue($response4->isAccepted());
        $this->assertFalse($response4->isDenied());
        $this->assertFalse($response4->isPending());
        $this->assertFalse($response4->isWaiting());
        $this->assertNull($response4->getCode());
        $this->assertNull($response4->getMessage());
        $this->assertNotEmpty($response4->getOrderStatus());


        return $reservationNumber;
    }

    /**
     * @depends testCheckOrderStatusAfterCaptureByInvoiceNumber
     */
    public function testCheckOrderStatusAfterCaptureByReservationNumber($reservationNumber)
    {
        $this->assertNotEmpty($reservationNumber);
        $data = ['reservationNumber' => $reservationNumber];
        $request = $this->gateway->checkOrderStatus($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $request);

        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusResponse', $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertNotEmpty($response->getMessage());
        $this->assertNotEmpty($response->getCode());
    }


    public function testAuthorizeDenied()
    {
        $data = [
            'card' => $this->deniedCard,
            'orderId1' => 'Denied: '.('H:i:s'),
        ];
        $request = $this->gateway->authorize($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceAuthorizeRequest', $request);
        $request->setItems($this->shoppingCart);

        if (empty($this->sharedSecret)) {
            $this->markTestSkipped('API credentials not provided, online test skipped.');
        }

        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceAuthorizeResponse', $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isAccepted());
        $this->assertNotEmpty($response->getMessage());
        $this->assertNotEmpty($response->getCode());
        $this->assertEmpty($response->getReservationNumber());
    }


    public function testAuthorizePending()
    {
        $data = [
            'card' => $this->card,
        ];
        $data['card']['email'] = 'pending_accepted@klarna.com';
        $data['card']['address1'] = 'Klarna-Straße';
        $data['card']['address2'] = '1/2/3';
        $request = $this->gateway->authorize($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceAuthorizeRequest', $request);
        $request->setItems($this->shoppingCart);

        if (empty($this->sharedSecret)) {
            $this->markTestSkipped('API credentials not provided, online test skipped.');
        }
        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceAuthorizeResponse', $response);

        $responseCode = $response->getCode();
        if (9120 === $responseCode) {
            $this->markTestSkipped('API credentials provided does not allow testing for this country.');
        }
        $this->assertNull($responseCode);

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isAccepted());
        $this->assertTrue($response->isPending());
        $this->assertTrue($response->isWaiting());
        $this->assertNull($response->getMessage());
        $reservationNumber = $response->getReservationNumber();
        $this->assertNotEmpty($reservationNumber);

        return $reservationNumber;
    }

    /**
     * @depends testAuthorizePending
     */
    public function testCheckOrderStatusPending($reservationNumber)
    {
        $this->assertNotEmpty($reservationNumber);
        $data = ['reservationNumber' => $reservationNumber];
        $request = $this->gateway->checkOrderStatus($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $request);
        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusResponse', $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isAccepted());
        $this->assertFalse($response->isDenied());
        $this->assertTrue($response->isPending());
        $this->assertTrue($response->isWaiting());
        $this->assertNull($response->getCode());
        $this->assertNull($response->getMessage());
        $this->assertNotEmpty($response->getOrderStatus());
        return $reservationNumber;
    }


    public function testAuthorizeAcceptedForSwedishCompany()
    {
        $orderId1 = uniqid();
        $orderId2 = uniqid();
        $this->gateway->setLocale('sv_se');
        $card = [
            'firstName' => 'Testperson-se',
            'lastName' => 'Approved',
            'company' => 'Testcompany-se',
            'billingAddress1' => 'Stårgatan 1',
            'billingAddress2' => null,
            'billingPostCode' => '12345',
            'city'     => 'Ankeborg',
            'country'  => 'se',
            'shippingAddress1' => 'Lillegatan',
            'shippingAddress2' => 1,
            'shippingPostCode' => '12334',
            'phone'    => '0765260000',
            'email'    => 'youremail@email.com',
            'socialSecurityNumber' => '002031-0132'
        ];
        $data = [
            'card' => $card,
            'transactionId' => $orderId1,
            'orderId2' => $orderId2,
        ];

        $request = $this->gateway->authorize($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceAuthorizeRequest', $request);
        $request->setItems($this->shoppingCart);

        if (empty($this->sharedSecret)) {
            $this->markTestSkipped('API credentials not provided, online test skipped.');
        }
        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceAuthorizeResponse', $response);

        $responseCode = $response->getCode();
        if (9120 === $responseCode) {
            $this->markTestSkipped('API credentials provided does not allow testing for this country.');
        }
        $this->assertNull($responseCode);

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isAccepted());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isWaiting());
        $this->assertNull($response->getMessage());
        $reservationNumber = $response->getReservationNumber();
        $this->assertNotEmpty($reservationNumber);

        return [$reservationNumber, $orderId1, $orderId2];
    }


    /**
     * @depends testAuthorizeAcceptedForSwedishCompany
     */
    public function testCheckOrderStatusAcceptedForSwedishCompany(array $numbers)
    {
        return $this->testCheckOrderStatusAccepted($numbers);
    }

    /**
     * @depends testCheckOrderStatusAcceptedForSwedishCompany
     */
    public function testPartialCaptureForSwedishCompany(array $numbers)
    {
        return $this->testPartialCapture($numbers);
    }

    /**
     * @depends testPartialCaptureForSwedishCompany
     */
    public function testFinalCaptureForSwedishCompany(array $numbers)
    {
        return $this->testFinalCapture($numbers);
    }

    /**
     * @depends testFinalCaptureForSwedishCompany
     */
    public function testCheckOrderStatusAfterCaptureByInvoiceNumberForSwedishCompany(array $numbers)
    {
        return $this->testCheckOrderStatusAfterCaptureByInvoiceNumber($numbers);
    }

    /**
     * @depends testCheckOrderStatusAfterCaptureByInvoiceNumberForSwedishCompany
     */
    public function testCheckOrderStatusAfterCaptureByReservationNumberForSwedishCompany($reservationNumber)
    {
        return $this->testCheckOrderStatusAfterCaptureByReservationNumber($reservationNumber);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage One of reservationNumber, invoiceNumber, orderId or transactionId need to be provided
     */
    public function testExceptionForSendingIncompleteDataWithInvoiceCheckOrderStatusRequest()
    {
        if (empty($this->sharedSecret)) {
            $this->markTestSkipped('API credentials not provided, online test skipped.');
        }
        $request = new InvoiceCheckOrderStatusRequest($this->getHttpClient(), $this->getHttpRequest());
        $data = [
            'testMode' => true,
            'merchantId' => $this->merchantId,
            'sharedSecret' => $this->sharedSecret,
            'country' => 'AT',
            'language' => 'de',
            'currency' => 'EUR',
        ];
        $request->sendData($data);
    }

    /**
     * Fix for testPurchaseParameters from parent class
     */
    public function testPurchaseParameters()
    {
        if ($this->gateway->supportsPurchase()) {
            parent::testPurchaseParameters();
        }
    }
}
