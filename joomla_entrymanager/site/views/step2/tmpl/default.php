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
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEvent.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEntry.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEntryEvent.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Member.php');


echo "<script src=\"components/com_entrymanager/entrymanager.js\" type=\"text/javascript\"></script>\n";

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

//echo "<p>\n";
//echo "Welcome $memberFullname. Your membership status is $memberStatus.\n";
//echo "</p>\n";

$sess = JFactory::getSession();
$jinput = JFactory::getApplication()->input;
$editEntry = $jinput->get('editEntry');

// If this is an edit request load the entry
if($editEntry != "") {
	
	$entryData = new MeetEntry();
    $entryId = $jinput->get('editEntry', true, string);
	$entryData->loadId($entryId);
	
	$sess->set('emEntrant', $entryData->getMemberId());
	$sess->set('emMeetId', $entryData->getMeetId());
	$sess->set('emClubId', $entryData->getClubId());
	$sess->set('emEntryData', serialize($entryData));
	$sess->set('emEntryEdit', 'true');
    $sess->set('emEntryId', $entryId);
	
}

// Show different title if you are editing an existing entry
if ($sess->get('emEntryEdit') == 'true') {
	
	echo "<h1>Edit Existing Entry</h1>\n";
	
} else {

	echo "<h1>Enter a Meet</h1>\n";
	
}

echo "<h2>Step 2</h2>\n";

echo "<style type=\"text/css\">\n";
echo "label {\n";
echo "	font-weight: bold;\n";
echo "	width: 12em;\n";
echo "	float: left;\n";
echo "}\n\n";
echo "#meetrules {\n";
echo "	margin-left: 12em;";
echo "	margin-bottom: 2em;";
echo "}\n";
echo "#entryErrors {\n";
echo "	margin-left: 12em;";
echo "	margin-bottom: 2em;";
echo "}\n";
echo "</style>\n";

$clubId = $sess->get('emClubId');
$meetId = $sess->get('emMeetId');

// Get listing of events in this meet
$meetDetails = new Meet();
$meetDetails->loadMeet($meetId);

echo "<h3>\n";
echo $meetDetails->getName();
echo "</h3>\n";

echo "<form method=\"post\" action=\"";
echo JRoute::_('index.php?option=com_entrymanager');
echo "\">\n";
echo "<p>\n";
echo "<label>Date";

if ($meetDetails->getDays() > 1) {
	
	echo "s";
	
}

echo ": </label>\n";

echo date('l jS \of F', strtotime($meetDetails->getStartDate()));

if ($meetDetails->getDays() > 1) {

	echo " - " . date('l jS \of F', strtotime($meetDetails->getEndDate()));
	
}

echo "<br />\n";
echo "<label>Location: </label>\n";
echo $meetDetails->getLocation();
echo "</p>\n";

// Load entry data
$tooManyEntries = false;
if (($sess->get('emEntryData')) !== null) {

	$entryD = $sess->get('emEntryData');
	$entryData = unserialize($entryD);
	$entryEvents = $entryData->getEvents();

	if ($entryData->getNumEntries() > $meetDetails->getMax()) {

		$tooManyEntries = true;

	}

	$entryErrors = $sess->get('emEntryErrorGroups');

}

echo "<fieldset>\n";

if ($meetDetails->getMealFee() > 0) {

    $mealName = "Meals";

    if ($meetDetails->getMealName() != '') {

        $mealName = $meetDetails->getMealName() . "s";

    }

	echo "<p>\n";
	echo "<label for=\"meals\">$mealName: </label>\n";
	$mealsIncluded = $meetDetails->getMealsIncluded();
	$mealsPreset = $mealsIncluded;
	
	if (isset($entryData)) {
		
		// Check if entry has already been started an number of meals set
		$mealsPreset = $entryData->getNumMeals();
		
		if ($mealsPreset < $mealsIncluded)
			$mealsPreset = $mealsIncluded;
		
	}
	
	echo "<input type=\"number\" name=\"numMeals\" style=\"width: 3em;\" min=\"0\" value=\"$mealsPreset\" />\n";
	
	if ($mealsIncluded != 0) {
		
		if ($mealsIncluded == 1) {
		
			echo "$mealsIncluded meal is included in the price of entry. Please add ";
			echo "additional meals if required.";
			
		} else {

			echo "$mealsIncluded meals are included in the price of entry. Please add ";
			echo "additional meals if required.";
			
		}
	}
		
	echo "</p>\n";
	
}

// Check if the medical certificate box has already been check
// and if there are notes
$preMedical = "";
$preNotes = "";
if (isset($entryData)) {
	
	if ($entryData->getMedical() == true) {
		
		$preMedical = "checked=\"checked\" ";
	
	}
	
	$preNotes = $entryData->getNotes();
			
}

// Check if there if there are massages
if ($meetDetails->getMassageFee() > 0) {

    if (isset($entryData)) {

        $ps_extra = $entryData->getMassages();

    } else {

        $ps_extra = 0;

    }

    echo "<p>\n";
    echo "<label for=\"numMassages\">Massages: </label>\n";
    echo "<input type=\"number\" id=\"numMassages\" name=\"numMassages\" min=\"0\" style=\"width: 3em;\" value=\"$ps_extra\"/>\n";
    echo "</p>\n";

}


//echo "<p>\n";
//echo "<label for=\"medical\">Medical Certificate: </label>\n";
//echo "<input type=\"checkbox\" id=\"medical\" name=\"medical\" $preMedical/>\n";
//echo "I have a medical certificate.";
//echo "</p>\n";

echo "<p style=\"clear: left;\">\n";
echo "<label for=\"notes\">Comments: </label>\n";
echo "<textarea name=\"comments\" rows=\"3\" cols=\"80\">$preNotes</textarea>\n";
echo "</p>\n";
echo "</fieldset>\n";

echo "<p>\n";
echo "<label>Meet Rules:</label>\n";
echo "</p>\n";
echo "<div id=\"meetrules\">\n";
$maxEvents = $meetDetails->getMax();
if ($maxEvents == 0) 
	echo "You may enter as many events as you wish.<br />\n";
else {
	
	if ($tooManyEntries == true) {
		
		echo "<strong>";
		//echo "<img src=\"/images/crossmark.jpg\" alt=\"Error!\" />";
		
	}
	
	echo "You may enter up to " . $meetDetails->getMax() . " individual events.<br />\n";
	
	if ($tooManyEntries == true) {
	
		echo "</strong>";
	
	}
	
}

echo $meetDetails->getRulesText();

echo "</div>\n";

if (isset($entryErrors)) {
	
	echo "<p>\n";
	echo "<label>Your entry fails the following rule(s): </label>\n";
	echo "</p>\n";
	
	echo "<div id=\"entryErrors\">\n";
	foreach ($entryErrors as $r) {
		
		$rules = $GLOBALS['db']->getOne("SELECT rule FROM meet_rules WHERE id = 
				(SELECT rule_id FROM meet_rules_groups WHERE meet_events_groups_id = '$r');");
		db_checkerrors($rules);
		
		echo "<strong>$rules</strong><br />\n";
		
	}
	
	echo "</div>\n";
}

echo "<p>\n";
echo "<label>Select your events:</label>\n";
echo "</p>\n";
echo "<table width=\"100%\">\n";
echo "<thead>\n";
echo "<tr>\n";
echo "<th>\n";
echo "Enter";
echo "</th>\n";
echo "<th colspan=\"2\">\n";
echo "Event";
echo "</th>\n";
echo "<th colspan=\"2\">\n";
echo "Seed Time(optional)";
echo "</th>\n";
echo "</tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

// Step through events

$eList = $meetDetails->getEventList();

foreach ($eList as $eId) {
	
	$e = new MeetEvent;
	$e->load($eId);
	
	echo "<tr>\n";
	echo "<td>\n";
	echo "<div align=\"center\">\n";
	echo "<input type=\"checkbox\" id=\"enter_$eId\" name=\"enterEvent[]\" value=\"$eId\" "; 
	echo "onclick=\"toggleFormVis($eId)\" ";
	
	$preSt = "";
	$seedSecs = "";
	$defVis = "hidden";
	$seedVis = "hidden";
	
	if (isset($entryD)) {
		
		if (isset($entryEvents)) {
		
			foreach($entryEvents as $d) {
				
				$eventCancelled = $d->getCancelled();
				
				if (($d->getEventId() == $eId) && ! $eventCancelled) {
					
					echo "checked=\"checked\"";
					$seedSecs = $d->getSeedTime();
					$preSt = $d->getSeedTime();
					$seedTimeMin = floor(floatval($preSt) / 60);
					$seedTimeSecs = fmod(floatval($preSt), 60);
					$preSt = $seedTimeMin . ':' . sprintf("%05.2f", $seedTimeSecs);
					$defVis = "visible";
					
					if ($preSt == "0:00.00") {
						
						$seedVis = "hidden";
						
					} else {
						
						$seedVis = "visible";
						
					}
					
				}
				
			}
		
		}
		
	}
	
	echo " />\n";
	echo "</div>\n";
	echo "</td>\n";
	
	echo "<td>\n";
	echo "<div align=\"center\">\n";
	echo $e->getProgNumber();
	echo "</div>\n";
	echo "</td>\n";
	echo "<td>\n";
	echo $e->getShortDetails();
	echo "</td>\n";
	
	echo "<td>\n";
	
	// If event type has more than 1 leg therefore is a relay, only show enter option.
	if ($e->getLegs() > 1) {
		
		echo "Tick the enter checkbox to nominate for this relay.";
		
	} else {
	
		echo "<input type=\"text\" id=\"st_$eId\" name=\"seedtime_$eId\" style=\"visibility: $seedVis; text-align: right;\" "; 
		echo "placeholder=\"00:00.00\" value=\"$preSt\" ";
		echo "onblur=\"fixSeedTimes($eId)\" />\n";
		
		$minSeedTime = $e->getDistanceMetres() * 0.28;
		
		if (($seedSecs < $minSeedTime) && ($seedSecs != 0)) {
			
			echo "<div id=\"timeshort_$eId\" style=\"margin-left: 10px; color: red;\">
				Error: The time you have entered is too short! Please use the correct time format! 
				e.g. 01:41.25 for 1 minute 41.25 seconds.</div>\n";
			
		} else {
		
			echo "<span id=\"info_$eId\" style=\"margin-left: 10px; visibility: hidden;\">e.g. 01:41.25</span>\n";
			
		}
	
	}
	
	echo "</td>\n";
	echo "<td>\n";
	echo "<input type=\"checkbox\" id=\"nt_$eId\" name=\"nt_$eId\" style=\"visibility: $defVis;\" ";
	echo "onclick=\"noTime($eId)\" ";
	
	if (isset($entryD)) {
		
		if ($preSt == "0:00.00") {
			
			echo "checked=\"checked\" ";
			
		}
		
	}
	
	echo "/>";
	echo "<label for=\"nt_$eId\" id=\"ntl_$eId\" style=\"margin-left: 10px; visibility: $defVis;\">No Time</label>\n";
	echo "</td>\n";
	
	echo "</tr>\n";

}

echo "</tbody>\n";
echo "</table>\n";

echo "<p>\n";
echo "<input type=\"submit\" name=\"emSubmit2\" value=\"Back\" /> ";
echo "<input type=\"submit\" name=\"emSubmit2\" value=\"Next\" />\n";
echo "</p>\n";

echo "</form>\n";

?>