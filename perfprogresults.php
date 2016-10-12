<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Club.php");
checkLogin();

$progId = mysql_real_escape_string($_GET['id']);

// If requested clear results table
if ($_POST['clearMSXResults']) {
	
	$deleteResults = $GLOBALS['db']->query("DELETE FROM performance_programs_awards 
			WHERE perf_prog_id = ?", array($progId));
	db_checkerrors($deleteResults);
	
}

// Store the results in the results table
if ($_POST['storeMSXResults']) {
	
	$qualList = $GLOBALS['db']->getAll("SELECT rt.m, MIN( rt.l ) , rt.c
			FROM (
			SELECT member_id AS m,
			LEVEL AS l, COUNT( * ) AS c
			FROM performance_programs_results
			WHERE perf_prog_id = '$progId'
			GROUP BY member_id, level
			) rt
			WHERE c >= (SELECT timesrequired FROM performance_programs WHERE id = '$progId')
			GROUP BY m
			ORDER BY l;");
	db_checkerrors($qualList);
	
	foreach($qualList as $q) {
		
		$mId = $q[0];
		$levelId = $q[1];
		
		$insert = $GLOBALS['db']->query("INSERT INTO performance_programs_awards 
				(perf_prog_id, member_id, level) 
				VALUES (?, ?, ?);", array($progId, $mId, $levelId));
		db_checkerrors($insert);
		
	}
	
}

// Get Performance Program Details
$progDets = $GLOBALS['db']->getRow("SELECT * FROM performance_programs WHERE id = '$progId';");
db_checkerrors($progDets);

htmlHeaders("Performance Program Setup - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Performance Programs</h1>\n";

echo "<h2>Performance Program Level Acheivements</h2>\n";

$qualList = $GLOBALS['db']->getAll("SELECT rt.m, MIN( rt.l ) , rt.c
		FROM (
		SELECT member_id AS m,
		LEVEL AS l, COUNT( * ) AS c
		FROM performance_programs_results
		WHERE perf_prog_id = '$progId'
		GROUP BY member_id, level
		) rt
		WHERE c >= (SELECT timesrequired FROM performance_programs WHERE id = '$progId')
		GROUP BY m
		ORDER BY l;");
db_checkerrors($qualList);

$cPlatinum = 0;
$cGold = 0;
$cSilver = 0;
$cBronze = 0;

// Count levels
foreach ($qualList as $a) {
	
	$levelId = $a[1];
	$levelName = $GLOBALS['db']->getOne("SELECT levelname FROM performance_programs_levels
			WHERE perf_prog_id = '$progId' AND id = '$levelId';");
	db_checkerrors($levelName);
	
	switch ($levelName) {
		
		case 'Platinum':
			$cPlatinum++;
			break;
			
		case 'Gold':
			$cGold++;
			break;
			
		case 'Silver':
			$cSilver++;
			break;

		case 'Bronze':
			$cBronze++;
			break;	
			
	}
	
}

$cTotal = $cPlatinum + $cGold + $cSilver + $cBronze;

echo "<p>\n";
echo "<label>Program Name: </label>" . $progDets[2] . "<br />\n";
echo "<label>Program Start Date: </label>" . $progDets[4] . "<br />\n";
echo "<label>Program End Date: </label>" . $progDets[5] . "<br />\n";
echo "<label>Qualifying Times Required: </label>" . $progDets[7] . "<br />\n";
echo "<label>Number of Platinum: </label> $cPlatinum <br />\n";
echo "<label>Number of Gold: </label> $cGold <br />\n";
echo "<label>Number of Silver: </label> $cSilver <br />\n";
echo "<label>Number of Bronze: </label> $cBronze <br />\n";
echo "<label>Total: </label> $cTotal<br />\n";
echo "</p>\n";

echo "<form method=\"post\">\n";

echo "<input type=\"submit\" name=\"clearMSXResults\" value=\"Clear Stored Results\" />\n";
echo "<input type=\"submit\" name=\"storeMSXResults\" value=\"Store Results\" />\n";

echo "</form>\n";

echo "<h2>Draft Results</h2>\n";

echo "<table class=\"list\">\n";
echo "<thead class=\"list\">\n";
echo "<tr class=\"list\">\n";
echo "<th>Club Code:</th>\n";
echo "<th>Member Name:</th>\n";
echo "<th>Age Group:</th>\n";
echo "<th>Level Achieved: </th>\n";
echo "</tr>\n";
echo "</thead>\n";
echo "<tbody class=\"list\">\n";

foreach ($qualList as $q) {
	
	$memberDetails = new Member();
	$memberDetails->loadId($q[0]);
	$memberClubs = $memberDetails->getClubIds();
	$memberName = $memberDetails->getFullname();
	$memberMSA = $memberDetails->getMSANumber();
	$ageGroup = $memberDetails->getAgeGroup();
	$levelId = $q[1];
	$levelName = $GLOBALS['db']->getOne("SELECT levelname FROM performance_programs_levels 
			WHERE perf_prog_id = '$progId' AND id = '$levelId';");
	db_checkerrors($levelName);

	echo "<tr class=\"list\">\n";
	echo "<td>\n";
	
	if (isset($memberClubs)) {
	
		foreach($memberClubs as $c) {
		
			$clubDet = new Club();
			$clubDet->load($c);
			$clubCode = $clubDet->getCode();
			echo "$clubCode<br />\n";
		
		}
	
	}
	
	echo "</td>\n";
	echo "<td>\n";
	echo "$memberName($memberMSA)";
	echo "</td>\n";
	echo "<td>\n";
	echo "$ageGroup";
	echo "</td>\n";
	echo "<td>\n";
	echo $levelName;
	echo "</td>\n";
	echo "</tr>\n";
	
}

echo "</tbody>\n";
echo "</table>\n";


htmlFooters();

?>