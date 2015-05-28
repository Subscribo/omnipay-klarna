<?php

namespace Omnipay\Klarna\Widget;

use PHPUnit_Framework_TestCase;
use Omnipay\Klarna\Widget\CheckoutResponseWidget;

class CheckoutResponseWidgetTest extends PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $widget = new CheckoutResponseWidget(['content' => 'Some Content']);
        $this->assertTrue($widget->isRenderable());
        $this->assertSame('<div>Some Content</div>', (string) $widget);
    }
}
