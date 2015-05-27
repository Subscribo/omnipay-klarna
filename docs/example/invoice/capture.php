<!DOCTYPE html>
<html>
    <head>
        <title>Omnipay Klarna Invoice Driver Capture Example page</title>
    </head>
<body>
    <h1>Omnipay Klarna Invoice Driver Capture Example page</h1>
<?php

use Omnipay\Omnipay;

$merchantId = getenv('KLARNA_MERCHANT_ID');
$sharedSecret = getenv('KLARNA_SHARED_SECRET');

$exampleUrlBase = 'https://your.web.site.example/path/to/example/invoice';

$reservationNumber = isset($_REQUEST['reservation_number']) ? $_REQUEST['reservation_number'] : null;
$partial = isset($_REQUEST['part']) ? $_REQUEST['part'] : null;

$checkPageUrlStubInvoice = 'https://your.web.site.example/path/to/check?invoice_number=';

/** @var \Omnipay\Klarna\InvoiceGateway $gateway */
$gateway = Omnipay::create('Klarna\\Invoice');
$gateway->setMerchantId($merchantId)
    ->setSharedSecret($sharedSecret)
    ->setLocale('de_de')
    ->setTestMode(true);

$reservationNumber = isset($_GET['reservation_number']) ? $_GET['reservation_number'] : null;
$partial = isset($_GET['part']) ? $_GET['part'] : null;

echo '<h2>Gateway Name: '.$gateway->getName()."</h2>\n";
echo '<h3>Reservation Number: '.$reservationNumber."</h>\n";


$data = [
    'reservationNumber' => $reservationNumber,
];

try {
    $request = $gateway->capture($data);

    if ($partial) {
        echo "<p>Partial activation</p>\n";
        $selectedItems = [
            [
                'identifier' => 'E01',
                'quantity' => 2,
            ],
            [
                'identifier' => 'HANDLING',
                'quantity' => 1,
            ],
        ];
        $request->setItems($selectedItems);
        $request->setOrderId1('Some partial order id'); //Let's change orderId1 (aliased as transactionId)
    }

    $response = $request->send();

    $transactionReference = $response->getTransactionReference();
    $invoiceNumber = $response->getInvoiceNumber();

    echo '<p>Invoice number: '.$invoiceNumber.'</p>';
    echo '<p>Transaction Reference: '.$transactionReference.'</p>';
    echo '<p>Risk status: '.$response->getRiskStatus().'</p>';

    if ($response->isSuccessful()) {
        echo '<h3>Capture has been approved</h3>';
        if ($partial) {
            echo "<ul>\n";
            echo '<li><a href="'.$exampleUrlBase.'/capture?reservation_number='.$reservationNumber
                .'">Capture rest of amount</a></li>';
            echo '<li><a href="'.$exampleUrlBase.'/capture?reservation_number='.$reservationNumber
                .'&part=1">Capture another part of amount</a></li>';
            echo "</ul>\n";
        }
    } elseif ($invoiceNumber) {
        echo '<h3>Risk has not been taken by Klarna</h3>';
        echo '<p>Sending goods is on your own risk</p>';
    } else {
        echo '<h3>Capture has not been approved</h3>';
        echo '<p>Message: '.$response->getMessage().' (Code: '.$response->getCode().')</p>';
    }
    echo "<ul>\n";
    echo '<li><a href="'.$exampleUrlBase.'/check?reservation_number='.$reservationNumber
        .'">Check by reservation number</a></li>';
    echo '<li><a href="'.$exampleUrlBase.'/check?invoice_number='.$invoiceNumber.'">Check by invoice number</a></li>';
    echo "</ul>\n";
} catch (\Exception $e) {
    echo '<p>Some error occurred: '.$e->getMessage().' (Code: '.$e->getCode().')</p>';
}
?>
    </body>
</html>
