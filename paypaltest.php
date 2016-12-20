<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 14/12/16
 * Time: 5:51 PM
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/PayPal-PHP-SDK/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/PayPalEntryPayment.php');

if (isset($_POST['pay']) && $_POST['pay'] == "Test Payment") {

    $pp = new PayPalEntryPayment();
    $pp->addItem("Entry Fee", 1, 70);
    $pp->addItem("Individual Events", 5, 9);
    $pp->addItem("Presentation Dinner", 1, 56);
    $pp->addItem("Massages", 1, 12);

    $payment = $pp->processPayment();

}

?>

<html>
<head>
    <title>PayPal Payment Test</title>
</head>
<body>

<h1>PayPal Payment Test</h1>

<?php

    if (isset($_GET['success']) && $_GET['success'] == 'true') {

        $paymentID = $_GET['paymentId'];
        $payerID = $_GET['PayerID'];

        $pp = new PayPalEntryPayment();
        $amountPaid = $pp->finalisePayment($paymentID, $payerID);

        echo "<h2>Amount paid $amountPaid</h2>\n";

    } elseif (isset($_GET['success']) && $_GET['success'] == 'false') {

        echo "<h2>Error: Payment Unsuccessful!</h2>\n";

    } else {

        ?>

        <form method="post">
            <input type="submit" name="pay" value="Test Payment"/>
        </form>

        <?php
    }
?>

</body>
</html>
