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


echo "<h1>Guest Member Registration</h1>\n";

echo "<p>\n";
echo "Before a Guest Member can swim at a meet, they must be registered. Please fill out the form below. ";
echo "The Guest can then be entered in meets for up to one month. If you have any issues ";
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
$memberClubs = $member->getClubIds();

echo "<form method=\"post\">\n";

if (count($memberClubs) > 1) {
	
	$clubChoices = array();
	
	foreach ($memberClubs as $c) {
		
		if ($member->checkRole($c, 1) || $member->checkRole($c, 2)) {
			
			$clubChoices[] = $c;
			
		}
		
	}
	
}

if (isset($clubChoices)) {
	if (count($clubChoices) > 1) {
		
		echo "<p>\n";
		echo "<label>Club:</label>\n";
		echo "<select name=\"emGuestClubId\">\n";
		
		foreach ($clubChoices as $c) {
			
			$clubDetails = new Club();
			$clubDetails->load($c);
			$clubName = $clubDetails->getName();
			
			echo "<option value=\"$c\">$clubName</option>\n";
			
		}
		
		echo "</select>\n";
		echo "</p>\n";
		
		
	} else {
		
		$memberClub = $memberClubs[0];
		echo "<input type=\"hidden\" name=\"emGuestClubId\" value=\"$memberClub\" />\n";
		
	}
}

echo "<p>\n";
echo "<label>First Name:</label>\n";
echo "<input type=\"text\" name=\"emGuestFirstName\" />\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Surname:</label>\n";
echo "<input type=\"text\" name=\"emGuestSurname\" />\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Other Names:</label>\n";
echo "<input type=\"text\" name=\"emGuestOtherNames\" />\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Date of Birth</label>\n";
echo "<input type=\"text\" name=\"emGuestDobDay\" size=\"3\" />\n";
echo "/";
echo "<input type=\"text\" name=\"emGuestDobMon\" size=\"3\" />\n";
echo "/";
echo "<input type=\"text\" name=\"emGuestDobYear\" size=\"5\" />\n";
echo " DD/MM/YYYY\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Gender:</label>\n";
echo "<input type=\"radio\" name=\"emGuestGender\" value=\"M\" />Male\n";
echo "<input type=\"radio\" name=\"emGuestGender\" value=\"F\" />Female\n";
echo "</p>\n";


echo "<p>\n";
echo "<input type=\"submit\" name=\"emGuestReg\" value=\"Submit\" />\n";
echo "</p>\n";
	
echo "</form>\n";





?>