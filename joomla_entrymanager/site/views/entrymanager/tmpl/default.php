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
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEntry.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEvent.php');
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

$member = new Member;
$member->loadId($memberId);
$memberFullname = $member->getFullname();
$memberClubs = $member->getClubIds();

// Get list of members this member is nominee for if any
$membersAccess1 = $member->isNominee();

echo "<h1>Current Entries</h1>\n";

echo "<style type=\"text/css\">\n";
echo "label {\n";
echo "	font-weight: bold;\n";
echo "	width: 12em;\n";
echo "	float: left;\n";
echo "}\n\n";
echo "th {\n";
echo "  padding-left: 5px;\n";
echo "  padding-right: 5px;\n";
echo "}\n";
echo "td {\n";
echo "  padding-left: 5px;\n";
echo "  padding-right: 5px;\n";
echo "}\n";
echo "</style>\n";

echo "<p>\n";
echo "Here is a list of your current and recent meet entries. The entry details \n";
echo "below show whether or not your entry has been accepted or confirmed in the \n";
echo "meet. It also shows the events you are entered in and the nominated seed \n";
echo "times. Check that all your events have the same status. If you are unsure \n";
echo "about your entry status or wish to change details of your entry please \n";
echo "contact your Club Recorder or Club Captain. To enter a meet click Enter \n";
echo "a Meet on the left hand menu.\n";
echo "</p>\n";

if (count($membersAccess1) > 0) {
	
	echo "<p>\n";
	echo "You have been granted access as a nominee for entries of the following persons: </p>\n";
	echo "<ul>\n";
	
	foreach ($membersAccess1 as $a) {
		
		$memNom = new Member;
		$memNom->loadId($a);
		$memNomName = $memNom->getFullname();
		
		echo "<li>$memNomName</li>\n";
		
	}
	
	echo "</ul>\n";
	echo "</p>\n";
	
}

echo "<p>\n";
echo "If you would like to be able to create entries for another person please request ";
echo "your Club Recorder add you as a nominee for that member.\n";
echo "</p>\n";

$accessListSQL = '';
if (count($membersAccess1) > 0) {
	
	$tmpMembersAccess = array();
	foreach ($membersAccess1 as $tmp) {
	
		$tmpMembersAccess[] = $tmp[0];
	
	}
	
	$accessList = implode(',', $tmpMembersAccess);
	
	$accessListSQL = "OR member_id IN ($accessList)";
	
}

// Start meet Filter
$sess = JFactory::getSession();
$psMeetFilter = $sess->get('emMeetFilter');

// Get a list of meets

echo "<form method=\"post\">\n";

// Start meet Filter
$psMeetFilter = $sess->get('emMeetFilter');
$psMeetId = $sess->get('emMeetView');

echo "<p>\n";
echo "<label>Meet Filter:</label>\n";
echo "<select name=\"emMeetFilter\">\n";

echo "<option value=\"current\"";
if ($psMeetFilter == "current") {
	
	echo " selected";
	
}
echo ">Current</option>\n";

echo "<option value=\"all\"";
if ($psMeetFilter == "all") {

	echo " selected";

}
echo ">All</option>\n";

echo "<option value=\"past\"";
if ($psMeetFilter == "past") {
	
	echo " selected";
	
}
echo ">Past</option>\n";

echo "<option value=\"future\"";
if ($psMeetFilter == "future") {
	
	echo " selected";
	
}
echo ">Future</option>\n";
echo "</select>\n";
echo "<input type=\"submit\" name=\"emMeetFilterSubmit\" id=\"emMeetFilterSubmit\" value=\"Update\" />\n";

echo "</p>\n";
// echo "<p>\n";

if ($psMeetFilter == "") {
	
	$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet WHERE startdate < DATE_ADD(NOW(), INTERVAL 3 MONTH)
		AND startdate > DATE_SUB(NOW(), INTERVAL 1 MONTH) ORDER BY startdate;");
	
	// echo "Showing entries for meets up to 1 month ago through to meets 3 months in the future.";
	
}

if ($psMeetFilter == "all") {

	$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet ORDER BY startdate;");
	
	//echo "Showing entries for all meets in the Entry Manager system.";

}

if ($psMeetFilter == "future") {

	$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet WHERE startdate > NOW() ORDER BY startdate;");
	
	//echo "Showing entries for all future meets.";

}

if ($psMeetFilter == "past") {

	$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet WHERE startdate < NOW() ORDER BY startdate DESC;");
	
	//echo "Showing entries for all past meets in the Entry Manager system.";

}

if ($psMeetFilter == "current") {

	$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet WHERE startdate < DATE_ADD(NOW(), INTERVAL 3 MONTH) AND startdate > DATE_SUB(NOW(), INTERVAL 1 MONTH) ORDER BY startdate;");
	
	//echo "Showing entries for meets up to 1 month ago through to meets 3 months in the future.";

}

//echo "</p>\n";

db_checkerrors($meetList);

echo "<p>\n";
echo "<label>Meet: </label>\n";
echo "<select name=\"meetSelect\">\n";

foreach ($meetList as $m) {
	
	$mId = $m[0];
	$mName = $m[1];
	
	echo "<option value=\"$mId\"";
	
	if ($psMeetId == $mId) {
		
		echo " selected=\"selected\"";
		
	}
	
	echo ">$mName</option>\n";
	
}

echo "</select>\n";
echo "<input type=\"submit\" name=\"emMeetViewSubmit\" id=\"emMeetViewSubmit\" value=\"View Entries\" />\n";
echo "</p>\n";

// End Meet Filter

// Get list of Meet Entries the member has through this club
$meetEntries = $GLOBALS['db']->getAll("SELECT * FROM meet_entries WHERE meet_id = '$psMeetId' AND
		member_id = '$memberId' $accessListSQL ORDER BY meet_id;");
db_checkerrors($meetEntries);

foreach ($meetEntries as $e) {

	$meetId = $e[1];
	$entrantId = $e[2];
	$c = $e[8];
	$curEntry = new MeetEntry($entrantId, $c, $meetId);
	$curEntry->load();
	
	$curMeet = new Meet();
	$curMeet->loadMeet($meetId);
	$meetName = $curMeet->getName();
	
	$entryStatus = $curEntry->getStatus();
	$entryStatusDesc = $curEntry->getStatusDesc();
	$meetStart = $curMeet->getStartDate();

	$clubDetails = new Club();
	$clubDetails->load($c);
	$clubName = $clubDetails->getName();
	
	$entrant = new Member();
	$entrant->loadId($entrantId);
	
	$memberStatus = $entrant->getMembershipStatusText($c, $meetStart);
	$entrantName = $entrant->getFullname();
	
	echo "<h2 style=\"margin-top: 1em;\">$meetName</h2>\n";
	echo "<p>\n";
	echo "<label>Date: </label>";
	
	echo date('l jS \of F Y', strtotime($curMeet->getStartDate()));
	
	if ($curMeet->getDays() > 1) {
	
		echo " - " . date('l jS \of F Y', strtotime($curMeet->getEndDate()));
		
	}

	echo "<br />\n";
	
	if (count($membersAccess1) > 0) {
	
		echo "<label>Entrant: </label>";
		echo $entrantName;
		echo "<br />\n";
	
	}
	
	echo "<label>Swimming For: </label>$clubName<br />\n";
	echo "<label>Membership Status: </label>$memberStatus<br />\n";
	echo "<label>Entry Status: </label>\n";
	echo "<div style=\"margin-left: 12em;\">$entryStatus - $entryStatusDesc</div><br />\n";
	
	echo "<label>View eProgram: </label>\n";
	
	// Check if eProgram is available
	$eProg = $GLOBALS['db']->getRow("SELECT * FROM meet_programs WHERE meet_id = '$meetId';");
	db_checkerrors($eProg);
	
	if (isset($eProg)) {
		
		echo "<a href=\"/eprogram/eprogram-$meetId\">eProgram View</a>\n";
		
	} else {
		
		echo "eProgram not available yet!";
		
	}
	
	echo "</p>\n";
	echo "<table border=\"1\">\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th colspan=\"5\"><div align=\"center\">Individual Events</div></th>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<th>No.</th>\n";
	echo "<th>Event:</th>\n";
	echo "<th>Type:</th>\n";
	echo "<th>Nominated Time:</th>\n";
	echo "<th>Status:</th>\n";		
	echo "</tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";

	$eventArray = array_reverse($curEntry->getEvents());		
	foreach ($eventArray as $v) {
		
		$eventId = $v->getEventId();
		$eventDetails = new MeetEvent();
		$eventDetails->load($eventId);
		$eventName = $eventDetails->getName();
		$eventProg = $eventDetails->getProgNumber();
		$eventShort = $eventDetails->getShortDetails();
		$eventType = $eventDetails->getType();
		
		$seedTime = sw_formatSecs($v->getSeedTime());
		$vStatus = $v->getStatusText();
		
		echo "<tr>\n";
		echo "<td><div align=\"center\">$eventProg</div></td>\n";
		echo "<td>$eventShort";
		
		if ($eventName != '') {
			
			echo " - $eventName";
			
		}
		echo "\n";
		echo "</td>\n";
		echo "<td>$eventType</td>\n";
		echo "<td><div align=\"right\">$seedTime</div></td>\n";
		echo "<td>$vStatus</td>\n";
					
		echo "</tr>\n";
	
	}
	
	echo "</tbody>\n";
	echo "</table>\n";

}



?>