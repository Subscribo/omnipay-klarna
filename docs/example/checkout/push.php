<!DOCTYPE html>
<html>
    <head>
        <title>Omnipay Klarna Checkout Driver Finalize Authorize Example Push result</title>
    </head>
    <body>
        <h1>Omnipay Klarna Checkout Driver Finalize Authorize Example Push result</h1>
<?php

use Omnipay\Omnipay;

$sharedSecret = getenv('KLARNA_SHARED_SECRET');


$exampleUrlBase = 'https://your.web.site.example/path/to/example/invoice';


/** @var \Omnipay\Klarna\CheckoutGateway $gateway */
$gateway = Omnipay::create('Klarna\\Checkout');
$gateway->setSharedSecret($sharedSecret)
    ->setTestMode(true);

echo '<h2>Gateway Name: '.$gateway->getName()."</h2>\n";

$data = [
    'processOrderCallback' => function($order) {
        echo "<div>Processing...</div>\n";
        return true;
    }
];

try {
    $request = $gateway->finalizeAuthorize($data);
    $response = $request->send();
    $reservationNumber = $response->getReservationNumber();
    echo '<p>Reservation number: '.$reservationNumber.'</p>';

    if ($response->isSuccessful()) {
        echo '<h3>Order has been confirmed</h3>';
        echo "<ul>\n";
        echo '<li><a href="'.$exampleUrlBase.'/capture?reservation_number='.$reservationNumber
            .'">Capture whole amount</a></li>';
        echo '<li><a href="'.$exampleUrlBase.'/capture?reservation_number='.$reservationNumber
            .'&part=1">Capture part of amount</a></li>';
        echo "</ul>\n";
    } else {
        echo '<h3>Authorization request was not successful</h3>';
        echo '<p>Message: '.$response->getMessage().' (Code: '.$response->getCode().')</p>';
    }
} catch (\Exception $e) {
    echo '<p>Some error occurred: '.$e->getMessage().' (Code: '.$e->getCode().')</p>';
}
?>
    </body>
</html>
