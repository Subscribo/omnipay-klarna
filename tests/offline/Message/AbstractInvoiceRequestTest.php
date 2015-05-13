<?php

namespace Omnipay\Klarna\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Klarna\Message\AbstractInvoiceRequest;


class AbstractInvoiceRequestTest extends TestCase
{
    public function testCreateKlarnaConnector()
    {
        $data = [
            'clientIp' => '192.0.2.1',
            'country' => 'AT',
            'language' => 'de',
            'currency' => 'EUR',
            'testMode' => true,
            'merchantId' => uniqid(),
            'sharedSecret' => uniqid(),
        ];
        $request = new ExtendedAbstractInvoiceRequestForTesting($this->getHttpClient(), $this->getHttpRequest());
        $connector = $request->createKlarnaConnector($data);
        $this->assertInstanceOf('\\Klarna', $connector);
        $this->assertSame('192.0.2.1', $connector->getClientIP());
    }

}

class ExtendedAbstractInvoiceRequestForTesting extends AbstractInvoiceRequest
{
    public function getData()
    {

    }

    public function sendData($data)
    {

    }

    /**
     * Exposing protected function for testing purposes
     *
     * @param array $data
     * @return \Klarna
     */
    public function createKlarnaConnector($data)
    {
        return parent::createKlarnaConnector($data);
    }
}

