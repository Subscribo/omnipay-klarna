<?php

namespace Omnipay\Klarna\Message;

use Klarna;
use KlarnaFlags;
use Omnipay\Klarna\Message\AbstractInvoiceRequest;
use Omnipay\Klarna\Message\InvoiceCaptureResponse;
use Omnipay\Klarna\Traits\InvoiceGatewayDefaultParametersGettersAndSettersTrait;
use Omnipay\Klarna\Traits\OrderIdOneAndTwoGettersAndSettersTrait;

/**
 * Class InvoiceCaptureRequest
 *
 * @package Omnipay\Klarna
 *
 * @method \Omnipay\Klarna\Message\InvoiceCaptureResponse send() send()
 */
class InvoiceCaptureRequest extends AbstractInvoiceRequest
{
    use InvoiceGatewayDefaultParametersGettersAndSettersTrait;
    use OrderIdOneAndTwoGettersAndSettersTrait;

    /**
     * @return string|int
     */
    public function getReservationNumber()
    {
        return $this->getParameter('reservationNumber');
    }

    /**
     * @param string|int $value
     * @return $this;
     */
    public function setReservationNumber($value)
    {
        return $this->setParameter('reservationNumber', $value);
    }

    /**
     * @return int
     */
    public function getFlags()
    {
        return $this->getParameter('flags');
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setFlags($value)
    {
        return $this->setParameter('flags', $value);
    }

    /**
     * @return mixed
     */
    public function getOCRNumber()
    {
        return $this->getParameter('OCRNumber');
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setOCRNumber($value)
    {
        return $this->setParameter('OCRNumber', $value);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $this->validate('merchantId', 'sharedSecret', 'country', 'language', 'currency', 'reservationNumber');
        $data = $this->getParameters();
        $data['articles'] = $this->extractArticles($this->getItems());
        return $data;
    }

    /**
     * @param $data
     * @return Klarna
     */
    protected function prepareConnector($data)
    {
        $connector = $this->createKlarnaConnector($data);

        if (isset($data['articles'])) {
            foreach ($data['articles'] as $article) {
                $connector->addArtNo($article['quantity'], $article['artNo']);
            }
        }
        if (isset($data['orderId1'])) {
            $connector->setActivateInfo('orderid1', strval($data['orderId1']));
        }
        if (isset($data['orderId2'])) {
            $connector->setActivateInfo('orderid2', strval($data['orderId2']));
        }

        return $connector;
    }


    protected function sendRequestViaConnector(Klarna $connector, array $data)
    {
        $rno = $data['reservationNumber'];
        $ocr = array_key_exists('OCRNumber', $data) ? $data['OCRNumber'] : null;
        $flags = array_key_exists('flags', $data) ? $data['flags'] : null;

        return $connector->activate($rno, $ocr, $flags);
    }

    /**
     * @param array|\KlarnaException $data
     * @return InvoiceCaptureResponse
     */
    protected function createResponse($data)
    {
        return new InvoiceCaptureResponse($this, $data);
    }
}
