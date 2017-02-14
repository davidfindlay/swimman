<?php
/**
 * @version		$Id: default.php 15 2009-11-02 18:37:15Z chdemko $
 * @package		Joomla16.Tutorials
 * @subpackage	Components
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @author		Christophe Demko
 * @link		http://joomlacode.org/gf/project/entrymanager_1_6/
 * @license		License GNU General Public License version 2 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/setup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Club.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Meet.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEvent.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEntry.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEntryEvent.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Member.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetSelector.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/PayPalEntryPayment.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/ConfirmationEmail.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/SlackNotification.php');

// Get Joomla User ID
$curJUser = JFactory::getUser();
$curUserId = $curJUser->id;
$curUsername = $curJUser->username;

// Look up Swimman DB to see if this user is linked to a member
$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite WHERE joomla_uid = '$curUserId';");
db_checkerrors($memberId);

$sess = JFactory::getSession();
$jinput = JFactory::getApplication()->input;

$member = new Member;
$member->loadId($memberId);
$memberFullname = $member->getFullname();
$memberClubs = $member->getClubIds();
$memberStatus = $member->getMembershipStatusText(1);

$memberId = $sess->get('emMemberId');

$meetDet = new Meet;
$meetId = $sess->get('emMeetId');
$meetDet->loadMeet($meetId);
$meetName = $meetDet->getName();

$clubDet = new Club;
$clubId = $sess->get('emClubId');
$clubDet->load($clubId);
$clubName = $clubDet->getName();

// Load entry details
$entry = new MeetEntry();
$entryId = $sess->get("emEntryId");
//echo "entryid = $entryId<br />\n";

if ($entry->loadId($entryId)) {
//    echo "Loaded Entry<br />\n";
} else {
//    echo "Unable to Load Entry<br />\n";
}

$entrant = new Member();
$entrant->loadId($entry->getMemberId());
$entrantName = $entrant->getFullname();

$amountPaid = 0;
$paymentStatus = false;

// Notify slack


if ($jinput->get('success') == 'true') {

    // Payment made

    $pp = new PayPalEntryPayment();
    $paymentId = $jinput->get('paymentId');
    $payerID =  $jinput->get('PayerID');

    $amountPaid = $pp->finalisePayment($paymentId, $payerID);
    addlog("Entry Manager", "PayPal Payment", "$entrantName paid $amountPaid for $meetId");

    $entry->makePayment($amountPaid, 1);    // Record payment as made by paypal

    if ($sess->get('emEntryEdit') == "true") {

        $message = "Edited entry to $meetName by $entrantName for $clubName - Paid \$$amountPaid.";

    } else {

        $message = "New entry to $meetName by $entrantName for $clubName - Paid \$$amountPaid.";

    }

    $slack = new SlackNotification();
    $slack->setMessage($message);
    $slack->setChannel("#nationals2017");
    $slack->send();

    $paymentStatus = true;

} elseif($jinput->get('success') == 'false') {

    // payment not made
    addlog("Entry Manager", "PayPal Payment Failed", "$entrantName did not pay for $meetId");

    if ($sess->get('emEntryEdit') == "true") {

        $message = "Edited entry to $meetName by $entrantName for $clubName - Payment unsuccessful - Paid \$$amountPaid.";

    } else {

        $message = "New entry to $meetName by $entrantName for $clubName - Payment unsuccessful - Paid \$$amountPaid.";

    }

    $slack = new SlackNotification();
    $slack->setMessage($message);
    $slack->setChannel("#nationals2017");
    $slack->send();

} else {

    // Payment wasn't required.
    $paymentStatus = true;

    if ($sess->get('emEntryEdit') == "true") {

        $refundAmount = -floatval($sess->get('emRefundAmount'));

        if ($refundAmount > 0) {

            $message = "Edited entry to $meetName by $entrantName for $clubName - Refund of \$$refundAmount required.";

        } else {

            $message = "Edited entry to $meetName by $entrantName for $clubName - No payment required.";

        }

    } else {

        $message = "New entry to $meetName by $entrantName for $clubName - No payment required.";

    }

    $entry->calcCost();

    //addlog("test", "Step 4 no payment", $entry->getPaid() . " >= " . $entry->getCost());
    if ($entry->getPaid() >= $entry->getCost()) {

        // Entry is already paid
        $entry->setEventStatuses(2);
        $entry->updateEventStatuses();
        $entry->setStatus(2);
        $entry->updateStatus();

        addlog("Entry Manager", "Status Updated", "As entry is fully paid, updated status to Accepted");

    }

    $slack = new SlackNotification();
    $slack->setMessage($message);
    $slack->setChannel("#nationals2017");
    $slack->send();

}
	
echo "<style type=\"text/css\">\n";
echo "label {\n";
echo "	font-weight: bold;\n";
echo "	width: 12em;\n";
echo "	float: left;\n";
echo "}\n\n";
echo "</style>\n";

// Show different title if you are editing an existing entry
echo "<h1>Enter a Meet</h1>\n";

echo "<h2>Entry Confirmation</h2>\n";

if ($sess->get('emEntryEdit') ==  "true") {

    echo "<h3 style='color: green'>&#x2714 Entry Edited</h3>\n";
    echo "<p>\n";
    echo "Your entry has been edited. Please see the details listed below.\n";
    echo "</p>\n";

} else {

    echo "<h3 style='color: green'>>&#x2714 Entry Created</h3>\n";
    echo "<p>\n";
    echo "Your entry has been created. Please see the details listed below.\n";
    echo "</p>\n";

}

if (!$paymentStatus) {

    echo "<h3 style='color: red'>&#x2718 Payment Cancelled or Failed</h3>\n";
    echo "<p>\n";
    echo "Your payment was cancelled or otherwise not confirmed. Please try again. If you have any ";
    echo "queries please <a href=\"mailto:recorder@mastersswimmingqld.org.au\">email the State Recorder</a>.";
    echo "</p>\n";

} else {

    echo "<h3 style='color: green'>&#x2714 Payment Received</h3>\n";
    echo "<p>\n";
    echo "Your payment has been received. You will receive a receipt via email from PayPal.";
    echo "</p>\n";

}

echo "<h3>Entry Details</h3>\n";
echo "<p>\n";
echo "<label>Swimmer: </label>\n";
echo "$entrantName<br />\n";
echo "<label>Club: </label>\n";
$clubDet = new Club;
$clubDet->load($sess->get('emClubId'));
echo $clubDet->getName();
echo "<br />\n";
echo "<label>Meet: </label>\n";
$meetDet = new Meet;
$meetId = $sess->get('emMeetId');
$meetDet->loadMeet($meetId);
echo $meetDet->getName() . "(" . date('d/m/Y', strtotime($meetDet->getStartDate()));

if (($meetDet->getEndDate() != "0000-00-00") && ($meetDet->getStartDate != $meetDet->getEndDate())) {

    echo " - " . date('d/m/Y', strtotime($meetDet->getEndDate()));

}

echo ")\n";

echo "<br />\n";

if ($meetDet->getMealFee() > 0) {

    $mealname = $meetDet->getMealName();

    echo "<label>$mealname:</label>\n";
    echo $entry->getNumMeals();
    echo "<br />\n";

}

if ($meetDet->getMassageFee()) {

    echo "<label>Massages:</label>\n";
    echo $entry->getMassages();
    echo "<br />\n";

}

if ($meetDet->getProgramFee()) {

    echo "<label>Programmes:</label>\n";
    echo $entry->getPrograms();
    echo "<br />\n";

}

echo "</p>\n";

$entryEvents = $entry->getEvents();

echo "<p>\n";
echo "<label>Event Entries:</label>\n";

if (isset($entryEvents)) {

	echo "<table>\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th colspan=\"2\">Event:</th>\n";
	echo "<th>Nominated Time:</th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";

	foreach($entryEvents as $e) {
		
		$eventDet = new MeetEvent();
		$eventDet->load($e->getEventId());
		$seedTimeMin = floor($e->getSeedTime() / 60);
		$seedTimeSecs = fmod($e->getSeedTime(), 60);
		$seedTimeDisp = $seedTimeMin . ':' . sprintf("%05.2f", $seedTimeSecs);

        if (!$e->getCancelled()) {

            echo "<tr>\n";
            echo "<td style=\"padding-left: 5px; padding-right: 5px; text-align: center;\">\n";
            echo $eventDet->getProgNumber();
            echo "</td>\n";
            echo "<td style=\"padding-right: 5px; padding-left: 5px;\">\n";
            echo $eventDet->getShortDetails();
            echo "</td>\n";
            echo "<td style=\"padding-left: 5px; padding-right: 5px; text-align: right;\">\n";

            if ($seedTimeDisp == "0:00.00") {

                if ($eventDet->getLegs() > 1)
                    echo "n/a - Relay\n";
                else
                    echo "No Time Nominated";

            } else {

                echo $seedTimeDisp;

            }

            echo "</td>\n";

            echo "</tr>\n";

        }
		
	}

	echo "</tbody>\n";
	echo "</table>\n";

} else {
	
	echo "No events entered!\n";
	
}
	
echo "</p>\n";

$meetFee = $meetDet->getMeetFee();
$mealFee = $meetDet->getMealFee() * $entry->getNumMeals();
$massageFee = $meetDet->getMassageFee() * $entry->getMassages();
$programFee = $meetDet->getProgramFee() * $entry->getPrograms();
$eventFees = $entry->calcEventFees();
$totalFee = $meetFee + $mealFee + $massageFee + $eventFees + $programFee;
$amountPaid = $entry->getPaid();
$amountToPay = $totalFee - $amountPaid;

echo "<p>\n";
echo "<label>Fees Payable: </label>\n";
echo "<table>\n";
echo "<tr>\n";
echo "<th style=\"padding-right: 5px; padding-left: 5px;\">\n";
echo "Entry Fee:\n";
echo "</th>\n";
echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
echo "\$" . number_format($meetFee, 2);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<th style=\"padding-right: 5px; padding-left: 5px;\">\n";
echo "Event Fees:\n";
echo "</th>\n";
echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
echo "\$" . number_format($eventFees, 2);
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<th style=\"padding-right: 5px; padding-left: 5px\">\n";
echo "Meal Fee:\n";
echo "</th>\n";
echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
echo "\$" . number_format($mealFee, 2);
echo "</td>\n";
echo "</tr>\n";

if ($meetDet->getMassageFee() > 0) {

    echo "<tr>\n";
    echo "<th style=\"padding-right: 5px; padding-left: 5px\">\n";
    echo "Massage Fee:\n";
    echo "</th>\n";
    echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
    echo "\$" . number_format($massageFee, 2);
    echo "</td>\n";
    echo "</tr>\n";

}

if ($meetDet->getProgramFee() > 0) {

    echo "<tr>\n";
    echo "<th style=\"padding-right: 5px; padding-left: 5px\">\n";
    echo "Programme Fee:\n";
    echo "</th>\n";
    echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
    echo "\$" . number_format($programFee, 2);
    echo "</td>\n";
    echo "</tr>\n";

}

echo "<tr>\n";
echo "<th style=\"padding-right: 5px; padding-left: 5px;\">Total Cost:</th>\n";
echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
echo "\$" . number_format($totalFee, 2);
echo "</td>\n";
echo "<tr>\n";
echo "<th style=\"padding-right: 5px; padding-left: 5px;\">Paid:</th>\n";
echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
echo "<strong>\$" . number_format($amountPaid, 2) . "</strong>";
echo "</td>\n";
echo "<tr>\n";
echo "<th style=\"padding-right: 5px; padding-left: 5px;\">Amount Due:</th>\n";
echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
echo "<strong>\$" . number_format($amountToPay, 2) . "</strong>";
echo "</td>\n";
echo "</tr>\n";

// Check if the user has paid


echo "</table>\n";
echo "</p>\n";

$sess->clear('emEntryData');
$sess->clear('emMemberId');
$sess->clear('emEntrant');
$sess->clear('emMeetId');
$sess->clear('emClubId');
$sess->clear('emEntryEdit');
$sess->clear('emEntryId');

?>