<?php

require_once ("EntryError.php");

class EntryChecker {

	private $meet_id;
	private $club_id;
	private $meetDetails;	// Meet object
	
	private $errors;		// Array of errors relating to the whole entry
	private $memberErrors; 	// Array of errors relating to members details
	private $eventErrors; 	// Array of errors relating to entries to particular events
	
	private $entries; 		// Array of the entries in this file
	private $relays;		// Array of relays in this file
	
	private $arrMembers; 	// Array of members in this file - used to understand relays
	
	private $hy3fh;			// Filehandle
	private $hy3file;
	private $cl2file;

	// Loads the file
	public function loadFile($uploadfile) {

		$uploaddir = $GLOBALS['home_dir'] . '/masters-data/entries';
		
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
					$this->cl2file = $cl2file;

				}

				if (strtolower(substr($tmpName, (strlen($tmpName) - 3), 3)) == "hy3") {

					$zip->extractTo($uploaddir, $tmpInfo['basename']);
					// echo "Extracted " . $tmpInfo['basename'] . ".<br />\n ";
					$hy3file = $uploaddir . '/' . $tmpInfo['basename'];
					$this->hy3file = $hy3file;

				}
					

			}

			// Process files
			
			if (isset($this->hy3file)) {
			
				$this->hy3fh = fopen($this->hy3file, 'r');

				if (!$this->hy3fh) {

					echo "Unable to load $this->$hy3file file.<br />\n";
				
					addlog("Enter Manager", "Unable to load TM Entry File!", "Filename $this->hy3file.", $curUserId);

				} else {
				
					return true;
				
				}
				
			} else {
				
				echo "<div style=\"font-weight: bold; color: red;\">Unable to read meet entry file! Please check that you have uploaded the correct one. Your file name should usually contain the word \"Entries\". If your file name starts with \"Meet_Events\" you are trying to upload the wrong file!</div>\n";
				
				addlog("Enter Manager", "Unable to load Upload File!", "Filename $uploadfile.", $curUserId);
				
			}


		} else {

			// Unable to open.
			echo "Unable to open ZIP file. \n";

		}
		
		return false;

	}

	public function processFile() {
		
		// Setup process
		$first = true;
		$qlCodeFound = false;
		$meetPeople = '';
		$meetEntries = '';
		$entryErrors = '';
		$curEntrant = 0;
		$errorCounter = 0;
		
		error_reporting(E_ALL);
		
		while (!feof($this->hy3fh)) {
		
			$line = fgets($this->hy3fh);
				
			// Check file is a valid HY3 file
			if ($first == true) {
		
				$firstCheck = $this->checkFileType($line);

				if ($firstCheck == false) {
					
					$this->errors[] = new EntryError("inv_hy3file");
					
				}
				
				$first = false;
				
			}
			
			// Identify Meet
			$this->findMeetId($line);

			// Get Club Code	
			$this->findClubId($line);
		
			// Check for QLQ LSC Code
			$this->findLSCCode($line);

			// Check for missing date of birth
			$this->checkDobExists($line);
			
			// Check for MSA Number
			$this->checkMSAExists($line);
			
			// Check if we can find the member's details
			$this->findMember($line);
			
			// Get Entry Details
			if (substr($line, 0, 1) == 'E') {
		
				$eventNumber = mysql_real_escape_string(trim(substr($line, 39, 2)));
				$eventSuffix = mysql_real_escape_string(substr($line, 41, 1));
				$eId = $GLOBALS['db']->getOne("SELECT id FROM meet_events 
						WHERE meet_id = ? AND prognumber = ? AND progsuffix = ?;",
						array($this->meet_id, $eventNumber, $eventSuffix));
				db_checkerrors($eId);
				
				$seedtime = floatval(substr($line, 52, 7));
		
				// Check for times that will be converted
				$seedCourse = substr($line, 59, 1);
				$distanceCourse = $GLOBALS['db']->getOne("SELECT course FROM event_distances WHERE id = (SELECT distance FROM meet_events WHERE id = '$eId');");
				db_checkerrors($distanceCourse);
		
				if ((($seedCourse == "S") && ($distanceCourse != "SCM")) || (($seedCourse == "L") && ($distanceCourse != "LCM"))) {
		
					if (isset($distanceCourse)) {

						$this->memberErrors[] = new entryError('entrycourse', $this->entries[(count($this->entries) - 1)]->getEntrantName(), '', '', 'Event entries are in wrong course!');
						
					}
					
									
				}
				
				//echo "found entry<br />\n";
		
				$this->entries[(count($this->entries) - 1)]->addEvent($eId, $seedtime);
		
		
			}
			
			// Find relay entries
 			if (substr($line, 0, 2) == 'F1') {

 				$clubCode = substr($line, 2, 3);
 				$clubDet = new Club();
 				$clubDet->load($clubCode);
 				$cId = $clubDet->getId();
				
 				$relayLetter = substr($line, 7, 1);
 				$age = substr($line, 9, 3);
 				$progNum = substr($line, 39, 2);
 				$progSuf = strtolower(substr($line, 41, 1));
 				$distance = substr($line, 17, 4);
 				$discLetter = substr($line, 21, 1);
 				$seedtime = floatval(substr($line, 52, 7));
				
 				$eId = $GLOBALS['db']->getOne("SELECT id FROM meet_events WHERE meet_id = ? 
 						AND prognumber = ? AND progsuffix = ?;", 
 						array($this->meet_id, $progNum, $progSuf));
 				db_checkerrors($eId);
 				
 				$eventDetails = new MeetEvent();
 				$eventDetails->load($eId);
 				$gId = $eventDetails->getGender();
 				
 				$aid = $GLOBALS['db']->getOne("SELECT id FROM age_groups WHERE min = ? AND swimmers = 4
 						AND gender = ? AND age_groups.set = 1;", array($age, $gId));
 				db_checkerrors($aid);
 				
 				//echo "Found relay - Event $progNum$progSuf $clubCode Over $age($aid) Team $relayLetter<br />\n";
 				
 				// Create a new relay team
 				$this->relays[] = new RelayEntry();
 				$relayIndex = count($this->relays) - 1;
 				
 				// Set the details of this relay
 				$this->relays[$relayIndex]->setMeet($this->meet_id);
 				$this->relays[$relayIndex]->setClub($this->club_id);
 				$this->relays[$relayIndex]->setLetter($relayLetter);
 				$this->relays[$relayIndex]->setAgeGroup($aid);
 				$this->relays[$relayIndex]->setEvent($eId);
 				$this->relays[$relayIndex]->setSeedTime($seedtime);
 				
 				//echo $this->relays[$relayIndex]->getAgeGroup() . "<br />\n";
				
 			}
 			
 			// Find relay members
 			if (substr($line, 0, 2) == 'F3') {
 				
 				// Members of relay
 				$mem1 = intval(trim(substr($line, 6, 2)));
 				$mem2 = intval(trim(substr($line, 19, 2)));
 				$mem3 = intval(trim(substr($line, 32, 2)));
 				$mem4 = intval(trim(substr($line, 45, 2)));
 				
 				// Add the members to the current relay
 				$relayIndex = count($this->relays) - 1;
 				$memId1 = $this->getEntrantId($mem1);
 				$this->relays[$relayIndex]->addMember(1, $memId1);
 				$memId2 = $this->getEntrantId($mem2);
 				$this->relays[$relayIndex]->addMember(2, $memId2);
 				$memId3 = $this->getEntrantId($mem3);
 				$this->relays[$relayIndex]->addMember(3, $memId3);
 				$memId4 = $this->getEntrantId($mem4);
 				$this->relays[$relayIndex]->addMember(4, $memId4);
 				
 				//echo "$memId1 - $memId2 - $memId3 - $memId4<br />\n";
 				
 			}
 			
 			//print_r($this->relays);
		
		}
			
		//print_r($this->arrMembers);
		
		//echo "processing completed<br />\n";
		
		fclose($this->hy3fh);
		
		// Now completed delete the ZIP file
		// unlink($uploadfile);
		unlink($this->cl2file);
		unlink($this->hy3file);
		
		//print_r($this->entries);
		
		if (!isset($this->meet_id)) {
			
			echo "Unable to determine which meet this entry is for!<br />\n";
			
		} 
		
		$meetDate = $this->meetDetails->getStartDate();
		$meetName = $this->meetDetails->getName();
		$meetMax = $this->meetDetails->getMax();
		
		// Check entries against meet rules
		$rCounter = 0;
		foreach ($this->entries as $r) {
								
			// Check if entry has too many events
			$numEnts = $r->getNumEntries();
			
			if (($meetMax < $numEnts) && ($meetMax > 0)) {
		
				//echo (count($this->entries) - 1);
				//echo $this->entries[(count($this->entries) -1)]->getEntrantName();
				
				
				$this->eventErrors[] = new entryError('toomany', $this->entries[$rCounter]->getEntrantName(), '', '', array($meetMax, $numEnts));
				
				//$entryErrors[$peopleCount] = $entryErrors[$peopleCount] . "\n<br />Too many events!\n";
				//$errorCounter++;
		
			}
									
			// Check against meet group rules
			$groupFailures = $r->checkMeetGroups();
			
			if (is_array($groupFailures)) {
		
				//$entryErrors[$peopleCount] = $entryErrors[$peopleCount] . "\n<br />Non-compliant!\n";
				//$errorCounter++;
				
				$meetRules = array();
				
				foreach ($groupFailures as $f) {
			
					// Get failure rule
					$meetRules[] = $GLOBALS['db']->getOne("SELECT meet_rules.rule FROM meet_rules, meet_rules_groups WHERE meet_rules.id = meet_rules_groups.rule_id AND meet_rules_groups.meet_events_groups_id = '$f';");
					db_checkerrors($meetRules);
										
				}
				
				$this->eventErrors[] = new entryError('rules', $this->entries[$rCounter]->getEntrantName(), '', '', $meetRules);
				
			}	
			
			$rCounter++;	
		
		}
		
	}
	
	public function getClubId() {
		
		return $this->club_id;
		
	}
	
	public function getMeetId() {
		
		return $this->meet_id;
		
	}
	
	public function checkFileType($line) {
		
		// Check file is a valid HY3 file
		if (substr($line, 0, 1) != "A") {
		
			// Invalid file
			return false;
		
		} else {
		
			return true;
		
		}
		
	}
	
	public function findMeetId($line) {
		
		// Look for line starting with B, indicates Meet
		if (substr($line, 0, 2) == "B1" ) {
		
			$meetName = trim(substr($line, 2, 45));
			$meetDate = substr($line, 92, 8);
			$meetStart = substr($meetDate, 4, 4) . "-" . substr($meetDate, 0, 2) . "-" . substr($meetDate, 2, 2);
			
			// Find a matching swim meet
			$meetId = $GLOBALS['db']->getOne("SELECT id FROM meet WHERE meetname = ? AND 
					startdate = ?;", array($meetName, $meetStart));
			db_checkerrors($meetId);
			
			$this->meet_id = $meetId;
		
			$this->meetDetails = new Meet();
			$this->meetDetails->loadMeet($this->meet_id);
			
		}
		
	}
	
	public function findClubId($line) {
		
		// Look for line starting with C, indicates Club Code
		if (substr($line, 0, 2) == "C1") {
		
			$clubCode = substr($line, 2, 3);
			$clubDetails = new Club();
			$clubDetails->load($clubCode);
			
			$this->club_id = $clubDetails->getId();
			return $clubDetails->getId();
		
		}
		
		return false;
		
	}
	
	// Check for LSC Codes
	public function findLSCCode($line) {
		
		$qlqLscCode = " QL ";
		if (substr($line, 0, 1) == "C") {
		
			if (strstr($line, $qlqLscCode) != false) {
		
				$this->errors[] = new EntryError("lsc_qlq");
	
			}
		
		}
		
		$bsLscCode = " BS ";
		if (substr($line, 0, 1) == "C") {
		
			if (strstr($line, $qlqLscCode) != false) {
		
				$this->errors[] = new EntryError("lsc_bs");
		
			}
		
		}	
		
	}
	
	public function checkDobExists($line) {
		
		if (substr($line, 0, 1) == "D") {
			
			$dob = substr($line, 92, 4) . '-' . substr($line, 88, 2) . '-' . substr($line, 90, 2);

			// Does the entrant's date of birth shown in the file
			if (trim(substr($line, 92, 4)) == '') {

				$lastname = trim(substr($line, 8, 20));
				$firstname = trim(substr($line, 28, 20));
				$this->memberErrors[] = new EntryError("no_dob", $firstname . ' ' . $lastname);
		
			}
			
		}
		
	}

	public function checkMSAExists($line) {
		
		if (substr($line, 0, 1) == "D") {
		
			$lastname = trim(substr($line, 8, 20));
			$firstname = trim(substr($line, 28, 20));
			$memNumTest = trim(substr($line, 69, 6));
			
			if ($memNumTest != '') {
				
				$this->memberErrors[] = new EntryError("msa_number", $firstname . ' ' . $lastname);
				
			}
			
		}
	}
	
	public function findMember($line) {
		
		if (substr($line, 0, 1) == "D") {
		
			$mId = false;
			
			$lastname = trim(substr($line, 8, 20));
			$firstname = trim(substr($line, 28, 20));
			$dob = substr($line, 92, 4) . '-' . substr($line, 88, 2) . '-' . substr($line, 90, 2);
			$memNumTest = trim(substr($line, 69, 6));
			
			$entrantNum = intval(trim(substr($line, 6, 2)));
			//echo "entrantNum = $entrantNum<br />\n";
			
			$memberCheck = new Member();
			$mId = $memberCheck->find($firstname, $lastname, $dob, $this->club_id);
			
			//echo "checking $firstname, $lastname, $dob, $this->club_id<br />\n";
			
			if ($mId != false) {
			
				// Got Member number on first try
				$memberCheck->loadId($mId);
				$memberNum = $memberCheck->getMSANumber();
			
			} else {
			
				// TODO try other combinations
			
				// Try surname and DOB
				$mId = $memberCheck->find('', $lastname, $dob, $this->club_id);
					
				if ($mId != false) {
			
					// First name does not match
					$this->memberErrors[] = new entryError('first_name', $firstname . ' ' . $lastname, '', '', 
									'IMG database shows first name as ' . $memberCheck->getFirstname());
					//$errorCounter++;
			
				} else {
			
					$mId = $memberCheck->find($firstname, $lastname, '', $this->club_id);
			
					if ($mId != false) {
			
						$this->memberErrors[] = new entryError('dob', $firstname . ' ' . $lastname, '', '',
									'IMG database shows dob as ' . $memberCheck->getDob());
			
					} else {
			
						// Member not found in database, may be guest or not registered
						$this->memberErrors[] = new entryError('guestornot', $firstname . ' ' . $lastname);
								
					}
			
				}
			
			}
			
			// Create entry for this person
			if ($mId != false) {
			
				// Create an entry for this member
				$this->entries[] = new MeetEntry($mId, $this->club_id, $this->meet_id);
				$this->entries[(count($this->entries) - 1)]->setEntrantName($firstname . ' ' . $lastname);
				
				// Check Member is Financial
				$finCheck = $this->checkFinancial($mId, $this->club_id, $this->meet_id);
				
				if ($finCheck == false) {
				
					$this->memberErrors[] = new EntryError('unfinancial', $firstname . ' ' . $lastname);
				
				}
				
				// Add person to relay array
				$this->addEntrantNum($entrantNum, $lastname, $mId);
			
			} else {
			
				// Entry for non member
				$this->entries[] = new MeetEntry('', $this->club_id, $this->meet_id);
				$this->entries[(count($this->entries) - 1)]->setEntrantName($firstname . ' ' . $lastname);
				
				// Add person to relay array
				$this->addEntrantNum($entrantNum, $lastname);
			
			}
			
			
			
			//echo count($this->entries) - 1;
			//echo " $firstname $lastname<br/>\n";
			
			return (count($this->entries) - 1);
			
		} else {
			
			//return false;
			
		}
		
	}
	
	// Track which member is linked to which entrant number in the Team Manager file
	public function addEntrantNum($entrantNum, $lastname, $mId = '') {
		
		$this->arrMembers[] = array($entrantNum, $lastname, $mId);
		
	}
	
	// Get the Member Id associated with this Entrant Number
	public function getEntrantId($entrantId) {
		
		$memberId = 0;
		
		foreach ($this->arrMembers as $m) {
			
			$mEntrant = $m[0];
			$mId = $m[2];
			
			if ($mEntrant == $entrantId) {
				
				$memberId = $mId;
				
				//echo "Found $mEntrant is $memberId\n";
				
			}
			
		}
		
		return $memberId;
		
	}
	
	public function checkFinancial($mId, $clubId, $meetId) {
	
		$memberCheck = new Member();
		$memberCheck->loadId($mId);
		
		$meetDetails = new Meet();
		$meetDetails->loadMeet($meetId);
		$meetDate = $meetDetails->getStartDate();
		
		// Member now found, check financial status
		if ($memberCheck->getMembershipStatus($clubId, $meetDate)) {
	
			// Success, member is financial and up to date
			// echo " Details correct, member is financial. ";
			return true;
	
		} else {
	
			return false;
	
		}		
		
	}
	
	public function getErrors() {
		
		return $this->errors;
		
	}
	
	public function getMemberErrors() {
		
		return $this->memberErrors;
		
	}
	
	public function getEventErrors() {
	
		return $this->eventErrors;
	
	}
	
	public function getEntries() {
		
		return $this->entries;
		
	}
	
	public function getRelays() {
	
		return $this->relays;
	
	}
	
}


?>