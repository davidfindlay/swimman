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
echo "<p>\n";
echo "If you are satisfied with your entry, please select a payment method and \n";
echo "press next.\n";
echo "</p>\n";

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
$meetDet->loadMeet($sess->get('emMeetId'));
echo $meetDet->getStartDate() . " - " . $meetDet->getName();
echo "</p>\n";

$entryD = $sess->get('emEntryData');
$entryDetails = unserialize($entryD);
$entryEvents = $entryDetails->getEvents();

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

	echo "</tbody>\n";
	echo "</table>\n";

} else {
	
	echo "No events entered!\n";
	
}
	
echo "</p>\n";

$meetFee = $meetDet->getMeetFee();
$mealFee = $meetDet->getMealFee() * $entryDetails->getNumMeals();
$eventFees = $entryDetails->calcEventFees();
$totalFee = $meetFee + $mealFee + $eventFees;

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
echo "<tr>\n";
echo "<th style=\"padding-right: 5px; padding-left: 5px;\">Total Cost:</th>\n";
echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
echo "\$" . number_format($totalFee, 2);
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</p>\n";


echo "<form method=\"post\" action=\"";
echo JRoute::_('index.php?option=com_entrymanager&view=my-entries');
echo "\">\n";
echo "<p style=\"clear: left;\" >\n";
//echo "<label>Select a Payment Method:</label>\n";

// Get list of payment methods club accepts


echo "</p>\n";

// Submit entry
echo "<p style=\"clear: left;\">\n";
echo "<input type=\"submit\" name=\"emSubmit3\" value=\"Back\" /> ";
echo "<input type=\"submit\" name=\"emSubmit3\" value=\"Submit\" />";
echo "</p>\n";

echo "</form>\n";

?>