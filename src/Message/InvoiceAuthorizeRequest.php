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
                $data['gender'] = strtolower(substr($gender, 0, 1));
            break;
            default:
                $pno = $card->getSocialSecurityNumber();
                if (empty($pno)) {
                    throw new InvalidRequestException('SocialSecurityNumber is a required parameter for this country');
                }
                $data['gender'] = null;
        }
        $data['pno'] = $pno;
        $data['articles'] = $this->extractArticles($this->getItems());
        return $data;
    }


    public function sendData($data)
    {
        $k = $this->createKlarnaConnector($data);

        /** @var \Subscribo\Omnipay\Shared\CreditCard $card */
        $card = $data['card'];
        $billingAddress = $this->createKlarnaAddr($card);
        if ($card->getShippingContactDifferences()) {
            $shippingAddress = $this->createKlarnaAddr($card, true);
        } else {
            $shippingAddress = $billingAddress;
        }
        $k->setAddress(KlarnaFlags::IS_BILLING, $billingAddress);
        $k->setAddress(KlarnaFlags::IS_SHIPPING, $shippingAddress);
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
        $result = $k->reserveAmount($data['pno'], $data['gender'], $data['amount']);

        $this->response = $this->createResponse($result);
        return $this->response;
    }


    protected function createResponse(array $data)
    {
        return new InvoiceAuthorizeResponse($this, $data);
    }


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
        return $result;
    }
}
