<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
checkLogin();

$progId = mysql_real_escape_string($_GET['id']);
$progDetails = $GLOBALS['db']->getRow("SELECT * FROM performance_programs WHERE id = '$progId';");
db_checkerrors($progDetails);
$ageSet = $progDetails[3];

if (isset($_POST['importsubmit'])) {

	$uploaddir = $GLOBALS['home_dir'] . '/masters-tmp';
	$uploadfile = $uploaddir . '/' . basename($_FILES['userfile']['name']);

	$progId = $_GET['id'];

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {

		// Get first row to determine which column is which age group and level
		$csvFile = fopen($uploadfile, "r");
		$firstRow = 1;
		$colAgeGroups = array();
		$colLevels = array();
		
		while (!feof($csvFile)) {
		
			$csvEntry = fgetcsv($csvFile);
			
			if (trim($csvEntry[1]) != "") {
			
				if ($firstRow == 1) {
					
					$colAgeGroups[] = 0;
					$colLevels[] = 0;
					
					// There are 64 age group/levels per gender
					for ($i = 1; $i <= 128; $i++) {
						
						$colTitle = explode(' ', trim($csvEntry[$i]));
						$colAgeGroup = mysql_real_escape_string($colTitle[0] . ' ' . $colTitle[1]);
						$colLevel = $colTitle[2];
						
						// Get the age group id
						$nextAgeGroup = $GLOBALS['db']->getOne("SELECT id FROM age_groups WHERE 
								age_groups.set = '$ageSet' AND groupname = '$colAgeGroup';");
						db_checkerrors($nextAgeGroup);
						$colAgeGroups[] = $nextAgeGroup;
						
						$nextLevel = $GLOBALS['db']->getOne("SELECT id FROM performance_programs_levels 
								WHERE perf_prog_id = '$progId' AND levelname = '$colLevel';");
						db_checkerrors($nextLevel);
						$colLevels[] = $nextLevel;
						
					}
					
					// Reset first row indicator
					$firstRow = 0;
					
				} else {
					
					// For other rows, first identify the performance program event related to the item
					$eventTitle = explode(' ', trim($csvEntry[0]));
					
					if (count($eventTitle) == 3) {
					
						$eventDisc = $eventTitle[0];
						$eventDist = $eventTitle[1] . ' ' . $eventTitle[2];
						
					} elseif (count($eventTitle) == 4) {
						
						$eventDisc = $eventTitle[0] . ' ' . $eventTitle[1];
						$eventDist = $eventTitle[2] . ' ' . $eventTitle[3];
						
					}
					
					$eventDiscId = $GLOBALS['db']->getOne("SELECT id FROM event_disciplines 
							WHERE discipline = '$eventDisc';");
					db_checkerrors($eventDiscId);
					
					$eventDistId = $GLOBALS['db']->getOne("SELECT id FROM event_distances 
							WHERE distance = '$eventDist';");
					db_checkerrors($eventDistId);
					
					//echo "$eventDiscId - $eventDistId<br />\n";
					
					for ($i = 1; $i <= 128; $i++) {
						
						$timeStd = $csvEntry[$i];
						$curAge = $colAgeGroups[$i];
						$curLevel = $colLevels[$i];
						
						// Reformat time standard into seconds
						if (strpos($timeStd, ':') !== FALSE) {
								
							$stArray = explode(':', $timeStd);
							
							if (count($stArray) == 3) {
								
								$secTimeStd = (floatval($stArray[0]) * 60 * 60) + (floatval($stArray[1]) * 60) +
									floatval($stArray[2]);
								
							} else {
							
								$secTimeStd = (floatval($stArray[0]) * 60) + floatval($stArray[1]);
								
							}
								
						} else {
								
							$secTimeStd = floatval($timeStd);
								
						}
						
						$perfEventId = $GLOBALS['db']->getOne("SELECT id FROM performance_programs_events
							WHERE discipline = '$eventDiscId' AND distance = '$eventDistId';");
						db_checkerrors($perfEventId);
						
						// Check if time standard already exists
						$existsCheck = $GLOBALS['db']->getRow("SELECT * FROM performance_programs_stds 
								WHERE perf_prog_id = '$progId' AND age_group = '$curAge' 
								AND level = '$curLevel' AND perf_prog_event = '$perfEventId';");
						db_checkerrors($existsCheck);
						
						if (isset($existsCheck)) {
							
							// Update existing time standard
							$update = $GLOBALS['db']->query("UPDATE performance_programs_stds 
									SET hightime = '$secTimeStd' WHERE perf_prog_id = '$progId' AND
									age_group = '$curAge' AND level = '$curLevel' 
									AND perf_prog_event = '$perfEventId';");
							db_checkerrors($update);
							
							echo "Updating perf_prog_event = $perfEventId $secTimeStd<br />\n";
							
						} else {
							
							// Add new time standard
							$insert = $GLOBALS['db']->query("INSERT INTO performance_programs_stds 
									(perf_prog_id, age_group, level, perf_prog_event, hightime) 
									VALUES ('$progId', '$curAge', '$curLevel', '$perfEventId', '$secTimeStd');");
							db_checkerrors($insert);
							
							echo "Inserting perf_prog_event = $perfEventId $secTimeStd<br />\n";
							
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

echo "<h2>Import Time Sheet</h2>\n";

echo "<p>\n";
echo "Import a Comma Separated Value file containing columns of age group/levels and rows of events.\n";
echo "</p>\n";

echo "<form enctype=\"multipart/form-data\" method=\"post\" action=\"perfprogimport.php?id=$progId\">\n";

echo "<p>\n";
echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"5000000\" />\n";

echo "<label for=\"uploadfile\">Upload CSV File:</label>\n";
echo "<input type=\"file\" name=\"userfile\" /><br />\n";
echo "<input type=\"submit\" name=\"importsubmit\" value=\"Import CSV File\" />\n";

echo "</p>\n";

echo "</form>\n";

echo "</div>\n";

htmlFooters();

?>