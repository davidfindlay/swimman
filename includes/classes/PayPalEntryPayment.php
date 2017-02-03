<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/setup.php');

use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\PaymentExecution;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

/**
 * Created by PhpStorm.
 * User: david
 * Date: 14/12/16
 * Time: 5:29 PM
 */
class PayPalEntryPayment
{

    private $memberId;
    private $total;
    private $entryId;
    private $meetName;
    private $invoiceId;
    private $payerName;
    private $payerEmail;

    private $apiContext;

    private $items;

    private $logger;

    public function __construct() {

        $this->apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $GLOBALS['paypalClientId'],     // ClientID
                $GLOBALS['paypalClientSecret']      // ClientSecret
            )
        );

        $this->apiContext->setConfig(array('mode' => 'live'));

        $this->logger = Logger::getLogger("PayPalEntryPayment");

    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }


    public function addItem($description, $qty, $amount) {

        $this->items[] = array('description' => $description, 'quantity' => $qty, 'amount' => $amount);

        // Update running total amount
        $this->total = $this->total + ($qty * $amount);

    }

    /**
     * @return mixed
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * @param mixed $entryId
     */
    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;
    }

    /**
     * @return mixed
     */
    public function getMeetName() {

        return $this->meetName;
    }

    /**
     * @param mixed $meetName
     */
    public function setMeetName($meetName) {

        $this->meetName = $meetName;
    }

    /**
     * @return mixed
     */
    public function getInvoiceId() {

        return $this->invoiceId;
    }

    public function processPayment() {

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $itemList = new ItemList();
        $arrItems = array();

        foreach ($this->items as $i) {

            $objItem = new Item();
            $objItem->setName($i['description']);
            $objItem->setQuantity($i['quantity']);
            $objItem->setPrice($i['amount']);
            $objItem->setCurrency("AUD");

            $arrItems[] = $objItem;

        }

        $itemList->setItems($arrItems);

        $amount = new Amount();
        $amount->setCurrency("AUD")
            ->setTotal($this->getTotal());

        $transaction = new Transaction();
        $this->invoiceId = uniqid();

        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription($this->meetName . " - Entry " . $this->entryId)
            ->setInvoiceNumber($this->invoiceId);

        //$baseUrl = "http://localhost:8888";
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("http://forum.mastersswimmingqld.org.au/entry-manager-new/enter-a-meet?view=step4&success=true")
            ->setCancelUrl("http://forum.mastersswimmingqld.org.au/entry-manager-new/enter-a-meet?view=step4&success=false");

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        $request = clone $payment;

        try {
            $payment->create($this->apiContext);
        } catch (Exception $ex) {

            //echo "<pre>\n";
            //print_r($ex);
            //echo "</pre>\n";
        }

        $approvalUrl = $payment->getApprovalLink();

        // Store the payment details
        $this->storePayment();

        return $approvalUrl;

    }

    /**
     *  Stores the initial details of the payment, linking the unique invoice
     *  id to the entry id.
     */
    private function storePayment() {

        $insert = $GLOBALS['db']->query("INSERT INTO paypal_payment (meet_entry_id, invoice_id) 
                                          VALUES (?, ?);",
                                        array($this->entryId, $this->invoiceId));
        db_checkerrors($insert);

    }

    public function finalisePayment($paymentId, $payerID) {

        $payment = Payment::get($paymentId, $this->apiContext);
        $execution = new PaymentExecution();

        $execution->setPayerId($payerID);

        $paidAmount = 0;

        try {

            $result = $payment->execute($execution, $this->apiContext);

            // Log the result
            $this->logger->debug($result);

            $transactions = $result->getTransactions();

            // Retrieve the paid amount
            $paidAmount = $transactions[0]->getAmount()->getTotal();
            $this->paid = $paidAmount;

            // Retreive the invoice id
            $this->invoiceId = $transactions[0]->getInvoiceNumber();

            // Get the entry Id associated with this one
            $entryId = $GLOBALS['db']->getOne("SELECT meet_entry_id FROM paypal_payment 
                                              WHERE invoice_id = ?", array($this->invoiceId));
            db_checkerors($entryId);

            // Load the entry and record payment
            $entry = new MeetEntry();
            $entry->loadId($entryId);
            $entry->makePayment($this->paid, 1, "PayPal Invoice " . $this->invoiceId);

            // Retrieve the payer details
            $payer = $payment->getPayer();
            $payerInfo = $payer->getPayerInfo();
            $this->payerName = $payerInfo->getFirstName() . ' ' . $payerInfo->getLastName();
            $this->payerEmail = $payerInfo->getEmail();

            // Log the details
            $this->logger->info("finalisePayment: $paidAmount for entry " . $this->entryId .
                " for entrant " . $this->payerName . " <" . $this->payerEmail . ">");

        } catch (Exception $ex) {

            // Log the exception
            $this->logger->error("finalisePayment exception: " . $ex);

        }

        return $paidAmount;

    }

}