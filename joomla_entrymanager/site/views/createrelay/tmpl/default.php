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
$memberStatus = $member->getMembershipStatusText(1);

$clubId = mysql_real_escape_string($_GET['club']);
$meetId = mysql_real_escape_string($_GET['meet']);

// Store data in session
$sess = JFactory::getSession();

$relayErrors = $sess->get('emRelayErrors');
$sess->set('emRelayErrors', '');

echo "<script src=\"components/com_entrymanager/entrymanager.js\" type=\"text/javascript\"></script>\n";

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
echo "td.short {\n";
echo "	text-align: center;\n";
echo "}\n";
echo "h2 {\n";
echo "	margin-top: 1em;\n";
echo "}\n";
echo "</style>\n";


echo "<h1>Create a Relay Team</h1>\n";

if ($relayErrors != "") {

	echo "<p><strong><i>$relayErrors</i></strong></p>\n";
	
}


echo "<p>\n";
echo "To create a relay team please first select the event and relay letter. "; 
echo "You can select either an age group with no swimmers or the four swimmers in the order \n";
echo "they will swim and the age group will be automatically calculated.\n";
echo "</p>\n";

// Get a list of events that have more than one leg for this meet
$eventList = $GLOBALS['db']->getAll("SELECT id FROM meet_events WHERE meet_id = ? 
		AND legs > 1;", array($meetId));
db_checkerrors($eventList);

// Get a list of swimmers for this club
//$swimmerList = $GLOBALS['db']->getAll("SELECT a.id, a.firstname, a.surname 
//		FROM member as a, member_memberships as b 
//		WHERE a.id = b.member_id  
//		AND b.club_id = ?
//		AND b.startdate <= CURDATE() AND b.enddate >= CURDATE();", array($clubId));

$swimmerList = $GLOBALS['db']->getAll("SELECT a.id, a.firstname, a.surname, a.dob
		FROM member as a, meet_entries as b
		WHERE a.id = b.member_id
		AND b.meet_id = ?
		AND b.club_id = ?
		ORDER BY a.surname, a.firstname;", array($meetId, $clubId));
db_checkerrors($swimmerList); 

echo "<form method=\"post\">\n";

echo "<input type=\"hidden\" name=\"meet\" value=\"$meetId\" />\n";
echo "<input type=\"hidden\" name=\"club\" value=\"$clubId\" />\n";

echo "<p>\n";
echo "<label>Event</label>\n";
echo "<select name=\"event\">\n";

foreach ($eventList as $e) {
	
	$eId = $e[0];
	$eDet = new MeetEvent();
	$eDet->load($eId);
	$progNum = $eDet->getProgNumber();
	$eDesc = $eDet->getShortDetails();
	
	echo "<option value=\"$eId\">Event $progNum - $eDesc</option>\n";
	
}

echo "</select>\n";

echo "</p>\n";

echo "<p>\n";
echo "<label>Letter</label>\n";
echo "<select name=\"letter\">\n";
echo "<option value=\"A\">A</option>\n";
echo "<option value=\"B\">B</option>\n";
echo "<option value=\"C\">C</option>\n";
echo "<option value=\"D\">D</option>\n";
echo "</select>\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Age Group</label>\n";
echo "<select name=\"agegroup\">\n";
echo "<option value=\"na\"></option>\n";

// Get age group list
$relayLegs = 4;
$ageGroupList = $GLOBALS['db']->getAll("SELECT * FROM age_groups WHERE age_groups.set = ? AND 
			swimmers = ?;", array(1, $relayLegs));
db_checkerrors($ageGroupList);

foreach ($ageGroupList as $g) {
	
	$gId = $g[0];
	$gName = $g[5];
	
	echo "<option value=\"$gId\">$gName</option>\n";
	
}

echo "</select>\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Swimmer 1:</label>\n";
echo "<select name=\"swimmer1\">\n";
echo "<option></option>\n";

foreach ($swimmerList as $s) {
	
	$sId = $s[0];
	$sName = $s[1] . " " . $s[2];
	
	echo "<option value=\"$sId\">$sName</option>\n";
	
}

echo "</select>\n";
echo "</p>\n";
	
echo "<p>\n";
echo "<label>Swimmer 2:</label>\n";
echo "<select name=\"swimmer2\">\n";
echo "<option></option>\n";

foreach ($swimmerList as $s) {

	$sId = $s[0];
	$sName = $s[1] . " " . $s[2];

	echo "<option value=\"$sId\">$sName</option>\n";

}

echo "</select>\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Swimmer 3:</label>\n";
echo "<select name=\"swimmer3\">\n";
echo "<option></option>\n";

foreach ($swimmerList as $s) {

	$sId = $s[0];
	$sName = $s[1] . " " . $s[2];

	echo "<option value=\"$sId\">$sName</option>\n";

}

echo "</select>\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Swimmer 4:</label>\n";
echo "<select name=\"swimmer4\">\n";
echo "<option></option>\n";

foreach ($swimmerList as $s) {

	$sId = $s[0];
	$sName = $s[1] . " " . $s[2];

	echo "<option value=\"$sId\">$sName</option>\n";

}

echo "</select>\n";
echo "</p>\n";

// echo "<p>If applicable fill out the following details:</p>\n";

// echo "<p>\n";
// echo "<label>Swimmer 5:</label>\n";
// echo "<select name=\"swimmer5\">\n";
// echo "<option></option>\n";

// foreach ($swimmerList as $s) {

// 	$sId = $s[0];
// 	$sName = $s[1] . " " . $s[2];

// 	echo "<option value=\"$sId\">$sName</option>\n";

// }

// echo "</select>\n";
// echo "</p>\n";

// echo "<p>\n";
// echo "<label>Swimmer 6:</label>\n";
// echo "<select name=\"swimmer6\">\n";
// echo "<option></option>\n";

// foreach ($swimmerList as $s) {

// 	$sId = $s[0];
// 	$sName = $s[1] . " " . $s[2];

// 	echo "<option value=\"$sId\">$sName</option>\n";

// }

// echo "</select>\n";
// echo "</p>\n";

echo "<p>\n";
echo "<label>Seed Time:</label>\n";
echo "<input type=\"text\" name=\"st_1\" id=\"st_1\" placeholder=\"00:00.00\" onblur=\"fixSeedTimes(1)\" />\n";
echo "<span id=\"info_1\" style=\"margin-left: 10px;\">e.g. 01:41.25</span>\n";
echo "</p>\n";

echo "<p>\n";
echo "<input type=\"submit\" name=\"createRelaySubmit\" value=\"Submit\" />\n";
echo "<input type=\"submit\" name=\"createRelayCancel\" value=\"Cancel\" />\n";
echo "</p>\n";

echo "</form>\n";





?>