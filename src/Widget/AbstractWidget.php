<?php

namespace Omnipay\Klarna\Widget;

use Subscribo\Omnipay\Shared\Widget\AbstractWidget as Base;

/**
 * Abstract Class AbstractWidget
 *
 * @package Omnipay\Klarna\Widget
 */
abstract class AbstractWidget extends Base
{
    public function getDefaultParameters()
    {
        return [
            'merchantId' => '',
            'country' => ['', 'de', 'at', 'dk', 'fi', 'nl', 'no', 'se'],
            'language' => ['', 'de', 'da', 'fi', 'nl', 'nb', 'sv'],
            'price' => '',
            'charge' => '',
            'color' => ['blue-black', 'white', 'black'],
        ];
    }

    /**
     * @param int|string $merchantId
     * @param string $locale
     * @param int|string $price
     * @param int|string|null $invoiceFee
     * @param string|null $layout
     * @param int|string|null $width
     * @param int|string|null $height
     * @param string|null $style
     * @return string
     */
    public static function assemblePaymentMethodWidgetHtml($merchantId, $locale, $price, $invoiceFee = null, $layout = null, $width = '210', $height = '70', $style = null)
    {
        if (is_null($width)) {
            $width = 210;
        }
        if (is_null($height)) {
            $height = 70;
        }
        $styleParts = [];
        if ($style) {
            $styleParts[] = $style;
        }
        if ($width) {
            $styleParts[] = 'width:'.$width.'px';
        }
        if ($height) {
            $styleParts[] = 'height:'.$height.'px';
        }
        $html = '<div';
        if ($styleParts) {
            $html .= ' style="'.addslashes(implode('; ', $styleParts)).'"';
        }
        $html .= ' class="klarna-widget klarna-part-payment"';
        $html .= ' data-eid="'.$merchantId.'"';
        $html .= ' data-locale="'.$locale.'"';
        $html .= ' data-price="'.$price.'"';
        if ($invoiceFee) {
            $html .= ' data-invoice-fee="'.$invoiceFee.'"';
        }
        if ($layout) {
            $html .= ' data-layout="'.$layout.'"';
        }
        $html .= ' > </div>';
        return $html;
    }

    /**
     * @param string $locale
     * @param string $color
     * @param int|string|null $width
     * @return string
     */
    public static function assembleLogoUrl($locale = 'de_at', $color = 'blue-black', $width = null)
    {
        $url = 'https://cdn.klarna.com/1.0/shared/image/generic/logo/'.$locale.'/basic/'.$color.'.png';
        if ($width) {
            $url .= '?width='.$width;
        }
        return $url;
    }

    /**
     * @param int|string $merchantId
     * @param string $locale
     * @param string $logoName
     * @param int|string|null $width
     * @param int|null $layout
     * @return string
     */
    public static function assembleTooltipHtml($merchantId, $locale = 'de_de', $logoName = 'blue-black', $width = null, $layout = null)
    {
        $html = '<div class="klarna-widget klarna-logo-tooltip"';
        $html .= ' data-eid="'.$merchantId.'"';
        $html .= ' data-locale="'.$locale.'"';
        if ($layout) {
            $html .= ' data-layout="'.$layout.'"';
        }
        $html .= ' data-logo-name="'.$logoName.'"';
        if ($width) {
            $html .= ' data-width="'.$width.'"';
        }
        $html .= ' > </div>';
        return $html;
    }

    /**
     * @return string
     */
    public static function assembleLoadJavascript()
    {
        return '<script async src="https://cdn.klarna.com/1.0/code/client/all.js"></script>';
    }

    /**
     * @param array $parameters
     * @param bool $loadJavascript
     * @return string
     */
    public function renderPaymentMethodWidget($parameters = [], $loadJavascript = true)
    {
        $parameters = $this->checkParameters($parameters, ['merchantId', 'country', 'language', 'price']);
        $locale = strtolower($parameters['language'].'_'.$parameters['country']);
        $charge = empty($parameters['charge']) ? 0 : $parameters['charge'];
        $width = isset($parameters['width']) ? $parameters['width'] : null;
        $height = isset($parameters['height']) ? $parameters['height'] : null;
        $style = isset($parameters['style']) ? $parameters['style'] : null;
        $layout = isset($parameters['layout']) ? $parameters['layout'] : null;
        if (is_null($layout)) {
            $color = isset($parameters['color']) ? $parameters['color'] : null;
            if ('white' === $color) {
                $layout = 'deep-v2';
            }
        }
        $html = static::assemblePaymentMethodWidgetHtml($parameters['merchantId'], $locale, $parameters['price'], $charge, $layout, $width, $height, $style);
        if ($loadJavascript) {
            return $html."\n".static::assembleLoadJavascript();
        }
        return $html;
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function renderLogoUrl($parameters = [])
    {
        $parameters = $this->checkParameters($parameters, ['country', 'language', 'color']);
        $locale = strtolower($parameters['language'].'_'.$parameters['country']);
        $width = isset($parameters['width']) ? $parameters['width'] : null;
        $color = static::sanitizeColor($parameters['color']);
        $url = static::assembleLogoUrl($locale, $color, $width);
        return $url;
    }

    /**
     * @param array $parameters
     * @param bool $loadJavascript
     * @return string
     */
    public function renderTooltip($parameters = [], $loadJavascript = true)
    {
        $parameters = $this->checkParameters($parameters, ['merchantId', 'country', 'language', 'color']);
        $locale = strtolower($parameters['language'].'_'.$parameters['country']);
        $width = isset($parameters['width']) ? $parameters['width'] : null;
        $layout = isset($parameters['layout']) ? $parameters['layout'] : null;
        $logoName = static::sanitizeColor($parameters['color']);
        if (( ! empty($parameters['tuv']))) {
            $logoName = ($logoName === 'white') ? 'white+tuv' : 'blue+tuv';
        }
        $html = static::assembleTooltipHtml($parameters['merchantId'], $locale, $logoName, $width, $layout);
        if ($loadJavascript) {
            return $html."\n".static::assembleLoadJavascript();
        }
        return $html;
    }

    /**
     * @return string|null
     */
    public function getCountry()
    {
        return $this->getParameter('country');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setCountry($value)
    {
        return $this->setParameter('country', $value);
    }

    /**
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->getParameter('language');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setLanguage($value)
    {
        return $this->setParameter('language', $value);
    }

    /**
     * @return string|null
     */
    public function getColor()
    {
        return $this->getParameter('color');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setColor($value)
    {
        return $this->setParameter('color', $value);
    }

    /**
     * @return string|int|null
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
     * @return string|int|null
     */
    public function getPrice()
    {
        return $this->getParameter('price');
    }

    /**
     * @param string|int $value
     * @return $this
     */
    public function setPrice($value)
    {
        return $this->setParameter('price', $value);
    }

    /**
     * @return string|int|null
     */
    public function getCharge()
    {
        return $this->getParameter('charge');
    }

    /**
     * @param string|int $value
     * @return $this
     */
    public function setCharge($value)
    {
        return $this->setParameter('charge', $value);
    }

    /**
     * @param string $color
     * @return string
     */
    protected static function sanitizeColor($color)
    {
        $color = trim(strtolower($color));
        if (('white' === $color) or ('black' === $color)) {
            return $color;
        }
        return 'blue-black';
    }
}
