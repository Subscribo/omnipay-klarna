<?php

namespace Omnipay\Klarna\Message;

use Klarna;
use KlarnaAddr;
use KlarnaCountry;
use KlarnaCurrency;
use KlarnaFlags;
use KlarnaLanguage;
use Omnipay\Klarna\Message\AbstractInvoiceRequest;
use Omnipay\Klarna\Message\InvoiceAuthorizeResponse;
use Omnipay\Klarna\Traits\InvoiceGatewayDefaultParametersGettersAndSettersTrait;
use Omnipay\Klarna\Traits\OrderIdOneAndTwoGettersAndSettersTrait;
use Omnipay\Klarna\Widget\InvoiceWidget;
use Omnipay\Common\Exception\InvalidRequestException;
use Subscribo\Omnipay\Shared\CreditCard;
use Subscribo\Omnipay\Shared\Helpers\AddressParser;

/**
 * Class InvoiceAuthorizeRequest
 *
 * @package Omnipay\Klarna
 *
 * @method \Omnipay\Klarna\Message\InvoiceAuthorizeResponse send() send()
 */
class InvoiceAuthorizeRequest extends AbstractInvoiceRequest
{
    use InvoiceGatewayDefaultParametersGettersAndSettersTrait;
    use OrderIdOneAndTwoGettersAndSettersTrait;

    public function getData()
    {
        $this->validate('merchantId', 'sharedSecret', 'country', 'language', 'currency', 'card');
        $data = $this->getParameters();
        $data['amount'] = $this->getAmount() ?: -1;
        $card = $this->getCard();
        $country = strtoupper($this->getCountry());
        switch ($country) {
            case 'AT':
            case 'DE':
            case 'NL':
                $gender = $card->getGender();
                $pno = $card->getBirthday('dmY');
                if (empty($gender)) {
                    throw new InvalidRequestException('Gender is a required parameter for AT/DE/NL');
                }
                if (empty($pno)) {
                    throw new InvalidRequestException('Birthday is a required parameter for AT/DE/NL');
                }
                if (CreditCard::GENDER_MALE === $gender) {
                    $data['gender'] = 'm';
                } elseif (CreditCard::GENDER_FEMALE === $gender) {
                    $data['gender'] = 'f';
                } else {
                    $data['gender'] = strtolower(substr($gender, 0, 1));
                }
                break;
            default:
                $pno = $card->getNationalIdentificationNumber();
                if (empty($pno)) {
                    throw new InvalidRequestException('NationalIdentificationNumber is a required parameter for this country');
                }
                $data['gender'] = null;
        }
        $data['pno'] = $pno;
        $data['articles'] = $this->extractArticles($this->getItems());
        $data['orderId1'] = $this->getOrderId1();
        $data['orderId2'] = $this->getOrderId2();

        return $data;
    }

    /**
     * @param array $parameters
     * @return InvoiceWidget
     */
    public function getWidget(array $parameters = [])
    {
        $parameters = array_replace($this->getParameters(), $parameters);
        if (empty($parameters['price'])) {
            $amount = $this->getAmountInteger();
            if ($amount <= 0) {
                $amount = $this->calculateAmount();
            }
            $parameters['price'] = $amount;
        }
        $widget = new InvoiceWidget($parameters);

        return $widget;
    }

    /**
     * @return string
     */
    public function calculateAmount()
    {
        $this->validate('merchantId', 'sharedSecret', 'country', 'language', 'currency');
        $data = $this->getParameters();
        $data['articles'] = $this->extractArticles($this->getItems());
        $k = $this->createKlarnaConnector($data);
        foreach ($data['articles'] as $article) {
            $flags = isset($article['flags']) ? $article['flags'] : KlarnaFlags::INC_VAT;
            $k->addArticle(
                $article['quantity'],
                $article['artNo'],
                $article['title'],
                $article['price'],
                $article['vat'],
                $article['discount'],
                $flags
            );
        }
        $summarized = $k->summarizeGoodsList();
        $amount = bcdiv(strval($summarized), '100', 2);

        return $amount;
    }

    /**
     * @param $data
     * @return Klarna
     */
    protected function prepareConnector($data)
    {
        $connector = $this->createKlarnaConnector($data);

        /** @var \Subscribo\Omnipay\Shared\CreditCard $card */
        $card = $data['card'];
        $billingAddress = $this->createKlarnaAddr($card);
        if ($card->getShippingContactDifferences()) {
            $shippingAddress = $this->createKlarnaAddr($card, true);
        } else {
            $shippingAddress = $billingAddress;
        }
        $connector->setAddress(KlarnaFlags::IS_BILLING, $billingAddress);
        $connector->setAddress(KlarnaFlags::IS_SHIPPING, $shippingAddress);
        foreach ($data['articles'] as $article) {
            $flags = isset($article['flags']) ? $article['flags'] : KlarnaFlags::INC_VAT;
            $connector->addArticle(
                $article['quantity'],
                $article['artNo'],
                $article['title'],
                $article['price'],
                $article['vat'],
                $article['discount'],
                $flags
            );
        }
        if (isset($data['orderId1']) or isset($data['orderId2'])) {
            $connector->setEstoreInfo($data['orderId1'], $data['orderId2']);
        }

        return $connector;
    }

    protected function sendRequestViaConnector(Klarna $connector, array $data)
    {
        return $connector->reserveAmount($data['pno'], $data['gender'], $data['amount']);
    }

    /**
     * @param array|\KlarnaException $data
     * @return InvoiceAuthorizeResponse
     */
    protected function createResponse($data)
    {
        return new InvoiceAuthorizeResponse($this, $data);
    }

    /**
     * @param CreditCard $card
     * @param bool $isShipping
     * @return KlarnaAddr
     */
    protected function createKlarnaAddr(CreditCard $card, $isShipping = false)
    {
        $phone = $isShipping ? $card->getShippingPhone() : $card->getPhone();
        $mobile = $isShipping ? $card->getShippingMobile() : $card->getMobile();
        $firstName = $isShipping ? $card->getShippingFirstName() : $card->getFirstName();
        $lastName = $isShipping ? $card->getShippingLastName() : $card->getLastName();
        $postCode = $isShipping ? $card->getShippingPostcode() : $card->getPostcode();
        $city = $isShipping ? $card->getShippingCity() : $card->getCity();
        $country = strtoupper($isShipping ? $card->getShippingCountry() : $card->getCountry());
        $address1 = $isShipping ? $card->getShippingAddress1() : $card->getAddress1();
        $address2 = $isShipping ? $card->getShippingAddress2() : $card->getAddress2();
        $company = $isShipping ? $card->getShippingCompany() : $card->getCompany();

        $careof = '';
        $street = $address1;
        $houseNo = null;
        $houseExt = null;
        if (('AT' === $country) or ('DE' === $country) or ('NL' === $country)) {
            if (is_null($address2)) {
                list($street, $houseNo) = AddressParser::parseFirstLine($address1);
            } else {
                $houseNo = $address2;
            }
        } else {
            if ($address2) {
                $street .= ' '.$address2;
            }
        }
        $result = new KlarnaAddr(
            $card->getEmail(),
            $phone,
            $mobile,
            $firstName,
            $lastName,
            $careof,
            $street,
            $postCode,
            $city,
            KlarnaCountry::fromCode($country),
            $houseNo,
            $houseExt
        );
        if ($company) {
            $result->setCompanyName($company);
            $result->isCompany = true;
        }
        return $result;
    }
}
