<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Club.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");
require_once("includes/classes/MeetSelector.php");
checkLogin();

if (isset($_POST['importtmsubmit'])) {

	$uploaddir = $GLOBALS['home_dir'] . '/masters-tmp';
	$uploadfile = $uploaddir . '/' . basename($_FILES['userfile']['name']);

	$meetId = $_POST['meetId'];

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
	
		// Handle Zip file
		$zip = new ZipArchive;
		// echo "ZIP file. <br />\n";

		htmlHeaders("Swimming Management System - Import IMG Meet");
		
		echo "<h1>Swimming Management System</h1>\n";
		echo "<h2>Import Team Manager Event File</h2>\n";
			
		echo "<div id=\"main\">\n";
		echo "<p>\n";
		
		if ($zip->open($uploadfile) === TRUE) {
		
			for ($i = 0; $i < $zip->numFiles; $i++) {
		
				$tmpName = $zip->getNameIndex($i);
				$tmpInfo = pathinfo($tmpName);
		
				// Find the MDB file in the zip
				if (strtolower(substr($tmpName, (strlen($tmpName) - 3), 3)) == "ev3") {
		
					$zip->extractTo($uploaddir, $tmpInfo['basename']);
					// echo "Extracted " . $tmpInfo['basename'] . ".<br />\n ";
					$ev3file = $uploaddir . '/' . $tmpInfo['basename'];
		
				}
		
				if (strtolower(substr($tmpName, (strlen($tmpName) - 3), 3)) == "hyv") {
		
					$zip->extractTo($uploaddir, $tmpInfo['basename']);
					// echo "Extracted " . $tmpInfo['basename'] . ".<br />\n ";
					$hyvfile = $uploaddir . '/' . $tmpInfo['basename'];
		
				}
					
		
			}
		
			// Process files
		
			$hyvfh = fopen($hyvfile, 'r');
		
			if (!$hyvfh) {
		
				echo "Unable to load $hyvfile file.<br />\n";
		
			}
			
			
			
			echo "Processing HYV File...<br />\n";
			
			$lineCount = 0;
			$eventCount = 0;
			
			while (!feof($hyvfh)) {
					
				$csvEntry = fgetcsv($hyvfh, 100, ';'); 
				
				// First line
				if ($lineCount == 0) {
					
					$meetName = $csvEntry[0];
					$meetStartDate = $csvEntry[1];
					$meetEndDate = $csvEntry[2];
					$ageUpDate = $csvEntry[3];
					$meetCourse = $csvEntry[4];
					$meetLocation = $csvEntry[5];
					
					echo "<strong>Meet Name:</strong> $meetName<br />\n";
					echo "<strong>Start Date:</strong> $meetStartDate<br />\n";
					echo "<strong>End Date:</strong> $meetEndDate<br />\n";
					
					if ($ageUpDate != date('12/31/Y')) {
					
						echo "<strong>Age Up Date:</strong> $ageUpDate - <strong><i>Warning: Bad Age Up Date!</i></strong><br />\n";
						
					} else {
						
						echo "<strong>Age Up Date:</strong> $ageUpDate<br />\n";
						
					}
					
					echo "<strong>Course Type:</strong> $meetCourse<br />\n";
					echo "<strong>Location:</strong> $meetLocation<br />\n";
					
					$_SESSION['imMeet'] = new Meet();
					
					$_SESSION['imMeet']->setName($meetName);
					$_SESSION['imMeet']->setDates($meetStartDate, $meetEndDate);
					$_SESSION['imMeet']->setLocation($meetLocation);
					
				} else {
					
					if ($csvEntry[1] == 'F') {
						
						// Event Number
						$eventNumber = $csvEntry[0];
						$eventNum = preg_replace('/[a-zA-Z]/', '', $eventNumber);
						$eventSuff = preg_replace('/(\d*)/', '', $eventNumber);
						
						// Type of event
						if ($csvEntry[3] == 'I') {
							
							$eventType = "Seeded Individual Mixed Finals";
						
						}
						
						if ($csvEntry[3] == 'R') {
							
							switch ($csvEntry[2]) {

							 	case 'X':
									$eventType = "Seeded Mixed Relay Finals";
									break;
								
							 	case 'M':
							 		$eventType = "Seeded Mens Relay Finals";
							 		break;
							 		
							 	case 'F':
							 		$eventType = "Seeded Womens Relay Finals";
							 		break;
								
							}
							
						}
						
						// Event discipline
						switch($csvEntry[7]) {
							
							case '1':
								$stroke = 'Freestyle';
								break;
							
							case '2':
								$stroke = 'Backstroke';
								break;
								
							case '3':
								$stroke = 'Breaststroke';
								break;
								
							case '4':
								$stroke = 'Butterfly';
								break;
								
							case '5':
								if ($csvEntry[3] == 'R') {
									$stroke = 'Medley';
								} 
								if ($csvEntry[3] == 'I') {
									$stroke = 'Individual Medley';									
								}
								break;
							
						}
						
						// Event distance
						if ($csvEntry[3] == 'I') {
							
							$distance = $csvEntry[6];
							$legs = 1;
							
						}
						
						if ($csvEntry[3] == 'R') {
							
							$distance = $csvEntry[6] / 4;
							$legs = 4;
							
						}
						
						if ($meetCourse == 'S') {
							
							$dCourse = "SCM";
							
						}
						
						if ($meetCourse == 'L') {
							
							$dCourse = "LCM";
							
						}
						
						echo "<strong>Event $eventNumber:</strong> $eventType, ";
						
						if ($legs > 1) {
							
							echo $legs . "x";
							
						}
						
						echo "$distance $stroke.<br />\n";
						
						// Add item
						$addEvent = new MeetEvent();
						$addEvent->setMeetId($meetId);
						$addEvent->setProgram($eventNum, $eventSuff);
						$addEvent->setTypeName($eventType);
						$addEvent->setDisciplineName($stroke);
						$addEvent->setDistanceM($distance, $dCourse);
						$addEvent->setLegs($legs);
						$success = $addEvent->create();
						
						if ($success == false) {
							
							echo "Event $eventNum$eventSuff already exists! Not created.<br />\n";						
							
						}
						
						$eventCount++;
						
					}
					
				}
				
				$lineCount++;
				
			}
			
			echo "Number of Events: $eventCount<br />\n";
			
			fclose($hyvfh);
			
			// Now completed delete the ZIP file
			// unlink($uploadfile);
			unlink($ev3file);
			unlink($hyvfile);
			
			echo "</p>\n";
			
			echo "<p>\n";
			echo "<form action=\"meetbuilder.php\" method=\"get\">\n";
			echo "<input type=\"hidden\" name=\"meetId\" value=\"$meetId\" />\n";
			echo "<input type=\"submit\" name=\"submit\" value=\"Edit Meet\" />\n";			
			echo "</form>\n";			
			echo "</p>\n";
			
			echo "</div>\n";
			
			htmlFooters();
			exit;
			
			
		} else {
			
			// Unable to open.
			echo "Unable to open ZIP file. \n";
			
		}
			
	}

}

htmlHeaders("Swimming Management System - Import IMG Meet");

sidebarMenu();

echo "<div id=\"main\">\n";

?>

<h1>Swimming Management System</h1>
<h2>Import Team Manager Meet File</h2>

<form enctype="multipart/form-data" method="post">

<p>
<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
<?php

if (isset($_GET['meet'])) {

	$meetId = $_GET['meet'];
	
	echo "<input type=\"hidden\" name=\"meetId\" value=\"$meetId\" />\n";

} else {

	echo "<label>Meet: </label>\n";

	$meetSel = new MeetSelector;
	$meetSel->setName("meetId");
	$meetSel->setStartDate(date('Y-01-01'));
	$meetSel->output();

	echo "<br />\n";
	
}

?>

<strong>TM File : </strong> <input type="file" name="userfile" /><br />
<input type="submit" name="importtmsubmit" value="Import TM File" />
</p>
</form>

<?php 

echo "</div>\n"; // main div

htmlFooters();


?>