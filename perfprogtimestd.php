<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("HTTP/Request2.php");
require_once("HTTP/Request2/CookieJar.php");
checkLogin();

htmlHeaders("Performance Program Setup - Swimming Management System");

sidebarMenu();

$progId = mysql_real_escape_string($_GET['id']);
$portalURL = "http://www.portal.aussi.org.au/ranking/ranking.php";

echo "<div id=\"main\">\n";

echo "<h1>Performance Program Time Standard Calculation</h1>\n";

// Before starting, clear the program
$delete = $GLOBALS['db']->query("DELETE FROM performance_programs_stds WHERE perf_prog_id = ?",
	array($progId));
db_checkerrors($delete);

// Get list of events
$eventsList = $GLOBALS['db']->getAll("SELECT a.id, b.discipline, c.distance 
		FROM performance_programs_events as a, event_disciplines as b, event_distances as c
		WHERE a.perf_prog_id = ? 
		AND a.discipline = b.id
		AND a.distance = c.id;", array($progId));
db_checkerrors($eventsList);

foreach ($eventsList as $e) {
	
	$eventId = $e[0];
	$eventDisc = $e[1];
	list($eventDist, $eventCourse) = explode(" ", $e[2]);

	$ageGroupList = $GLOBALS['db']->getAll("SELECT * FROM age_groups WHERE age_groups.set = 1
			AND swimmers = 1;");
	db_checkerrors($ageGroupList);
	
	foreach ($ageGroupList as $a) {
		
		$ageGroupId = $a[0];
		
		if ($a[4] == 1) {
			
			$ageGender = "Male";
			
		} elseif ($a[4] == 2) {
			
			$ageGender = "Female";
			
		}
		
		// Work around minimum ranking group issue on portal.
		if ($a[2] == 18) {
			
			$a[2] = 20;
			
		}
		
		$groupName = $a[2] . "-" . $a[3];
	
		// Request Data 
		$gender = $ageGender;
		$course = $eventCourse;
		$dist = $eventDist;
		$stroke = str_replace(" ", "+", $eventDisc);
		$group = $groupName;
		
		$cacheLoc = $GLOBALS['home_dir'] . '/masters-data/perf_prog/cache/';
		$cacheFile = $group . "_" . $gender . "_" . $dist . "_" . $course . "_" . $stroke . ".html";
		
		//echo "Starting $eventDist $eventCourse $eventDisc - $ageGender $groupName.<br />\n";
		
		$parameters = "?type=0&display=best&gender=$gender&course=$course&dist=$dist&stroke=$stroke&group=$group&year=all&state=All&clubcode=*&filter=0&view=1&finaonly=0&print=Printer+Friendly";
		$req =& new HTTP_Request2($portalURL . $parameters);
		$req->setMethod(HTTP_Request2::METHOD_POST);
		$req->setCookieJar($cookiejar);
		
		// Add form data to request
		$req->setConfig("follow_redirects", "false");
		
		// Check if cache exists
		if (file_exists($cacheLoc . $cacheFile)) {
			
			// Read cache
			$req1data = file_get_contents($cacheLoc . $cacheFile);
			
			echo "Read cache... ";
			
		} else {
		
			// If not request the data
			try {
					
				$req1data = $req->send()->getBody();
				
				echo "Retrieved... ";
				
			} catch (HttpException $ex) {
				echo $ex;
			}
		
			// Cache the response 
			$retval = file_put_contents($cacheLoc . $cacheFile, $req1data);
			
			if ($retval === FALSE) {
				
				echo "unable to cache!\n";
				exit;
				
			}
		
		}
		
		// Iterate through returned lines looking for data
		// look for line starting nested table
		
		// Array for data
		$timeData = array();
		
		//print_r($req1data);
		
		$tableNo = 0;
		foreach(preg_split("/((\r?\n)|(\r\n?))/", $req1data) as $line) {
			
			// We're in the second nested table
			
			if ($tableNo == 2) {
				
				//echo "table 2\n";
				
				if (substr($line, 0, 30) == "</td></tr><tr><td height='25 '") {
					
					// This is our line
					// Remove everything before the <hr>
					//echo "foundit!";
					
					$line = substr($line, strpos($line, "<hr>") + 14, strlen($line));
					
					// Split the remainder of the table by <tr>'s
					$rows = explode("<tr>", $line);
					
					foreach ($rows as $r) {
						
						$cols = explode("<td", $r);
						
						for ($i = 0; $i < 6; $i++) {
							
							// Get the date
							echo $cols[3] . "<br />\n";
							
							if ($i == 5) {
								
								// Get the ranking
								$leftPos = strpos($cols[5], ">") + 1;
								$rightPos = strpos($cols[5], "<");
								$len = $rightPos - $leftPos;
								
								// Drop data into array
								$timeRaw = substr($cols[5], $leftPos, $len);
								$timeData[] = sw_timeToSecs($timeRaw);
								
								
							}
							
						}
						
					}
				
				}
				
			}
			
			if (substr($line, 0, 6) == "<table") {
				
				//echo $tableNo;
				$tableNo++;
				
			}
			
			//echo "<p>" . substr($line, 0, 6) . "</p>\n";
			
		}
		
		// Now we have our values
		$numTimes = count($timeData);
		echo "We now have " . $numTimes . " times to work with!\n";
		
		if ($numTimes > 0) {
		
			$middle = round($numTimes / 2);
			$median = $timeData[$middle];
			$mean = array_sum($timeData) / $numTimes;

		}
			
		//echo "The median time is " . sw_formatSecs($median) .".\n";
		//echo "The mean time is " . sw_formatSecs(round($mean, 2)) .".\n";
		
		// Test proposals
		$platinum = 10;
		$gold = 20;
		$silver = 30;
		$bronze = 40;
		
		$platinumTime = $timeData[round($numTimes * ($platinum / 100))];
		$goldTime = $timeData[round($numTimes * ($gold / 100))];
		$silverTime = $timeData[round($numTimes * ($silver / 100))];
		$bronzeTime = $timeData[round($numTimes * ($bronze / 100))];
		
		echo "<p>\n";
		echo "The platinum time is: " . sw_formatSecs($platinumTime);
 		echo "</p>\n";
		
		echo "<p>\n";
		echo "The gold time is: " . sw_formatSecs($goldTime);
		echo "</p>\n";
		
		echo "<p>\n";
		echo "The silver time is: " . sw_formatSecs($silverTime);
		echo "</p>\n";
		
		echo "<p>\n";
		echo "The bronze time is: " . sw_formatSecs($bronzeTime);
		echo "</p>\n";

		// Make sure all times are set and there are at least 10 times in each event to base off
		if (($platinumTime != 0) && ($numTimes >= 10)) {
		
			$insertPlat = $GLOBALS['db']->query("INSERT INTO performance_programs_stds (perf_prog_id, 
					age_group, level, perf_prog_event, hightime) 
					VALUES (?, ?, (SELECT id FROM performance_programs_levels WHERE levelname = ? AND perf_prog_id = ?), ?, ?);",
					array($progId, $ageGroupId, "Platinum", $progId, $eventId, $platinumTime));
			db_checkerrors($insertPlat);
			
			$insertGold = $GLOBALS['db']->query("INSERT INTO performance_programs_stds (perf_prog_id,
					age_group, level, perf_prog_event, hightime)
					VALUES (?, ?, (SELECT id FROM performance_programs_levels WHERE levelname = ? AND perf_prog_id = ?), ?, ?);",
					array($progId, $ageGroupId, "Gold", $progId, $eventId, $goldTime));
			db_checkerrors($insertGold);
			
			$insertSilver = $GLOBALS['db']->query("INSERT INTO performance_programs_stds (perf_prog_id,
					age_group, level, perf_prog_event, hightime)
					VALUES (?, ?, (SELECT id FROM performance_programs_levels WHERE levelname = ? AND perf_prog_id = ?), ?, ?);",
					array($progId, $ageGroupId, "Silver", $progId, $eventId, $silverTime));
			db_checkerrors($insertSilver);
			
			$insertBronze = $GLOBALS['db']->query("INSERT INTO performance_programs_stds (perf_prog_id,
					age_group, level, perf_prog_event, hightime)
					VALUES (?, ?, (SELECT id FROM performance_programs_levels WHERE levelname = ? AND perf_prog_id = ?), ?, ?);",
					array($progId, $ageGroupId, "Bronze", $progId, $eventId, $bronzeTime));
			db_checkerrors($insertBronze);
		
		} else {
			
			// Get the times from the previous age group
			$ageGroupDetails = $GLOBALS['db']->getRow("SELECT * FROM age_groups 
					WHERE age_groups.set = 1
					AND id = ?;", array($ageGroupId));
			db_checkerrors($ageGroupDetails);
			
			$prevMin = $ageGroupDetails[2] - 5;
			$prevMax = $ageGroupDetails[3] - 5;
			$prevGender = $ageGroupDetails[4];
			
			echo "<br />Duplicating previous age group $prevMin - $prevMax $prevGender...\n";
			
			$prevAgeGroupId = $GLOBALS['db']->getOne("SELECT id FROM age_groups 
					WHERE age_groups.set = 1
					AND min = ? AND max = ? AND gender = ?;",
					array($prevMin, $prevMax, $prevGender));
			db_checkerrors($prevAgeGroupId);
			
			echo " previous age group id $prevAgeGroupId found.<br />\n";
			
			$platinumTimes = $GLOBALS['db']->getRow("SELECT hightime, lowtime FROM performance_programs_stds
					WHERE perf_prog_id = ? AND age_group = ? 
					AND level = (SELECT id FROM performance_programs_levels WHERE levelname = ? 
					AND perf_prog_id = ?);",
					array($progId, $prevAgeGroupId, "Platinum", $progId));
			db_checkerrors($platinumTime);
			
			$insertPlat = $GLOBALS['db']->query("INSERT INTO performance_programs_stds (perf_prog_id,
					age_group, level, perf_prog_event, hightime, lowtime)
					VALUES (?, ?, (SELECT id FROM performance_programs_levels WHERE levelname = ? AND perf_prog_id = ?), ?, ?, ?);",
					array($progId, $ageGroupId, "Platinum", $progId, $eventId, $platinumTimes[0], 
					$platinumTimes[1]));
			db_checkerrors($insertPlat);
			
			$goldTimes = $GLOBALS['db']->getRow("SELECT hightime, lowtime FROM performance_programs_stds
					WHERE perf_prog_id = ? AND age_group = ?
					AND level = (SELECT id FROM performance_programs_levels WHERE levelname = ?
					AND perf_prog_id = ?);",
					array($progId, $prevAgeGroupId, "Gold", $progId));
			db_checkerrors($goldTimes);
			
			$insertGold = $GLOBALS['db']->query("INSERT INTO performance_programs_stds (perf_prog_id,
					age_group, level, perf_prog_event, hightime, lowtime)
					VALUES (?, ?, (SELECT id FROM performance_programs_levels WHERE levelname = ? AND perf_prog_id = ?), ?, ?, ?);",
					array($progId, $ageGroupId, "Gold", $progId, $eventId, $goldTimes[0], $goldTimes[1]));
			db_checkerrors($insertGold);
			
			$silverTimes = $GLOBALS['db']->getRow("SELECT hightime, lowtime FROM performance_programs_stds
					WHERE perf_prog_id = ? AND age_group = ?
					AND level = (SELECT id FROM performance_programs_levels WHERE levelname = ?
					AND perf_prog_id = ?);",
					array($progId, $prevAgeGroupId, "Silver", $progId));
			db_checkerrors($silverTimes);
			
			$insertSilver = $GLOBALS['db']->query("INSERT INTO performance_programs_stds (perf_prog_id,
					age_group, level, perf_prog_event, hightime, lowtime)
					VALUES (?, ?, (SELECT id FROM performance_programs_levels WHERE levelname = ? AND perf_prog_id = ?), ?, ?, ?);",
					array($progId, $ageGroupId, "Silver", $progId, $eventId, $silverTimes[0], $silverTimes[1]));
			db_checkerrors($insertSilver);
			
			$bronzeTimes = $GLOBALS['db']->getRow("SELECT hightime, lowtime FROM performance_programs_stds
					WHERE perf_prog_id = ? AND age_group = ?
					AND level = (SELECT id FROM performance_programs_levels WHERE levelname = ?
					AND perf_prog_id = ?);",
					array($progId, $prevAgeGroupId, "Bronze", $progId));
			db_checkerrors($bronzeTimes);
			
			$insertBronze = $GLOBALS['db']->query("INSERT INTO performance_programs_stds (perf_prog_id,
					age_group, level, perf_prog_event, hightime, lowtime)
					VALUES (?, ?, (SELECT id FROM performance_programs_levels WHERE levelname = ? AND perf_prog_id = ?), ?, ?, ?);",
					array($progId, $ageGroupId, "Bronze", $progId, $eventId, $bronzeTimes[0], $bronzeTimes[1]));
			db_checkerrors($insertBronze);
			
		}
		
		echo "processed $eventDist $course $eventDisc - $ageGender $groupName. Platinum: $platinumTime Gold: $goldTime Silver: $silverTime Bronze: $bronzeTime<br />\n";
		
	}

}

echo "</div>\n";

htmlFooters();

?>