<!DOCTYPE html>
<html>
    <head>
        <title>Omnipay Klarna Invoice Driver Authorize Example page</title>
    </head>
    <body>
        <h1>Omnipay Klarna Invoice Driver Authorize Example page</h1>
<?php

use Omnipay\Omnipay;

$merchantId = getenv('KLARNA_MERCHANT_ID');
$sharedSecret = getenv('KLARNA_SHARED_SECRET');

$exampleUrlBase = 'https://your.web.site.example/path/to/example/invoice';

$workflow = isset($_REQUEST['workflow']) ? $_REQUEST['workflow'] : null;

/** @var \Omnipay\Klarna\InvoiceGateway $gateway */
$gateway = Omnipay::create('Klarna\\Invoice');
$gateway->setMerchantId($merchantId)
    ->setSharedSecret($sharedSecret)
    ->setLocale('de_de')
    ->setTestMode(true);

echo '<h2>Gateway Name: '.$gateway->getName()."</h2>\n";

switch ($workflow) {
    case 'approved':
        $email = 'youremail@email.com';
        $denied = false;
        break;
    case 'pending-approved':
        $email = 'pending_accepted@klarna.com';
        $denied = false;
        break;
    case 'pending-denied':
        $email = 'pending_denied@klarna.com';
        $denied = false;
        break;
    case 'denied':
    default:
        $workflow = 'denied';
        $email = 'youremail@email.com';
        $denied = true;
};

echo '<h3>Used workflow: '.$workflow."</h3>\n";

if ($denied) {
    $card = [
        'gender' => 'Male',
        'birthday' => '1960-07-07',
        'firstName' => 'Testperson-de',
        'lastName' => 'Denied',
        'address1' => 'Hellersbergstraße',
        'address2' => '14',
        'postCode' => '41460',
        'city'     => 'Neuss',
        'country'  => 'de',
        'phone'    => '01522113356',
        'email'    => $email,
    ];
} else {
    $card = [
        'gender' => 'Male',
        'birthday' => '1960-07-07',
        'firstName' => 'Testperson-de',
        'lastName' => 'Approved',
        'address1' => 'Hellersbergstraße',
        'address2' => '14',
        'postCode' => '41460',
        'city'     => 'Neuss',
        'country'  => 'de',
        'phone'    => '01522113356',
        'email'    => $email,
    ];
}
$data = [
    'card' => $card,
    'transactionId' => 'Some optional identifier for transaction defined by you', //alias for orderId1
    'orderId2' => 'Another optional identifier for transaction'
];
$shoppingCart = [
    [
        'name' => 'Example Article',
        'identifier' => 'E01',
        'price' => '4.00',
        'quantity' => 10,
        'taxPercent' => '20',
        'discountPercent' => '10',
    ],
    [
        'name' => 'Handling fee',
        'identifier' => 'HANDLING',
        'price' => '1.00',
        'quantity' => 5,
        'flags' => \KlarnaFlags::IS_HANDLING,
    ],
];

try {
    $request = $gateway->authorize($data);
    $request->setItems($shoppingCart);
    echo $request->getWidget(['charge' => '0.95']);
    echo '<p>Calculated amount: '.$request->calculateAmount().'</p>';
    $response = $request->send();
    $reservationNumber = $response->getReservationNumber();
    echo '<p>Reservation number: '.$reservationNumber.'</p>';

    echo '<p>Invoice status: '.$response->getInvoiceStatus().'</p>';

    if ($response->isSuccessful()) {
        echo '<h3>Authorization request was resolved</h3>';
        echo "<ul>\n";
        echo '<li><a href="'.$exampleUrlBase.'/capture?reservation_number='.$reservationNumber
            .'">Capture whole amount</a></li>';
        echo '<li><a href="'.$exampleUrlBase.'/capture?reservation_number='.$reservationNumber
            .'&part=1">Capture part of amount</a></li>';
        echo "</ul>\n";
    } elseif ($response->isWaiting()) {
        echo '<h3>Authorization request is pending</h3>';
        echo '<a href="'.$exampleUrlBase.'/check?reservation_number='.$reservationNumber.'">Check again</a>';
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
