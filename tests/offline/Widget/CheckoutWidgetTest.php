<?php

namespace Omnipay\Klarna\Widget;

use PHPUnit_Framework_TestCase;
use Omnipay\Klarna\Widget\CheckoutWidget;

class CheckoutWidgetTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyWidget()
    {
        $widget = new CheckoutWidget();

        $this->assertFalse($widget->isRenderable());
        $this->assertSame('', (string) $widget);
        $this->assertSame('', $widget->getMerchantId());
        $this->assertSame('', $widget->getCountry());
        $this->assertSame('', $widget->getLanguage());
        $this->assertSame('', $widget->getPrice());
        $this->assertSame('', $widget->getCharge());
        $this->assertSame('blue-black', $widget->getColor());
        $this->assertSame('long', $widget->getDesign());
        $expectedParameters = [
            'merchantId' => '',
            'country' => '',
            'language' => '',
            'price' => '',
            'charge' => '',
            'color' => 'blue-black',
            'design' => 'long',
        ];
        $this->assertSame($expectedParameters, $widget->getParameters());
        $expectedDefaults = [
            'merchantId' => '',
            'country' => ['', 'de', 'at', 'dk', 'fi', 'nl', 'no', 'se', 'us', 'gb'],
            'language' => ['', 'de', 'da', 'fi', 'nl', 'nb', 'sv', 'en'],
            'price' => '',
            'charge' => '',
            'color' => ['blue-black', 'white', 'black'],
            'design' => ['long', 'short'],
        ];
        $this->assertSame($expectedDefaults, $widget->getDefaultParameters());
        $this->assertSame(['merchantId', 'country', 'language', 'price'], $widget->getRequiredParameters());
    }

    public function testSetters()
    {
        $widget = new CheckoutWidget();

        $merchantId = uniqid();
        $this->assertSame('', $widget->getMerchantId());
        $this->assertSame($widget, $widget->setMerchantId($merchantId));
        $this->assertSame($merchantId, $widget->getMerchantId());
        $this->assertSame($widget, $widget->setMerchantId(''));
        $this->assertSame('', $widget->getMerchantId());

        $this->assertSame($widget, $widget->setCountry('at'));
        $this->assertSame('at', $widget->getCountry());

        $this->assertSame($widget, $widget->setLanguage('de'));
        $this->assertSame('de', $widget->getLanguage());

        $this->assertSame($widget, $widget->setPrice('10.05'));
        $this->assertSame('10.05', $widget->getPrice());

        $this->assertSame($widget, $widget->setCharge('0.95'));
        $this->assertSame('0.95', $widget->getCharge());

        $this->assertSame($widget, $widget->setColor('white'));
        $this->assertSame('white', $widget->getColor());

        $this->assertSame($widget, $widget->setDesign('short'));
        $this->assertSame('short', $widget->getDesign());
    }



    /**
     * @expectedException \Subscribo\Omnipay\Shared\Exception\WidgetInvalidRenderingParametersException
     * @expectedExceptionMessage is required
     */
    public function testInsufficientArgumentForRender()
    {
        $widget = new CheckoutWidget();
        $widget->render();
    }

    /**
     * @expectedException \Subscribo\Omnipay\Shared\Exception\WidgetInvalidRenderingParametersException
     * @expectedExceptionMessage Parameters should be an array
     */
    public function testInvalidArgumentForRender()
    {
        $widget = new CheckoutWidget();
        $widget->render(null);
    }


    public function testRender()
    {
        $merchantId = uniqid();
        $widget = new CheckoutWidget([
            'merchantId' => $merchantId,
            'country' => 'AT',
            'language' => 'de',
            'price' => 15,
        ]);
        $this->assertSame($merchantId, $widget->getMerchantId());
        $this->assertSame('AT', $widget->getCountry());
        $this->assertSame('de', $widget->getLanguage());
        $this->assertSame(15, $widget->getPrice());
        $this->assertTrue($widget->isRenderable());

        $expectedSimpleWidget = '<div style="width:210px; height:70px" class="klarna-widget klarna-part-payment" data-eid="'
            .$merchantId.'" data-locale="de_at" data-price="15" > </div>';
        $expectedSimpleWidget .= "\n".'<script async src="https://cdn.klarna.com/1.0/code/client/all.js"></script>';
        $this->assertSame($expectedSimpleWidget, (string) $widget);
        $this->assertSame($expectedSimpleWidget, $widget->render());
        $this->assertSame($expectedSimpleWidget, $widget->renderPaymentMethodWidget());

        $parameters = [
            'style' => 'z-index:3',
            'width' => 400,
            'height' => 100,
            'layout' => 'pale-v2',
            'charge' => '1.05',
        ];
        $expectedParametrizedWidget = '<div style="z-index:3; width:400px; height:100px" class="klarna-widget klarna-part-payment" data-eid="'
            .$merchantId.'" data-locale="de_at" data-price="15" data-invoice-fee="1.05" data-layout="pale-v2" > </div>';
        $this->assertStringStartsWith($expectedParametrizedWidget, $widget->render($parameters));
        $this->assertSame($expectedParametrizedWidget, $widget->renderPaymentMethodWidget($parameters, false));

        $parameters2 = [
            'width' => '',
            'height' => '',
            'color' => 'white',
            'charge' => 1,
        ];
        $expectedParametrizedWidget2 = '<div class="klarna-widget klarna-part-payment" data-eid="'
            .$merchantId.'" data-locale="de_at" data-price="15" data-invoice-fee="1" data-layout="deep-v2" > </div>';
        $this->assertStringStartsWith($expectedParametrizedWidget2, $widget->render($parameters2));
        $this->assertSame($expectedParametrizedWidget2, $widget->renderPaymentMethodWidget($parameters2, false));
    }


    public function testLogoUrl()
    {
        $widget = new CheckoutWidget([
            'country' => 'AT',
            'language' => 'de',
            'color' => 'white',
        ]);
        $this->assertSame('AT', $widget->getCountry());
        $this->assertSame('de', $widget->getLanguage());
        $this->assertSame('white', $widget->getColor());


        $expectedLogoUrl = 'https://cdn.klarna.com/1.0/shared/image/generic/logo/de_at/basic/white.png';
        $this->assertSame($expectedLogoUrl, $widget->renderLogoUrl());
        $expectedLogoUrl2 = 'https://cdn.klarna.com/1.0/shared/image/generic/logo/de_at/basic/black.png?width=400';
        $this->assertSame($expectedLogoUrl2, $widget->renderLogoUrl(['color' => 'black', 'width' => 400]));
        $expectedLogoUrl3 = 'https://cdn.klarna.com/1.0/shared/image/generic/logo/de_at/basic/blue-black.png';
        $this->assertSame($expectedLogoUrl3, $widget->renderLogoUrl(['color' => 'nonexistent']));
    }


    public function testRenderTooltip()
    {
        $merchantId = uniqid();
        $widget = new CheckoutWidget([
            'merchantId' => $merchantId,
            'country' => 'AT',
            'language' => 'de',
            'color' => 'white',
        ]);
        $this->assertSame($merchantId, $widget->getMerchantId());
        $this->assertSame('AT', $widget->getCountry());
        $this->assertSame('de', $widget->getLanguage());

        $expectedTooltip = '<div class="klarna-widget klarna-logo-tooltip" data-eid="'
            .$merchantId.'" data-locale="de_at" data-logo-name="white" > </div>';
        $jsLoad = "\n".'<script async src="https://cdn.klarna.com/1.0/code/client/all.js"></script>';
        $this->assertSame($expectedTooltip.$jsLoad, $widget->renderTooltip());
        $this->assertSame($expectedTooltip, $widget->renderTooltip([], false));
        $parameters = [
            'tuv' => true,
            'layout' => 'white',
            'color' => 'blue',
            'width' => 300,
        ];
        $expectedTooltip2 = '<div class="klarna-widget klarna-logo-tooltip" data-eid="'.$merchantId
            .'" data-locale="de_at" data-layout="white" data-logo-name="blue+tuv" data-width="300" > </div>';
        $this->assertSame($expectedTooltip2.$jsLoad, $widget->renderTooltip($parameters));
        $this->assertSame($expectedTooltip2, $widget->renderTooltip($parameters, false));
    }



    public function testRenderBadgeUrl()
    {
        $widget = new CheckoutWidget([
            'country' => 'AT',
            'language' => 'de',
        ]);
        $this->assertSame('AT', $widget->getCountry());
        $this->assertSame('de', $widget->getLanguage());
        $this->assertSame('blue-black', $widget->getColor());


        $expectedBadgeUrl = 'https://cdn.klarna.com/1.0/shared/image/generic/badge/de_at/checkout/long-blue.png';
        $this->assertSame($expectedBadgeUrl, $widget->renderBadgeUrl());
        $expectedBadgeUrl2 = 'https://cdn.klarna.com/1.0/shared/image/generic/badge/de_at/checkout/short-white.png?width=400';
        $this->assertSame($expectedBadgeUrl2, $widget->renderBadgeUrl(['color' => 'white', 'design'=> 'short', 'width' => 400]));
        $expectedBadgeUrl3 = 'https://cdn.klarna.com/1.0/shared/image/generic/badge/de_at/checkout/long-blue.png';
        $this->assertSame($expectedBadgeUrl3, $widget->renderBadgeUrl(['color' => 'nonexistent']));    }


    public function testRenderBadgeTooltip()
    {
        $merchantId = uniqid();
        $widget = new CheckoutWidget([
            'merchantId' => $merchantId,
            'country' => 'AT',
            'language' => 'de',
        ]);
        $this->assertSame($merchantId, $widget->getMerchantId());
        $this->assertSame('AT', $widget->getCountry());
        $this->assertSame('de', $widget->getLanguage());

        $expectedTooltip = '<div class="klarna-widget klarna-badge-tooltip" data-eid="'.$merchantId
            .'" data-locale="de_at" data-badge-name="long-blue" > </div>';
        $jsLoad = "\n".'<script async src="https://cdn.klarna.com/1.0/code/client/all.js"></script>';
        $this->assertSame($expectedTooltip.$jsLoad, $widget->renderBadgeTooltip());
        $this->assertSame($expectedTooltip, $widget->renderBadgeTooltip([], false));
        $parameters = [
            'layout' => 'white',
            'color' => 'white',
            'design' => 'short',
            'width' => 300,
        ];
        $expectedTooltip2 = '<div class="klarna-widget klarna-badge-tooltip" data-eid="'.$merchantId
            .'" data-locale="de_at" data-layout="white" data-badge-name="short-white" data-badge-width="300" > </div>';
        $this->assertSame($expectedTooltip2.$jsLoad, $widget->renderBadgeTooltip($parameters));
        $this->assertSame($expectedTooltip2, $widget->renderBadgeTooltip($parameters, false));
    }
}
