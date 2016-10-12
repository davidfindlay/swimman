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

echo "<style type=\"text/css\">\n";
echo "label {\n";
echo "	font-weight: bold;\n";
echo "	width: 12em;\n";
echo "	float: left;\n";
echo "}\n\n";
echo "</style>\n";

echo "<h1>Import TM Entries</h1>\n";

echo "<p>\n";
echo "This form will automatically import any entries in a Team Manager entry file into Entry Manager. \n";
echo "The name of the file you need to upload will look something like this: ";
echo "QXX-Entries-State-Championships-28Feb2015-001.ZIP";
echo "</p>\n";

$numClubs = 0;

foreach ($memberClubs as $c) {

	if ( $member->checkRole($c, 1) || $member->checkRole($c, 2)) {
		
		$numClubs++;
		
	}

}

// Member can submit entries
if ($numClubs > 0) {
		
	echo "<form enctype=\"multipart/form-data\" method=\"post\" action=\"";
	echo JRoute::_('index.php?option=com_entrymanager');
	echo "\">\n";
	echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"5000000\">\n";
	
	// If more than one club, allow user to select which club they're submitting entries for
	if ($numClubs > 1) {
	
		echo "<p>\n";
		echo "<label>Club Entry is For:</label>\n";
		echo "<div id=\"clubSelectionDiv\">\n";
		
		foreach ($memberClubs as $c) {
			
			if ($member->checkRole($c, 1) || $member->checkRole($c, 2)) {
			
				echo "<input type=\"radio\" name=\"tmClubId\" id=\"emClubId\" value=\"$c\" >";
		
				$clubDetails = new Club;
				$clubDetails->load($c);
				echo $clubDetails->getName();
				echo "<br />\n";
		
			}
		
		}
		
		echo "</div>\n";
		echo "</p>\n";
		
	}
	
// 	echo "<p>\n";
// 	echo "<label>Select Meet:</label>\n";
	
// 	$meetSel = new MeetSelector;
// 	$meetSel->setName("tmMeetId");
// 	$meetSel->publishedOnly();
	
// 	// Select the first one after today
// 	$firstMeetAfter = $GLOBALS['db']->getOne("SELECT id FROM meet WHERE startdate > now() LIMIT 1;");
// 	db_checkerrors($firstMeetAfter);
	
// 	$meetSel->selected($firstMeetAfter);
	
// 	$meetSel->output();
// 	echo "</p>\n";
	
	echo "<p>\n";
	echo "<label>TM Entry File: </label>\n";
	echo "<input type=\"file\" name=\"emUserfile\">\n";
	echo "</p>\n";
	echo "<p>\n";
	echo "<input type=\"submit\" name=\"emSendEntries\" value=\"Check Entries\" />\n";
	echo "</p>\n";
	echo "</form>\n";
		
}

?>