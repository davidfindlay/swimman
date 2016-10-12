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
echo "</style>\n";


echo "<h1>Member Nominee Access</h1>\n";

echo "<p>\n";
echo "Setting up a nominee for a member allows the nominee to submit entries for them. ";
echo "Below is a list of current members with nominees in your club. If you have any issues ";
echo "please contact the <a href=\"mailto:recorder@mastersswimmingqld.org.au\">Director of Recording</a>.";
echo "</p>\n";

// Get a list of meets

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
$memberClubs = $member->getAccess();

echo "<form method=\"post\">\n";

if (isset($memberClubs)) {
	
	foreach ($memberClubs as $c) {
		
		if (count($memberClubs) > 1) {
			
			$clubDet = new Club();
			$clubDet->load($c);
			$clubName = $clubDet->getName();
			
			echo "<h3>$clubName</h3>\n";
			
		}
		
		$nomList = $GLOBALS['db']->getAll("SELECT * FROM member_access WHERE member IN
				(SELECT member_id FROM member_memberships WHERE club_id = '$c' AND startdate <= now()
				AND enddate >= now());");
		db_checkerrors($nomList);
		
		if (count($nomList) > 0) {
		
			echo "<p>\n";
			echo "<table>\n";
			echo "<thead>\n";
			echo "<tr>\n";
			echo "<th>Member Name</th>\n";
			echo "<th>Nominee Name</th>\n";
			echo "<th>Start Date</th>\n";
			echo "<th>End Date</th>\n";
			echo "<th> </th>\n";
			echo "</tr>\n";
			echo "</thead>\n";
			echo "<tbody>\n";
			
			if (isset($nomList)) {
				
				foreach ($nomList as $n) {
					
					$nomArrId = $n[0];
					
					$memDet = new Member();
					$memDet->loadId($n[1]);
					$memName = $memDet->getFullname();
					
					$nomDet = new Member();
					$nomDet->loadId($n[2]);
					$nomName = $nomDet->getFullname();
					
					$nomStartDate = date('d/m/Y', strtotime($n[3]));
					
					if ($n[4] == '0000-00-00') {
						
						$nomEndDate = '';
					
					} else {
	
						$nomEndDate = date('d/m/Y', strtotime($n[4]));
						
					}
					
					echo "<tr>\n";
					echo "<td>\n";
					echo $memName;
					echo "</td>\n";
					echo "<td>\n";
					echo $nomName;
					echo "</td>\n";
					echo "<td>\n";
					echo $nomStartDate;
					echo "</td>\n";
					echo "<td>\n";
					echo $nomEndDate;
					echo "</td>\n";
					echo "<td>\n";
					echo "<input type=\"checkbox\" name=\"nomDel\" id=\"nomDel\" value=\"$nomArrId\" />";
					echo "Remove\n";
					echo "</td>\n";
					echo "</tr>\n";
					
				}
				
			}
			
			echo "</tbody>\n";
			echo "</table>\n";
			echo "</p>\n";
			
		} else {
			
			echo "<p>\n";
			echo "No members of your clubs currently have nominees for entering meets. ";
			echo "</p>\n";
			
		}
		
		echo "<h4>Add a Member Nominee:</h4>\n";
		
		echo "<p>\n";
		echo "<label>Member:</label>\n";
		
		echo "<select name=\"nomMember\">\n";
		
		// Get list of members at those clubs
		$membersAccess2 = $GLOBALS['db']->getAll("SELECT id, firstname, surname FROM member WHERE id IN 
			(SELECT member_id FROM member_memberships
			WHERE club_id = '$c' AND startdate < now() AND enddate > now())
			ORDER BY surname, firstname;");
		db_checkerrors($membersAccess2);
		
		echo "<option></option>\n";
		
		foreach ($membersAccess2 as $m) {
			
			$mId = $m[0];
			$mName = $m[1] . ' ' . $m[2];
			echo "<option value=\"$mId\">$mName</option>\n";
			
		}
		
		echo "</select>\n";
		echo "</p>\n";
		
		echo "<p>\n";
		echo "<label>Nominee:</label>\n";
		
		echo "<select name=\"nomNominee\">\n";
		echo "<option></option>\n";
		foreach ($membersAccess2 as $m) {
			
			$mId = $m[0];
			$mName = $m[1] . ' ' . $m[2];
			echo "<option value=\"$mId\">$mName</option>\n";
				
		}
		
		echo "</select>\n";
		echo "</p>\n";
		
		echo "<p>\n";
		echo "<label>End Date:</label>";
		echo "<input type=\"text\" name=\"emNomEndDay\" size=\"3\" />\n";
		echo "/";
		echo "<input type=\"text\" name=\"emNomEndMon\" size=\"3\" />\n";
		echo "/";
		echo "<input type=\"text\" name=\"emNomEndYear\" size=\"5\" />\n";
		echo " DD/MM/YYYY - Leave blank if arrangement doesn't need a specific end date.\n";
		echo "</p>\n";
		
		echo "<p>\n";
		echo "<input type=\"submit\" name=\"emNomSubmit\" value=\"Submit\" />\n";
		echo "</p>\n";
		
	}
	
}
	
echo "</form>\n";





?>