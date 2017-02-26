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

// Store data in session
$sess = JFactory::getSession();

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
echo ".cellCentre {\n";
echo "  text-align: center;\n";
echo "}\n";
echo "</style>\n";


echo "<h1>Meet Entries</h1>\n";

echo "<p>\n";
echo "Here is a list of the entries for your meet. ";
echo "If you believe some entries are missing or you can't access a meet you should be able to,  ";
echo "please contact the Director of Recording. A club that is listed as Pending has not yet ";
echo "submitted their entry, so it may be subject to change. Entries listed as Submitted may be ";
echo "taken as final. When viewing entries by member, you will see the entry status as shown to ";
echo "that member. ";
echo "</p>\n";

// Retrieve a list of meets this meet official should have access to

$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet_access WHERE member_id = '$memberId'
		OR juser = '$curUserId';");
db_checkerrors($meetList);

if (count($meetList) == 0) {
	
	echo "<p>You do not have access to any meets!</p>\n";
	
} else {

	foreach ($meetList as $m) {
		
		$meetId = $m[1];
		$meetDetails = new Meet();
		$meetDetails->loadMeet($meetId);
		$meetName = $meetDetails->getName();
			
		echo "<h2>$meetName</h2>\n";
		
		echo "<table>\n";
		echo "<thead>\n";
		echo "<tr>\n";
		echo "<th>\n";
		echo "Club\n";
		echo "</th>\n";
		echo "<th>\n";
		echo "Entrants";
		echo "</th>\n";
		echo "<th>\n";
		echo "Meals";
		echo "</th>\n";
		echo "<th>\n";
		echo "Fees\n";
		echo "</th>\n";
		echo "<th>\n";
		echo "Download";
		echo "</th>\n";
		echo "</tr>\n";
		echo "</thead>\n";
		echo "<tbody>\n";
		
		// Get list of all entries into this meet
		$clubsEntering = $GLOBALS['db']->getAll("SELECT DISTINCT(club_id) FROM meet_entries 
				WHERE meet_id = '$meetId'
				ORDER BY club_id;");
		db_checkerrors($clubsEntering);
		
		$totalEntrants = 0;
		$totalMeals = 0;
		$totalFees = 0;
		
		foreach ($clubsEntering as $c) {
			
			$clubId = $c[0];
			$clubDetails = new Club;
			$clubDetails->load($clubId);
			$clubName = $clubDetails->getName();
		
			$entryInfo = $GLOBALS['db']->getRow("SELECT count(*), sum(meals), sum(cost) FROM meet_entries 
					WHERE meet_id = '$meetId' AND club_id = '$clubId' AND cancelled = 0;");
			
			echo "<tr>\n";
			
			$meetEntries = $GLOBALS['db']->getAll("SELECT * FROM meet_entries WHERE meet_id = '$meetId'
				AND cancelled = 0 ORDER BY club_id;");
			db_checkerrors($meetEntries);
			
			echo "<td>\n";
			echo "<a href=\"#$clubId\" onclick=\"displayEntryList($clubId)\">\n";
			echo $clubName;
			echo "</a>\n";
			echo "</td>\n";
			
			echo "<td class=\"cellCentre\">\n";
			echo $entryInfo[0];
			$totalEntrants = $totalEntrants + $entryInfo[0];
			echo "</td>\n";
				
			echo "<td class=\"cellCentre\">\n";
			echo $entryInfo[1];
			$totalMeals = $totalMeals + $entryInfo[1];
			echo "</td>\n";
			
			echo "<td class=\"cellCentre\">\n";
			echo "\$" . number_format($entryInfo[2], 2);
			$totalFees = $totalFees + $entryInfo[2];
			echo "</td>\n";
			
// 			echo "<td>\n";
			
// 			// Check if entry file is available for this club yet
// 			$fileName = $GLOBALS['db']->getOne("SELECT filename FROM meet_entry_files 
// 					WHERE clubid = '$clubId' AND meetid = '$meetId';");
// 			db_checkerrors($fileName);
			
// 			if (isset($fileName)) {
// 				echo "Submitted\n";
// 			} else {
// 				echo "Pending\n";
// 			}
			
// 			echo "</td>\n";

			echo "<td>\n";
			echo "<a href=\"/swimman/gettmentries.php?meet=$meetId&club=$clubId\">Download</a>\n";
			echo "</td>\n";
			
			echo "</tr>\n";
			
			// Add hidden row
			echo "<tr id=\"eventList_$clubId\" style=\"visibility: collapse; display: none;\">\n";
			echo "<td colspan=\"4\">\n";
			echo "<h4>Club Contact Details:</h4>\n";
			
//			// Get a list of club recorders for this club
//			$clubRoles = $clubDetails->getRoles();
//
//			foreach ($clubRoles as $r) {
//
//
//
//			}
			
			echo "</td>\n";
			echo "</tr>\n";
			
		}
		
		echo "<tr>\n";
		echo "<th>Total</th>\n";
		echo "<th class=\"cellCentre\">\n";
		echo $totalEntrants;
		echo "</th>\n";
		echo "<th class=\"cellCentre\">\n";
		echo $totalMeals;
		echo "</th>\n";
		echo "<th class=\"cellCentre\">\n";
		echo "$" . number_format($totalFees, 2);;
		echo "</th>\n";
		echo "<td class=\"cellCentre\">\n";
		echo "<a href=\"/swimman/gettmentries.php?meet=$meetId\">Download All</a>\n";
		echo "</td>\n";
		echo "</tr>\n";
		
		echo "</tbody>\n";
		echo "</table>\n";
		
		echo "<h3>Download Entries</h3>\n";
		echo "<p>\n";
		echo "<label>All Entries in One File:</label>\n";
		echo "<a href=\"/swimman/gettmentries.php?meet=$meetId\">Download</a>\n";
		echo "</p>\n";
		
		echo "<h3>Registration and Records Files:</h3>";
		echo "<p>\n";
		echo "<label>Registration RE1 File:</label>\n";
		echo "<a href=\"/swimman/re1/registrations.zip\">Download</a>\n";
		
		echo "<br />\n";
		echo "<label>Records File:</label>\n";
		echo "<a href=\"/swimman/records/records-LCM.zip\">Long Course Records</a> - \n";
		echo "<a href=\"/swimman/records/records-SCM.zip\">Short Course Records</a>\n";
		echo "<br />\n";
		echo "</p>\n";
		
	}

}


?>