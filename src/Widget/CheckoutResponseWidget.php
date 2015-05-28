<?php

namespace Omnipay\Klarna\Widget;

use Subscribo\Omnipay\Shared\Widget\SimpleWidget;

class CheckoutResponseWidget extends SimpleWidget
{
    protected function processContent($content, array $parameters = [])
    {
        return '<div>'.$content.'</div>';
    }
}
