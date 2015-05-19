<?php

namespace Omnipay\Klarna\Widget;

use Subscribo\Omnipay\Shared\Widget\AbstractWidget;
use Subscribo\Omnipay\Shared\Exception\WidgetNotRenderableException;

/**
 * Class InvoiceWidget
 *
 * @package Omnipay\Klarna
 */
class InvoiceWidget extends AbstractWidget
{
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
     * @param string $elementId
     * @param int|string $merchantId
     * @param string $locale
     * @param int|string $charge
     * @param string|null $type
     * @param string|null $linkClassName
     * @param string|null $openPopupCallback
     * @param string|null $closePopupCallback
     * @return string
     */
    public static function assembleTermsInvoiceObject($elementId, $merchantId, $locale = 'de_at', $charge = 0, $type = null, $linkClassName = null, $openPopupCallback = null, $closePopupCallback = null)
    {
        return static::assembleTermsObject('Klarna.Terms.Invoice', $elementId, $merchantId, $locale, $charge, $type, $linkClassName, $openPopupCallback, $closePopupCallback);
    }

    /**
     * @param string $elementId
     * @param int|string $merchantId
     * @param string $locale
     * @param string|null $type
     * @param string|null $linkClassName
     * @param string|null $openPopupCallback
     * @param string|null $closePopupCallback
     * @return string
     */
    public static function assembleTermsConsentObject($elementId, $merchantId, $locale = 'de_at', $type = null, $linkClassName = null, $openPopupCallback = null, $closePopupCallback = null)
    {
        return static::assembleTermsObject('Klarna.Terms.Consent', $elementId, $merchantId, $locale, null, $type, $linkClassName, $openPopupCallback, $closePopupCallback);
    }

    /**
     * @param string $elementId
     * @param int|string $merchantId
     * @param string $locale
     * @param string|null $type
     * @param string|null $linkClassName
     * @param string|null $openPopupCallback
     * @param string|null $closePopupCallback
     * @return string
     */
    public static function assembleTermsAccountObject($elementId, $merchantId, $locale = 'de_de', $type = null, $linkClassName = null, $openPopupCallback = null, $closePopupCallback = null)
    {
        return static::assembleTermsObject('Klarna.Terms.Account', $elementId, $merchantId, $locale, null, $type, $linkClassName, $openPopupCallback, $closePopupCallback);
    }

    /**
     * @param string $elementId
     * @param string|null $AGBUrl
     * @return string
     */
    public static function assembleTermsConsentText($elementId, $AGBUrl = null)
    {
        $einwilligung = '<span id="'.addslashes($elementId).'"></span>';
        $dieAGB = $AGBUrl ? '<a href="'.$AGBUrl.'" target="_blank">die AGB</a>' : 'die AGB';

        $text = 'Mit der Übermittlung der für die Abwicklung der gewählten Klarna Zahlungsmethode und einer Identitäts-';
        $text .= ' und Bonitätsprüfung erforderlichen Daten an Klarna bin ich einverstanden. Meine ';
        $text .= $einwilligung;
        $text .= ' kann ich jederzeit mit Wirkung für die Zukunft widerrufen. Es gelten ';
        $text .= $dieAGB;
        $text .= ' des Händlers.';
        return $text;
    }

    /**
     * @param string $constructor
     * @param string $elementId
     * @param string|int $merchantId
     * @param string $locale
     * @param string|int|null $charge
     * @param string|null $type
     * @param string|null $linkClassName
     * @param string|null $openPopupCallback
     * @param string|null $closePopupCallback
     * @return string
     */
    protected static function assembleTermsObject($constructor, $elementId, $merchantId, $locale = 'de_at', $charge = null, $type = null, $linkClassName = null, $openPopupCallback = null, $closePopupCallback = null)
    {
        $javascript = 'new '.$constructor.'({';
        $javascript .= " el: '".addslashes($elementId)."'";
        $javascript .= ", eid: '".addslashes($merchantId)."'";
        $javascript .= ", locale: '".addslashes($locale)."'";
        if (isset($charge)) {
            $javascript .= ", charge: '".addslashes($charge)."'";
        }
        if ($type) {
            $javascript .= ", type: '".addslashes($type)."'";
        }
        if ($linkClassName) {
            $javascript .= ", linkClassName: '".addslashes($linkClassName)."'";
        }
        if ($openPopupCallback) {
            //todo to implement
        }
        if ($closePopupCallback) {
            //todo to implement
        }
        $javascript .= ' });';
        return $javascript;
    }

    /**
     * @return string
     */
    public static function assembleLoadJavascript()
    {
        return '<script async src="https://cdn.klarna.com/1.0/code/client/all.js"></script>';
    }

    /**
     * @return string
     */
    public static function assembleLoadTermsJavascript()
    {
        $javascript = '<script src="https://cdn.klarna.com/public/kitt/core/v1.0/js/klarna.min.js"></script>'."\n";
        $javascript .= '<script src="https://cdn.klarna.com/public/kitt/toc/v1.1/js/klarna.terms.min.js"></script>';
        return $javascript;
    }

    /**
     * @param array $parameters
     * @return bool
     */
    public function isRenderable($parameters = [])
    {
        if (is_array($parameters)) {
            $parameters = array_replace($this->getParameters(), $parameters);
        }
        return ! $this->collectRenderingObstacles($parameters, true);
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function render($parameters = [])
    {
        return $this->renderPaymentMethodWidget($parameters);
    }

    /**
     * @param array $parameters
     * @param bool $loadJavascript
     * @return string
     */
    public function renderPaymentMethodWidget($parameters = [], $loadJavascript = true)
    {
        $parameters = $this->checkParameters($parameters);
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
     * @param array $parameters
     * @param bool $loadJavascript
     * @param string|bool $elementId
     * @return string
     */
    public function renderTermsInvoiceHtml($parameters = [], $loadJavascript = true, $elementId = true)
    {
        $parameters = $this->checkParameters($parameters, ['merchantId', 'country', 'language']);
        $locale = strtolower($parameters['language'].'_'.$parameters['country']);
        if (true === $elementId) {
            $elementId = 'klarna-invoice-rechnungsbedingungen-n'.mt_rand();
        }
        $charge = empty($parameters['charge']) ? 0 : $parameters['charge'];
        $type = isset($parameters['outputDeviceType']) ? $parameters['outputDeviceType'] : null;
        $linkClassName = isset($parameters['linkClassName']) ? $parameters['linkClassName'] : null;
        $openPopupCallback = isset($parameters['openPopupCallback']) ? $parameters['openPopupCallback'] : null;
        $closePopupCallback = isset($parameters['closePopupCallback']) ? $parameters['closePopupCallback'] : null;

        $object = static::assembleTermsInvoiceObject($elementId, $parameters['merchantId'], $locale, $charge, $type, $linkClassName, $openPopupCallback, $closePopupCallback);
        $html = '<span id="'.$elementId.'"></span>'."\n";
        $html .= '<script>'.$object.'</script>';
        if ($loadJavascript) {
            return static::assembleLoadTermsJavascript()."\n".$html;
        }
        return $html;
    }

    /**
     * @param array $parameters
     * @param bool $loadJavascript
     * @param string|bool $elementId
     * @return string
     */
    public function renderTermsConsentHtml($parameters = [], $loadJavascript = true, $elementId = true)
    {
        $parameters = $this->checkParameters($parameters, ['merchantId', 'country', 'language']);
        $locale = strtolower($parameters['language'].'_'.$parameters['country']);
        if (true === $elementId) {
            $elementId = 'klarna-consent-einwilligung-n'.mt_rand();
        }
        $type = isset($parameters['outputDeviceType']) ? $parameters['outputDeviceType'] : null;
        $linkClassName = isset($parameters['linkClassName']) ? $parameters['linkClassName'] : null;
        $openPopupCallback = isset($parameters['openPopupCallback']) ? $parameters['openPopupCallback'] : null;
        $closePopupCallback = isset($parameters['closePopupCallback']) ? $parameters['closePopupCallback'] : null;
        $AGBUrl = isset($parameters['AGBUrl']) ? $parameters['AGBUrl'] : null;

        $object = static::assembleTermsConsentObject($elementId, $parameters['merchantId'], $locale, $type, $linkClassName, $openPopupCallback, $closePopupCallback);
        $html = static::assembleTermsConsentText($elementId, $AGBUrl)."\n";
        $html .= '<script>'.$object.'</script>';
        if ($loadJavascript) {
            return static::assembleLoadTermsJavascript()."\n".$html;
        }
        return $html;
    }

    /**
     * @param array $parameters
     * @param bool $loadJavascript
     * @param string|bool $elementId
     * @return string
     */
    public function renderTermsAccountHtml($parameters = [], $loadJavascript = true, $elementId = true)
    {
        $parameters = $this->checkParameters($parameters, ['merchantId', 'country', 'language']);
        $locale = strtolower($parameters['language'].'_'.$parameters['country']);
        if (true === $elementId) {
            $elementId = 'klarna-account-lesen-sie-mehr-n'.mt_rand();
        }

        $type = isset($parameters['outputDeviceType']) ? $parameters['outputDeviceType'] : null;
        $linkClassName = isset($parameters['linkClassName']) ? $parameters['linkClassName'] : null;
        $openPopupCallback = isset($parameters['openPopupCallback']) ? $parameters['openPopupCallback'] : null;
        $closePopupCallback = isset($parameters['closePopupCallback']) ? $parameters['closePopupCallback'] : null;

        $object = static::assembleTermsAccountObject($elementId, $parameters['merchantId'], $locale, $type, $linkClassName, $openPopupCallback, $closePopupCallback);
        $html = '<span id="'.$elementId.'"></span>'."\n";
        $html .= '<script>'.$object.'</script>';
        if ($loadJavascript) {
            return static::assembleLoadTermsJavascript()."\n".$html;
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
     * @return string|null
     */
    public function getOutputDeviceType()
    {
        return $this->getParameter('outputDeviceType');
    }

    /**
     * @param string|null $value 'desktop' or 'mobile'
     * @return $this
     */
    public function setOutputDeviceType($value)
    {
        return $this->setParameter('outputDeviceType', $value);
    }


    /**
     * @return string|null
     */
    public function getAGBUrl()
    {
        return $this->getParameter('AGBUrl');
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function setAGBUrl($value)
    {
        return $this->setParameter('AGBUrl', $value);
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
     * @param array $parameters
     * @param bool|array $requiredParameters
     * @return array
     * @throws \Subscribo\Omnipay\Shared\Exception\WidgetNotRenderableException
     */
    protected function checkParameters($parameters = [], $requiredParameters = true)
    {
        if (is_array($parameters)) {
            $parameters = array_replace($this->getParameters(), $parameters);
        }
        $obstacles = $this->collectRenderingObstacles($parameters, $requiredParameters);
        if ($obstacles) {
            throw new WidgetNotRenderableException(reset($obstacles));
        }
        return $parameters;
    }

    /**
     * @param array $parameters
     * @param bool|array $requiredParameters
     * @return array
     */
    protected function collectRenderingObstacles($parameters = [], $requiredParameters = true)
    {
        if (true === $requiredParameters) {
            $requiredParameters = ['merchantId', 'country', 'language', 'price'];
        }
        if (( ! is_array($parameters))) {
            return ['Parameters have to be an array'];
        }
        $obstacles = [];
        foreach ($requiredParameters as $requiredParameter) {
            if (empty($parameters[$requiredParameter])) {
                $obstacles[] = "Parameter '".$requiredParameter."' is required";
            }
        }
        return $obstacles;
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
