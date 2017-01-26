<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");
require_once("includes/classes/MeetEntry.php");
require_once("includes/classes/MeetEntryEvent.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Club.php");
checkLogin();

// Identify if an entry has been opened
$entryId = mysql_real_escape_string($_GET['entry']);

// Submit entry
if (isset($_POST['updateEntry'])) {
	
	// Get the current entry status
	$entryStatus = mysql_real_escape_string($_POST['entryStatus']);

	$objEntry = new MeetEntry();
	$objEntry->loadId($entryId);

	// Set the new status
	$objEntry->setStatus($entryStatus);
	$objEntry->updateStatus();

	// Step through each entered event
	foreach ($_POST['enterEvent'] as $enteredId) {

		$seedTime = sw_timeToSecs($_POST["seedtime_$enteredId"]);
		$eventStatus = $_POST["entryEventStatus_$enteredId"];
		
		// Check if the event is already part of the entry
		$eventEntryId = $GLOBALS['db']->getRow("SELECT id FROM meet_events_entries 
				WHERE meet_entry_id = ? AND event_id = ?;", array($entryId, $enteredId));
		db_checkerrors($eventEntryId);
		
		if (count($eventEntryId) == 0) {
			
			
			
		}
		
		$objEntry->updateEvent($enteredId, $seedTime, $eventStatus);

	}

	//$objEntry->update();

}

// Editing
if (isset($_GET['entry'])) {
	
	$curEntry = new MeetEntry();
	$curEntry->loadId($entryId);
	
	$meetId = $curEntry->getMeetId();
	$memberId = $curEntry->getMemberId();
	$clubId = $curEntry->getClubId();

	$curMeet = new Meet();
	$curMeet->loadMeet($meetId);
	
	$curMember = new Member();
	$curMember->loadId($memberId);
	
	$curClub = new Club();
	$curClub->load($clubId);
	
	// Get Preset Values
	$psMemberName = $curMember->getFullname();
	$psClubCode = $curClub->getCode();
	$psClubName = $curClub->getName();
	$psMSANumber = $curMember->getMSANumber();
	$psMemberDob = $curMember->getDob();
	$psMemberGender = $curMember->getGender();
	
	$meetStartDate = $curMeet->getStartDate();

	$psAgeGroup = $curMember->getAgeGroup($meetStartDate);

} else {
	
	$entryId = '';
	
}



htmlHeaders("Swimming Management System - Enter a Meet");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Entry Details</h1>\n";

echo "<h3>Entrant Details</h3>\n";

echo "<form method=\"post\" class=\"form-horizontal\" action=\"meetentry.php?entry=$entryId\">\n";
echo "<input type=\"hidden\" name=\"entryId\" value=\"$entryId\" />\n";

echo "<h3>Member Details</h3>\n";
echo "<p>\n";
echo "<label class=\"control-label\">Name: </label>$psMemberName<br />\n";

echo "<label class=\"control-label\">Club: </label>$psClubName($psClubCode)<br />\n";
echo "\n";

echo "<label class=\"control-label\">MSA Number: </label>$psMSANumber<br />\n";

echo "<label class=\"control-label\">Date of Birth: </label>$psMemberDob<br />\n";

echo "<label class=\"control-label\">Gender: </label>\n";

if ($psMemberGender == "M") {
	
	echo "Male";
	
} else {
	
	echo "Female";
	
}

echo "<br />\n";

echo "<label class=\"control-label\">Age Group: </label>$psAgeGroup<br />\n";
echo "<label class=\"control-label\">Membership Status: </label>\n";

if($curMember->getMembershipStatus($clubId)) {
	
	echo "Financial";	
	
} else {
	
	echo "<span class=\"error\">Unfinancial</span>\n";
	$unfinancial = true;
	
}


// Get Memberstatus Label
// $memberStatusLabel = $GLOBALS['db']->getOne("SELECT membership_statuses.desc FROM membership_statuses WHERE id = '$memberStatusId';");
// db_checkerrors($memberStatusLabel);

// if (time() > strtotime($memberEndDate)) {
	
// 	echo "<span class=\"error\">Unfinancial</span>\n";
	
// } else {
	
// 	echo "$memberStatusLabel\n";
	
// }

echo "<br />\n";


echo "<label class=\"control-label\">Membership End Date: </label>\n";

echo $curMember->getMembershipEnd($clubId);
	
echo "</p>\n";

echo "<h3>Meet Details:</h3>\n";

echo "<p>\n";
echo "<label class=\"control-label\">Meet Name: </label>\n";
echo $curMeet->getName();
echo "<br />\n";
echo "<label class=\"control-label\">Meet Date: </label>\n";
echo $curMeet->getStartDate();

if ($curMeet->getEndDate() != "0000-00-00") {
	
	echo " - ";
	echo $curMeet->getEndDate();
	
}

echo "</p>\n";

// Get Meet events
$eventsList = $GLOBALS['db']->getAll("SELECT * FROM meet_events WHERE meet_id = '$meetId' ORDER BY prognumber, progsuffix;");
db_checkerrors($eventsList);

// Does user already have entries in this event?
$entryDetails = $GLOBALS['db']->getRow("SELECT * FROM meet_entries WHERE meet_id = '$meetId' AND member_id = '$memberId';");
db_checkerrors($entryDetails);

if (isset($entryDetails)) {

	$entryId = $entryDetails[0];

	$curEntry = new MeetEntry();
	$curEntry->loadId($entryId);
	$entryStatus = $curEntry->getStatus();

}

echo "<h3>Entry Status</h3>\n";

echo "<p>\n";
echo "<label class=\"control-label\">Entry Id:</label>\n";
echo $entryId;
echo "</p>\n";

echo "<p>\n";
echo "<label class=\"control-label\">Status:</label>\n";

$entryStatuses = $GLOBALS['db']->getAll("SELECT * FROM meet_entry_status_codes;");
db_checkerrors($entryStatuses);

echo "<select name=\"entryStatus\">\n";

foreach ($entryStatuses as $s) {
	
	$sId = $s[0];
	$sLabel = $s[1];
	
	echo "<option value=\"$sId\" ";
	
	if ($sLabel == $entryStatus) {
		
		echo "selected=\"selected\"";
		
	}
	
	echo ">$sLabel</option>\n";
	
}

echo "</select>\n";

echo "</p>\n";

// Display entry rules
$maxEvents = $curMeet->getMax();

echo "<h4>Meet Rules:</h4>\n";

echo "<p>\n";

if ($maxEvents == 0) {
	
	echo "There are no restrictions on the total number of events you may swim.\n";	
	
} else {
	
	echo "You may swim up to $maxEvents individual events total.\n";
	
}

echo "<br />\n";

echo $curMeet->getRulesText();

echo "</p>\n";

echo "<table width=\"100%\">\n";

echo "<tr>\n";
echo "<th>Event: </th>\n";
echo "<th>Type: </th>\n";
echo "<th>Distance: </th>\n";
echo "<th>Discipline: </th>\n";
echo "<th>Status: </th>\n";
echo "<th>Enter: </th>\n";
echo "<th>Seed Time: </th>\n";
echo "</tr>\n";

foreach ($eventsList as $e) {
	
	$eId = $e[0];
	$eTypeId = $e[2];
	$eTypeData = $GLOBALS['db']->getRow("SELECT * FROM event_types WHERE id = '$eTypeId';");
	db_checkerrors($eTypeData);
	$eType = $eTypeData[1];
	$eDistId = $e[5];
	$eLegs = $e[4];
	$eDist = $GLOBALS['db']->getOne("SELECT distance FROM event_distances WHERE id ='$eDistId';");
	db_checkerrors($eDist);
	$eDiscId = $e[3];
	$eDisc = $GLOBALS['db']->getOne("SELECT discipline FROM event_disciplines WHERE id = '$eDiscId';");
	db_checkerrors($eDisc);
	$eNum = $e[7] . $e[8];
	$psSeedTime = "";
	
	if (isset($curEntry)) {
	
		$entered = false;
	
		foreach ($curEntry->getEvents() as $event) {
			
			if ($event->getEventId() == $eId) {
	
				$entered = true;
				$psSeedTime = sw_formatSecs($event->getSeedTime());
				$eventEntryStatus = $event->getStatus();
	
			}
				
		}
	
	}
	
	echo "<tr>\n";
	
	echo "<td>\n";
	echo "$eNum\n";
	echo "</td>\n";
	
	echo "<td>\n";
	echo "$eType\n";
	echo "</td>\n";
	
	echo "<td>\n";
	if ($eLegs > 1) {
		
		echo $eLegs . "x";
		
	}
	echo "$eDist\n";
	echo "</td>\n";
		
	echo "<td>\n";
	echo "$eDisc\n";
	echo "</td>\n";
	
	echo "<td>\n";
	
	if ($entered == true) {
	
		$entryStatuses = $GLOBALS['db']->getAll("SELECT * FROM meet_entry_status_codes;");
		db_checkerrors($entryStatuses);
		
		echo "<select name=\"entryEventStatus_$eId\">\n";
		
		foreach ($entryStatuses as $s) {
		
			$sId = $s[0];
			$sLabel = $s[1];
		
			echo "<option value=\"$sId\" ";
		
			if ($sId == $eventEntryStatus) {
		
				echo "selected=\"selected\"";
		
			}
		
			echo ">$sLabel</option>\n";
		
		}
		
		echo "</select>\n";
		
	}
	
	echo "</td>\n";
	
	echo "<td>\n";
	
	if ($entered == true) {
		
		echo "<input type=\"checkbox\" name=\"enterEvent[]\" value=\"$eId\" checked=\"checked\" />\n";
		
	} else {

		echo "<input type=\"checkbox\" name=\"enterEvent[]\" value=\"$eId\" />\n";
		
	}

	if ($eTypeData[2] == 0) {
		
		echo "Enter\n";
		
	} elseif ($eTypeData[2] == 1) {
		
		echo "Nominate\n";
		
	}
	
	echo "</td>\n";
	
	echo "<td>\n";	
	
	echo "<input type=\"text\" name=\"seedtime_$eId\" size=\"10\" value=\"";
	if ($psSeedTime != "") {
	
		echo $psSeedTime;
		
	}
	echo "\" />\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	
}

echo "</table>\n";

echo "<p>\n";

// Only ask meal related questions if there are meals on offer
$mealsPrice = 0;
if ($mealsPrice > 0) {

	echo "<label>Meals Included: </label>\n ";
	echo $mealsIncluded;
	echo "<br />\n";

	echo "<label>Meals Required: </label>\n";
	echo "<input type=\"text\" name=\"numMealsAddl\" value=\"$psMealsAddl\" size=\"3\" /><br />\n";

}
	
echo "<label>Medical Certificate: </label>\n";

if (!isset($psMedCert)) {
	
	$psMedCert = 0;
	
}

if ($psMedCert == 1) {
	
	echo "<input type=\"checkbox\" name=\"medcert\" checked=\"checked\" />";
	
} else {

	echo "<input type=\"checkbox\" name=\"medcert\" />";
	
} 

echo "<br />\n";

echo "<label>Notes: </label><br />\n";
echo "<textarea name=\"notes\" cols=\"80\" rows=\"5\">\n";
if (isset($psNotes)) {
	
	echo $psNotes;
	
}
echo "</textarea>\n";
echo "</p>\n";


echo "<input type=\"submit\" name=\"submitRefresh\" value=\"Refresh\" />\n";
echo "<input type=\"submit\" name=\"updateEntry\" value=\"Update\" />\n";

echo "<h3>Entry Cost</h3>\n";

// Get list of payments for this entry
$totalFee = $curEntry->calcCost();
$meetFee = $curMeet->getMeetFee();
$eventFees = $curEntry->calcEventFees();
$mealFee = $curEntry->getNumMeals() * $curMeet->getMealFee();
$massageFee = $curEntry->getMassages() * $curMeet->getMassageFee();
$programFee = $curEntry->getPrograms() * $curMeet->getProgramFee();
$amountPaid = $curEntry->getPaid();
$amountToPay = $totalFee - $amountPaid;


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

if ($curMeet->getMassageFee() < 0) {

    echo "<tr>\n";
    echo "<th style=\"padding-right: 5px; padding-left: 5px\">\n";
    echo "Massage Fee:\n";
    echo "</th>\n";
    echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
    echo "\$" . number_format($massageFee, 2);
    echo "</td>\n";
    echo "</tr>\n";

}

if ($curMeet->getProgramFee() < 0) {

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
echo "<tr>\n";
echo "<th style=\"padding-right: 5px; padding-left: 5px;\">Paid:</th>\n";
echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
echo "<strong>\$" . number_format($amountPaid, 2) . "</strong>";
echo "</td>\n";
echo "<tr>\n";
echo "<th style=\"padding-right: 5px; padding-left: 5px;\">Amount Due:</th>\n";
echo "<td style=\"text-align: right; padding-left: 5px;\">\n";
echo "<strong>\$" . number_format($amountToPay, 2) . "</strong>";
echo "</td>\n";
echo "</tr>\n";

// Check if the user has paid


echo "</table>\n";
echo "</p>\n";

echo "</form>\n";


echo "</div>\n"; // main div

htmlFooters();

?>
