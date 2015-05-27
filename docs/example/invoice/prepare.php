<!DOCTYPE html>
<html>
    <head>
        <title>Omnipay Klarna Invoice Driver Prepare Example page</title>
    </head>
    <body>
        <h1>Omnipay Klarna Invoice Driver Prepare Example page</h1>
<?php

use Omnipay\Omnipay;

$merchantId = env('KLARNA_MERCHANT_ID');

$exampleUrlBase = 'https://your.web.site.example/path/to/example/invoice';

/** @var \Omnipay\Klarna\InvoiceGateway $gateway */
$gateway = Omnipay::create('Klarna\\Invoice');
$gateway->setMerchantId($merchantId)
    ->setLanguage('de')
    ->setCountry('de')
    ->setCurrency('eur')
    ->setTestMode(true);
$widget = $gateway->getWidget(['AGBUrl' => $exampleUrlBase.'/AGB', 'color' => 'blue-black', 'charge' => '0.95']);
$jsCallbacks = [
    'openPopupCallback' => 'function(){ alert("Opening!"); }',
    'closePopupCallback' => 'function(){ alert("Closing!"); }'
];

    echo '<img src="'.$widget->renderLogoUrl(['width' => 200]).'">'."\n";
?>

        <h2> Gateway Name: <?php echo $gateway->getName(); ?></h2>
        <div>
            <?php echo $widget->renderTooltip(); ?>
        </div>
        <div>
            <?php echo $widget->renderTermsInvoiceHtml(); ?>
        </div>
        <div>
            <?php echo $widget->renderTermsConsentHtml(); ?>
        </div>
        <div>
            <?php echo $widget->renderTermsAccountHtml($jsCallbacks); ?>
        </div>
        <h3> You can try to be invoiced on our behalf. Bellow you can select preferred workflow.</h3>

        <form action="<?php echo $exampleUrlBase ?>/authorize" method="POST">
            <button type="submit" name="workflow" value="approved">Approved</button>
            <button type="submit" name="workflow" value="pending-approved">Pending -> Approved</button>
            <button type="submit" name="workflow" value="pending-denied">Pending -> Denied</button>
            <button type="submit" name="workflow" value="denied">Denied</button>
        </form>
    </body>
</html>
