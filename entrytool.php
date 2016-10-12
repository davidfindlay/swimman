<?php
require_once("includes/setup.php");
require_once("includes/classes/Club.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetSelector.php");
require_once("includes/classes/MeetEntry.php");
require_once("includes/classes/MeetEvent.php");
require_once("includes/classes/MeetEntryEvent.php");
require_once("includes/classes/EntryChecker.php");

// Send report to club


// Validate entries
if (isset($_POST['sendentries'])) {

	$uploaddir = '/home/masters-tmp';
	$uploadfile = $uploaddir . '/' . basename($_FILES['userfile']['name']);
	$uploadname = $_FILES['userfile']['name'];
	$enteringClub = $_POST['clubId'];
	
	$meetId = mysql_real_escape_string($_POST['meetId']);
	
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
	
		$entryChecker = new EntryChecker();
		
		if ($entryChecker->loadFile($uploadfile)) {
	
			$entryChecker->processFile($meetId, $enteringClub);
			
			$errorCount = count($entryChecker->getErrors());
			$memErrorCount = count($entryChecker->getMemberErrors());
			$eventErrorCount = count($entryChecker->getEventErrors());
			
			$errors = $entryChecker->getErrors();
			$memberErrors = $entryChecker->getMemberErrors();
			$entryErrors = $entryChecker->getEventErrors();
			
			if (count($errors) > 0) {
			
				foreach ($errors as $e) {
			
					echo "<div class=\"entrytool_error\">";
					echo "<table border=\"0\"><tr><td>\n";
					echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
					echo "</td><td><h3>\n";
			
					echo $e->getTitle();
			
					echo "</h3></td></tr></table>\n";
					echo "<p>";
			
					echo $e->getDesc();
			
					echo "</p>\n";
					echo "</div>\n";
			
				}
			
			}
			
			// Show Member Errors
			if (count($memberErrors) > 0) {
			
				foreach ($memberErrors as $e) {
			
					echo "<div class=\"entrytool_error\">";
					echo "<table border=\"0\"><tr><td>\n";
					echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
					echo "</td><td><h3>\n";
			
					echo $e->getTitle();
			
					echo "</h3></td></tr></table>\n";
					echo $e->getDesc();
					echo "</div>\n";
			
				}
			
			}
			
			// Show Member Errors
			if (count($entryErrors) > 0) {
			
				foreach ($entryErrors as $e) {
			
					echo "<div class=\"entrytool_error\">";
					echo "<table border=\"0\"><tr><td>\n";
					echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
					echo "</td><td><h3>\n";
			
					echo $e->getTitle();
			
					echo "</h3></td></tr></table>\n";
					echo "<p>";
			
					echo $e->getDesc();
			
					echo "</p>\n";
					echo "</div>\n";
			
				}
			
			}
			
			// Create table of entry data
			echo "<h3>\n";
			echo "List of Entries:\n";
			echo "</h3>\n";
			
			echo "<p>\n";
			
			echo "<table>\n";
			
			echo "<thead>\n";
			echo "<tr>\n";
			echo "<th>\n";
			echo "Entrant:\n";
			echo "</th>\n";
			echo "<th>\n";
			echo "Events:\n";
			echo "</th>\n";
			echo "<th>\n";
			echo "Number of Events:\n";
			echo "</th>\n";
			echo "<th>\n";
			echo "Entry Check Result:\n";
			echo "</th>\n";
			
			echo "</tr>\n";
			echo "</thead>\n";
			
			echo "<tbody>\n";
			
			// Step through list of entrant
			$entryList = $entryChecker->getEntries();
			
			if (count($entryList) > 0) {
			
				foreach ($entryList as $e) {
			
					echo "<tr>\n";
			
					echo "<td>\n";
					$entrantName = $e->getEntrantName();
					echo $entrantName;
					echo "</td>\n";
			
					echo "<td>\n";
					echo $e->getEventList();
					echo "</td>\n";
			
					echo "<td style=\"text-align: center;\">\n";
					echo $e->getNumEntries();
					echo "</td>\n";
			
					echo "<td style=\"text-align: center;\">\n";
			
					// Get list of any errors for this user
					$memErrCheck = 0;
			
					if (count($memberErrors) > 0) {
			
						foreach ($memberErrors as $e) {
			
							$errMemName = $e->getEntrantName();
			
							if ($errMemName == $entrantName) {
									
								$memErrCheck++;
									
							}
			
						}
							
					}
			
					if (count($entryErrors) > 0) {
			
						foreach ($entryErrors as $e) {
			
							$errMemName = $e->getEntrantName();
			
							if ($errMemName == $entrantName) {
			
								$memErrCheck++;
							}
			
						}
							
					}
			
					if ($memErrCheck == 0) {
							
						echo "Ok";
							
					} else {
							
						echo $memErrCheck . " errors found!\n";
							
					}
			
					echo "</td>\n";
			
					echo "</tr>\n";
			
				}
			
			}
			
			echo "</tbody>\n";
			
			echo "</table>\n";
			
			echo "</p>\n";
			
			$totalErrors = count($errors) + count($memberErrors) + count($entryErrors);
			
			if ($totalErrors == 0) {
			
				echo "<p>\n";
			
				echo "No errors have been found in your Team Manager entry file have been found. ";
				echo "You are now ready to submit it! Click Submit Entry to complete this process.";
			
				echo "</p>\n";
				echo "<form method=\"post\" name=\"ecSubmit\">\n";
			
				echo "<p>\n";
				echo "<input type=\"submit\" name=\"ecSubmitEntry\" value=\"Submit Entries\" />\n";
				echo "</p>\n";
				echo "</form>\n";
			
			} else {
			
				echo "<p>\n";
				echo $totalErrors . " errors have been found in your Team Manager entry file. Please ";
				echo "correct these before submitting using the instructions above. Once these have been ";
				echo "corrected, please resubmit your entry.\n";
				echo "</p>\n";
			
			}
				
			
		}
		
	}

}

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
echo "<html>\n";
echo "<head>\n";
	
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style/screen.css\">\n";
		
echo "<title>MSQ Entry Checker</title>\n";
	
echo "</head>\n";
	
echo "<body>\n";

echo "<div id=\"main\">\n";

?>

<h2>Entry Checker</h2>

<p>
This tool allows you to check your Team Manager entry file before sending to the club organising the meet. Ensuring you
check your entry and correct any errors the tool finds will reduce the amount of work the club running the meet will 
have to do to accept your entry. It also makes it easier for the meet results to be uploaded to the Results Portal at the
end of the meet. 
</p>

<p>
To use the tool, please select the meet that the entry is for, then select Browse and select the entry file for upload. If
you have any difficulties using this tool or have any comments, please send them to the <a href="mailto:recorder@mastersswimmingqld.org.au">Director of Recording</a>.
</p>

<form enctype="multipart/form-data" method="post">
<p>
<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
<?php
echo "<label>Meet: </label>\n";

$meetSel = new MeetSelector;
$meetSel->setName("meetId");

if (isset($_POST['meetId'])) {

	$meet = $_POST['meetId'];
	$meetSel->selected($meet);

}

$meetSel->output();

?><br />
<label>Club: </label> 

<?php 

$clubList = $GLOBALS['db']->getAll("SELECT * FROM clubs;");
db_checkerrors($clubList);

echo "<select name=\"clubId\">\n";

foreach ($clubList as $c) {

	$cId = $c[0];
	$cName = $c[2];

	echo "<option value=\"$cId\">$cName</option>\n";

}

?>

<label>TM Entry File: </label>
<input type="file" name="userfile" />
</p>
<input type="submit" name="sendentries" value="Check Entry" />

</form>

<?php 

echo "</div>\n"; // main div

htmlFooters();

?>