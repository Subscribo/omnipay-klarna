<?php

namespace Omnipay\Klarna\Widget;

use PHPUnit_Framework_TestCase;
use Omnipay\Klarna\Widget\InvoiceWidget;

class InvoiceWidgetTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyWidget()
    {
        $widget = new InvoiceWidget();

        $this->assertFalse($widget->isRenderable());
        $this->assertSame('', (string) $widget);
        $this->assertNull($widget->getMerchantId());
        $this->assertNull($widget->getCountry());
        $this->assertNull($widget->getLanguage());
        $this->assertNull($widget->getPrice());
        $this->assertNull($widget->getCharge());
        $this->assertNull($widget->getColor());
        $this->assertNull($widget->getOutputDeviceType());
        $this->assertNull($widget->getAGBUrl());
        $this->assertSame([], $widget->getParameters());
        $this->assertSame([], $widget->getDefaultParameters());
    }


    public function testSetters()
    {
        $widget = new InvoiceWidget();

        $merchantId = uniqid();
        $this->assertNull($widget->getMerchantId());
        $this->assertSame($widget, $widget->setMerchantId($merchantId));
        $this->assertSame($merchantId, $widget->getMerchantId());
        $this->assertSame($widget, $widget->setMerchantId(null));
        $this->assertNull($widget->getMerchantId());

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

        $this->assertSame($widget, $widget->setOutputDeviceType('desktop'));
        $this->assertSame('desktop', $widget->getOutputDeviceType());

        $this->assertSame($widget, $widget->setAGBUrl('https://your.web.site.example/path/to/AGB'));
        $this->assertSame('https://your.web.site.example/path/to/AGB', $widget->getAGBUrl());
    }

    /**
     * @expectedException \Subscribo\Omnipay\Shared\Exception\WidgetNotRenderableException
     * @expectedExceptionMessage is required
     */
    public function testInsufficientArgumentForRender()
    {
        $widget = new InvoiceWidget();
        $widget->render();
    }

    /**
     * @expectedException \Subscribo\Omnipay\Shared\Exception\WidgetNotRenderableException
     * @expectedExceptionMessage Parameters have to be an array
     */
    public function testInvalidArgumentForRender()
    {
        $widget = new InvoiceWidget();
        $widget->render(null);
    }


    public function testRender()
    {
        $merchantId = uniqid();
        $widget = new InvoiceWidget([
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

        $expectedSimpleWidget = '<div style="width:210px; height:70px" class="klarna-widget klarna-part-payment" data-eid="'.$merchantId.'" data-locale="de_at" data-price="15" > </div>';
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
        $expectedParametrizedWidget = '<div style="z-index:3; width:400px; height:100px" class="klarna-widget klarna-part-payment" data-eid="'.$merchantId.'" data-locale="de_at" data-price="15" data-invoice-fee="1.05" data-layout="pale-v2" > </div>';
        $this->assertStringStartsWith($expectedParametrizedWidget, $widget->render($parameters));
        $this->assertSame($expectedParametrizedWidget, $widget->renderPaymentMethodWidget($parameters, false));

        $parameters2 = [
            'width' => '',
            'height' => '',
            'color' => 'white',
            'charge' => 1,
        ];
        $expectedParametrizedWidget2 = '<div class="klarna-widget klarna-part-payment" data-eid="'.$merchantId.'" data-locale="de_at" data-price="15" data-invoice-fee="1" data-layout="deep-v2" > </div>';
        $this->assertStringStartsWith($expectedParametrizedWidget2, $widget->render($parameters2));
        $this->assertSame($expectedParametrizedWidget2, $widget->renderPaymentMethodWidget($parameters2, false));
    }


    public function testLogoUrl()
    {
        $widget = new InvoiceWidget([
            'country' => 'AT',
            'language' => 'de',
            'color' => 'white',
        ]);
        $this->assertSame('AT', $widget->getCountry());
        $this->assertSame('de', $widget->getLanguage());
        $this->assertSame('white', $widget->getColor());


        $expectedLogoUrl = 'https://cdn.klarna.com/1.0/shared/image/generic/logo/de_at/basic/white.png';
        $expectedLogoUrl2 = 'https://cdn.klarna.com/1.0/shared/image/generic/logo/de_at/basic/white.png';
        $this->assertSame($expectedLogoUrl, $widget->renderLogoUrl());
        $expectedLogoUrl2 = 'https://cdn.klarna.com/1.0/shared/image/generic/logo/de_at/basic/black.png?width=400';
        $this->assertSame($expectedLogoUrl2, $widget->renderLogoUrl(['color' => 'black', 'width' => 400]));
        $expectedLogoUrl3 = 'https://cdn.klarna.com/1.0/shared/image/generic/logo/de_at/basic/blue-black.png';
        $this->assertSame($expectedLogoUrl3, $widget->renderLogoUrl(['color' => 'nonexistent']));
    }

    public function testRenderTooltip()
    {
        $merchantId = uniqid();
        $widget = new InvoiceWidget([
            'merchantId' => $merchantId,
            'country' => 'AT',
            'language' => 'de',
            'color' => 'white',
        ]);
        $this->assertSame($merchantId, $widget->getMerchantId());
        $this->assertSame('AT', $widget->getCountry());
        $this->assertSame('de', $widget->getLanguage());

        $expectedTooltip = '<div class="klarna-widget klarna-logo-tooltip" data-eid="'.$merchantId.'" data-locale="de_at" data-logo-name="white" > </div>';
        $jsLoad = "\n".'<script async src="https://cdn.klarna.com/1.0/code/client/all.js"></script>';
        $this->assertSame($expectedTooltip.$jsLoad, $widget->renderTooltip());
        $this->assertSame($expectedTooltip, $widget->renderTooltip([], false));
        $parameters = [
            'tuv' => true,
            'layout' => 'white',
            'color' => 'blue',
            'width' => 300,
        ];
        $expectedTooltip2 = '<div class="klarna-widget klarna-logo-tooltip" data-eid="'.$merchantId.'" data-locale="de_at" data-layout="white" data-logo-name="blue+tuv" data-width="300" > </div>';
        $this->assertSame($expectedTooltip2.$jsLoad, $widget->renderTooltip($parameters));
        $this->assertSame($expectedTooltip2, $widget->renderTooltip($parameters, false));
    }

    public function testRenderTermsInvoiceHtml()
    {
        $merchantId = uniqid();
        $widget = new InvoiceWidget([
            'merchantId' => $merchantId,
            'country' => 'AT',
            'language' => 'de',
        ]);

        $basicPattern = '<span id="klarna-invoice-rechnungsbedingungen-n([0-9]+)"></span>'."\n"
                      . "<script>new Klarna.Terms.Invoice\\({ el: 'klarna-invoice-rechnungsbedingungen-n\\1', eid: '".$merchantId."', locale: 'de_at', charge: '0' }\\);</script>";
        $loadJs = '<script src="https://cdn.klarna.com/public/kitt/core/v1.0/js/klarna.min.js"></script>'."\n"
                . '<script src="https://cdn.klarna.com/public/kitt/toc/v1.1/js/klarna.terms.min.js"></script>'."\n";
        $this->assertRegExp('#^'.$loadJs.$basicPattern.'$#', $widget->renderTermsInvoiceHtml());
        $this->assertRegExp('#^'.$basicPattern.'$#', $widget->renderTermsInvoiceHtml([], false));
        $expectedHtml = '<span id="some-element-id"></span>'."\n"
            . "<script>new Klarna.Terms.Invoice({ el: 'some-element-id', eid: '".$merchantId."', locale: 'de_at', charge: '0' });</script>";
        $this->assertSame($loadJs.$expectedHtml, $widget->renderTermsInvoiceHtml([], true, 'some-element-id'));
        $this->assertSame($expectedHtml, $widget->renderTermsInvoiceHtml([], false, 'some-element-id'));
        $parameters = [
            'outputDeviceType' => 'mobile',
            'charge' => '1.05',
            'linkClassName' => 'someClass',
        ];
        $extendedPattern = '<span id="klarna-invoice-rechnungsbedingungen-n([0-9]+)"></span>'."\n"
            . "<script>new Klarna.Terms.Invoice\\({ el: 'klarna-invoice-rechnungsbedingungen-n\\1', eid: '".$merchantId."', locale: 'de_at', charge: '1.05', type: 'mobile', linkClassName: 'someClass' }\\);</script>";
        $this->assertRegExp('#^'.$loadJs.$extendedPattern.'$#', $widget->renderTermsInvoiceHtml($parameters));
        $this->assertRegExp('#^'.$extendedPattern.'$#', $widget->renderTermsInvoiceHtml($parameters, false));
    }
}
