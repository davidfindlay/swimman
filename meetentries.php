<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Club.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");
require_once("includes/classes/MeetEntry.php");
require_once("includes/classes/MeetEntryEvent.php");
checkLogin();

if (isset($_GET['meet'])) {
	
	$meetId = mysql_real_escape_string($_GET['meet']);
	
}

// Default entry type
$entryType = 1;

if (isset($_GET['type'])) {
	
	if ($_GET['type'] == "relay") {
		
		$entryType = 2;
		
	}
	
}

if (isset($_GET['delete'])) {
	
	$deleteId = mysql_real_escape_string($_GET['delete']);
	
	$emDetails = $GLOBALS['db']->getRow("SELECT member_id, club_id, meet_id FROM meet_entries 
			WHERE id = '$deleteId';");
	db_checkerrors($emDetails);
	
	$deleteEntry = new MeetEntry($emDetails[0], $emDetails[1], $emDetails[2]);
	$deleteEntry->load();
	$deleteEntry->delete();
	
	addlog("Entry Manager", "Admin deleted entry $deleteId");
	
}

// Check array of entries to be deleted
if (isset($_POST['entrySelectSubmit'])) {
	
	if (isset($_POST['entrySelect'])) {
		
		foreach ($_POST['entrySelect'] as $e) {
			
			$deleteId = mysql_real_escape_string($e);
			
			$emDetails = $GLOBALS['db']->getRow("SELECT member_id, club_id, meet_id FROM meet_entries
					WHERE id = '$deleteId';");
			db_checkerrors($emDetails);
			
			$deleteEntry = new MeetEntry($emDetails[0], $emDetails[1], $emDetails[2]);
			$deleteEntry->load();
			$deleteEntry->delete();
			
			addlog("Entry Manager", "Admin deleted entry $deleteId");
			
		}
		
	}
	
}

$timeFrame = "current";

if (isset($_POST['filter'])) {
	
	$timeFrame = mysql_real_escape_string($_POST['filter']);
	
}

htmlHeaders("Swimming Management System - Meet Entry List");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Meet Entries</h1>\n";

echo "<h2>Search and Filter</h2>\n";
echo "<form method=\"get\" action=\"meetentries.php\">\n";
echo "<p>\n";
echo "<label>Event Filter: </label>\n";
echo "<input type=\"radio\" name=\"filter\" value=\"past\" />Past \n";
echo "<input type=\"radio\" name=\"filter\" value=\"current\" checked=\"yes\" />Current \n";
echo "<input type=\"radio\" name=\"filter\" value=\"future\" />Future \n";
echo "<input type=\"radio\" name=\"filter\" value=\"all\" />All \n";
echo "<p />\n";

// Get List of swimmers this club captain has access to

// Meet Filter Selector
echo "<p>\n";
echo "<label>Meet: </label>\n";
echo "<select name=\"meet\">\n";
echo "<option value=\"none\"></option>\n";

// Get list of meets
if ($timeFrame == "current") {
	
	$meets = $GLOBALS['db']->getAll("SELECT * FROM meet 
		WHERE startdate > DATE_SUB(CURDATE(), INTERVAL 1 MONTH)  
		AND startdate < DATE_ADD(CURDATE(), INTERVAL 3 MONTH) 
		 ORDER BY startdate DESC;");
	
} elseif ($timeFrame == "future") {

	$meets = $GLOBALS['db']->getAll("SELECT * FROM meet
		WHERE startdate > CURDATE()
		 ORDER BY startdate ASC;");
	
} elseif ($timeFrame == "past") {
	
	$meets = $GLOBALS['db']->getAll("SELECT * FROM meet
		WHERE startdate < CURDATE()
		 ORDER BY startdate DESC;");
	
} else {
	
	$meets = $GLOBALS['db']->getAll("SELECT * FROM meet
		 ORDER BY startdate DESC;");
	
}

db_checkerrors($meets);

foreach ($meets as $m) {
	
	$mId = $m[0];
	$mName = $m[1];
	$mDate = $m[2];

	if (!isset($meetId)) {
		
		$meetId = '';
		
	}
	
	if ($meetId == $mId) {
		
		echo "<option value=\"$mId\" selected=\"selected\">$mDate - $mName</option>\n";
		
	} else {
		
		echo "<option value=\"$mId\">$mDate - $mName</option>\n";
		
	}
	
}


echo "</select></p>\n";

echo "<p>\n";
echo "<label>Club: </label>\n";
echo "<select name=\"club\">\n";

echo "</select>\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Entry Type:</label>\n";
echo "<input type=\"radio\" name=\"type\" value=\"individual\" ";

// Handle preselection
if ($entryType == "1") {
	
	echo " checked=\"checked\" ";
	
}

echo "/> Individual \n";

echo "<input type=\"radio\" name=\"type\" value=\"relay\"";

// Handle preselection
if ($entryType == "2") {

	echo " checked=\"checked\" ";

}

echo "/> Relay \n";
echo "</p>\n";

echo "<input type=\"submit\" name=\"refreshSubmit\" value=\"Refresh\" />\n";

echo "</form>\n";



echo "<form method=\"post\" action=\"meetentries.php?meet=$meetId&filter=$timeFrame\">\n";

// Individual entry mode
if ($entryType == 1) {
	
	// Get list of entries
	$st = microtime();
	
	if ($meetId != '') {
	
		$entryList = $GLOBALS['db']->getAll("SELECT * FROM meet_entries WHERE meet_id = ?
			ORDER BY club_id;",
				array($meetId));
	
	} else {
	
		if ($timeFrame == "current") {
	
			$entryList = $GLOBALS['db']->getAll("SELECT * FROM meet_entries WHERE meet_id IN (
				SELECT id FROM meet
				WHERE startdate > DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
				AND startdate < DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
		 		ORDER BY startdate DESC);");
	
		} elseif ($timeFrame == "future") {
	
			$entryList = $GLOBALS['db']->getAll("SELECT * FROM meet_entries WHERE meet_id IN (
				SELECT id FROM meet
				WHERE startdate > CURDATE()
		 		ORDER BY startdate ASC);");
	
		} elseif ($timeFrame == "past") {
	
			$entryList = $GLOBALS['db']->getAll("SELECT * FROM meet_entries WHERE meet_id IN (
				SELECT id FROM meet
				WHERE startdate < CURDATE()
				ORDER BY startdate DESC);");
	
		} else {
	
			$entryList = $GLOBALS['db']->getAll("SELECT * FROM meet_entries WHERE meet_id IN (
				SELECT id FROM meet
		 		ORDER BY startdate DESC);");
	
		}
	
	}
	
	$et = microtime();
	$d = $et - $st;
	addlog("Meet Entries Query", "Query run time $d.");
	
	db_checkerrors($entryList);
	
	echo "<table width=\"100%\" class=\"list\">\n";
	
	echo "<thead class=\"list\">\n";
	
	echo "<tr>\n";
	echo "<th>\n";
	
	echo "</th>\n";
	echo "<th>\n";
	echo "Meet Name:";
	echo "</th>\n";
	echo "<th>\n";
	echo "Club:";
	echo "</th>\n";
	echo "<th>\n";
	echo "Member:";
	echo "</th>\n";
	echo "<th>\n";
	echo "Age Group:";
	echo "</th>\n";
	echo "<th>\n";
	echo "Events:";
	echo "</th>\n";
	echo "<th>\n";
	echo "Status:";
	echo "</th>\n";
	echo "<th>\n";
	echo "";
	echo "</th>\n";
	echo "</tr>\n";
	
	echo "</thead>\n";
	
	echo "<tbody class=\"list\">\n";
	
	foreach ($entryList as $l) {
		
		$lId = $l[0];
		$lMeet = $l[1];
		$lMember = $l[2];
		$lClub = $l[8];
		
		$curMeet = new Meet();
		$curMeet->loadMeet($lMeet);
		
		$curEntry = new MeetEntry();
		$curEntry->loadId($lId);
		
		$curMember = new Member();
		$curMember->loadId($lMember);
		
		$curClub = new Club();
		$curClub->load($lClub);
		
		echo "<tr class=\"list\">\n";
		echo "<td>\n";
		
		// Selection option
		echo "<input type=\"checkbox\" name=\"entrySelect[]\" value=\"$lId\" />\n";
		
		echo "</td>\n";
		echo "<td>\n";
		echo $curMeet->getName();
		echo "</td>\n";
		echo "<td>\n";
		echo $curClub->getCode();
		echo "</td>\n";
		echo "<td>\n";
		echo $curMember->getFullname();
		echo "(" . $curMember->getMSANumber() . ")";
		echo "</td>\n";
		echo "<td>\n";
		echo $curMember->getAgeGroup();
		echo "</td>\n";
		echo "<td>\n";
		
		// Get number of entries
		echo $curEntry->getNumEntries();
		
		echo "</td>\n";
		echo "<td>\n";
		
		// Get status
		echo $curEntry->getStatus();
		
		echo "</td>\n";
		echo "<td>\n";
		echo "<a href=\"meetentry.php?entry=$lId\">View/Edit</a>\n";
		echo " | \n";
		echo "<a href=\"meetentries.php?meet=$meetId&filter=$timeFrame&delete=$lId\">Delete</a>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		
	}
	
	echo "</tbody>\n";
	echo "</table>\n";
	
	echo "<select name=\"entrySelectOption\">\n";
	echo "<option value=\"delete\">Delete </a>\n";
	echo "</select>\n";
	
	echo "<input type=\"submit\" name=\"entrySelectSubmit\" value=\"Submit\" />\n";

}

// Relay Mode 

if ($entryType == 2) {
	
	echo "<table width=\"100%\" class=\"list\">\n";
	
	echo "<thead class=\"list\">\n";
	
	echo "<tr>\n";
	echo "<th>\n";
	
	echo "</th>\n";
	echo "<th>\n";
	echo "Meet Name:";
	echo "</th>\n";
	echo "<th>\n";
	echo "Club:";
	echo "</th>\n";
	echo "<th>\n";
	echo "Event:\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Age Group:";
	echo "</th>\n";
	echo "<th>\n";
	echo "Letter:";
	echo "</th>\n";
	echo "<th>\n";
	echo "Swimmers:";
	echo "</th>\n";
	echo "<th>\n";
	echo "";
	echo "</th>\n";
	echo "</tr>\n";
	
	echo "</thead>\n";
	
	echo "<tbody class=\"list\">\n";
	
	// Get relays
	$relayEntries = $GLOBALS['db']->getAll("SELECT a.id, b.meetname, c.code, d.prognumber, 
			d.progsuffix, e.groupname, a.letter
			FROM meet_entries_relays as a, meet as b, clubs as c, meet_events as d, age_groups as e
			WHERE b.id = a.meet_id AND c.id = a.club_id AND a.meetevent_id = d.id 
			AND e.set = 1 AND a.agegroup = e.id
			AND a.meet_id = ?;", array($meetId));
	db_checkerrors($relayEntries);
	
	foreach($relayEntries as $r) {
		
		$relayId = $r[0];
		$meetName = $r[1];
		$clubCode = $r[2];
		$progNumber = $r[3];
		$progSuffix = $r[4];
		$ageGroup = $r[5];
		$letter = $r[6];
	
		echo "<tr class=\"list\">\n";

		echo "<td>\n";
		echo "<input type=\"checkbox\" name=\"relaySelect\" value=\"$relayId\" />";
		echo "</td>\n";
		
		echo "<td>\n";
		echo $meetName;
		echo "</td>\n";
		
		echo "<td>\n";
		echo $clubCode;
		echo "</td>\n";
		
		echo "<td>\n";
		echo $progNumber . $progSuffix;
		echo "</td>\n";
		
		echo "<td>\n";
		echo $ageGroup;
		echo "</td>\n";
		
		echo "<td>\n";
		echo $letter;
		echo "</td>\n";
		
		// Get the swimmers names if set
		$swimmers = $GLOBALS['db']->getAll("SELECT member_id 
				FROM meet_entries_relays_members WHERE relay_team = ? 
				ORDER BY leg ASC;", array($relayId));
		db_checkerrors($swimmers);
		
		echo "<td>\n";
		
		foreach ($swimmers as $s) {
				
			$sId = $s[0];
				
			$objMember = new Member();
			$objMember->loadId($sId);
				
			echo $objMember->getFullname() . "(" . $objMember->getGender() . $objMember->getAge() . ")<br />";
				
		}
		
		echo "</td>\n";
		
		echo "</tr>\n";
		
	}
	
	echo "</tbody>\n";
	echo "</table>\n";
	
}
	
	
echo "</form>\n";

echo "</div>\n"; // main div

htmlFooters();

?>
