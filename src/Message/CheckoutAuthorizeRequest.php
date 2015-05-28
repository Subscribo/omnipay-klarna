<?php

namespace Omnipay\Klarna\Message;

use Klarna_Checkout_Connector;
use Klarna_Checkout_Order;
use InvalidArgumentException;
use Subscribo\Omnipay\Shared\ItemBag;
use Omnipay\Klarna\Message\AbstractCheckoutRequest;
use Omnipay\Klarna\Message\CheckoutAuthorizeResponse;
use Subscribo\PsrHttpMessageTools\Factories\UriFactory;

/**
 * Class CheckoutAuthorizeRequest
 *
 * @package Omnipay\Klarna
 */
class CheckoutAuthorizeRequest extends AbstractCheckoutRequest
{
    public function getData()
    {
        $this->validate('sharedSecret');
        $data = ['sharedSecret' => $this->getSharedSecret()];
        session_start();
        if (( ! empty($_SESSION['klarna_checkout']))) {
            $data['checkoutOrderUri'] = $_SESSION['klarna_checkout'];

            return $data;
        }
        $this->validate('merchantId', 'language', 'country', 'currency', 'termsUrl', 'authorizeUrl', 'returnUrl', 'pushUrl');

        $create = $this->addItemsToArray($this->getItems());
        $create['purchase_country'] = strtoupper($this->getCountry());
        $create['purchase_currency'] = strtoupper($this->getCurrency());
        $create['locale'] = strtolower($this->getLanguage().'-'.$this->getCountry());
        $create['merchant']['id'] = $this->getMerchantId();
        $create['merchant']['terms_uri'] = $this->getTermsUrl();
        $create['merchant']['checkout_uri'] = $this->getAuthorizeUrl();
        $queryData = $this->getAdditionalQueryParameters();
        $create['merchant']['confirmation_uri'] = $this->prepareParametrizedUrl($this->getReturnUrl(), $queryData);
        $create['merchant']['push_uri'] = $this->prepareParametrizedUrl($this->getPushUrl(), $queryData);

        $data = $this->getParameters();
        $data['create'] = $create;

        return $data;
    }

    public function sendData($data)
    {
        if (( ! is_array($data))) {
            throw new InvalidArgumentException('Data parameter should be an array');
        }
        Klarna_Checkout_Order::$baseUri = $this->getEndpointUrl();
        Klarna_Checkout_Order::$contentType = 'application/vnd.klarna.checkout.aggregated-order-v2+json';
        $connector = Klarna_Checkout_Connector::create($data['sharedSecret']);
        if (isset($data['checkoutOrderUri'])) {
            $order = new Klarna_Checkout_Order($connector, $data['checkoutOrderUri']);
        } else {
            $order = new Klarna_Checkout_Order($connector);
            $order->create($data['create']);
        }
        $order->fetch();
        $sessionId = $order->getLocation();
        $_SESSION['klarna_checkout'] = $sessionId;
        $this->response = $this->createResponse(['order' => $order]);
        return $this->response;
    }

    /**
     * @return string|null
     */
    public function getTermsUrl()
    {
        return $this->getParameter('termsUrl');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setTermsUrl($value)
    {
        return $this->setParameter('termsUrl', $value);
    }

    /**
     * @return string|null
     */
    public function getAuthorizeUrl()
    {
        return $this->getParameter('authorizeUrl');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setAuthorizeUrl($value)
    {
        return $this->setParameter('authorizeUrl', $value);
    }

    /**
     * @return string|null
     */
    public function getPushUrl()
    {
        return $this->getParameter('pushUrl');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPushUrl($value)
    {
        return $this->setParameter('pushUrl', $value);
    }

    /**
     * @return array
     */
    public function getAdditionalQueryParameters()
    {
        $value = $this->getParameter('additionalQueryParameters');
        return $value ?: [];
    }

    /**
     * @param array $value
     * @return $this
     */
    public function setAdditionalQueryParameters(array $value)
    {
        return $this->setParameter('additionalQueryParameters', $value);
    }

    /**
     * @param array $data
     * @return CheckoutAuthorizeResponse
     */
    protected function createResponse($data)
    {
        return new CheckoutAuthorizeResponse($this, $data);
    }

    /**
     * @param ItemBag $itemBag
     * @param array $addTo
     * @return array
     */
    protected function addItemsToArray(ItemBag $itemBag = null, $addTo = [])
    {
        if (empty($itemBag)) {
            return $addTo;
        }
        $items = $itemBag->all();
        /** @var \Subscribo\Omnipay\Shared\Item $item */
        foreach ($items as $item) {
            $taxPercent = $item->getTaxPercent() ?: '0';
            $taxRate = intval(bcmul($taxPercent, '100', 0));
            $price = $item->getPrice() ?: '0';
            $unitPrice = intval(bcmul($price, '100', 0));
            $article = [
                'name' => $item->getName(),
                'reference' => $item->getIdentifier(),
                'quantity' => $item->getQuantity(),
                'unit_price' => $unitPrice,
                'tax_rate'   => $taxRate,
            ];
            $discountPercent = $item->getDiscountPercent();
            if ($discountPercent) {
                $article['discount_rate'] = intval(bcmul($discountPercent, '100', 0));
            }
            $addTo['cart']['items'][] = $article;
        }
        return $addTo;
    }

    /**
     * Prepares Url with parameters, adding not url encoded 'klarna_order' query parameter
     *
     * @param string $baseUrl
     * @param array $queryParameters
     * @return string
     */
    private function prepareParametrizedUrl($baseUrl, array $queryParameters)
    {
        $queryParameters['klarna_order'] = '{checkout.order.uri}';
        $uri = (string) UriFactory::make($baseUrl, $queryParameters);
        $decoded = '{checkout.order.uri}';
        $encoded = urlencode($decoded);
        $uri = str_replace($encoded, $decoded, $uri);
        return $uri;
    }

}
