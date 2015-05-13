<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\InvoiceAuthorizeRequest;

class InvoiceAuthorizeRequestTest extends TestCase
{
    public function testGetData()
    {
        $request = new InvoiceAuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->setMerchantId(uniqid());
        $request->setSharedSecret(uniqid());
        $request->setLocale('de_at');
        $request->setTestMode(true);
        $card = [
            'gender' => 'Male',
            'birthday' => '1960-04-14',
            'firstName' => 'Testperson-at'
        ];
        $request->setCard($card);
        $data = $request->getData();
        $this->assertTrue($data['testMode']);
        $this->assertNotEmpty($data['merchantId']);
        $this->assertNotEmpty($data['sharedSecret']);
        $this->assertSame('m', $data['gender']);
        $this->assertSame(-1, $data['amount']);
        $this->assertSame('de', $data['language']);
        $this->assertSame('AT', $data['country']);
        $this->assertSame('EUR', $data['currency']);


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
