<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
checkLogin();

addlog("Access", "Accessed meetbuilder.php");

if (isset($_POST['meetCreate']) OR isset($_POST['meetUpdate'])) {

	$newMeetName = mysql_real_escape_string($_POST['meetname']);
	$newMeetStartDate = mysql_real_escape_string($_POST['meetstartdate']);
	$newMeetEndDate = mysql_real_escape_string($_POST['meetenddate']);
	
	if (isset($_POST['meetCreate']) && $_POST['meetdeadline'] == '') {
	
		$deadDt = new DateTime($newMeetStartDate);
		$deadDt->sub(new DateInterval('P3W'));
		$newDeadline = $deadDt->format('Y-m-d');
		
	} else {
	
		$newDeadline = mysql_real_escape_string($_POST['meetdeadline']);
		
	}
	
	$newContactName = mysql_real_escape_string($_POST['meetcontactname']);
	$newContactEmail = mysql_real_escape_string($_POST['meetcontactemail']);
	$newContactPhone = mysql_real_escape_string($_POST['meetcontactphone']);
	$newMeetFee = mysql_real_escape_string($_POST['meetfee']);
	$newMeetMealFee = mysql_real_escape_string($_POST['meetmealfee']);
    $newMeetMealName = $_POST['mealname'];
	$newMeetLocation = mysql_real_escape_string($_POST['meetlocation']);
	$newMaxEvents = mysql_real_escape_string($_POST['meetmaxevents']);
	
}

if (isset($_POST['meetCreate'])) {
	
	$newContactEmailId = sw_addEmail($newContactEmail, 10);
	$newContactPhoneId = sw_addPhone($newContactPhone, 10);
		
	$insert1 = $GLOBALS['db']->query("INSERT INTO meet (meetname, startdate, enddate, deadline, contactname, contactemail, contactphone, meetfee, mealfee, location, maxevents) VALUES ('$newMeetName', '$newMeetStartDate', '$newMeetEndDate', '$newDeadline', '$newContactName', '$newContactEmailId', '$newContactPhoneId', '$newMeetFee', '$newMeetMealFee', '$newMeetLocation', '$newMaxEvents');");
	db_checkerrors($insert1);
	
	$meetId = mysql_insert_id();
	
	header("Location: meetbuilder.php?meetId=$meetId");
	
}

if (isset($_POST['meetUpdate'])) {
	
	$editMeetId = mysql_real_escape_string($_POST['meetId']);
	$newContactEmailId = sw_addEmail($newContactEmail, 10);
	$newContactPhoneId = sw_addPhone($newContactPhone, 10);
	
	$update1 = $GLOBALS['db']->query("UPDATE meet SET meetname = '$newMeetName', startdate = '$newMeetStartDate', enddate = '$newMeetEndDate', deadline = '$newDeadline', contactname = '$newContactName', contactemail = '$newContactEmailId', contactphone = '$newContactPhoneId', meetfee = '$newMeetFee', mealfee = '$newMeetMealFee', location = '$newMeetLocation', maxevents = '$newMaxEvents', mealname = '$newMeetMealName' WHERE id = '$editMeetId';");
	db_checkerrors($update1);
	
	header("Location: meetbuilder.php?meetId=$editMeetId");
	
}

if (isset($_POST['addEvent'])) {
	
	$eventMeetId = mysql_real_escape_string($_POST['eventMeetId']);
	
	$eventProgNumber = mysql_real_escape_string($_POST['eventNumber']);
	$eventProgSuffix = mysql_real_escape_string($_POST['eventNumberSuffix']);
	
	if ($eventProgSuffix == 'na') {
		
		$eventProgSuffix = '';
		
	}
	
	$eventType = mysql_real_escape_string($_POST['eventType']);
	$eventDiscipline = mysql_real_escape_string($_POST['eventDiscipline']);
	$eventLegs = mysql_real_escape_string($_POST['eventLegs']);
	$eventDistance = mysql_real_escape_string($_POST['eventDistance']);
	$eventName = mysql_real_escape_string($_POST['eventName']);
	$eventFee = mysql_real_escape_string($_POST['eventFee']);
	
	$insertEvent = $GLOBALS['db']->query("INSERT INTO meet_events (meet_id, type, discipline, legs, distance, eventname, prognumber, progsuffix, eventfee) VALUES ('$eventMeetId', '$eventType', '$eventDiscipline', '$eventLegs', '$eventDistance', '$eventName', '$eventProgNumber', '$eventProgSuffix', '$eventFee');");
	db_checkerrors($insertEvent);
	
	header("Location: meetbuilder.php?meetId=$eventMeetId");
	
}

if (isset($_POST['delEventSubmit'])) {
	
	$eventMeetId = mysql_real_escape_string($_POST['eventMeetId']);
	
	if (isset($_POST['delEvent'])) {
	
		foreach ($_POST['delEvent'] as $d) {
			
			$eventId = mysql_real_escape_string($d);
				
			$delete = $GLOBALS['db']->query("DELETE FROM meet_events WHERE id = '$eventId';");
			db_checkerrors($delete);
			
			// Check if event was in any event groups
			$deleteEventGroupItems = $GLOBALS['db']->query("DELETE FROM meet_events_groups_items WHERE event_id = '$eventId';");
			db_checkerrors($deleteEventGroupItems);
				
		}
		
	}
	
	// Check for linking of events into groups
	$listEventIds = $GLOBALS['db']->getAll("SELECT id FROM meet_events WHERE meet_id = '$eventMeetId';");
	db_checkerrors($listEventIds);
	
	foreach ($listEventIds as $i) {
		
		$iNum = $i[0];
		
		if ($_POST["addtogroup_$iNum"] != '0') {
			
			$groupToAddTo = mysql_real_escape_string($_POST["addtogroup_$iNum"]);
			
			$insert = $GLOBALS['db']->query("INSERT INTO meet_events_groups_items (group_id, event_id) VALUES ('$groupToAddTo', '$iNum');");
			db_checkerrors($insert);
			
		}
				
	}
	
	// Check for removal of events from groups
	if (isset($_POST['deleteFromGroup'])) {
		
		foreach ($_POST['deleteFromGroup'] as $f) {
			
			list($eId, $gId) = explode(' ', $f); 
			$delete = $GLOBALS['db']->query("DELETE FROM meet_events_groups_items WHERE event_id = '$eId' AND group_id = '$gId';");
			db_checkerrors($delete);
			
		}
		
	}
		
		
	header("Location: meetbuilder.php?meetId=$eventMeetId");
	
}

if (isset($_POST['updateEventGroups'])) {
	
	$eventMeetId = mysql_real_escape_string($_POST['eventMeetId']);
	
	// Get list of groups
	$eventGroups = $GLOBALS['db']->getAll("SELECT * FROM meet_events_groups WHERE meet_id = '$eventMeetId';");
	db_checkerrors($eventGroups);
	
	foreach ($eventGroups as $v) {
	
		// Update changes to group maxes
		$gId = $v[0];
		$groupMax = mysql_real_escape_string($_POST["groupmax_$gId"]);
		$update = $GLOBALS['db']->query("UPDATE meet_events_groups SET max_choices = '$groupMax' WHERE id = '$gId';");
		db_checkerrors($update);
	
	}
	
	if (isset($_POST['deleteGroup'])) {
		
		foreach ($_POST['deleteGroup'] as $d) {
			
			$delete1 = $GLOBALS['db']->query("DELETE FROM meet_events_groups_items WHERE group_id = '$d';");
			db_checkerrors($delete1);
			
			$delete2 = $GLOBALS['db']->query("DELETE FROM meet_events_groups WHERE id = '$d';");
			db_checkerrors($delete2);
			
		}
		
	}
	
	if (isset($_POST['newGroupName'])) {
		
		$newEventGroupName = mysql_real_escape_string($_POST['newGroupName']);
		$newEventMax = mysql_real_escape_string($_POST['newGroupMax']);
		$newGroupRuleId = mysql_real_escape_string($_POST['newGroupRule']);
		$newGroupRuleText = mysql_real_escape_string($_POST['newRuleText']);
		
		if ($newEventGroupName != '') {
		
			$insert = $GLOBALS['db']->query("INSERT INTO meet_events_groups (meet_id, max_choices, groupname) VALUES ('$eventMeetId', '$newEventMax', '$newEventGroupName');");
			db_checkerrors($insert);
			
			$groupId = mysql_insert_id();
			
			if ($newGroupRuleText != '') {
			
				// Create new rule text
				$insert1 = $GLOBALS['db']->query("INSERT INTO meet_rules (rule, priority) VALUES ('$newGroupRuleText', 1);");
				db_checkerrors($insert1);
				
				$ruleId = mysql_insert_id();
				
				$insert2 = $GLOBALS['db']->query("INSERT INTO meet_rules_groups (rule_id, meet_events_groups_id) VALUES ('$ruleId', '$groupId');");
				db_checkerrors($insert2);
			
			} else {
			
				if (isset($newGroupRuleId)) {
				
					$insert1 = $GLOBALS['db']->query("INSERT INTO meet_rules_groups (rule_id, meet_events_groups_id) VALUES ('$newGroupRuleId', '$groupId');");
					db_checkerrors($insert1);
				
				}
			
			}
			
		}
		
	}
	
	header("Location: meetbuilder.php?meetId=$eventMeetId");
}

if (isset($_GET['meetId'])) {
	
	// Retreive meet information
	$meetId = mysql_real_escape_string($_GET['meetId']);
	$psMeetInfo = $GLOBALS['db']->getRow("SELECT * FROM meet WHERE id = '$meetId';");
	db_checkerrors($psMeetInfo);

	$psMeetName = $psMeetInfo[1];
	$psMeetStartDate = $psMeetInfo[2];
	$psMeetEndDate = $psMeetInfo[3];
	$psMeetDeadline = $psMeetInfo[4];
	$psMeetContactName = $psMeetInfo[5];
	$psMeetContactEmailId = $psMeetInfo[6];
	$psMeetContactEmail = $GLOBALS['db']->getOne("SELECT address FROM emails WHERE id = '$psMeetContactEmailId';");
	db_checkerrors($psMeetContactEmail);	
	$psMeetContactPhoneId = $psMeetInfo[7];
	$psMeetContactPhone = $GLOBALS['db']->getOne("SELECT phonenumber FROM phones WHERE id = '$psMeetContactPhoneId';");
	db_checkerrors($psMeetContactPhone);
	$psMeetFee = $psMeetInfo[8];
	$psMeetMealFee = $psMeetInfo[9];
	$psMeetEventLimit = $psMeetInfo[12];
	$psMeetLocation = $psMeetInfo[10];
    $psMealName = $psMeetInfo[14];
	
} else {

	$psMeetName = '';
	$psMeetStartDate = '';
	$psMeetEndDate = '';
	$psMeetDeadline = '';
	$psMeetContactName = '';
	$psMeetContactEmail = '';
	$psMeetContactPhone = '';
	$psMeetFee = '';
	$psMeetMealFee = '';
    $psMealName = '';
	$psMeetEventLimit = '';
	$psMeetLocation = '';
	
}

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"DTD/xhtml1-frameset.dtd\">\n";
echo "<html>\n";
echo "<head>\n";
	
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style/screen.css\">\n";
echo "<script src=\"meetbuilder.js\"></script>\n";
	
echo "<title>Meet Builder</title>\n";
	
echo "</head>\n";
	
echo "<body>\n";

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Meet Builder</h1>\n";

echo "<div id=\"meetDetailsForm\">\n";
echo "<form method=\"post\">\n";

echo "<fieldset>\n";

echo "<label for=\"meetname\">Meet Name: </label>\n";
echo "<input type=\"text\" size=\"40\" name=\"meetname\" value=\"$psMeetName\" /><br />";

echo "<label for=\"meetstartdate\">Start Date: </label>\n";
echo "<input type=\"date\" size=\"15\" name=\"meetstartdate\" value=\"$psMeetStartDate\" /><br />";

echo "<label for=\"meetenddate\">End Date: </label>\n";
echo "<input type=\"date\" size=\"15\" name=\"meetenddate\" value=\"$psMeetEndDate\" /><br />";

echo "<label for=\"meetdeadline\">Deadline: </label>\n";
echo "<input type=\"date\" size=\"15\" name=\"meetdeadline\" value=\"$psMeetDeadline\" /><br />";

echo "<label for=\"meetcontactname\">Contact Name: </label>\n";
echo "<input type=\"text\" size=\"40\" name=\"meetcontactname\" value=\"$psMeetContactName\" /><br />";

echo "<label for=\"meetcontactemail\">Contact Email: </label>\n";
echo "<input type=\"text\" size=\"40\" name=\"meetcontactemail\" value=\"$psMeetContactEmail\" /><br />";

echo "<label for=\"meetcontactphone\">Contact Phone: </label>\n";
echo "<input type=\"text\" size=\"40\" name=\"meetcontactphone\" value=\"$psMeetContactPhone\"  /><br />";

echo "<label for=\"meetfee\">Meet Fee: </label>\n";
echo "<input type=\"text\" size=\"15\" name=\"meetfee\" value=\"$psMeetFee\" /><br />";

echo "<label for=\"meetmealfee\">Meal Fee: </label>\n";
echo "<input type=\"text\" size=\"15\" name=\"meetmealfee\" value=\"$psMeetMealFee\" /><br />";

echo "<label for=\"mealname\">Meal Name: </label>\n";
echo "<input type=\"text\" size=\"30\" name=\"mealname\" value=\"$psMealName\" /><br />";

echo "<label for=\"meetmaxevents\">Maximum Event Limit: </label>\n";
echo "<input type=\"text\" size=\"15\" name=\"meetmaxevents\" value=\"$psMeetEventLimit\" /><br />";

echo "<label for=\"meetlocation\">Location: </label>\n";
echo "<input type=\"text\" size=\"40\" name=\"meetlocation\" value=\"$psMeetLocation\" /><br />";

if (isset($meetId)) {
	
	echo "<input type=\"hidden\" name=\"meetId\" value=\"$meetId\" />\n";
	echo "<input type=\"submit\" name=\"meetUpdate\" value=\"Update Meet\" />\n";
	
} else {
	
	// If stage one of creating meet not yet completed, require this before continuing
	echo "<input type=\"submit\" name=\"meetCreate\" value=\"Create Meet\" />\n";
	
}

echo "</fieldset>\n";

echo "</form>\n";
echo "</div>\n";


if (isset($meetId)) {

	// Create Meet Events
	$psEventNumber = '1';
	$psEventName = '';
	$psEventFee = '';
	
	echo "<div id=\"eventAdd\">\n";
	
	echo "<h3>Add Events</h3>\n";
	echo "<p>\n";
	
	echo "<form method=\"post\" name=\"eventAdd\">\n";
	
	echo "<input type=\"hidden\" name=\"eventMeetId\" value=\"$meetId\">\n";
	
	echo "<table width=\"100%\">\n";
	echo "<thead class=\"list\">\n";
	echo "<tr>\n";
	echo "<th>\n";
	echo "Num:\n";	
	echo "</th>\n";
	echo "<th>\n";
	echo "Groups:\n";	
	echo "</th>\n";
	echo "<th>\n";
	echo "Type:\n";	
	echo "</th>\n";
	echo "<th>\n";
	echo "Discipline:\n";	
	echo "</th>\n";
	echo "<th>\n";
	echo "Legs:\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Distance:\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Event Name:\n";	
	echo "</th>\n";
	echo "<th>\n";
	echo "Fee:\n";	
	echo "</th>\n";
	echo "<th>\n";
	echo "</th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	
	// Get existing events list
	$eventsList = $GLOBALS['db']->getAll("SELECT * FROM meet_events WHERE meet_id = '$meetId' ORDER BY prognumber ASC;");
	db_checkerrors($eventsList);
	$numEvents = sizeof($eventsList);
	$eNumber = 0;
	
	foreach ($eventsList as $e) {
		
		$eventId = $e[0];
		$eNumber = $e[7];
		$eNumSuff = $e[8];
		$eTypeId = $e[2];
		$eType = $GLOBALS['db']->getOne("SELECT typename FROM event_types WHERE id = '$eTypeId';");
		db_checkerrors($eType);
		
		$eDiscId = $e[3];
		$eDisc = $GLOBALS['db']->getOne("SELECT discipline FROM event_disciplines WHERE id = '$eDiscId';");
		db_checkerrors($eDisc);		
		
		$eLegs = $e[4];
		$eDistId = $e[5];
		$eDist = $GLOBALS['db']->getOne("SELECT distance FROM event_distances WHERE id = '$eDistId';");
		db_checkerrors($eDist);
		
		$eName = $e[6];
		$eFee = money_format('%i', $e[9]);
		
		echo "<tr class=\"list\">\n";
		echo "<td>\n";
		echo "$eNumber";

		if ($eNumSuff != '') {
			
			echo ".$eNumSuff\n";
			
		} else {
			
			echo "\n";
			
		}
		
		echo "</td>\n";
		echo "<td>\n";

		// Find what groups this event is part of
		$groupList = $GLOBALS['db']->getAll("SELECT * FROM meet_events_groups WHERE id IN (SELECT group_id FROM meet_events_groups_items WHERE event_id = '$eventId');");
		db_checkerrors($groupList);
		
		foreach ($groupList as $l) {
			
			$gId = $l[0];
			$groupName = $l[3];
			echo "$groupName\n";
			
			echo "<input type=\"checkbox\" name=\"deleteFromGroup[]\" value=\"$eventId $gId\" />\n";
			echo "<label>Remove</label>\n";
			
			echo "<br />\n";
			
		}
		
		echo "<label>Add to Group: </label>\n";
		echo "<select name=\"addtogroup_$eventId\">\n";
		echo "<option value=\"0\"></option>\n";
		
		$groupsAll = $GLOBALS['db']->getAll("SELECT * FROM meet_events_groups WHERE meet_id = '$meetId';");
		db_checkerrors($groupsAll);
		
		foreach ($groupsAll as $b) {
			
			$bId = $b[0];
			$bName = $b[3];
			
			echo "<option value=\"$bId\">$bName</option>\n";
			
		}
		
		echo "</select>\n";
		
		echo "</td>\n";
		echo "<td>\n";
		echo "$eType\n";	
		echo "</td>\n";
		echo "<td>\n";
		echo "$eDisc\n";	
		echo "</td>\n";
		echo "<td>\n";
		echo "$eLegs\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "$eDist\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "$eName\n";	
		echo "</td>\n";
		echo "<td>\n";
		echo "$eFee\n";	
		echo "</td>\n";
		echo "<td>\n";	
		echo "<input type=\"checkbox\" name=\"delEvent[]\" value=\"$eventId\" />Delete\n";
		echo "</td>\n";
		echo "</tr>\n";
		
	}
	
	// New event input
	echo "<tr>\n";
	echo "<td>\n";	
	$psEventNumber = $eNumber + 1;
	echo "<input type=\"text\" name=\"eventNumber\" size=\"1\" value=\"$psEventNumber\" />\n";
	echo " . \n";
	echo "<select name=\"eventNumberSuffix\">\n";
	echo "<option value=\"na\"></option>\n";
	echo "<option value=\"1\">1</option>\n";
	echo "<option value=\"2\">2</option>\n";
	echo "<option value=\"3\">3</option>\n";
	echo "<option value=\"4\">4</option>\n";
	echo "<option value=\"5\">5</option>\n";
	echo "<option value=\"a\">a</option>\n";
	echo "<option value=\"b\">b</option>\n";
	echo "<option value=\"c\">c</option>\n";
	echo "<option value=\"d\">d</option>\n";
	echo "<option value=\"e\">e</option>\n";
	echo "</select><br />\n";
	echo "</td>";
	
	// Event groups
	echo "<td>\n";

	echo "</td>\n";
		
	// Event type - load types of events from database

	echo "<td>\n";
	echo "<select name=\"eventType\">\n";
	echo "<option></option>\n";
	
	$eventTypes = $GLOBALS['db']->getAll("SELECT * FROM event_types ORDER BY typename;");
	db_checkerrors($eventTypes);
	
	foreach ($eventTypes as $t) {
		
		$eventTypeId = $t[0];
		$eventTypeLabel = $t[1];

		echo "<option value=\"$eventTypeId\">$eventTypeLabel</option>\n";
		
	}
	
	echo "</select><br />\n";
	echo "</td>\n";
	
	// Load disciplines from database

	echo "<td>\n";
	echo "<select name=\"eventDiscipline\">\n";
	echo "<option></option>\n";

	$eventDisc = $GLOBALS['db']->getAll("SELECT * FROM event_disciplines ORDER BY id;");
	db_checkerrors($eventDisc);
	
	foreach ($eventDisc as $d) {
		
		$eventDiscId = $d[0];
		$eventDiscLabel = $d[1];

		echo "<option value=\"$eventDiscId\">$eventDiscLabel</option>\n";
		
	}	
	
	echo "</select><br />\n";
	echo "</td>\n";
	
	echo "<td>\n";
	echo "<select name=\"eventLegs\">\n";

	for ($i = 1; $i < 11; $i++) {

		echo "<option value=\"$i\">$i</option>\n";
		
	}	

	echo "</select>\n";
	echo "</td>\n";
	
	// Load distances from database
	echo "<td>\n";
	echo "<select name=\"eventDistance\">\n";
	echo "<option></option>\n";
	
	$eventDist = $GLOBALS['db']->getAll("SELECT * FROM event_distances ORDER BY id;");
	db_checkerrors($eventDist);
	
	foreach ($eventDist as $s) {
		
		$eventDistId = $s[0];
		$eventDistLabel = $s[1];

		echo "<option value=\"$eventDistId\">$eventDistLabel</option>\n";
		
	}		
	
	echo "</select><br />\n";
	echo "</td>\n";
	
	// Event Name
	echo "<td>\n";
	echo "<input type=\"text\" name=\"eventName\" size=\"20\" value=\"$psEventName\" /><br />\n";
	
	echo "</td>\n";
	
	echo "<td>\n";
	echo "<input type=\"text\" name=\"eventFee\" size=\"4\" value=\"$psEventFee\" /><br />\n";
	echo "</td>\n";
	
	echo "<td>\n";
	echo "<input type=\"submit\" name=\"addEvent\" value=\"Add\" /> \n";
	echo "</td>\n";
	
	echo "</tr>\n";
	echo "</table>\n";
	
	echo "<input type=\"submit\" name=\"delEventSubmit\" value=\"Update\" />";
	
	echo "</form>\n";
	
	echo "</p>\n";



	echo "<h3>Event Groups</h3>\n";

	echo "<form method=\"post\">\n";
	
	// Get existing groups
	$eventGroups = $GLOBALS['db']->getAll("SELECT * FROM meet_events_groups WHERE meet_id = '$meetId';");
	db_checkerrors($eventGroups);
	
	echo "<table width=\"100%\">\n";
	
	echo "<tr>\n";
	echo "<th>\n";
	echo "Group Name: ";
	echo "</th>\n";
	echo "<th>\n";
	echo "Event Numbers: ";
	echo "</th>\n";
	echo "<th>\n";
	echo "Max Events: ";
	echo "</th>\n";
	echo "<th>\n";
	echo "";
	echo "</th>\n";
	echo "</tr>\n";
	
	foreach ($eventGroups as $g) {
		
		$gId = $g[0];
		$gMax = $g[2];
		$gName = $g[3];
		$gEvents = $GLOBALS['db']->getAll("SELECT * FROM meet_events_groups_items WHERE group_id = '$gId';");
		db_checkerrors($gEvents);
		
		echo "<tr>\n";
		
		echo "<td>\n";
		echo "$gName\n";
		echo "</td>\n";
		
		echo "<td>\n";
		foreach ($gEvents as $h) {
			
			$hId = $h[2];
			$hProgDetails = $GLOBALS['db']->getRow("SELECT prognumber, progsuffix FROM meet_events WHERE id = '$hId';");
			db_checkerrors($hProgDetails);
			
			echo $hProgDetails[0] . $hProgDetails[1]. ', '; // yike dirty hack
			
		}
		echo "</td>\n";
		
		echo "<td>\n";
		echo "<input type=\"text\" size=\"3\" name=\"groupmax_$gId\" value=\"$gMax\" />\n";
		echo "</td>\n";
		
		echo "<td>\n";
		echo "<input type=\"checkbox\" name=\"deleteGroup[]\" value=\"$gId\" />\n";
		echo "<label>Delete Group</label>\n";
		echo "</td>\n";
		
		
		echo "</tr>\n";
		
	}
	
	echo "</table>\n";

	echo "<label>Create an Event Group:</label><br />\n";
	echo "<label>Group Name: </label>\n";
	echo "<input type=\"text\" name=\"newGroupName\" /><br />\n";
	echo "<label>Maximum Events: </label>\n";
	echo "<input type=\"text\" name=\"newGroupMax\" size=\"3\" /><br />\n";
	
	// Meet rule selector
	echo "<label>Select Rule Text: </label>\n";
	echo "<select name=\"newGroupRule\">\n";
	
	$ruleList = $GLOBALS['db']->getAll("SELECT * FROM meet_rules;");
	db_checkerrors($ruleList);
	
	foreach ($ruleList as $r) {
	
		$ruleId = $r[0];
		$ruleText = $r[1];
	
		echo "<option value=\"$ruleId\">$ruleText</option>\n";
	
	}
	
	echo "</select><br />\n";
	echo "or write new rule: \n";
	echo "<input type=\"text\" name=\"newRuleText\" length=\"80\" />\n";	
	
	echo "<input type=\"submit\" name=\"updateEventGroups\" value=\"Update Group\" />\n";
	echo "<input type=\"hidden\" name=\"eventMeetId\" value=\"$meetId\">\n";
	
	echo "</form>\n";


}

echo "</div>\n";

echo "</div>\n"; // main div

htmlFooters();

?>