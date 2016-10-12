<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Member.php");
checkLogin();

$progId = mysql_real_escape_string($_GET['id']);

if (isset($_POST['getlist'])) {
	
	if ($_POST['getlist'] == "Get Meet List") {
		
		$state = mysql_real_escape_string($_POST['state']);
		$branch = mysql_real_escape_string($_POST['branch']);
		$year = mysql_real_escape_string($_POST['year']);
		

		
		

		
	}
	
}

if (isset($_POST['uploadCsv'])) {
	
	$uploaddir = $GLOBALS['home_dir'] . '/masters-tmp/programs/' . $progId;
	
	// Check directory exists
	if (!file_exists($uploaddir)) {
	
		mkdir($uploaddir);
	
	}

	$uploadfile = $uploaddir . '/' . basename($_FILES['userfile']['name']);
	
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		
		// File uploaded successfully.
		//echo "File uploaded!\n";
		
	} else {
		
		echo "Unable to upload file $uploadfile\n";
		
	}
	
}

if (isset($_POST['getmeets'])) {
	
	if ($_POST['getmeets']) {
		
		foreach ($_POST['importId'] as $i) {
		
			$meetFile = mysql_real_escape_string($i);
			
			echo "Opening $meetFile... <br />\n";
			
			// Temporary
			$tempDir = $GLOBALS['home_dir'] . '/masters-tmp/programs/' . $progId;
			
			$fh = fopen($tempDir . '/' . $i, 'r');
			
			$line = fgets($fh);
			
			list($meetName, $meetCourse, $meetLoc, $meetDate) = explode('-', $line);
			
			$curPerfEvent = '';
			$curDiscId = '';
			$curDistId = '';
			$curGender = '';
			$curAgeGroup = '';
			
			while ($line = fgets($fh)) {
				
				if (count(trim($line)) != 0) {

					// Search for an event
					if (strpos($line, 'Female') !== false || strpos($line, 'Male') !== false) {

						list($eventDist, $eventDisc, , $eventCourse, , $eventGender) = explode(' ', 
								trim(str_replace('-', '', $line)));
						
						
						$eventDisc = trim($eventDisc);
						
						// Handle Individual Medley
						if ($eventDisc == "Individual") {
							
							list($eventDist, $eventDisc, , , $eventCourse, , $eventGender) = explode(' ',
									trim(str_replace('-', '', $line)));
							$eventDisc = "Individual Medley";
							
						}
						
						$eventDist = trim($eventDist);
						$eventCourse = trim($eventCourse);
						
					 	echo "$eventDist-$eventDisc-$eventCourse-$eventGender<br />\n";
						
						$eventData = $GLOBALS['db']->getRow("SELECT * FROM performance_programs_events WHERE
								discipline = (SELECT id FROM event_disciplines WHERE discipline = '$eventDisc') 
								AND distance = (SELECT id FROM event_distances 
								WHERE distance = '$eventDist $eventCourse') AND 
								perf_prog_id = '$progId';");
						
						db_checkerrors($eventData);
						
						$curPerfEvent = $eventData[0];
						$curDiscId = $eventData[2];
						$curDistId = $eventData[3];
						
						if ($eventGender == "Male") {
							
							$curGender = 1;
							
						} else {
							
							$curGender = 2;
							
						}
						
						 echo "Found event - $curPerfEvent - $curDiscId - $curDistId - $curGender<br />\n";
						
					}
					
					// Search for an age group
					if (strpos($line, 'Age Group') !== false) {
						
						list($lowAge, $highAge) = explode('-', str_replace('Age Group', '', trim($line)));
						echo "Found Age Group $lowAge - $highAge - $curGender<br />\n";
						
						$curAgeGroup = $GLOBALS['db']->getOne("SELECT id FROM age_groups WHERE 
								age_groups.set = (SELECT agegroupset FROM performance_programs WHERE 
								id = '$progId') AND min = '$lowAge' AND max = '$highAge' AND 
								gender = '$curGender';");
						db_checkerrors($curAgeGroup);
						
					}
					
					// Search for individual results
					if (substr_count($line, ',') == 6) {
						
						// Read result line as a CSV
						$lineData = explode(',', trim($line));
						
						if ($lineData[5] == '') {
						
							$swimmerName = $lineData[0];
							$swimmerId = $lineData[3];
							$swimmerTime = $lineData[4];
							
							echo "Checking $swimmerName($swimmerId) with time $swimmerTime<br />\n";
							
							// Reformat time result into seconds
							if (strpos($swimmerTime, ':') !== FALSE) {
							
								$stArray = explode(':', $swimmerTime);
									
								if (count($stArray) == 3) {
							
									$secResult = (floatval($stArray[0]) * 60 * 60) + (floatval($stArray[1]) * 60) +
									floatval($stArray[2]);
							
								} else {
										
									$secResult = (floatval($stArray[0]) * 60) + floatval($stArray[1]);
							
								}
							
							} else {
							
								$secResult = floatval($swimmerTime);
							
							}
							
							// Lookup standard
							$timeStds = $GLOBALS['db']->getAll("SELECT * FROM performance_programs_stds WHERE 
									perf_prog_id = '$progId' AND age_group = '$curAgeGroup' AND 
									perf_prog_event = '$curPerfEvent';");
							db_checkerrors($timeStds);
							
							// echo "$progId - $curAgeGroup - $curPerfEvent<br />\n";
							//print_r($timeStds);
							
							foreach ($timeStds as $t) {
								
								$stdId = $t[0];
								$standard = $t[5];
								
								 echo "$swimmerName - $standard - $secResult<br />\n";
								
								if ($standard >= $secResult) {
									
									$level = $t[3];
									
									$levelName = $GLOBALS['db']->getOne("SELECT levelname FROM 
											performance_programs_levels WHERE id = '$level' AND perf_prog_id = '$progId';");
									db_checkerrors($levelName);
									
									$curAgeGroupData = $GLOBALS['db']->getRow("SELECT min, max FROM age_groups WHERE 
											id= '$curAgeGroup';");
									db_checkerrors($curAgeGroupData);
									list($curAgeMin, $curAgeMax) = $curAgeGroupData;
									
									$eventDetails = $GLOBALS['db']->getRow("SELECT event_disciplines.discipline, 
											event_distances.distance FROM event_disciplines, event_distances 
											WHERE event_distances.id = (SELECT distance FROM performance_programs_events WHERE 
											id = '$curPerfEvent') AND event_disciplines.id = (SELECT discipline FROM 
											performance_programs_events WHERE id = '$curPerfEvent');");
									db_checkerrors($eventDetails);
									
									$eventDetailInfo = $eventDetails[0] . ' ' . $eventDetails[1];
									$secResultText = sw_formatSecs($secResult);
									$secStdText = sw_formatSecs($standard);
									
									// Swimmer has met this standard
									echo "Swimmer $swimmerName($swimmerId) has met standard $levelName for 
										$curAgeMin-$curAgeMax in $eventDetailInfo with time $secResultText under 
										$secStdText.<br />\n";
									
									// Find member id
									$memDets = new Member();
									$memCheck = $memDets->loadNumber($swimmerId);
									$memId = $memDets->getId();
									
									// Insert into results table
									
									if ($memCheck !== false) {
									
										$insert = $GLOBALS['db']->query("INSERT INTO performance_programs_results 
											(perf_prog_id, perf_prog_event, perf_prog_std, member_id, 
											age_group, level, time, meet_name) 
											VALUES ('$progId', '$curPerfEvent', '$stdId', '$memId', '$curAgeGroup', 
											'$level', '$secResult', '$meetName');");
										db_checkerrors($insert);
										
									}
									
								}
							}
						}
						
					}
					
				}
				
			}
			
		}
		
	}
	
	
}

htmlHeaders("Performance Program Setup - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Performance Program Setup</h1>\n";

echo "<h2>Import Meets</h2>\n";

echo "<p>Retreive a list of meets for the relevant year from the Results Portal\n";
echo " and import the relevant results</p>\n";

echo "<form method=\"post\" action=\"perfprogmeet.php?id=$progId\">\n";

echo "<p>\n";
echo "<label>State: </label>\n";
echo "<select name=\"state\">\n";
echo "<option value=\"---\">National</option>\n";
echo "<option value=\"QLD\" selected=\"selected\">QLD</option>\n";
echo "</select><br />\n";
echo "<label>Year: </label>\n";
echo "<select name=\"year\">\n";
echo "<option value=\"2013\" selected=\"selected\">2013</option>\n";
echo "<option value=\"2014\">2014</option>\n";
echo "<option value=\"2015\">2015</option>\n";
echo "</select><br />\n";
echo "<label>Branch: </label>\n";
echo "<select name=\"branch\">\n";
echo "<option value=\"*\">National</option>\n";
echo "<option value=\"QLD\" selected=\"selected\">QLD</option>\n";
echo "</select><br />\n";
echo "</p>\n";

echo "<p>\n";
echo "<input type=\"submit\" name=\"getlist\" value=\"Get Meet List\" />\n";
echo "</p>\n";
echo "</form>\n";

// Temporary
$tempDir = $GLOBALS['home_dir'] . '/masters-tmp/programs/' . $progId;

if (file_exists($tempDir)) {

	$fileList = scandir($tempDir);
	$meetList = array();
	
	foreach ($fileList as $f) {
		
		$fh = fopen($tempDir . '/' . $f, 'r');
		
		$line = fgets($fh);
		
		if ($f != "." && $f != "..") {
			
			$meetId = $f;
			list($meetName, $meetCourse, $meetLoc, $meetDate) = explode('-', $line);
			$meetList[] = array($meetId, $meetDate, $meetName, $meetCourse, $meetLoc);
			
		}
	
		fclose($fh);
		
	}

}

if (isset($meetList)) {

	echo "<form method=\"post\" action=\"perfprogmeet.php?id=$progId\">\n";
	
	echo "<h2>Select Meets to Import</h2>\n";
	echo "<table>\n";
	echo "<thead class=\"list\">\n";
	echo "<tr class=\"list\">\n";
	echo "<th>\n";
	echo "Select\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Date\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Meet Name\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Course\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Location\n";
	echo "</th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	
	echo "<tbody class=\"list\">\n";
	
	foreach ($meetList as $m) {
	
		echo "<tr class=\"list\">\n";
		echo "<td>\n";
		
		$mId = $m[0];
		echo "<input type=\"checkbox\" name=\"importId[]\" value=\"$mId\" />\n";
		
		echo "</td>\n";
		echo "<td>\n";
		echo $m[1];
		echo "</td>\n";

		echo "<td>\n";
		echo $m[2];
		echo "</td>\n";
		
		echo "<td>\n";
		echo $m[3];
		echo "</td>\n";
		
		echo "<td>\n";
		echo $m[4];
		echo "</td>\n";
		
		echo "</tr>\n";
	
	}
		
	echo "</table>\n";
	
	echo "<p>\n";
	echo "<input type=\"submit\" name=\"getmeets\" value=\"Import Meets\" />\n";
	echo "</p>\n";
	
	echo "</form>\n";
	
}

echo "<h2>Upload Meet CSV Files</h2>\n";

echo "<form method=\"post\" enctype=\"multipart/form-data\" action=\"perfprogmeet.php?id=$progId\">\n";

echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"5000000\" />\n";

echo "<p>\n";
echo "<label for=\"csvUploadFile\">CSV File</label>\n";
echo "<input type=\"file\" name=\"userfile\" />";
echo "</p>\n";

echo "<p>\n";
echo "<input type=\"submit\" name=\"uploadCsv\" value=\"Upload CSV\" />\n";
echo "</p>\n";

echo "</form>\n";

echo "</div>\n";

htmlFooters();

?>