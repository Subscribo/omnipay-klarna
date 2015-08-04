<?php

namespace Omnipay\Klarna;

use Omnipay\Tests\GatewayTestCase;
use Omnipay\Klarna\CheckoutGateway;
use Subscribo\Omnipay\Shared\CreditCard;

class CheckoutGatewayOnlineTest extends GatewayTestCase
{
    public function setUp()
    {
        $this->merchantId = getenv('KLARNA_MERCHANT_ID');
        $this->sharedSecret = getenv('KLARNA_SHARED_SECRET');
        $this->urlBase = 'https://your.web.site.example';

        $this->gateway = new CheckoutGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setTestMode(true)
            ->setLocale('de_at');

        $this->gateway->setMerchantId($this->merchantId);
        $this->gateway->setSharedSecret($this->sharedSecret);

        $this->card = $card = [
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


    public function testFetchingAuthorizationWidget()
    {
        $this->markTestSkipped("Skipped until implementation of Session handling callbacks");
        $orderId1 = uniqid();
        $orderId2 = uniqid();
        $data = [
            'termsUrl' => $this->urlBase.'/about/terms',
            'authorizeUrl' => $this->urlBase.'/path/to/example/checkout/authorize',
            'returnUrl' => $this->urlBase.'/path/to/example/checkout/complete_authorize',
            'pushUrl' => $this->urlBase.'/scripts/checkout/push',
        ];
        $request = $this->gateway->authorize($data);
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\CheckoutAuthorizeRequest', $request);
        $request->setItems($this->shoppingCart);

        if (empty($this->sharedSecret)) {
            $this->markTestSkipped('API credentials not provided, online test skipped.');
        }
        $response = $request->send();

        $this->assertInstanceOf('\\Omnipay\\Klarna\\Message\\CheckoutAuthorizeResponse', $response);

        $responseCode = $response->getCode();
        if (9120 === $responseCode) {
            $this->markTestSkipped('API credentials provided does not allow testing for this country.');
        }
        $this->assertNull($responseCode);

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isWaiting());
        $this->assertNull($response->getMessage());
        $this->assertFalse($response->isTransactionToken());
        $this->assertNull($response->getTransactionReference());
        $this->assertTrue($response->haveWidget());
        $widget = $response->getWidget();
        $this->assertInstanceOf('\\Omnipay\\Klarna\\Widget\\CheckoutResponseWidget', $widget);
        $this->assertTrue($widget->isRenderable());
        $rendered = (string) $widget;
        $this->assertNotEmpty($widget);
        $this->assertStringStartsWith('<div>', $rendered);
        $this->assertStringEndsWith('</div>', $rendered);
        $this->assertNotEmpty($widget->getContent());
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
