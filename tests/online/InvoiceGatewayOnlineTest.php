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
        try {
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
            $this->assertTrue($response->isResolved());
            $this->assertFalse($response->isPending());
            $this->assertFalse($response->isWaiting());
            $reservationNumber = $response->getReservationNumber();
            $this->assertNotEmpty($reservationNumber);

            return $reservationNumber;
        } catch (\KlarnaException $e) {
            $this->assertSame(9120, $e->getCode());
            $this->markTestSkipped('API credentials provided does not allow testing for this country.');
        }
    }

    /**
     * @depends testAuthorizeAccepted
     */
    public function testCheckOrderStatusAccepted($reservationNumber)
    {
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
        $this->assertNotEmpty($response->getOrderStatus());
        return $reservationNumber;
    }

    /**
     * @depends testCheckOrderStatusAccepted
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
        $invoiceNumber = $response->getInvoiceNumber();
        $this->assertNotEmpty($invoiceNumber);
        $this->assertSame($invoiceNumber, $response->getTransactionReference());
        return [$reservationNumber, $invoiceNumber];
    }

    /**
     * @depends testFinalCapture
     */
    public function testCheckOrderStatusAfterCaptureByInvoiceNumber(array $numbers)
    {
        list($reservationNumber, $invoiceNumber) = $numbers;
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
        $this->assertNotEmpty($response->getOrderStatus());
        return $reservationNumber;
    }

    /**
     * @depends testCheckOrderStatusAfterCaptureByInvoiceNumber
     * @expectedException \KlarnaException
     */
    public function testCheckOrderStatusAfterCaptureByReservationNumber($reservationNumber)
    {
        $this->assertNotEmpty($reservationNumber);
        $data = ['reservationNumber' => $reservationNumber];
        $request = $this->gateway->checkOrderStatus($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCheckOrderStatusRequest', $request);

        $response = $request->send();
    }

    /**
     * @expectedException \KlarnaException
     */
    public function testAuthorizeDenied()
    {
        $data = [
            'card' => $this->deniedCard,
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
        $this->assertTrue($response->isResolved());
        $reservationNumber = $response->getReservationNumber();
        $this->assertNotEmpty($reservationNumber);

        $data2 = ['reservationNumber' => $reservationNumber];
        $request2 = $this->gateway->capture($data2);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceCaptureRequest', $request2);

        $response2 = $request2->send();
    }


    public function testAuthorizePending()
    {
        try {
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
            $this->assertFalse($response->isSuccessful());
            $this->assertFalse($response->isResolved());
            $this->assertTrue($response->isPending());
            $this->assertTrue($response->isWaiting());
            $reservationNumber = $response->getReservationNumber();
            $this->assertNotEmpty($reservationNumber);

            return $reservationNumber;
        } catch (\KlarnaException $e) {
            $this->assertSame(9120, $e->getCode());
            $this->markTestSkipped('API credentials provided does not allow testing for this country.');
        }
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
        $this->assertNotEmpty($response->getOrderStatus());
        return $reservationNumber;
    }


    public function testAuthorizeAcceptedForSwedishCompany()
    {
        try {
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
            $data = ['card' => $card];

            $request = $this->gateway->authorize($data);
            $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceAuthorizeRequest', $request);
            $request->setItems($this->shoppingCart);

            if (empty($this->sharedSecret)) {
                $this->markTestSkipped('API credentials not provided, online test skipped.');
            }
            $response = $request->send();

            $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\InvoiceAuthorizeResponse', $response);
            $this->assertTrue($response->isSuccessful());
            $this->assertTrue($response->isResolved());
            $this->assertFalse($response->isPending());
            $this->assertFalse($response->isWaiting());
            $reservationNumber = $response->getReservationNumber();
            $this->assertNotEmpty($reservationNumber);

            return $reservationNumber;
        } catch (\KlarnaException $e) {
            $this->assertSame(9120, $e->getCode());
            $this->markTestSkipped('API credentials provided does not allow testing for this country.');
        }
    }


    /**
     * @depends testAuthorizeAcceptedForSwedishCompany
     */
    public function testCheckOrderStatusAcceptedForSwedishCompany($reservationNumber)
    {
        return $this->testCheckOrderStatusAccepted($reservationNumber);
    }

    /**
     * @depends testCheckOrderStatusAcceptedForSwedishCompany
     */
    public function testPartialCaptureForSwedishCompany($reservationNumber)
    {
        return $this->testPartialCapture($reservationNumber);
    }

    /**
     * @depends testPartialCaptureForSwedishCompany
     */
    public function testFinalCaptureForSwedishCompany($reservationNumber)
    {
        return $this->testFinalCapture($reservationNumber);
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
     * @expectedException \KlarnaException
     */
    public function testCheckOrderStatusAfterCaptureByReservationNumberForSwedishCompany($reservationNumber)
    {
        return $this->testCheckOrderStatusAfterCaptureByReservationNumber($reservationNumber);
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
