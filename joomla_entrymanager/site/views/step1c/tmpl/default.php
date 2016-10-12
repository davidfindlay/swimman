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

echo "<h1>Enter a Meet</h1>\n";
echo "<h2>Select Who This Entry is For</h2>\n";
echo "<p>\n";
echo "Because you have access to create entries for more than one member, you have ";
echo "been directed to this step. Please select the member who this entry is for.";
echo "</p>\n";
echo "<hr />\n";
echo "<form method=\"post\" action=\"";
echo JRoute::_('index.php?option=com_entrymanager');
		
echo "\">\n";

echo "<style type=\"text/css\">\n";
echo "label {\n";
echo "	font-weight: bold;\n";
echo "	width: 12em;\n";
echo "	float: left;\n";
echo "}\n\n";
echo "</style>\n";

// Check if there's an error
$errors = $sess->get('emErrorStep1c');

if ($errors != '') {
	
	echo "<p><strong>\n";
	echo $errors;
	echo "</strong></p>\n";
	
}


// Club information
echo "<p>\n";
echo "<label for=\"clubSelector\" >Tick here if this entry is for $memberFullname:";
echo "</label> \n";
echo "<input type=\"checkbox\" name=\"emYourself\" id=\"emYourself\" />";
echo "</p>\n";

echo "<p style=\"clear: both; margin-top: 10px;\">\n";
echo "<label for=\"entryManagerMeetId\">Or someone else: </label>\n";

echo "<div style=\"float: left;\">\n";
echo "<select name=\"emSomeoneElse\" id=\"emSomeoneElse\">\n";

// Option for if they don't need to select someone else
echo "<option value=\"\"></option>\n";

// Get list of members this user can access
$clubsAccess = $GLOBALS['db']->getAll("SELECT DISTINCT(club_id) FROM club_roles 
			WHERE member_id = '$memberId';");
db_checkerrors($clubsAccess);

if (count($clubsAccess) > 0) {

	$tmpClubsAccess = array();
	foreach ($clubsAccess as $tmp) {
	
		$tmpClubsAccess[] = $tmp[0];
	
	}

	$clubsAccessList = implode(',', $tmpClubsAccess);

	// Get list of members at those clubs
	$membersAccess2 = $GLOBALS['db']->getAll("SELECT id, firstname, surname FROM member WHERE id IN 
		(SELECT member_id FROM member_memberships
		WHERE club_id IN ($clubsAccessList) AND enddate > now())
		ORDER BY surname, firstname;");
	
	
// 	$membersAccess2 = $GLOBALS['db']->getAll("SELECT id, firstname, surname FROM member WHERE id IN
// 			(SELECT member_id FROM member_memberships
// 			WHERE club_id IN ($clubsAccessList) AND startdate < now() AND enddate > now())
// 			ORDER BY surname, firstname;");
	
	db_checkerrors($membersAccess2);
	
} else {
	
	// Member is not a club captain
	$membersAccess2 = array();
	
}

// Get list of any members user has been granted access to
$membersAccess1 = $member->isNominee();

if (count($membersAccess1) >= 1) {

	// Output list of members this user is nominee for
	foreach ($membersAccess1 as $m) {
		
		$memNom = new Member();
		$memNom->loadId($m);
		$memName = $memNom->getFullname();

		echo "<option value=\"$m\">$memName</option>\n";
	
	}
	
	if (count($membersAccess2) > 0) {
		
		echo "<option value=\"\"></option>\n";
		
	}
	
}

foreach ($membersAccess2 as $m) {

	$memId = $m[0];
	$memFirst = $m[1];
	$memLast = $m[2];
	echo "<option value=\"$memId\">$memFirst $memLast</option>\n";
	
}

echo "</select>\n";
echo "</div>\n";
echo "</p>\n";

echo "<p style=\"color: red\">\n";
echo $sess->get('emErrorList');
echo "</p>\n";

echo "<p style=\"clear: both\">\n";
echo "<input type=\"submit\" value=\"Back\" name=\"emSubmit1c\" />\n";
echo "<input type=\"submit\" value=\"Next\" name=\"emSubmit1c\" />\n";
echo "</p>\n";
echo "</form>\n";

?>