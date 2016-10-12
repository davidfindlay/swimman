<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Member.php");
checkLogin();

$progId = mysql_real_escape_string($_GET['id']);

// Get Performance Program Details
$progDets = $GLOBALS['db']->getRow("SELECT * FROM performance_programs WHERE id = '$progId';");
db_checkerrors($progDets);

htmlHeaders("Performance Program Setup - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Performance Programs</h1>\n";

echo "<h2>Performance Program Qualifying Times</h2>\n";

echo "<p>\n";
echo "<label>Program Name: </label>" . $progDets[2] . "<br />\n";
echo "<label>Program Start Date: </label>" . $progDets[4] . "<br />\n";
echo "<label>Program End Date: </label>" . $progDets[5] . "<br />\n";
echo "<label>Qualifying Times Required: </label>" . $progDets[7] . "<br />\n";
echo "</p>\n";

echo "<table class=\"list\">\n";
echo "<thead class=\"list\">\n";
echo "<tr class=\"list\">\n";
echo "<th>Meet Name:</th>\n";
echo "<th>Member Name:</th>\n";
echo "<th>Event: </th>\n";
echo "<th>Age Group:</th>\n";
echo "<th>Level:</th>\n";
echo "<th>Qualifying Time:</th>\n";
echo "</tr>\n";
echo "</thead>\n";
echo "<tbody class=\"list\">\n";

$qualList = $GLOBALS['db']->getAll("SELECT * FROM performance_programs_results WHERE 
		perf_prog_id = '$progId' ORDER BY member_id, level;");
db_checkerrors($qualList);

foreach ($qualList as $q) {
	
	$meetName = $q[9];
	$memberDet = new Member();
	$memberDet->loadId($q[4]);
	$memberName = $memberDet->getFullname();
	$memberMSA = $memberDet->getMSANumber();
	$eventId = $q[2];
	$eventDetails = $GLOBALS['db']->getRow("SELECT event_disciplines.discipline, 
			event_distances.distance FROM event_disciplines, event_distances 
			WHERE event_distances.id = (SELECT distance FROM performance_programs_events WHERE 
			id = '$eventId') AND event_disciplines.id = (SELECT discipline FROM 
			performance_programs_events WHERE id = '$eventId');");
	db_checkerrors($eventDetails);
	$eventDetailInfo = $eventDetails[0] . ' ' . $eventDetails[1];
	$ageGroupId = $q[5];
	$groupName = $GLOBALS['db']->getOne("SELECT groupname FROM age_groups WHERE id = '$ageGroupId';");
	db_checkerrors($groupName);
	$resultTime = sw_formatSecs($q[8]);
	$levelId = $q[6];
	$levelName = $GLOBALS['db']->getOne("SELECT levelname FROM performance_programs_levels
			WHERE perf_prog_id = '$progId' AND id = '$levelId';");
	db_checkerrors($levelName);
	
	echo "<tr class=\"list\">\n";
	echo "<td>\n";
	echo $meetName;
	echo "</td>\n";
	echo "<td>\n";
	echo "$memberName($memberMSA)";
	echo "</td>\n";
	echo "<td>\n";
	echo $eventDetailInfo;
	echo "</td>\n";
	echo "<td>\n";
	echo $groupName;
	echo "</td>\n";
	echo "<td>\n";
	echo $levelName;
	echo "</td>\n";
	echo "<td>\n";
	echo $resultTime;
	echo "</td>\n";
	echo "</tr>\n";
	
}

echo "</tbody>\n";
echo "</table>\n";

echo "</div>\n";

htmlFooters();

?>