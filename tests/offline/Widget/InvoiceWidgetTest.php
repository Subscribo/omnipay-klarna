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
        $this->assertSame('', $widget->getMerchantId());
        $this->assertSame('', $widget->getCountry());
        $this->assertSame('', $widget->getLanguage());
        $this->assertSame('', $widget->getPrice());
        $this->assertSame('', $widget->getCharge());
        $this->assertSame('blue-black', $widget->getColor());
        $this->assertSame('', $widget->getOutputDeviceType());
        $this->assertSame('', $widget->getAGBUrl());
        $expectedParameters = [
            'merchantId' => '',
            'country' => '',
            'language' => '',
            'price' => '',
            'charge' => '',
            'color' => 'blue-black',
            'outputDeviceType' => '',
            'AGBUrl' => '',
        ];
        $this->assertSame($expectedParameters, $widget->getParameters());
        $expectedDefaults = [
            'merchantId' => '',
            'country' => ['', 'de', 'at', 'dk', 'fi', 'nl', 'no', 'se', 'us', 'gb'],
            'language' => ['', 'de', 'da', 'fi', 'nl', 'nb', 'sv', 'en'],
            'price' => '',
            'charge' => '',
            'color' => ['blue-black', 'white', 'black'],
            'outputDeviceType' => ['', 'desktop', 'mobile'],
            'AGBUrl' => '',
        ];
        $this->assertSame($expectedDefaults, $widget->getDefaultParameters());
        $this->assertSame(['merchantId', 'country', 'language', 'price'], $widget->getRequiredParameters());
    }


    public function testSetters()
    {
        $widget = new InvoiceWidget();

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

        $this->assertSame($widget, $widget->setOutputDeviceType('desktop'));
        $this->assertSame('desktop', $widget->getOutputDeviceType());

        $this->assertSame($widget, $widget->setAGBUrl('https://your.web.site.example/path/to/AGB'));
        $this->assertSame('https://your.web.site.example/path/to/AGB', $widget->getAGBUrl());
    }

    /**
     * @expectedException \Subscribo\Omnipay\Shared\Exception\WidgetInvalidRenderingParametersException
     * @expectedExceptionMessage is required
     */
    public function testInsufficientArgumentForRender()
    {
        $widget = new InvoiceWidget();
        $widget->render();
    }

    /**
     * @expectedException \Subscribo\Omnipay\Shared\Exception\WidgetInvalidRenderingParametersException
     * @expectedExceptionMessage Parameters should be an array
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
        $widget = new InvoiceWidget([
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
        $widget = new InvoiceWidget([
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


    public function testRenderTermsInvoiceHtml()
    {
        $merchantId = uniqid();
        $widget = new InvoiceWidget([
            'merchantId' => $merchantId,
            'country' => 'AT',
            'language' => 'de',
        ]);
        $this->assertSame($merchantId, $widget->getMerchantId());
        $this->assertSame('AT', $widget->getCountry());
        $this->assertSame('de', $widget->getLanguage());

        $basicPattern = '<span id="klarna-invoice-rechnungsbedingungen-n([0-9]+)"></span>'."\n"
                      . "<script>new Klarna.Terms.Invoice\\({ el: 'klarna-invoice-rechnungsbedingungen-n\\1', eid: '"
                      .$merchantId."', locale: 'de_at', charge: '0' }\\);</script>";
        $loadJs = '<script src="https://cdn.klarna.com/public/kitt/core/v1.0/js/klarna.min.js"></script>'."\n"
                . '<script src="https://cdn.klarna.com/public/kitt/toc/v1.1/js/klarna.terms.min.js"></script>'."\n";
        $this->assertRegExp('#^'.$loadJs.$basicPattern.'$#', $widget->renderTermsInvoiceHtml());
        $this->assertRegExp('#^'.$basicPattern.'$#', $widget->renderTermsInvoiceHtml([], false));

        $expectedHtml = '<span id="some-element-id"></span>'."\n"
                        . "<script>new Klarna.Terms.Invoice({ el: 'some-element-id', eid: '"
                        .$merchantId."', locale: 'de_at', charge: '0' });</script>";
        $this->assertSame($loadJs.$expectedHtml, $widget->renderTermsInvoiceHtml([], true, 'some-element-id'));
        $this->assertSame($expectedHtml, $widget->renderTermsInvoiceHtml([], false, 'some-element-id'));

        $parameters = [
            'outputDeviceType' => 'mobile',
            'charge' => '1.05',
            'linkClassName' => 'someClass',
            'openPopupCallback' => 'function(){ alert("Opening!"); }',
            'closePopupCallback' => 'function(){ alert("Closing!"); }'
        ];
        $extendedPattern = '<span id="klarna-invoice-rechnungsbedingungen-n([0-9]+)"></span>'."\n"
            . "<script>new Klarna.Terms.Invoice\\({ el: 'klarna-invoice-rechnungsbedingungen-n\\1', eid: '".$merchantId
            ."', locale: 'de_at', charge: '1.05', type: 'mobile', linkClassName: 'someClass', openPopupCallback: function\\(\\){ alert\\(\"Opening!\"\\); }, closePopupCallback: function\\(\\){ alert\\(\"Closing!\"\\); } }\\);</script>";
        $this->assertRegExp('#^'.$loadJs.$extendedPattern.'$#', $widget->renderTermsInvoiceHtml($parameters));
        $this->assertRegExp('#^'.$extendedPattern.'$#', $widget->renderTermsInvoiceHtml($parameters, false));
    }


    public function testRenderTermsConsentHtml()
    {
        $merchantId = uniqid();
        $widget = new InvoiceWidget([
            'merchantId' => $merchantId,
            'country' => 'AT',
            'language' => 'de',
        ]);
        $this->assertSame($merchantId, $widget->getMerchantId());
        $this->assertSame('AT', $widget->getCountry());
        $this->assertSame('de', $widget->getLanguage());

        $basicPattern = 'Mit der Übermittlung der für die Abwicklung der gewählten Klarna Zahlungsmethode und einer Identitäts- und Bonitätsprüfung erforderlichen Daten an Klarna bin ich einverstanden. Meine <span id="klarna-consent-einwilligung-n([0-9]+)"></span> kann ich jederzeit mit Wirkung für die Zukunft widerrufen. Es gelten die AGB des Händlers.'."\n"
            . "<script>new Klarna.Terms.Consent\\({ el: 'klarna-consent-einwilligung-n\\1', eid: '"
            .$merchantId."', locale: 'de_at' }\\);</script>";
        $loadJs = '<script src="https://cdn.klarna.com/public/kitt/core/v1.0/js/klarna.min.js"></script>'."\n"
            . '<script src="https://cdn.klarna.com/public/kitt/toc/v1.1/js/klarna.terms.min.js"></script>'."\n";
        $this->assertRegExp('#^'.$loadJs.$basicPattern.'$#', $widget->renderTermsConsentHtml());
        $this->assertRegExp('#^'.$basicPattern.'$#', $widget->renderTermsConsentHtml([], false));

        $expectedHtml = 'Mit der Übermittlung der für die Abwicklung der gewählten Klarna Zahlungsmethode und einer Identitäts- und Bonitätsprüfung erforderlichen Daten an Klarna bin ich einverstanden. Meine <span id="some-element-id"></span> kann ich jederzeit mit Wirkung für die Zukunft widerrufen. Es gelten die AGB des Händlers.'."\n"
            . "<script>new Klarna.Terms.Consent({ el: 'some-element-id', eid: '"
            .$merchantId."', locale: 'de_at' });</script>";
        $this->assertSame($loadJs.$expectedHtml, $widget->renderTermsConsentHtml([], true, 'some-element-id'));
        $this->assertSame($expectedHtml, $widget->renderTermsConsentHtml([], false, 'some-element-id'));

        $parameters = [
            'outputDeviceType' => 'desktop',
            'AGBUrl' => 'https://your.web.site.example/path/to/AGB',
            'linkClassName' => 'someClass',
            'openPopupCallback' => 'function(){ alert("Opening!"); }',
            'closePopupCallback' => 'function(){ alert("Closing!"); }'
        ];
        $extendedPattern = 'Mit der Übermittlung der für die Abwicklung der gewählten Klarna Zahlungsmethode und einer Identitäts- und Bonitätsprüfung erforderlichen Daten an Klarna bin ich einverstanden. Meine <span id="klarna-consent-einwilligung-n([0-9]+)"></span> kann ich jederzeit mit Wirkung für die Zukunft widerrufen. Es gelten <a href="https://your.web.site.example/path/to/AGB" target="_blank">die AGB</a> des Händlers.'."\n"
            . "<script>new Klarna.Terms.Consent\\({ el: 'klarna-consent-einwilligung-n\\1', eid: '".$merchantId
            ."', locale: 'de_at', type: 'desktop', linkClassName: 'someClass', openPopupCallback: function\\(\\){ alert\\(\"Opening!\"\\); }, closePopupCallback: function\\(\\){ alert\\(\"Closing!\"\\); } }\\);</script>";
        $this->assertRegExp('#^'.$loadJs.$extendedPattern.'$#', $widget->renderTermsConsentHtml($parameters));
        $this->assertRegExp('#^'.$extendedPattern.'$#', $widget->renderTermsConsentHtml($parameters, false));
    }


    public function testRenderTermsAccountHtml()
    {
        $merchantId = uniqid();
        $widget = new InvoiceWidget([
            'merchantId' => $merchantId,
            'country' => 'DE',
            'language' => 'de',
        ]);
        $this->assertSame($merchantId, $widget->getMerchantId());
        $this->assertSame('DE', $widget->getCountry());
        $this->assertSame('de', $widget->getLanguage());

        $basicPattern = '<span id="klarna-account-lesen-sie-mehr-n([0-9]+)"></span>'."\n"
            . "<script>new Klarna.Terms.Account\\({ el: 'klarna-account-lesen-sie-mehr-n\\1', eid: '"
            .$merchantId."', locale: 'de_de' }\\);</script>";
        $loadJs = '<script src="https://cdn.klarna.com/public/kitt/core/v1.0/js/klarna.min.js"></script>'."\n"
            . '<script src="https://cdn.klarna.com/public/kitt/toc/v1.1/js/klarna.terms.min.js"></script>'."\n";
        $this->assertRegExp('#^'.$loadJs.$basicPattern.'$#', $widget->renderTermsAccountHtml());
        $this->assertRegExp('#^'.$basicPattern.'$#', $widget->renderTermsAccountHtml([], false));

        $expectedHtml = '<span id="some-element-id"></span>'."\n"
            . "<script>new Klarna.Terms.Account({ el: 'some-element-id', eid: '"
            .$merchantId."', locale: 'de_de' });</script>";
        $this->assertSame($loadJs.$expectedHtml, $widget->renderTermsAccountHtml([], true, 'some-element-id'));
        $this->assertSame($expectedHtml, $widget->renderTermsAccountHtml([], false, 'some-element-id'));

        $parameters = [
            'outputDeviceType' => 'mobile',
            'linkClassName' => 'someClass',
            'openPopupCallback' => 'function(){ alert("Opening!"); }',
            'closePopupCallback' => 'function(){ alert("Closing!"); }'
        ];
        $extendedPattern = '<span id="klarna-account-lesen-sie-mehr-n([0-9]+)"></span>'."\n"
            . "<script>new Klarna.Terms.Account\\({ el: 'klarna-account-lesen-sie-mehr-n\\1', eid: '".$merchantId
            ."', locale: 'de_de', type: 'mobile', linkClassName: 'someClass', openPopupCallback: function\\(\\){ alert\\(\"Opening!\"\\); }, closePopupCallback: function\\(\\){ alert\\(\"Closing!\"\\); } }\\);</script>";
        $this->assertRegExp('#^'.$loadJs.$extendedPattern.'$#', $widget->renderTermsAccountHtml($parameters));
        $this->assertRegExp('#^'.$extendedPattern.'$#', $widget->renderTermsAccountHtml($parameters, false));
    }


    public function testJavascriptLoaders()
    {
        $expectedJavascriptLoader = '<script async src="https://cdn.klarna.com/1.0/code/client/all.js"></script>';
        $this->assertSame($expectedJavascriptLoader, InvoiceWidget::assembleLoadJavascript());
        $expectedTermsJavascriptLoader = '<script src="https://cdn.klarna.com/public/kitt/core/v1.0/js/klarna.min.js"></script>'."\n"
                                       . '<script src="https://cdn.klarna.com/public/kitt/toc/v1.1/js/klarna.terms.min.js"></script>';
        $this->assertSame($expectedTermsJavascriptLoader, InvoiceWidget::assembleLoadTermsJavascript());
    }
}
