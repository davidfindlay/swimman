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

// Get Joomla User ID
$curJUser = JFactory::getUser();
$curUserId = $curJUser->id;
$curUsername = $curJUser->username;

// Look up Swimman DB to see if this user is linked to a member
$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite WHERE joomla_uid = '$curUserId';");
db_checkerrors($memberId);

$sess = JFactory::getSession();

$member = new Member;
$member->loadId($memberId);
$memberFullname = $member->getFullname();
$memberClubs = $member->getClubIds();
$memberStatus = $member->getMembershipStatusText(1);

$entrantId = $sess->get('emEntrant');

if ($entrantId != '') {

	$entrant = new Member();
	$entrant->loadId($entrantId);
	$entrantName = $entrant->getFullname();

} else {
	
	$entrantName = $memberFullname;
	
}
	
echo "<style type=\"text/css\">\n";
echo "label {\n";
echo "	font-weight: bold;\n";
echo "	width: 12em;\n";
echo "	float: left;\n";
echo "}\n\n";
echo "</style>\n";

// Show different title if you are editing an existing entry
if ($sess->get('emEntryEdit') == 'true') {
	
	echo "<h1>Edit Existing Entry</h1>\n";
	
} else {

	echo "<h1>Enter a Meet</h1>\n";
	
}

echo "<h2>Step 3</h2>\n";

echo "<p>\n";
echo "Please confirm the details of your entries. If they are incorrect, press \n";
echo "the back button at the bottom of this page to return to the previous page \n";
echo "and correct the details.\n";
echo "</p>\n";
//echo "<p>\n";
//echo "If you are satisfied with your entry, please select a payment method and \n";
//echo "press next.\n";
//echo "</p>\n";

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
echo $meetDet->getStartDate() . " - " . $meetDet->getName();
echo "<br />\n";


$entryD = $sess->get('emEntryData');
$entryDetails = unserialize($entryD);
$entryEvents = $entryDetails->getEvents();

if ($meetDet->getMealFee() > 0) {

    $mealname = $meetDet->getMealName();

    echo "<label>$mealname:</label>\n";
    echo $entryDetails->getNumMeals();
    echo "<br />\n";

}

if ($meetDet->getMassageFee()) {

    echo "<label>Massages:</label>\n";
    echo $entryDetails->getMassages();
    echo "<br />\n";

}

if ($meetDet->getProgramFee()) {

    echo "<label>Programmes:</label>\n";
    echo $entryDetails->getPrograms();
    echo "<br />\n";

}

echo "</p>\n";

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
$mealFee = $meetDet->getMealFee() * $entryDetails->getNumMeals();
$massageFee = $meetDet->getMassageFee() * $entryDetails->getMassages();
$programFee = $meetDet->getProgramFee() * $entryDetails->getPrograms();
$eventFees = $entryDetails->calcEventFees();
$totalFee = $meetFee + $mealFee + $massageFee + $eventFees + $programFee;
$amountPaid = 0;
$amountToPay = $totalFee;

if ($sess->get('emEntryEdit') == 'true') {

    $existingEntry = new MeetEntry();
    $entryId = $sess->get("emEntryId");

    if ($existingEntry->loadId($entryId)) {
        // echo "Loaded $entryId<br />\n";
    } else {
        // echo "Unable to load $entryId<br />\n";
    }

    $amountPaid = $existingEntry->getPaid();
    $amountToPay = $totalFee - $amountPaid;

}

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
echo "</tr>\n";

// Check if the user has paid

if ($sess->get('emEntryEdit') == 'true') {

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

}

echo "</table>\n";
echo "</p>\n";


echo "<form method=\"post\" action=\"";
echo JRoute::_('index.php?option=com_entrymanager&view=my-entries');
echo "\">\n";

if ($amountToPay > 0) {

    $meetPaymentDetails = $GLOBALS['db']->getAll("SELECT * FROM meet_payment_methods, payment_types WHERE
                            payment_types.id = meet_payment_methods.payment_type_id AND meet_id = ?;",
        array($meetId));
    db_checkerrors($meetPaymentDetails);

    if (count($meetPaymentDetails) > 1) {

        echo "<fieldset>\n";
        echo "<h3>Select a Payment Method:</h3>\n";
        echo "<p style=\"clear: left;\">Only payment methods accepted by this meet are shown. </p>\n";

// Get list of payment methods for this meet


// Step through available payment methods
        $first = true;

        foreach ($meetPaymentDetails as $p) {

            $methodId = $p[4];
            $methodName = $p[5];
            $methodLogo = $p[6];
            $methodWarning = $p[7];

            echo "<p style=\"clear: left;\">\n";
            echo "<input type=\"radio\" name=\"paymentType\" id=\"paymentType_$methodId\" value=\"$methodId\" ";

            if ($first) {

                echo "checked=\"checked\" ";
                $first = false;

            }

            echo "/>";

            if ($methodLogo != "") {

                // Decorative image only so don't include alt tags
                echo "<img src=\"$methodLogo\" />";

            }

            echo "<label for=\"paymentType_$methodId\">$methodName</label>\n";
            echo "</p>\n";

//    // Allows a HTML
//    if ($methodWarning != "") {
//
//        echo "<p style=\"clear: left;\">\n";
//        echo "$methodWarning\n";
//        echo "</p>\n";
//
//    }

        }

        echo "</fieldset>\n";

    } else {

        // TODO: Explain how payments are being taken for this meet


    }

}

// Submit entry
echo "<p style=\"clear: left;\">\n";
echo "<input type=\"submit\" name=\"emSubmit3\" value=\"Back\" /> ";

echo "<input type=\"submit\" name=\"emSubmit3\" value=\"Submit\" />";

//}

echo "</p>\n";

echo "</form>\n";

?>