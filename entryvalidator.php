<?php
require_once("includes/setup.php");
require_once("includes/classes/Club.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetSelector.php");
require_once("includes/classes/MeetEntry.php");
require_once("includes/classes/MeetEntryEvent.php");

if (isset($_POST['sendentries'])) {

	$uploaddir = '/home/masters-tmp';
	$uploadfile = $uploaddir . '/' . basename($_FILES['userfile']['name']);
	$uploadname = $_FILES['userfile']['name'];
	
	$meetId = mysql_real_escape_string($_POST['meetId']);
	
	$meetDetails = new Meet();
	$meetDetails->loadMeet($meetId);
	$meetDate = $meetDetails->getStartDate();
	$meetName = $meetDetails->getName();
	$meetMax = $meetDetails->getMax();

	$cl2file = '';
	$hy3file = '';
	$curEntrant = 0;
	
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
	
		if (strtolower(substr($uploadname, (strlen($uploadname) - 3), 3)) == "zip") {
		
			// Handle Zip file
			$zip = new ZipArchive;
			// echo "ZIP file. <br />\n";
		
			if ($zip->open($uploadfile) === TRUE) {
		
				for ($i = 0; $i < $zip->numFiles; $i++) {
		
					$tmpName = $zip->getNameIndex($i);
					$tmpInfo = pathinfo($tmpName);
		
					// Find the MDB file in the zip
					if (strtolower(substr($tmpName, (strlen($tmpName) - 3), 3)) == "cl2") {
		
						$zip->extractTo($uploaddir, $tmpInfo['basename']);
						// echo "Extracted " . $tmpInfo['basename'] . ".<br />\n ";
						$cl2file = $uploaddir . '/' . $tmpInfo['basename'];
		
					}

					if (strtolower(substr($tmpName, (strlen($tmpName) - 3), 3)) == "hy3") {
		
						$zip->extractTo($uploaddir, $tmpInfo['basename']);
						// echo "Extracted " . $tmpInfo['basename'] . ".<br />\n ";
						$hy3file = $uploaddir . '/' . $tmpInfo['basename'];
		
					}
					
		
				}
				
				// Process files
				
				$hy3fh = fopen($hy3file, 'r');
				
				if (!$hy3fh) {
				
					echo "Unable to load $hy3file file.<br />\n";
				
				}
				
				echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
				echo "<html>\n";
				echo "<head>\n";
				echo "<meta http-equiv=\"Content-type\" content=\"text/html;charset=UTF-8\">\n";
				echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style/screen.css\">\n";
		
				echo "<title>MSQ Entry Checker</title>\n";
	
				echo "</head>\n";
	
				echo "<body>\n";
				
				echo "<div id=\"main\">\n";
				echo "<h1>Entry Checking Report</h1>\n";
				echo "<h3>Validating entries for $meetName on $meetDate.</h3>\n";
				
				$first = true;
				$qlCodeFound = false;
				$meetPeople = '';
				$meetEntries = '';
				$entryErrors = '';
				$errorCounter = 0;
				
				while (!feof($hy3fh)) {
					
					$line = fgets($hy3fh);
					
					// Check file is a valid HY3 file
					if ((substr($line, 0, 1) != "A") && ($first == true)) {
					
						echo "<p class=\"entrytool_error\">Not a valid HY3 File!</p>\n";
						break;
					
					} else {
						
						$first = false;
						
					}

					// Get Club Code
					if ((substr($line, 0, 1) == "C") && (!isset($clubId))) {
						
						$clubCode = substr($line, 2, 3);
						$clubDetails = new Club();
						$clubDetails->load($clubCode);
						$clubId = $clubDetails->getId();
						$clubName = $clubDetails->getName();
						
						echo "<p>Found club code $clubCode - $clubName.</p>\n";
						
					}
										
					// Check for QLQ LSC Code
					$qlqLscCode = " QL ";
					if ((substr($line, 0, 1) == "C") && (!$qlCodeFound)) {
					
						if (strstr($line, $qlqLscCode) != false) {
							
							$qlCodeFound = true;
							echo "<div class=\"entrytool_error\">\n";
							echo "<table border=\"0\"><tr><td>\n";
							echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
							echo "</td><td><h3>\n";
							echo "File contains QLQ LSC Code!</h3></td></tr></table>\n";
							echo "<p>\n";
							echo "<strong><i>What is this?</i></strong><br />\n";
							echo "The QLQ LSC code is caused by an incorrect setting in Team Manager and causes ";
							echo "incorrect club codes to be shown in meet results. There are two places in Team Manager this should be removed. Please see this link on how to ";
							echo "fix the problem: <a target=\"new\" href=\"https://assets.imgstg.com/assets/console/document/documents/Help-Recorder-Remove-Incorrect-ClubCode2012.pdf\">";
							echo "Remove Incorrect Club Code</a>.\n";
							echo "</p>\n";
							echo "<p>\n";
							echo "Please fix this problem and resubmit the file.";
							echo "</p>\n";
							echo "</div>\n";
							
							$errorCounter++;
						
						}
					
					}
					
					// Check member details
					if (substr($line, 0, 1) == "D") {
					
						$curEntrant++;  	// Entrant counter
						$entryErrors[] = '';
						$memberNum = '';
						$foundWrongCourse = 0; // Course checker
					
						$lastname = trim(substr($line, 8, 20));
						$firstname = trim(substr($line, 28, 20));
						$dob = substr($line, 92, 4) . '-' . substr($line, 88, 2) . '-' . substr($line, 90, 2);
						$memNumTest = trim(substr($line, 69, 6));
						
						// Does the entrant's date of birth shown in the file
						if (trim(substr($line, 92, 4)) == '') {
							
							echo "<div class=\"entrytool_error\">";
							echo "<table border=\"0\"><tr><td>\n";
							echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
							echo "</td><td><h3>\n";
							echo "Member $firstname $lastname does not have their date of birth entered!</h3></td></tr></table>\n";
							echo "<p>";
							echo "This member's entry does not list their date of birth. Please add the date of birth for \n";
							echo "this entrant in the Team Manager athlete entry for them. If this is not correctly listed \n";
							echo "it will be require manual follow up by the meet organisers to correctly match up this member.\n";
							echo "</p>\n";
							echo "</div>\n";
							
							$entryErrors[$curEntrant - 1] = "No Date of Birth";
							$errorCounter++;							
							
						}
						
						// Does the entrant have their MSA Registration number shown in the file
						if ($memNumTest != '') {
							
							echo "<div class=\"entrytool_error\">";
							echo "<table border=\"0\"><tr><td>\n";
							echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
							echo "</td><td><h3>\n";
							echo "Member $firstname $lastname has an MSA Registration Number in their entry!</h3></td></tr></table>\n";
							echo "<p>";
							echo "This member's entry shows their Masters Swimming Australia registration number. This causes \n";
							echo "issues for meet organisers checking a member's membership status and also can cause problems \n";
							echo "when the results are uploaded to the Results Portal. For instructions \n";
							echo "on how to fix this see <a href=\"https://assets.imgstg.com/assets/console/document/documents/Removing%20IDs%20from%20the%20Team%20Manager%20File.pdf\">Removing IDs from the Team Manager file</a>.\n";
							echo "</p>\n";
							echo "<p>Please remove this number and resubmit the file.</p>\n";
							echo "</div>\n";
							
							$entryErrors[$curEntrant - 1] = "MSA Number Included";
							$errorCounter++;
							
						}
						
						// echo "Checking member details of $firstname $lastname ($dob)...";
						
						$memberCheck = new Member();
						$mId = $memberCheck->find($firstname, $lastname, $dob, $clubId);
						
						if ($mId != false) {
						
							$memberCheck->loadId($mId);
							$memberNum = $memberCheck->getMSANumber();
							if ($memberCheck->getMembershipStatus($clubId, $meetDate)) {
							
								// Success, member is financial and up to date
								// echo " Details correct, member is financial. ";
								
							
							} else {
							
								echo "<div class=\"entrytool_error\">";
								echo "<table border=\"0\"><tr><td>\n";
								echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
								echo "</td><td><h3>\n";
								echo "Member $firstname $lastname is not financial!</h3></td></tr></table>\n";
								echo "<p>";
								echo "This member's registration has been checked against the IMG Membership Registration ";
								echo "Database and it has been indicated that the member is not currently a financial ";
								echo "member of $clubName.";
								echo "</p>\n";
								echo "<p>\n";
								echo "Please advise the member to renew their membership as soon as possible. ";
								echo "To be eligible to compete in this meet they need to be a financial member by the ";
								echo "closing date for entries.\n";
								echo "</p>\n";
								echo "<p>\n";
								echo "If you believe this message to be in error, please check the IMG Console for your club. ";
								echo "If this member has recently transferred or joined from another club, please check transfer ";
								echo "has been done or second claim membership has been registered. ";
								echo "</p>\n";
								echo "<p>\n";
								echo "More information on how to check details, activate and transfer members in the IMG Console is ";
								echo "available in the <a href=\"http://assets.imgstg.com/assets/console/document/documents/Registrar%20Help-book%20-%202012.pdf\">Registrar's Help Book</a>.\n";
								echo "</p>\n";							
								echo "</div>\n";
								
								$entryErrors[$curEntrant - 1] = "Unfinancial";
								$errorCounter++;
								
							}
							
						} else {
						
							// TODO try other combinations
							
							// Try surname and DOB
							$mId2 = $memberCheck->find('', $lastname, $dob, $clubId);
							
							if ($mId2 != false) {
								
								// First name does not match
								echo "<div class=\"entrytool_error\">";
								echo "<table border=\"0\"><tr><td>\n";
								echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
								echo "</td><td><h3>\n";
								echo "Unable to match $firstname $lastname's first name in the IMG Membership Registration database!</h3></td></tr></table>\n";
								echo "<p>";
								echo "<strong><i>Why is this?</i></strong><br>\n";
								echo "Possible reasons may be: <br>\n";
								echo "</p>\n";
								echo "<ul>\n";
								echo "<li>A shortened or preferred first name has been recorded in either the Members Portal or Team Manager.</li>\n";
								echo "<li>The first name recorded in the Members Portal or Team Manager is incorrect or spelt wrong.</li>\n";
								echo "</ul>\n";
								echo "<p>\n";
								echo "Please check the first name recorded on the IMG Console and Team Manager and correct these to match.\n";
								echo "</p>\n";
								
								echo "</div>\n";
									
								$entryErrors[$curEntrant - 1] = "Incorrect First Name";
								$errorCounter++;
								
							} else {
								
								$mId3 = $memberCheck->find($firstname, $lastname, '', $clubId);
								
								if ($mId3 != false) {
									
									echo "<div class=\"entrytool_error\">";
									echo "<table border=\"0\"><tr><td>\n";
									echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
									echo "</td><td><h3>\n";
									echo "Unable to match $firstname $lastname's date of birth in the IMG Membership Registration database!</h3></td></tr></table>\n";
									echo "<p>\n";
									echo "The date of birth listed for this entrant is incorrectly listed either in the IMG Member's Portal or your Team Manager entry file. \n";
									echo "Please check the date of birth recorded on the IMG Console and Team Manager and correct these to match.\n";
									echo "</p>\n";
									
									echo "</div>\n";
										
									$entryErrors[$curEntrant - 1] = "Incorrect Date of Birth";
									$errorCounter++;
									
								} else {
							
									// Member not found in database, may be guest or not registered
									echo "<div class=\"entrytool_error\">";
									echo "<table border=\"0\"><tr><td>\n";
									echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
									echo "</td><td><h3>\n";
									echo "Member $firstname $lastname has not been found in the IMG Membership Registration database!</h3></td></tr></table>\n";
									echo "<p>";
									echo "<strong><i>Why is this?</i></strong><br>\n";
									echo "Possible reasons may be: \n";
									echo "</p>\n";
									echo "<ul>\n";
									echo "<li>The first and last names in your Team Manager file do not match the member's name on the IMG Console</li>\n";
									echo "<li>The member has not yet joined or renewed on the Member Portal</li>\n";
									echo "<li>The member has not yet been activated by the $clubName Club Recorder</li>\n";
									echo "<li>If this is a second claim member or transfering member, this may not yet have been completed on the IMG Console</li>\n";
									echo "<li>This is a guest member</li>\n";
									echo "</ul>\n";
									echo "<p>\n";
									echo "More information on how to check details, activate and transfer members in the IMG Console is ";
									echo "available in the <a href=\"http://assets.imgstg.com/assets/console/document/documents/Registrar%20Help-book%20-%202012.pdf\">Registrar's Help Book</a>.\n";
									echo "</p>\n";
									// echo "<p>\n";
									// echo "If this is a guest member please certify this by clicking here: ";
									// echo "</p>\n";
									echo "</div>\n";
								
									$entryErrors[$curEntrant - 1] = "Guest or Not Registered";
									$errorCounter++;
									
								}
							
							}
							
						}
						
						if (isset($memberNum)) {
						
							$meetPeople[] = array($firstname, $lastname, $dob, $memberNum);
							
						} else {
							
							$meetPeople[] = array($firstname, $lastname, $dob, '');
							
						}
						
						// Create a Meet Entry 
						
						if (isset($mId)) {
						
							$meetEntries[] = new MeetEntry($mId, $clubId, $meetId);
								
						} else {
							
							$meetEntries[] = new MeetEntry('', $clubId, $meetId);
								
						}
							
							
					}
					
					// Create data for checking entries against entry rules
					if (substr($line, 0, 1) == 'E') {
					
						// Add this event entry to the latest meet entry
						$entryNum = count($meetEntries) - 1;

						$eventNumber = mysql_real_escape_string(trim(substr($line, 39, 2)));
						$eventSuffix = mysql_real_escape_string(substr($line, 41, 1));
						$eId = $GLOBALS['db']->getOne("SELECT id FROM meet_events WHERE meet_id = '$meetId' AND prognumber = '$eventNumber' AND progsuffix = '$eventSuffix';");
						db_checkerrors($eId);
						
						$seedtime = floatval(substr($line, 52, 7));
						
						// Check for times that will be converted
						$seedCourse = substr($line, 59, 1);
						$distanceCourse = $GLOBALS['db']->getOne("SELECT course FROM event_distances WHERE id = (SELECT distance FROM meet_events WHERE id = '$eId');");
						db_checkerrors($distanceCourse);
						
						if ((($seedCourse == "S") && ($distanceCourse != "SCM")) || (($seedCourse == "L") && ($distanceCourse != "LCM"))) {
							
							if ($foundWrongCourse == 0) {
								
								echo "<div class=\"entrytool_error\">";
								echo "<table border=\"0\"><tr><td>\n";
								echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
								echo "</td><td><h3>\n";
								echo "Member $firstname $lastname's entry has incorrect course!</h3></td></tr></table>\n";
								echo "<p>";
								echo "<strong><i>What does this mean?</i></strong><br>\n";
								echo "Custom times entered in Team Manager have either an S or an L following the time, indicating \n";
								echo "either a short or long course time. Your entry shows $seedCourse but this is a $distanceCourse event. \n";
								echo "When the Meet organisers load your entry the custom time will be converted to $distanceCourse. This \n";
								echo "may mean the entrant will be seeded incorrectly. Please check you are using the correct TM \n";
								echo "events file. If you are still having a problem with this contact the Meet Recorder or \n";
								echo "<a href=\"mailto:recorder@mastersswimmingqld.org.au\">Director of Recording</a>.\n";
								echo "</p>\n";
								echo "</div>\n";
								
								$entryErrors[$curEntrant - 1] = $entryErrors[$curEntrant - 1] . "<br />\nTimes will be converted";
								$errorCounter++;
								
								$foundWrongCourse = 1;
							
							}
							
						}
						
						$meetEntries[$entryNum]->addEvent($eId, $seedtime);
						
						
					}
					
					if ($curEntrant != 0) {

						if ($entryErrors[$curEntrant - 1] == '') {
							
							$entryErrors[$curEntrant - 1] = "Financial";
							
						}
						
					}
				
				}
												
				fclose($hy3fh);
				
				// Now completed delete the ZIP file
				// unlink($uploadfile);
				unlink($cl2file);
				unlink($hy3file);
				
				// Check entries against meet rules
				$peopleCount = 0;
				foreach ($meetEntries as $r) {
					
					$firstname = $meetPeople[$peopleCount][0];
					$lastname = $meetPeople[$peopleCount][1];
					
					// Check if entry has too many events
					$numEnts = $r->getNumEntries();
					if (($meetMax < $numEnts) && ($meetMax > 0)) {
						
						$entryErrors[$peopleCount] = $entryErrors[$peopleCount] . "\n<br />Too many events!\n";
						$errorCounter++;
						
						echo "<div class=\"entrytool_error\">";
						echo "<table border=\"0\"><tr><td>\n";
						echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
						echo "</td><td><h3>\n";
						echo "Member $firstname $lastname has entered in too many events!</h3></td></tr></table>\n";
						echo "<p>";
						echo "This event allows a maximum of $meetMax individual events per competitor, but ";
						echo "$firstname $lastname has $numEnts. Please amend this entry.\n";
						echo "</p>\n";
						echo "</div>\n";
						
						
					}
					
					// Check against meet group rules
					$groupFailures = $r->checkMeetGroups();
										
					if (is_array($groupFailures)) {
						
						$entryErrors[$peopleCount] = $entryErrors[$peopleCount] . "\n<br />Non-compliant!\n";
						$errorCounter++;
						
						echo "<div class=\"entrytool_error\">";
						echo "<table border=\"0\"><tr><td>\n";
						echo "<img class=\"entrytool_sign\" src=\"/swimman/images/warning_sign.png\" alt=\"Warning!\">\n";
						echo "</td><td><h3>\n";
						echo "Member $firstname $lastname's entry does not comply with meet rules!</h3></td>\n";
						echo "</tr></table>\n";
						echo "<p>";
												
						echo "$firstname $lastname's entry fails the following meet rules: <br />\n";
						
						echo "</p>\n";
						echo "<ul>\n";
						
						foreach ($groupFailures as $f) {
							
							// Get failure rule
							$meetRule = $GLOBALS['db']->getOne("SELECT meet_rules.rule FROM meet_rules, meet_rules_groups WHERE meet_rules.id = meet_rules_groups.rule_id AND meet_rules_groups.meet_events_groups_id = '$f';");
							db_checkerrors($meetRule);
							
							echo "<li>$meetRule</li>\n";
							
						}
						
						echo "</ul>\n";
						
						echo "<p>Please contact this member to correct their entry. Once updated please submit the file again</p>\n";
						
						echo "</div>\n";
						
					}
					
					
					$peopleCount++;
				}
				
				
				echo "<h3>Entries in this file: </h3>\n";
				
				$peopleCount = 0;
				
				echo "<table border=\"1\">\n";
				echo "<tr>\n";
				echo "<th>\n";
				echo "Name\n";
				echo "</th>\n";
				echo "<th>\n";
				echo "Date of Birth\n";
				echo "</th>\n";
				echo "<th>\n";
				echo "MSA Number\n";
				echo "</th>\n";
				echo "<th>\n";
				echo "Number of Events\n";
				echo "</th>\n";
				echo "<th>\n";
				echo "<div align=\"center\">\n";
				echo "Status\n";
				echo "</div>\n";
				echo "</th>\n";
				echo "</tr>\n";
								
				
				foreach ($meetEntries as $r) {
					
					$numEntries = $r->getNumEntries();
					
					echo "<tr>\n";
					echo "<td>\n";
					echo $meetPeople[$peopleCount][0] . ' ' . $meetPeople[$peopleCount][1];
					echo "</td>\n";
					echo "<td>\n";
					echo "<div align=\"center\">\n";
					echo $meetPeople[$peopleCount][2];
					echo "</div>\n";
					echo "</td>\n";
					echo "<td>\n";
					echo "<div align=\"center\">\n";
					echo $meetPeople[$peopleCount][3];
					echo "</div>\n";
					echo "</td>\n";
					echo "<td>\n";
					echo "<div align=\"center\">\n";
					echo $numEntries;
					echo "</div>\n";
					echo "</td>\n";
					echo "<td>\n";
					echo "<div align=\"center\">\n";
					echo $entryErrors[$peopleCount];
					echo "</div>\n";
					echo "</td>\n";
					
					echo "</tr>\n";
					
					$peopleCount++;
					
					
				}
				
				echo "</table>\n";
				
				echo "<h3>Recommendations: </h3>\n";
				
				if ($errorCounter > 0) {
					
					echo "<p>\n";
					echo "Your Team Manager entry file contains a total of $errorCounter errors. Please check and correct ";
					echo "all these errors before emailing your file to the meet organisers.\n";			
					echo "</p>\n";
					echo "<p>\n";
					echo "If the only entries showing errors are genuine guest members swimming at this meet as their first ";
					echo "meet you are ready to email the file.";
					echo "</p>\n";
					echo "<p>\n";
					echo "If you require any assistance in correcting the errors this validation test has detected please ";
					echo "read the error boxes above, check the documenation links or contact the <a href=\"mailto:recorder@mastersswimmingqld.org.au\">Director of Recording</a>.";
					echo "</p>\n";
					
				} else {
					
					echo "<p>\n";
					echo "Congratulations! Your Team Manager entry file has passed all validation test and is ready to be ";
					echo "sent to the meet organisers by email.\n";
					echo "</p>\n";
					
				}
				
				$errorList = implode(", ", $entryErrors);
				addLog("EntryChecker", "TM File checked for $clubCode, meet $meetId", "$errorCounter errors found. Errors: $errorList");
				
				echo "<form method=\"post\" action=\"entryvalidator.php\">\n";
				echo "<p>\n";
	
				echo "<input type=\"hidden\" name=\"meetId\" value=\"$meetId\">\n";
				
				// echo "<input type=\"submit\" name=\"submitReport\" value=\"Send Report to Club\"> \n";
				// echo "<input type=\"submit\" name=\"submitEntry\" value=\"Send Entry to Meet Organiser\"> \n";
				echo "<input type=\"submit\" name=\"submitNew\" value=\"Check Another Entry File\">\n";
				
				echo "</p>\n";
				echo "</form>\n";
				
				echo "</div>\n"; // main div
				
				htmlFooters();
				
				exit();
		
			} else {
		
				// Unable to open.
				echo "Unable to open ZIP file. \n";
		
			}
	
		}

	}

}

addlog("Access", "Accessed entryvalidator.php");

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
echo "<html>\n";
echo "<head>\n";
echo "<meta http-equiv=\"Content-type\" content=\"text/html;charset=UTF-8\">\n";
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

<form enctype="multipart/form-data" method="post" action="entryvalidator.php">

<table border="0">
<tr>
<td>
<input type="hidden" name="MAX_FILE_SIZE" value="5000000">
<?php
echo "<strong>Meet: </strong>\n";

echo "</td>\n";
echo "<td>\n";

$meetSel = new MeetSelector;
$meetSel->setName("meetId");
$meetSel->publishedOnly();

if (isset($_POST['meetId'])) {

	$meet = $_POST['meetId'];
	$meetSel->selected($meet);

} else {

	// Select the first one after today
	$firstMeetAfter = $GLOBALS['db']->getOne("SELECT id FROM meet WHERE startdate > now() LIMIT 1;");
	db_checkerrors($firstMeetAfter);
	
	$meetSel->selected($firstMeetAfter);

}

$meetSel->output();

echo "</td>\n";
echo "</tr>\n";

?>

<tr>
<td>
<strong>TM Entry File: </strong>
</td>
<td>
<input type="file" name="userfile">
</td>
</tr>
</table>
<input type="submit" name="sendentries" value="Check Entry">

</form>

<?php 

echo "</div>\n"; // main div

htmlFooters();

?>