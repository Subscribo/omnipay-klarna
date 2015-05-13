<?php

namespace Omnipay\Klarna\Message;

use Klarna;
use KlarnaCountry;
use KlarnaCurrency;
use KlarnaLanguage;
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
    /**
     * Creates an instance of Klarna, an also check, whether provided data are an array (common functionality)
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
        $mode = $data['testMode'] ? Klarna::BETA : Klarna::LIVE;
        $klarnaConnector->config(
            $data['merchantId'],
            $data['sharedSecret'],
            $country,
            $language,
            $currency,
            $mode
        );
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
