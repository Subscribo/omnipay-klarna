<!DOCTYPE html>
<html>
    <head>
        <title>Omnipay Klarna Checkout Driver Prepare Example page</title>
    </head>
    <body>
        <h1>Omnipay Klarna Checkout Driver Prepare Example page</h1>
<?php

use Omnipay\Omnipay;

$merchantId = env('KLARNA_MERCHANT_ID');

$exampleUrlBase = 'https://your.web.site.example/path/to/example/checkout';

/** @var \Omnipay\Klarna\CheckoutGateway $gateway */
$gateway = Omnipay::create('Klarna\\Checkout');
$gateway->setMerchantId($merchantId)
    ->setLanguage('de')
    ->setCountry('de')
    ->setCurrency('eur')
    ->setTestMode(true);
$widget = $gateway->getWidget(['color' => 'blue-black', 'charge' => '0.95']);

    echo '<img src="'.$widget->renderLogoUrl(['width' => 200]).'">'."\n";
?>

        <h2> Gateway Name: <?php echo $gateway->getName(); ?></h2>
        <div>
            <?php echo $widget->renderBadgeTooltip(); ?>
        </div>
        <h3> You can try to be invoiced on our behalf. Bellow you can select preferred workflow.</h3>

        <form action="<?php echo $exampleUrlBase ?>/authorize" method="POST">
            <button type="submit" name="workflow" value="approved">Approved</button>
            <button type="submit" name="workflow" value="pending-approved">Pending -> Approved</button>
            <button type="submit" name="workflow" value="pending-denied">Pending -> Denied</button>
            <button type="submit" name="workflow" value="denied">Denied</button>
        </form>
        <img src="<?php echo $widget->renderBadgeUrl(['design' => 'short']); ?>">
    </body>
</html>
