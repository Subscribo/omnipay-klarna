<!DOCTYPE html>
<html>
    <head>
        <title>Omnipay Klarna Checkout Driver Complete Authorize Example page</title>
    </head>
    <body>
        <h1>Omnipay Klarna Checkout Driver Complete Authorize Example page</h1>
<?php

use Omnipay\Omnipay;

$sharedSecret = getenv('KLARNA_SHARED_SECRET');


$exampleUrlBase = 'https://your.web.site.example/path/to/example/checkout';
$exampleUrlInvoiceBase = 'https://your.web.site.example/path/to/example/invoice';

/** @var \Omnipay\Klarna\CheckoutGateway $gateway */
$gateway = Omnipay::create('Klarna\\Checkout');
$gateway->setSharedSecret($sharedSecret)
    ->setTestMode(true);

echo '<h2>Gateway Name: '.$gateway->getName()."</h2>\n";

try {
    $request = $gateway->completeAuthorize();
    $response = $request->send();
    $reservationNumber = $response->getReservationNumber();
    echo '<p>Reservation number: '.$reservationNumber."</p>\n";
    $widget = $response->getWidget();
    echo $widget;
?>
        <form action="<?php echo $exampleUrlBase.'/push?klarna_order='.urlencode($response->getCheckoutOrderUri()) ?>" method="POST">
            <button type="submit">Simulate Klarna Push request</button>
        </form>
        <ul>
            <li><a href="<?php echo $exampleUrlInvoiceBase.'/capture?reservation_number='.$reservationNumber; ?>">Capture whole amount</a></li>
            <li><a href="<?php echo $exampleUrlInvoiceBase.'/capture?reservation_number='.$reservationNumber; ?>&part=1">Capture part of amount</a></li>
        </ul>
<?php
} catch (\Exception $e) {
    echo '<p>Some error occurred: '.$e->getMessage().' (Code: '.$e->getCode().')</p>';
}
?>
    </body>
</html>
