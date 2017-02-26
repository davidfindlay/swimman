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
echo "td.short {\n";
echo "	text-align: center;\n";
echo "}\n";
echo "h2 {\n";
echo "	margin-top: 1em;\n";
echo "}\n";
echo "</style>\n";


echo "<h1>Club Entries</h1>\n";

echo "<p>\n";
echo "Here is a list of the entries for each meet for the clubs you have access ";
echo "to. If you are unable to see details for club you should have access to ";
echo "please contact the Director of Recording.";
echo "</p>\n";

echo "<p>\n";
echo "<strong>Any entries listed here will be automatically send to the meet organisers \n";
echo "at midnight on the date of the meet entry deadline.</strong>\n";
echo "</p>\n";

// Get a list of meets

echo "<form method=\"post\" name=\"frmMeetFilter\">\n";

// Start meet Filter
$psMeetFilter = $sess->get('emMeetFilter');
$psMeetId = $sess->get('emMeetView');

// Start year selector
echo "<p>\n";
echo "<label>Year:</label>\n";
echo "<select name=\"emMeetFilter\" onchange=\"document.frmMeetFilter.submit()\">\n";

// Get list of years
$curYear = intval(date('Y'));
$yearStart = $curYear - 2;
$yearEnd = $curYear + 1;
$yearCnt = $yearStart;

while ($yearCnt <= $yearEnd) {
	
	// Default to selecting the current year unless a year has been preset
	if ($psMeetFilter != "" && $psMeetFilter == $yearCnt) {
		
		echo "<option value=\"$yearCnt\" selected>$yearCnt</option>\n";
	
	} elseif ($psMeetFilter == "" && $yearCnt == $curYear) {
	
		echo "<option value=\"$yearCnt\" selected>$yearCnt</option>\n";
		
	} else {
		
		echo "<option value=\"$yearCnt\">$yearCnt</option>\n";
		
	}

	$yearCnt++;	
}

echo "</select>\n";
echo "</p>\n";
// End year selector

// echo "<p>\n";

if ($psMeetFilter == "") {
	
	$psMeetFilter = $curYear;



	// echo "Showing entries for meets up to 1 month ago through to meets 3 months in the future.";

}

$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet 
		WHERE DATE_FORMAT(startdate, '%Y') = ? 
		ORDER BY startdate;", array($psMeetFilter));

db_checkerrors($meetList);
echo "<p>\n";
echo "<label>Meet: </label>\n";
echo "<select name=\"meetSelect\" onchange=\"document.frmMeetFilter.submit()\">\n";

foreach ($meetList as $m) {
	
	$mId = $m[0];
	$mName = $m[1];
	
	echo "<option value=\"$mId\"";
	
	if ($psMeetId == $mId) {
		
		echo " selected=\"selected\"";
		
	}
	
	echo ">$mName</option>\n";
	
}

echo "</select>\n";
echo "<input type=\"submit\" name=\"emMeetViewSubmit\" id=\"emMeetViewSubmit\" value=\"View Entries\" />\n";
echo "</p>\n";

// Club filter
$clubList = $GLOBALS['db']->getAll("SELECT DISTINCT(club_id) FROM club_roles
		WHERE member_id = '$memberId';");
db_checkerrors($clubList);

if (count($clubList) > 1) {
	
	echo "<p>\n";

	
	
	echo "</p>\n";

}


echo "</form>\n";

// End Meet Filter

if (isset($psMeetId)) {

	$meetId = $psMeetId;
	
	$meetDet = new Meet;
	$meetDet->loadMeet($psMeetId);
	$meetName = $meetDet->getName();
	$meetStart = $meetDet->getStartDate();
    $meetDeadline = $meetDet->getDeadline();
	
	echo "<h2>$meetName</h2>\n";
	
	// If Club Captain has access to more than one club then show the club name.
	
	$clubList = $GLOBALS['db']->getAll("SELECT DISTINCT(club_id) FROM club_roles 
			WHERE member_id = '$memberId';");
	db_checkerrors($clubList);
	
	$numClubs = count($clubList);
	$meetShown = 0;

	foreach ($clubList as $c) {
		
		$clubId = $c[0];
		$clubDet = new Club();
		$clubDet->load($clubId);
		
		// Get a list of entries for this meet for this club
		$entryList = $GLOBALS['db']->getAll("SELECT * FROM meet_entries WHERE meet_id = ? 
				AND club_id = ?;", array($psMeetId, $clubId));
		db_checkerrors($entryList);
		
		$numEntries = count($entryList);


		if ($numClubs > 1) {
			
			echo "<h3>";
			echo $clubDet->getName();
			echo "</h3>\n";
			
		}
		
		echo "<h4>Individual Entries:</h4>\n";
		
		if ($numEntries < 1) {
			
			echo "<p>No individual entries!</p>\n";
			
		} else {
			
			echo "<table width=\"100%\">\n";
			echo "<thead>\n";
			echo "<tr>\n";
			echo "<th>\n";
			echo "Entrant\n";
			echo "</th>\n";
			echo "<th>\n";
			echo "MSA Number\n";
			echo "</th>\n";
			echo "<th>\n";
			echo "Date of Birth\n";
			echo "</th>\n";
			echo "<th>\n";
			echo "Age Group\n";
			echo "</th>\n";
			echo "<th>\n";
			echo "Membership Status\n";
			echo "</th>\n";
			echo "<th>\n";
			echo "Meals\n";
			echo "</th>\n";
			echo "<th>\n";
			echo "Events\n";
			echo "</th>\n";
			echo "<th>\n";
			echo "Paid\n";
			echo "</th>\n";
			echo "<th>\n";
			echo "Status\n";
			echo "</th>\n";
			echo "<th>\n";
			echo "View/Edit\n";
			echo "</th>\n";
			echo "</tr>\n";
			echo "</thead>\n";
			echo "<tbody>\n";
	
			foreach ($entryList as $e) {
				
				$entryId = $e[0];
				
				$c = $e[8];
				$curEntry = new MeetEntry($e[2], $e[8], $meetId);
				$curEntry->load();
				
				$entryStatus = $curEntry->getStatus();
				
				$clubDetails = new Club();
				$clubDetails->load($c);
				$clubName = $clubDetails->getName();
				
				$curMem = $e[2];
				$curMemDet = new Member();
				$curMemDet->loadId($curMem);
				
				$memberStatus = $curMemDet->getMembershipStatusText($c, $meetStart);
				$memberName = $curMemDet->getFullname();
				$memberNumber = $curMemDet->getMSANumber();
				$memberDob = date('j/m/Y', strtotime($curMemDet->getDob()));
				$memberAgeGroup = $curMemDet->getAgeGroup($meetDet->getStartDate());
				$numMeals = $curEntry->getNumMeals();
				if (!isset($numMeals))
					$numMeals = "0";
				
				$eventArray = $curEntry->getEvents();
				$numEvents = $curEntry->getNumEntries();
				
				$meetFee = $meetDet->getMeetFee();
				$mealFee = $meetDet->getMealFee() * $numMeals;
                $massageFee = $meetDet->getMassageFee() * $curEntry->getMassages();
                $programFee = $meetDet->getProgramFee() * $curEntry->getPrograms();
				$eventFees = $curEntry->calcEventFees();
                $totalFee = $meetFee + $mealFee + $massageFee + $eventFees + $programFee;
				$entryPaid = $curEntry->getPaid();
				
				echo "<tr id=\"$entryId\">\n";
				echo "<td><a></a>$memberName</td>\n";
				echo "<td>$memberNumber</td>\n";
				echo "<td>$memberDob</td>\n";
				echo "<td>$memberAgeGroup</td>\n";
				echo "<td>$memberStatus</td>\n";
				echo "<td class=\"short\">$numMeals</td>\n";
				echo "<td class=\"short\">$numEvents</td>\n";
				echo "<td class=\"short\">";
				echo "\$" . number_format($entryPaid, 2);		
				echo "</td>\n";
				echo "<td>$entryStatus</td>";
				echo "<td>";
				
				// If this is a future event, allow adding an entry
				if (strtotime($meetDeadline . " 23:59:59") > time()) {
				
					echo "<a href=\"index.php?option=com_entrymanager&view=step2&editEntry=$entryId\">Edit</a>\n";
					echo " | ";
						
				}
				
				echo "<a href=\"#$entryId\" onclick=\"displayEntryList($entryId)\">Details</a>";
				echo "</td>\n";
				echo "</tr>\n";
	
				echo "<tr id=\"eventList_$entryId\" style=\"visibility: collapse; display: none;\">\n";
				echo "<td></td>\n";
				echo "<td colspan=\"9\"><p>";
				echo "<label>Entry Id: </label>\n";
				echo $entryId;
				echo "</p>\n";
				echo "<p>\n";
				echo "<h3>Event Entries:</h3> \n";
				echo "<table border=\"1\" style=\"margin-bottom: 1em;\">\n";
				echo "<tr>\n";
				echo "<th>No.</th>\n";
				echo "<th>Event:</th>\n";
				echo "<th>Type:</th>\n";
				echo "<th>Nominated Time:</th>\n";
				echo "<th>Status:</th>\n";
				echo "</tr>\n";
				echo "</thead>\n";
				echo "<tbody>\n";
				
				if (is_array($eventArray)) {
						
					$eventArray = array_reverse($eventArray);
						
					foreach ($eventArray as $v) {
							
						$eventId = $v->getEventId();
						$eventDetails = new MeetEvent();
						$eventDetails->load($eventId);
						$eventName = $eventDetails->getName();
						$eventProg = $eventDetails->getProgNumber();
						$eventShort = $eventDetails->getShortDetails();
						$eventType = $eventDetails->getType();
							
						$seedTime = sw_formatSecs($v->getSeedTime());
						$vStatus = $v->getStatusText();
						
						if ($eventProg != "") {
						
							echo "<tr>\n";
							echo "<td><div align=\"center\">$eventProg</div></td>\n";
							echo "<td>$eventShort\n";
							echo "</td>\n";
							echo "<td>$eventType</td>\n";
							echo "<td><div align=\"right\">$seedTime</div></td>\n";
							echo "<td>$vStatus</td>\n";
										
							echo "</tr>\n";
							
						}
									
						}
			
					}
								
				echo "</tbody>\n";
				echo "</table>\n";
				
				echo "</p>\n";
				
				echo "<p>\n";
				echo "<label>Fees Payable: </label>\n";
				echo "<table>\n";
				echo "<tr>\n";
				echo "<th style=\"padding-right: 5px; padding-left: 5px;\">\n";
				echo "Entry Fee:\n";
				echo "</th>\n";
				echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
				echo "\$" . number_format($meetFee, 2);
				echo "</td>\n";
				echo "</tr>\n";
				echo "<tr>\n";
				echo "<th style=\"padding-right: 5px; padding-left: 5px;\">\n";
				echo "Event Fees:\n";
				echo "</th>\n";
				echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
				echo "\$" . number_format($eventFees, 2);
				echo "</td>\n";
				echo "</tr>\n";
				echo "<tr>\n";
				echo "<th style=\"padding-right: 5px; padding-left: 5px\">\n";
				echo "Meal Fee:\n";
				echo "</th>\n";
				echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
				echo "\$" . number_format($mealFee, 2);
				echo "</td>\n";
				echo "</tr>\n";

                if ($meetDet->getMassageFee() > 0) {

                    echo "<tr>\n";
                    echo "<th style=\"padding-right: 5px; padding-left: 5px\">\n";
                    echo "Massage Fee:\n";
                    echo "</th>\n";
                    echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
                    echo "\$" . number_format($massageFee, 2);
                    echo "</td>\n";
                    echo "</tr>\n";

                }

                if ($meetDet->getProgramFee() > 0) {

                    echo "<tr>\n";
                    echo "<th style=\"padding-right: 5px; padding-left: 5px\">\n";
                    echo "Programme Fee:\n";
                    echo "</th>\n";
                    echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
                    echo "\$" . number_format($programFee, 2);
                    echo "</td>\n";
                    echo "</tr>\n";

                }


				echo "<tr>\n";
				echo "<th style=\"padding-right: 5px; padding-left: 5px;\">Total Cost:</th>\n";
				echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
				echo "\$" . number_format($totalFee, 2);
				echo "</td>\n";
				echo "</tr>\n";
				
				// Paid so far block
				echo "<tr>\n";
				echo "<th style=\"padding-right: 5px; padding-left: 5px;\">Paid:</th>\n";
				echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
				echo "\$" . number_format($entryPaid, 2);
				echo "</td>\n";
				echo "</tr>\n";
				
				
				echo "</table>\n";
				echo "</p>\n";

                echo "<form method=\"post\">\n";
							
				echo "<p style=\"margin: 1em 0 1em 0;\">\n";

                if (strtotime($meetDeadline . " 23:59:59") > time()) {

                    if ($entryStatus != 11) {

                        echo "<label>Cancel Entry: </label>\n";
                        echo "<input type=\"checkbox\" name=\"cancelEntry[]\" value=\"$entryId\" />\n";


                    } else {

                        //echo "<label>Restore Entry: </label>\n";
                        //echo "<input type=\"checkbox\" name=\"restoreEntry[]\" value=\"$entryId\" />\n";

                    }

                }

				echo "</p>\n";
				
				echo "<p style=\"margin: 1em 0 1em 0;\">\n";
				echo "<input type=\"submit\" name=\"emClubUpdate\" value=\"Update\" />\n";
				echo "</p>\n";
				
				echo "</form>\n";
				
			}
			
			echo "</tbody>\n";
			echo "</table>\n";
			
		}

		// If this is a future event, allow adding an entry
		if (strtotime($meetDeadline . " 23:59:59") > time()) {
		
			echo "<p><a href=\"index.php?option=com_entrymanager&view=step1c\">Add an Individual Entry</a></p>\n";
			
		}
		
		// Get relay entries if they exist
		
		echo "<h4>Relay Entries:</h4>\n";
			
		// Get a list of relay teams
		$relayTeams = $GLOBALS['db']->getAll("SELECT a.id FROM meet_entries_relays as a,
				meet_events as b
				WHERE a.meet_id = ? AND a.club_id = ?
				AND a.meetevent_id = b.id
				ORDER BY b.prognumber, b.progsuffix", array($meetId, $clubId));
		db_checkerrors($relayTeams);
			
		if (count($relayTeams) > 0) {
				
			echo "<table>\n";
			echo "<thead>\n";
			echo "<tr>\n";
			echo "<th>Event</th>\n";
			echo "<th>Age Group</th>\n";
			echo "<th>Letter</th>\n";
			echo "<th>Members</th>\n";
			echo "<th>Seed Time</th>\n";
			echo "<th>Control</th>\n";
			echo "</tr>\n";
			echo "</thead>\n";
			echo "<tbody>\n";
			
			foreach ($relayTeams as $r) {
				
				$rTeam = new RelayEntry();
				$rTeam->load($r[0]);
				
				echo "<tr>\n";
				
				echo "<td class=\"short\">\n";
				echo $rTeam->getProgNum();
				echo "</td>\n";
				
				echo "<td>\n";
				echo $rTeam->getAgeGroupText();
				echo "</td>\n";
				
				echo "<td class=\"short\">\n";
				echo $rTeam->getLetter();
				echo "</td>\n";
				
				echo "<td>\n";
				echo $rTeam->getMemberList();
				
				echo "</td>\n";
				
				echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
				echo $rTeam->getSeedTime();
				echo "</td>\n";
				
				echo "<td>\n";
				echo "<a href=\"/entry-manager-new/club-entries?deleteRelay=$r[0]\">\n";
				echo "Delete\n";
				echo "</a>\n";
				echo "</td>\n";
				
				echo "</tr>\n";
					
			}
			
			echo "</tbody>\n";
			echo "</table>\n";
				
		} else {
			
			echo "<p>None relay entries found!</p>\n";
				
		}

		// If this is a future event, allow adding an entry
        $eventsOpen = $meetDet->getOpenEvents();

		if ($eventsOpen > 0) {
		
			echo "<p><a href=\"index.php?option=com_entrymanager&view=createrelay&meet=$meetId&club=$clubId\">Add a Relay Team</a></p>\n";
			
		}
		
		// If this is a future event, allow adding an entry
		if (strtotime($meetDeadline . " 23:59:59") > time()) {
		
			echo "<p><a href=\"index.php?option=com_entrymanager&view=entrytool\">Import Team Manager Entries</a></p>\n";
				
		}
		
	}
		
}
	

	
	
echo "</form>\n";





?>