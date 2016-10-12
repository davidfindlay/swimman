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
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/RelayEntry.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/RelayEntryMember.php');

// Get Joomla User ID
$curJUser = JFactory::getUser();
$curUserId = $curJUser->id;
$curUsername = $curJUser->username;

// Store data in session
$sess = JFactory::getSession();

// Look up Swimman DB to see if this user is linked to a member
$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite WHERE joomla_uid = '$curUserId';");
db_checkerrors($memberId);

$member = new Member;
$member->loadId($memberId);
$memberFullname = $member->getFullname();
$memberClubs = $member->getClubIds();

echo "<style type=\"text/css\">\n";
echo "label {\n";
echo "	font-weight: bold;\n";
echo "	width: 12em;\n";
echo "	float: left;\n";
echo "}\n\n";

echo ".error {";

echo "	color: red; ";
echo "	font-weight: bold;";

echo "}";

echo ".entrytool_error {";

echo "	margin-left: 2em;";
echo "	margin-right: 2em;";
echo "	margin-top: 1em;";
echo "	margin-bottom: 1em;";
echo "	pading-left: 1em;";
echo "	pading-right: 1em;";
echo "	pading-top: 1em;";
echo "	pading-bottom: 1em;";
echo "	border-style: solid;";
echo "	border-width: 2px;";
echo "	border-color: black;";
echo "	background-color: #FFFFCC;";
echo "	font-size: -1;";
echo "}";

echo ".entrytool_reason {";
echo "	clear: both;";
echo "}";

echo ".entrytool_sign {";
echo "	float: left;";
echo "}";

echo "h3.entrytool_error {";
echo "	margin-top: 5px;";
echo "}";

echo "td {";
echo "	border: 0;";
echo "	padding: 0 5px 0 5px;";
echo "}";

echo "th {";
echo "	border: 0;";
echo "	padding: 0 5px 0 5px;";
echo "}";

echo "tr {";
echo "	border: 0;";
echo "}";

echo "table {";
echo "	border-style: none;";
echo "  border: 0;";
echo "}";

echo "li {";
echo "	margin-left: 1em;";
echo "}";

echo "</style>\n";

echo "<h1>Entry Checker Results</h1>\n";

$errors = $sess->get('ecErrors');
$memberErrors = $sess->get('ecMemberErrors');
$entryErrors = $sess->get('ecEventErrors');

// echo "Errors:";
// print_r($errors);
// echo "<br />\n";
// echo "Member Errors:";
// print_r($memberErrors);
// echo "<br />\n";
// echo "Entry Errors:";
// print_r($entryErrors);

// Show errors

if (count($errors) > 0) {
	
	foreach ($errors as $e) {
		
		echo "<div class=\"entrytool_error\">";
		echo "<table border=\"0\"><tr><td>\n";
		echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
		echo "</td><td><h3>\n";
		
		echo $e->getTitle();
		
		echo "</h3></td></tr></table>\n";
		echo "<p>";
		
		echo $e->getDesc();
		
		echo "</p>\n";
		echo "</div>\n";
		
	}
	
}

// Show Member Errors
if (count($memberErrors) > 0) {

	foreach ($memberErrors as $e) {

		echo "<div class=\"entrytool_error\">";
		echo "<table border=\"0\"><tr><td>\n";
		echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
		echo "</td><td><h3>\n";

		echo $e->getTitle();

		echo "</h3></td></tr></table>\n";
		echo $e->getDesc();
		echo "</div>\n";

	}

}

// Show Member Errors
if (count($entryErrors) > 0) {

	foreach ($entryErrors as $e) {

		echo "<div class=\"entrytool_error\">";
		echo "<table border=\"0\"><tr><td>\n";
		echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
		echo "</td><td><h3>\n";

		echo $e->getTitle();

		echo "</h3></td></tr></table>\n";
		echo "<p>";

		echo $e->getDesc();

		echo "</p>\n";
		echo "</div>\n";

	}

}

// Create table of entry data
echo "<h3>\n";
echo "List of Entries:\n";
echo "</h3>\n";

echo "<p>\n";

echo "<table>\n";

echo "<thead>\n";
echo "<tr>\n";
echo "<th>\n";
echo "Entrant:\n";
echo "</th>\n";
echo "<th>\n";
echo "Events:\n";
echo "</th>\n";
echo "<th>\n";
echo "Number of Events:\n";
echo "</th>\n";
echo "<th>\n";
echo "Entry Check Result:\n";
echo "</th>\n";

echo "</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";

// Step through list of entrant
$entryList = unserialize($sess->get('ecEntries'));

if (count($entryList) > 0) {

	foreach ($entryList as $e) {
		
		echo "<tr>\n";
		
		echo "<td>\n";
		$entrantName = $e->getEntrantName();
		echo $entrantName;
		echo "</td>\n";
		
		echo "<td>\n";
		echo $e->getEventList();
		echo "</td>\n";
		
		echo "<td style=\"text-align: center;\">\n";
		echo $e->getNumEntries();
		echo "</td>\n";
		
		echo "<td style=\"text-align: center;\">\n";
		
		// Get list of any errors for this user
		$memErrCheck = 0;		
		
		if (count($memberErrors) > 0) {
		
			foreach ($memberErrors as $e) {
				
				$errMemName = $e->getEntrantName();

				if ($errMemName == $entrantName) {
					
					$memErrCheck++;
					
				}
				
			}
			
		}
		
		if (count($entryErrors) > 0) {
		
			foreach ($entryErrors as $e) {
				
				$errMemName = $e->getEntrantName();
				
				if ($errMemName == $entrantName) {
						
					$memErrCheck++;	
				}
				
			}
			
		}
		
		if ($memErrCheck == 0) {
			
			echo "Ok";
			
		} else {
			
			echo $memErrCheck . " errors found!\n";
			
		}
		
		echo "</td>\n";
		
		echo "</tr>\n";
		
	}

}

echo "</tbody>\n";

echo "</table>\n";

echo "</p>\n";

echo "<h3>\n";
echo "List of Relays:\n";
echo "</h3>\n";

echo "<p>\n";

// Step through list of entrant
$relayList = unserialize($sess->get('ecRelays'));

if (count($relayList) > 0) {
	
	echo "<table>\n";

	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th>\n";
	echo "Event:\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Age Group:\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Letter:\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Members:\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Entry Check Result:\n";
	echo "</th>\n";
	
	echo "</tr>\n";
	echo "</thead>\n";
	
	echo "<tbody>\n";

	foreach ($relayList as $r) {
		
		$eventId = $r->getMeetEvent();
		$eventDetails = new MeetEvent();
		$eventDetails->load($eventId);
		$progNum = $eventDetails->getProgNumber();

		echo "<tr>\n";
		
		echo "<td style=\"text-align: center;\">\n";
		echo $progNum;
		echo "</td>\n";
				
		echo "<td style=\"text-align: center;\">\n";
		echo $r->getAgeGroupText();
		echo "</td>\n";
		
		echo "<td style=\"text-align: center;\">\n";
		echo $r->getLetter();
		echo "</td>\n";
		
		echo "<td>\n";
		echo $r->getMemberList();
		echo "</td>\n";
		
		echo "<td style=\"text-align: center;\">\n";
		echo "Ok";			// TODO: Actually check relays
		echo "</td>\n";
		
		echo "</tr>\n";
		
	}
	
	echo "</tbody>\n";

	echo "</table>\n";
	
} else {
	
	echo "No relays found!\n";
	
}

echo "</p>\n";

$totalErrors = count($errors) + count($memberErrors) + count($entryErrors);

if ($totalErrors == 0) {
	
	echo "<p>\n";
	
	echo "No errors have been found in your Team Manager entry file have been found. ";
	echo "You are now ready to submit it! Click Submit Entry to complete this process.";

	echo "</p>\n";
	echo "<form method=\"post\" name=\"ecSubmit\">\n";

	echo "<p>\n";
	echo "<input type=\"submit\" name=\"ecSubmitEntry\" value=\"Submit Entries\" />\n";
	echo "</p>\n";
	echo "</form>\n";
	
} else {
	
	echo "<p>\n";
	echo $totalErrors . " errors have been found in your Team Manager entry file. Please ";
	echo "correct these before submitting using the instructions above. Once these have been ";
	echo "corrected, please resubmit your entry.\n";
	echo "</p>\n";
	
}




?>