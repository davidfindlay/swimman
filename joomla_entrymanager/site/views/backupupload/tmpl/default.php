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


echo "<h1>Upload Backup File</h1>\n";

echo "<p>\n";
echo "Uploading your Meet Manager backup file will automatically confirm all the entries for \n";
echo "the members listed in the program. Members will also be able to access their custom \n";
echo "eProgram. You can upload your backup file whenever there is a change and this will automatically \n";
echo "be reflected in Entry Manager and eProgram. Results will also populate as uploaded. \n";
echo " \n";
echo "</p>\n";
echo "<p>This process will take about 30-60 seconds and will return you to the upload form when complete.\n";
echo "</p>\n";

// Get a list of meets

echo "<form enctype=\"multipart/form-data\" method=\"post\">\n";

echo "<p>\n";
echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"5000000\" />\n";
echo "<label>Meet: </label>\n";

$meetSel = new MeetSelector;
$meetSel->setName("meetId");
$meetSel->publishedOnly();
$meetSel->output();

echo "<br />\n";

echo "<strong>Upload file: </strong> <input type=\"file\" name=\"userfile\" /><br />\n";
echo "<input type=\"submit\" name=\"importbackupfile\" value=\"Upload Backup File\" />\n";
echo "</p>\n";
echo "</form>\n";


?>