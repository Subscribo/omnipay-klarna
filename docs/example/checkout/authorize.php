<!DOCTYPE html>
<html>
    <head>
        <title>Omnipay Klarna Checkout Driver Authorize Example page</title>
    </head>
    <body>
        <h1>Omnipay Klarna Checkout Driver Authorize Example page</h1>
<?php

use Omnipay\Omnipay;

$merchantId = getenv('KLARNA_MERCHANT_ID');
$sharedSecret = getenv('KLARNA_SHARED_SECRET');

$exampleUrlBase = 'https://your.web.site.example/path/to/example/checkout';

$workflow = isset($_REQUEST['workflow']) ? $_REQUEST['workflow'] : null;

/** @var \Omnipay\Klarna\CheckoutGateway $gateway */
$gateway = Omnipay::create('Klarna\\Checkout');
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
    'orderId2' => 'Another optional identifier for transaction',
    'termsUrl' => $exampleUrlBase.'/terms',
    'authorizeUrl' => $exampleUrlBase.'/authorize',
    'returnUrl' => $exampleUrlBase.'/complete_authorize',
    'pushUrl' => $exampleUrlBase.'/push',
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
//    echo $request->getWidget(['charge' => '0.95']);
    $response = $request->send();
    $widget = $response->getWidget();
    echo $widget;
} catch (\Exception $e) {
    echo '<p>Some error occurred: '.$e->getMessage().' (Code: '.$e->getCode().')</p>';
}
?>
    </body>
</html>
