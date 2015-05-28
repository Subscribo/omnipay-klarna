<?php

namespace Omnipay\Klarna\Widget;

use Omnipay\Klarna\Widget\AbstractWidget;

/**
 * Class CheckoutWidget
 *
 * @package Omnipay\Klarna
 */
class CheckoutWidget extends AbstractWidget
{
    /**
     * @param string $locale
     * @param string $design
     * @param string $color
     * @param null $width
     * @param int|string|null $width
     * @return string
     */
    public static function assembleBadgeUrl($locale = 'de_at', $design = 'long', $color = 'blue', $width = null)
    {
        $color = (strtolower($color) === 'white') ? 'white' : 'blue';
        $design = ((strtolower($design) === 'short')) ? 'short' : 'long';
        $url = 'https://cdn.klarna.com/1.0/shared/image/generic/badge/'.$locale.'/checkout/'.$design.'-'.$color.'.png';
        if ($width) {
            $url .= '?width='.$width;
        }
        return $url;
    }

    /**
     * @param int|string $merchantId
     * @param string $locale Possible values: 'sv_se', 'nb_no', 'fi_fi', 'de_de', 'en_us', 'en_gb'
     * @param string $design Possible values: 'long', 'short'
     * @param string $color Possible values: 'blue', 'white'
     * @param int|string|null $width Width in pixels without 'px'
     * @param string|null $layout Possible values: 'blue' (default), 'white'
     * @return string
     */
    public static function assembleBadgeTooltipHtml($merchantId, $locale = 'de_de', $design = 'long', $color = 'blue', $width = null, $layout = null)
    {
        $color = (strtolower($color) === 'white') ? 'white' : 'blue';
        $design = ((strtolower($design) === 'short')) ? 'short' : 'long';
        $locale = ((strtolower($locale) === 'at_de')) ? 'de_de' : $locale;
        $html = '<div class="klarna-widget klarna-badge-tooltip"';
        $html .= ' data-eid="'.$merchantId.'"';
        $html .= ' data-locale="'.$locale.'"';
        if ($layout) {
            $html .= ' data-layout="'.$layout.'"';
        }
        $html .= ' data-badge-name="'.$design.'-'.$color.'"';
        if ($width) {
            $html .= ' data-badge-width="'.$width.'"';
        }
        $html .= ' > </div>';
        return $html;
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function renderBadgeUrl($parameters = [])
    {
        $parameters = $this->checkParameters($parameters, ['country', 'language', 'design', 'color']);
        $locale = strtolower($parameters['language'].'_'.$parameters['country']);
        $width = isset($parameters['width']) ? $parameters['width'] : null;
        $url = static::assembleBadgeUrl($locale, $parameters['design'], $parameters['color'], $width);
        return $url;
    }

    /**
     * @param array $parameters
     * @param bool $loadJavascript
     * @return string
     */
    public function renderBadgeTooltip($parameters = [], $loadJavascript = true)
    {
        $parameters = $this->checkParameters($parameters, ['merchantId', 'country', 'language', 'design', 'color']);
        $locale = strtolower($parameters['language'].'_'.$parameters['country']);
        $width = isset($parameters['width']) ? $parameters['width'] : null;
        $layout = isset($parameters['layout']) ? $parameters['layout'] : null;
        $html = static::assembleBadgeTooltipHtml(
            $parameters['merchantId'],
            $locale,
            $parameters['design'],
            $parameters['color'],
            $width,
            $layout
        );
        if ($loadJavascript) {
            return $html."\n".static::assembleLoadJavascript();
        }
        return $html;
    }


    public function getDefaultParameters()
    {
        $result = parent::getDefaultParameters();
        $result['design'] = ['long', 'short'];

        return $result;
    }

    /**
     * @return string|null
     */
    public function getDesign()
    {
        return $this->getParameter('design');
    }

    /**
     * @param string $value Possible values: 'long', 'short'
     * @return $this
     */
    public function setDesign($value)
    {
        return $this->setParameter('design', $value);
    }
}
