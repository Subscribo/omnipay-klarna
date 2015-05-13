<?php

namespace Omnipay\Klarna\Traits;

trait AbstractGatewayDefaultParametersGettersAndSettersTrait
{
    /**
     * @return string|int
     */
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    /**
     * @param string|int $value
     * @return $this
     */
    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * @return string
     */
    public function getSharedSecret()
    {
        return $this->getParameter('sharedSecret');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setSharedSecret($value)
    {
        return $this->setParameter('sharedSecret', $value);
    }

    /**
     * @return int|string
     */
    public function getLanguage()
    {
        return $this->getParameter('language');
    }

    /**
     * @param int|string $value
     * @return $this
     */
    public function setLanguage($value)
    {
        return $this->setParameter('language', $value);
    }

    /**
     * Get country of merchant
     * @return int|string
     */
    public function getCountry()
    {
        return $this->getParameter('country');
    }

    /**
     * Set country of merchant
     * @param int|string $value
     * @return $this
     */
    public function setCountry($value)
    {
        return $this->setParameter('country', $value);
    }

    /**
     * Sets Language, Country, and  Currency (if available)
     * @param string $value
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setLocale($value)
    {
        $normalized = strtr($value, ['_' => '-']);
        $parts = explode('-', $normalized);
        if (empty($parts[1])) {
            throw new \InvalidArgumentException('Locale should be in the format language-country');
        }
        $language = strtolower($parts[0]);
        $country = strtoupper($parts[1]);
        $this->setLanguage($language);
        $this->setCountry($country);
        $this->setDefaultCurrency($country);
        return $this;
    }

    /**
     * Sets default currency for country according to current (May 2015) situation
     *
     * @param string $country
     * @return $this
     */
    private function setDefaultCurrency($country)
    {
        switch ($country) {
            case 'DK':
                return $this->setCurrency('DKK');
            case 'NO':
                return $this->setCurrency('NOK');
            case 'SE':
                return $this->setCurrency('SEK');
            case 'AT':
            case 'BE':
            case 'CY':
            case 'DE':
            case 'EE':
            case 'ES':
            case 'FI':
            case 'FR':
            case 'GR':
            case 'IE':
            case 'IT':
            case 'LT':
            case 'LU':
            case 'LV':
            case 'MT':
            case 'NL':
            case 'PT':
            case 'SI':
            case 'SK':
                return $this->setCurrency('EUR');
            default:
        }
        return $this->setCurrency('');
    }
}
