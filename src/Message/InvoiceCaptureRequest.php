<?php

namespace Omnipay\Klarna\Message;

use Klarna;
use KlarnaFlags;
use Omnipay\Klarna\Message\AbstractInvoiceRequest;
use Omnipay\Klarna\Message\InvoiceCaptureResponse;
use Omnipay\Klarna\Traits\InvoiceGatewayDefaultParametersGettersAndSettersTrait;

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
     * @param array $data
     * @return InvoiceCaptureResponse
     */
    public function sendData($data)
    {
        $k = $this->createKlarnaConnector($data);
        if (isset($data['articles'])) {
            foreach ($data['articles'] as $article) {
                $k->addArtNo($article['quantity'], $article['artNo']);
            }
        }
        $rno = $data['reservationNumber'];
        $ocr = array_key_exists('OCRNumber', $data) ? $data['OCRNumber'] : null;
        $flags = array_key_exists('flags', $data) ? $data['flags'] : null;
        $result = $k->activate($rno, $ocr, $flags);
        $this->response = $this->createResponse($result);

        return $this->response;
    }

    /**
     * @param array $data
     * @return InvoiceCaptureResponse
     */
    protected function createResponse(array $data)
    {
        return new InvoiceCaptureResponse($this, $data);
    }
}
