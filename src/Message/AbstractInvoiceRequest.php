<?php

namespace Omnipay\Klarna\Message;

use Klarna;
use KlarnaCountry;
use KlarnaCurrency;
use KlarnaLanguage;
use KlarnaException;
use InvalidArgumentException;
use Subscribo\Omnipay\Shared\Message\AbstractRequest;
use Subscribo\Omnipay\Shared\ItemBag;

/**
 * Abstract Class AbstractInvoiceRequest
 *
 * @package Omnipay\Klarna
 */
abstract class AbstractInvoiceRequest extends AbstractRequest
{
    public function sendData($data)
    {
        $connector = $this->prepareConnector($data);

        try {
            $result = $this->sendRequestViaConnector($connector, $data);

            $this->response = $this->createResponse($result);

        } catch (KlarnaException $e) {
            $code = $e->getCode();
            if (($code < 0) or ($code > 1100)) {
                $this->response = $this->createResponse($e);
            } else {
                throw $e;
            }
        }

        return $this->response;
    }

    /**
     * @param Klarna $connector
     * @param array $data
     * @return array
     * @throws KlarnaException
     */
    abstract protected function sendRequestViaConnector(Klarna $connector, array $data);

    /**
     * @param array|KlarnaException $data
     * @return \Omnipay\Klarna\Message\AbstractInvoiceResponse
     */
    abstract protected function createResponse($data);

    /**
     * @param $data
     * @return Klarna
     */
    protected function prepareConnector($data)
    {
        return $this->createKlarnaConnector($data);
    }

    /**
     * Creates an instance of Klarna, an also check, whether provided data are an array (common functionality)
     *
     * @param array $data
     * @return Klarna
     * @throws \InvalidArgumentException
     */
    protected function createKlarnaConnector($data)
    {
        if (( ! is_array($data))) {
            throw new InvalidArgumentException('Data parameter should be an array');
        }
        $klarnaConnector = new Klarna();
        $country = KlarnaCountry::fromCode($data['country']);
        $language = KlarnaLanguage::fromCode($data['language']);
        $currency = KlarnaCurrency::fromCode($data['currency']);
        $mode = empty($data['testMode']) ? Klarna::LIVE : Klarna::BETA;
        $klarnaConnector->config(
            $data['merchantId'],
            $data['sharedSecret'],
            $country,
            $language,
            $currency,
            $mode
        );
        if (( ! empty($data['clientIp']))) {
            $klarnaConnector->setClientIP($data['clientIp']);
        }
        return $klarnaConnector;
    }

    /**
     * @param ItemBag|null $itemBag
     * @return array
     */
    protected function extractArticles(ItemBag $itemBag = null)
    {
        if (empty($itemBag)) {
            return [];
        }
        $articles = [];
        $items = $itemBag->all();
        /** @var \Subscribo\Omnipay\Shared\Item $item */
        foreach ($items as $item) {
            $article = [
                'quantity' => $item->getQuantity(),
                'artNo' => $item->getIdentifier(),
                'title' => $item->getName(),
                'price' => $item->getPrice(),
                'vat'   => $item->getTaxPercent() ?: 0,
                'discount' => $item->getDiscountPercent() ?: 0,
                'flags' => $item->getFlags(),
            ];
            $articles[] = $article;
        }
        return $articles;
    }
}
