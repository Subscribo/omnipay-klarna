<?php

namespace Omnipay\Klarna\Message;

use Klarna;
use KlarnaException;
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

    /**
     * @expectedException \KlarnaException
     * @expectedExceptionMessage This is technical exception
     */
    public function testTechnicalException()
    {
        $request = new ExtendedAbstractInvoiceRequestForTesting($this->getHttpClient(), $this->getHttpRequest());
        $data = [
            'clientIp' => '192.0.2.1',
            'country' => 'AT',
            'language' => 'de',
            'currency' => 'EUR',
            'testMode' => true,
            'merchantId' => uniqid(),
            'sharedSecret' => uniqid(),
            'throwTechnicalException' => true,
        ];
        $request->sendData($data);
    }
}


class ExtendedAbstractInvoiceRequestForTesting extends AbstractInvoiceRequest
{
    public function getData()
    {

    }

    protected function sendRequestViaConnector(Klarna $connector, array $data)
    {
        if (isset($data['throwTechnicalException'])) {
            throw new KlarnaException('This is technical exception');
        }

    }

    protected function createResponse($data)
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
