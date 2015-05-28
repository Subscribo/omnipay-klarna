<?php

namespace Omnipay\Klarna\Widget;

use Omnipay\Klarna\Widget\AbstractWidget;

/**
 * Class InvoiceWidget
 *
 * @package Omnipay\Klarna
 */
class InvoiceWidget extends AbstractWidget
{
    public function getDefaultParameters()
    {
        $result = parent::getDefaultParameters();
        $result['outputDeviceType'] = ['', 'desktop', 'mobile'];
        $result['AGBUrl'] = '';

        return $result;
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
            $javascript .= ", openPopupCallback: ".$openPopupCallback;
        }
        if ($closePopupCallback) {
            $javascript .= ", closePopupCallback: ".$closePopupCallback;
        }
        $javascript .= ' });';
        return $javascript;
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
}
