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

// Store data in session
$sess = JFactory::getSession();

echo "<h1>Enter a Meet</h1>\n";
echo "<h2>Step 1</h2>\n";
echo "<p>\n";
echo "First, choose the club you are swimming for and the meet you wish to enter. If you are a member of ";
echo "only one club, this will be preselected for you. Your membership status is shown beside the name of ";
echo "the club. If you are a member of more than one club, you can choose which club you wish to swim for ";
echo "in this meet. If you believe the membership status shown below is incorrect, please check with the ";
echo "Club Captain or Club Recorder.</p>\n";
echo "<p>Only meets that are currently open to accept entries will be shown. \n";
echo "If the meet you wish to enter is not shown here please contact your Club Captain or Club Recorder.\n";
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

// Entrant 
$entrantId = $sess->get('emEntrant');

if (isset($entrantId)) {
	
	echo "<p>\n";
	echo "<label>Entrant:</label>\n";
	
	$entrantDetails = new Member();
	$entrantDetails->loadId($entrantId);
	echo $entrantDetails->getFullname();
	
	echo "</p>\n";
	
} else {

	$entrantId = $memberId;
	$entrantDetails = new Member();
	$entrantDetails->loadId($entrantId);
	
}

$entrantClubs = $entrantDetails->getClubIds();


if ($sess->get('emClubError') != "") {

    echo "<p style='color: red; font-weight: bold;'>\n";
    echo $sess->get('emClubError');
    echo "</p>\n";

}

// Club information
echo "<p>\n";
echo "<label for=\"clubSelector\" >Swimming for Club:";

echo "</label> \n";

echo "<div id=\"clubSelectionDiv\">\n";

// if only one club, no selection required
if (count($entrantClubs) > 1) {

	foreach ($entrantClubs as $c) {

		echo "<input type=\"radio\" name=\"emClubId\" id=\"emClubId\" value=\"$c\" ";
		
		if ($sess->get('emClubId'))
			echo "checked=\"checked\"";
		
		echo " />\n";
		$clubDetails = new Club;
		$clubDetails->load($c);
		echo $clubDetails->getName();
		echo "<br />\n";
		
	}

} elseif (count($entrantClubs) == 1) {

	$clubDetails = new Club;
	$cId = $entrantClubs[0];
	$clubDetails->load($cId);
	$clubName = $clubDetails->getName();

    echo "<input type=\"radio\" name=\"emClubId\" id=\"emClubId\" value=\"$cId\" checked=\"checked\" />";
    echo $clubName . "<br />";

} else {

	echo "<strong>Not currently an active financial member of any MSQ club!</strong>\n";

}

echo "</div>\n";

echo "</p>\n";

echo "<p style=\"clear: both; margin-top: 10px;\">\n";
echo "<label for=\"entryManagerMeetId\">Select a Meet: </label>\n";

echo "<div style=\"float: left;\">\n";

$meetList = new MeetSelector();
$meetList->setName("emMeetId");
$meetList->selected($sess->get('emMeetId'));
$meetList->publishedOnly();
$meetList->availableOnly();
$meetList->output();

echo "</div>\n";
echo "</p>\n";

echo "<p style=\"color: red\">\n";
echo $sess->get('emErrorList');
echo "</p>\n";

echo "<p style=\"clear: both\">\n";
echo "<input type=\"submit\" value=\"Back\" name=\"emSubmit1\" />\n";
echo "<input type=\"submit\" value=\"Next\" name=\"emSubmit1\" />\n";
echo "</p>\n";
echo "</form>\n";

?>