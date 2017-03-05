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

// Store data in session
$sess = JFactory::getSession();

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
echo "table {\n";
echo "  margin-top: 5px;\n";
echo "  margin-bottom: 5px;\n";
echo "}\n";
echo "</style>\n";


echo "<h1>Meet List</h1>\n";

echo "<p>\n";
echo "Below is a list of upcoming and past meets. Some meets have not yet published a program of ";
echo "events. For those that have, you can click on the name of the meet to see the list. If you have any issues ";
echo "please contact the <a href=\"mailto:recorder@mastersswimmingqld.org.au\">Director of Recording</a>.";
echo "</p>\n";

// Get a list of meets

// Get Joomla User ID
$curJUser = JFactory::getUser();
$curUserId = $curJUser->id;
$curUsername = $curJUser->username;
	
echo "<div id=\"filterandsearch\">\n";
echo "<form method=\"post\">\n";

echo "<fieldset>\n";
echo "<label ford=\"filter\">Filter: </label>\n";
echo "<select name=\"filter\">\n";
echo "<option value=\"current\">Current</option>\n";
echo "<option value=\"all\">All</option>\n";
echo "<option value=\"past\">Past</option>\n";
echo "<option value=\"future\">Future</option>\n";
echo "</select>\n";
echo "<input type=\"submit\" name=\"filterSubmit\" value=\"Filter\" />\n";
echo "</fieldset>\n";
echo "</form>\n";
echo "</div>\n";

echo "<div id=\"meetlist\">\n";

echo "<form method=\"post\">\n";

// Get list of meets in criteria

if (isset($_POST['filterSubmit'])) {

	if ($_POST['filter'] == "all") {

		$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet ORDER BY startdate;");

	}

	if ($_POST['filter'] == "future") {

		$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet WHERE startdate > NOW() ORDER BY startdate;");

	}

	if ($_POST['filter'] == "past") {

		$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet WHERE startdate < NOW() ORDER BY startdate DESC;");

	}

	if ($_POST['filter'] == "current") {

		$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet WHERE startdate < DATE_ADD(NOW(), INTERVAL 3 MONTH) AND startdate > DATE_SUB(NOW(), INTERVAL 1 MONTH) ORDER BY startdate;");

	}

} else {

	$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet WHERE startdate < DATE_ADD(NOW(), INTERVAL 3 MONTH) AND startdate > DATE_SUB(NOW(), INTERVAL 1 MONTH) ORDER BY startdate;");

}

db_checkerrors($meetList);

echo "<script type=\"text/javascript\" src=\"/swimman/meets.js\"></script>\n";


echo "<p>\n";
echo "<table width=\"100%\">\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>Date</th>\n";
echo "<th>Meet</th>\n";
echo "<th style=\"text-align: center;\">Number of Events</th>\n";
echo "<th style=\"text-align: center;\">Status</th>\n";
echo "<th style=\"text-align: center;\">Files</th>\n";
echo "</tr>\n";
echo "</thead>\n";

foreach ($meetList as $m) {

	$meetId = $m[0];

	$meetDate = $m[2];
	$meetEnd = $m[3];
	$meetDeadline = $m[4];
	$meetContactName = $m[5];
	$meetEmailId = $m[6];
	$meetEmail = $GLOBALS['db']->getOne("SELECT address FROM emails WHERE id = '$meetEmailId';");
	db_checkerrors($meetEmail);
	$meetPhoneId = $m[7];
	$meetPhone = $GLOBALS['db']->getOne("SELECT phonenumber FROM phones WHERE id = '$meetPhoneId';");
	db_checkerrors($meetPhone);
	$meetName = $m[1];
	$meetLocation = $m[10];
	$meetNumEvents = $GLOBALS['db']->getOne("SELECT count(*) FROM meet_events WHERE meet_id = '$meetId';");
	db_checkerrors($meetNumEvents);
	$meetPublished = $m[11];

	echo "<tr class=\"list\">\n";

	// Meet Start Date
	echo "<td valign=\"top\" class=\"short\">\n";
	echo "<strong>\n";
	echo "<a id=\"meet_$meetId\">\n";
	echo date('j M', strtotime($meetDate));

	if (($meetEnd != $meetDate) && ($meetEnd != "0000-00-00")) {

		echo " to ";
		echo date('j M', strtotime($meetEnd));

	}

	if (date('Y', strtotime($meetDate)) != date('Y')) {
		
		echo " " . date('Y', strtotime($meetDate));
		
	}
	
	echo "</strong>\n";
	echo "<br />\n";
	echo "<span class=\"smalltext\" style=\"font-size: -1;\">\n";
	echo date('l', strtotime($meetDate));

	if (($meetEnd != $meetDate) && ($meetEnd != "0000-00-00")) {

		echo " to ";
		echo date('l', strtotime($meetEnd));

	}
	echo "</span>\n";
	echo "</a>\n";
	echo "</td>\n";

	// Meet name
	echo "<td valign=\"top\">\n";
	echo "<a href=\"#meet_$meetId\" onclick=\"displayDetails($meetId)\">\n";
	echo $meetName;
	echo "</a>\n";
	echo "<br />\n";
	echo "<span class=\"smalltext\">\n";
	echo $meetLocation;
	echo "</span>\n";
	echo "</td>\n";

	// Number of events
	echo "<td valign=\"top\" class=\"short\" style=\"text-align: center;\">\n";

	if ($meetNumEvents > 0) {
		
		echo "$meetNumEvents\n";
		
	}

	echo "</td>\n";

	// Published to users?
	echo "<td valign=\"top\" class=\"short\" style=\"text-align: center;\">\n";

	if ($meetPublished == 1 && strtotime($meetDeadline . " 23:59:59") >= time()) {

		echo "<a href=\"/entry-manager-new/enter-a-meet\">Open</a>\n";

	} elseif ($meetPublished == 1 && strtotime($meetDeadline . " 23:59:59") < time()) {
		
		echo "Closed\n";
		
	}

	echo "</td>\n";

	echo "<td valign=\"top\" class=\"short\" style=\"text-align: center;\">\n";

	// Get a list of files associated with this meet
	$filesList = $GLOBALS['db']->getAll("SELECT * FROM meet_files, meet_file_types 
			WHERE meet_files.type = meet_file_types.id AND meet_files.meet_id = $meetId;");
	db_checkerrors($filesList);
	
	foreach ($filesList as $f) {
		
		$fileTitle = $f[4];
		$filename = $f[3];
		
		echo "<a href=\"masters-data/meets/$meetId/$filename\">$fileTitle</a><br />\n";
		
	}
	
	// If eProgram exists show link
	$eProgramExists = $GLOBALS['db']->getRow("SELECT * FROM meet_programs WHERE meet_id = '$meetId';");
	db_checkerrors($eProgramExists);
	
	if (isset($eProgramExists)) {

		//echo "<a href=\"eprogram.php?id=$meetId\" class=\"tooltip\" data-tip=\"View the eProgram for this meet\">";
		//echo "eProgram</a><br />\n";

	}

	echo "</td>\n";

	echo "</tr>\n";

	// Hidden rows
	echo "<tr class=\"meetInfo\" id=\"mEventList_$meetId\" style=\"visibility: collapse; display: none;\">\n";

	// if event is single day, don't show date column info
	// if multiday, show day x and day name
	echo "<td>\n";
	// TODO: multiple session days
	echo "</td>\n";

	echo "<td colspan=\"4\">\n";
	$eventList = $GLOBALS['db']->getAll("SELECT id FROM meet_events WHERE meet_id = '$meetId';");
		db_checkerrors($eventList);

	// Show deadline
	echo "<div class=\"smalltext\">\n";
	
	echo "<strong>Entry Deadline: </strong>\n";
	echo date('l jS \of F, Y', strtotime($meetDeadline));
	
	echo "<br /><strong>Contact:</strong> $meetContactName<br />\n";
	echo "<strong>Email:</strong> <a href=\"mailto:$meetEmail\">$meetEmail</a><br />\n";
	echo "<strong>Phone:</strong> $meetPhone<br />\n";
	
	echo "</div>\n";

	echo "<table border=\"0\">\n";

	echo "<tr>\n";
	echo "<td>\n";

	$eventCounter = 0;
	$ePerCol = round($meetNumEvents / 3);

	foreach ($eventList as $e) {

	$eId = $e[0];
	$eObj = new MeetEvent;
	$eObj->load($eId);

	echo "<span class=\"smalltext\">\n";
	echo $eObj->getProgNumber();
	echo " - </span>\n";

	echo "<span class=\"smalltext\">\n";
	echo $eObj->getShortDetails() . "<br />\n";
	echo "</span>\n";

	$eventCounter++;

	if (($eventCounter == $ePerCol) || ($eventCounter == ($ePerCol * 2))) {
				
		echo "</td><td>\n";
				
	}

	}
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>\n";

	echo "</td>\n";

	echo "</tr>\n";

}

echo "</table>\n";
echo "</p>\n";





?>