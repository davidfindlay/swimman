<?php

class MeetProgram {
	
	private $meetId;
	private $filename;
	private $updated;
	private $version;
	private $uploaddir = '/home/masters-eprogram/';
	private $url;
	private $qldMemberOnly = false; 
	
	public function load() {

		$getProgram = $GLOBALS['db']->getRow("SELECT * FROM meet_programs WHERE meet_id = '$this->meetId';");
		db_checkerrors($getProgram);
		
		if (isset($getProgram)) {
				
			$this->filename = $getProgram[1];
			$this->updated = $getProgram[2];
			$this->version = $getProgram[3];
			
			return true;
				
		} else {

			return false;
		}
		
		
	} 
	
	// Returns true if a Meet Program has been uploaded for this meet,
	// Returns false if no program has been uploaded yet
	public function exists() {
		
		$progExists = $GLOBALS['db']->getRow("SELECT * FROM meet_programs WHERE meet_id = '$this->meetId';");
		db_checkerrors($progExists);
		
		if (count($progExists) > 0) {
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
	
	public function update() {
		
		// Check if this Meet Program exists yet
		$getProgram = $GLOBALS['db']->getRow("SELECT * FROM meet_programs WHERE meet_id = '$this->meetId';");
		db_checkerrors($getProgram);
		
		if (isset($getProgram)) {

			$v = $getProgram[3] + 1;
			
			$update = $GLOBALS['db']->query("UPDATE meet_programs SET filename = '$this->filename', version = '$v', updated = now() WHERE meet_id = '$this->meetId';");
			db_checkerrors($update);

			$this->load();
			
			//echo "Meet program updated. \n";
			
		} else {
			
			$this->create();
			
		}
		
	}
	
	// Loads data from existing member variables
	private function create() {

		$this->version = 1;
		$insert = $GLOBALS['db']->query("INSERT INTO meet_programs (meet_id, filename, version) VALUES ('$this->meetId', '$this->filename', '$this->version');");
		db_checkerrors($insert);
		
		echo "Meet Program Created. \n";
		
	}
	
	// Import required data into meet tables
	public function import($uploadname) {
		
		$uploadfile = $this->uploaddir . $uploadname;
		
		//echo "Uploaded file is ";
		
		if (strtolower(substr($uploadname, (strlen($uploadname) - 3), 3)) == "mdb") {
		
			// Handle MDB file
			//echo "MDB file. \n";
			$this->setFilename($uploadfile);
		
		}
		
		if (strtolower(substr($uploadname, (strlen($uploadname) - 3), 3)) == "zip") {
		
			// Handle Zip file
			$zip = new ZipArchive;
			//echo "ZIP file. \n";
		
			if ($zip->open($uploadfile) === TRUE) {
		
				for ($i = 0; $i < $zip->numFiles; $i++) {
		
					$tmpName = $zip->getNameIndex($i);
					$tmpInfo = pathinfo($tmpName);
		
					// Find the MDB file in the zip
					if (strtolower(substr($tmpName, (strlen($tmpName) - 3), 3)) == "mdb") {
		
						// Flattens zip file to have no directory information
						copy("zip://" . $uploadfile . "#" . $tmpName, $this->uploaddir . '/' . $tmpInfo['basename']);
						$zip->extractTo($this->uploaddir, $tmpInfo['basename']);
		
						//echo "Extracted " . $tmpInfo['basename'] . ".\n ";
						$this->setFilename($tmpInfo['basename']);
								
					}
		
		
				}
		
				// Now completed delete the ZIP file
				unlink($uploadfile);
		
			} else {
		
				// Unable to open.
				//echo "Unable to open ZIP file. \n";
		
			}
		
		}
		
		$this->exportCsv();		
		$this->update();
		
	}
	
	private function exportCsv() {
		
		$filePath = $this->uploaddir . $this->filename;
		$athleteCsv = $this->uploaddir . $this->meetId . '-athlete.csv';
		$eventCsv = $this->uploaddir . $this->meetId . '-event.csv';
		$teamCsv = $this->uploaddir . $this->meetId . '-team.csv';
		$entryCsv = $this->uploaddir . $this->meetId . '-entry.csv';
		$relayCsv = $this->uploaddir . $this->meetId . '-relay.csv';
		$relaynamesCsv = $this->uploaddir . $this->meetId . '-relaynames.csv';
		
		exec("mdb-export -H '$filePath' athlete > $athleteCsv");
		
		$athleteFile = fopen($athleteCsv, "r");
		
		while (!feof($athleteFile)) {
		
			$csvEntry = fgetcsv($athleteFile);
			
			if (count($csvEntry) > 1) {
			
				$ath_no = mysql_real_escape_string(trim($csvEntry[0]));
				
				// Has ath_no been loaded already?
				$athTest = $GLOBALS['db']->getRow("SELECT * FROM eprogram_athletes WHERE meet_id = '$this->meetId' AND ath_no = '$ath_no';");
				db_checkerrors($athTest);
				
				if (isset($athTest)) {
					
					// Athlete already dataloaded,
					//echo "Athlete already matched.<br />\n";
					continue;
					
				}
				
				$last_name = mysql_real_escape_string(trim($csvEntry[1]));
				$first_name = mysql_real_escape_string(trim($csvEntry[2]));
				$team_no = mysql_real_escape_string(trim($csvEntry[6]));
				$reg_no = mysql_real_escape_string(trim($csvEntry[9]));
				$dob = mysql_real_escape_string(substr($csvEntry[5], 0, 8));
				
				// CSV file outputs in american date format
				$dobYear = '19' . substr($dob, 6, 2);
				$dobDay = substr($dob, 3, 2);
				$dobMonth = substr($dob, 0, 2);				
				$dob = $dobYear . '-' . $dobMonth . '-' . $dobDay;
				
				// echo "$dob <br />\n";
				
				$age = mysql_real_escape_string(trim($csvEntry[8]));
				
				if ($csvEntry[4] == 'M') {
					
					$gender = 1;
					
				} else {
					
					$gender = 2;
					
				}
				
				// Datamatch with Member table
				// Match only membership number, due prefered name issue
				$memberConfirm = $GLOBALS['db']->getOne("SELECT id FROM member WHERE number = '$reg_no';");
				db_checkerrors($memberConfirm);
				
				if (isset($memberConfirm)) {
					
					$insert = $GLOBALS['db']->query("INSERT INTO eprogram_athletes (meet_id, member_id, ath_no, team_no, gender, age) VALUES ('$this->meetId', '$memberConfirm', '$ath_no', '$team_no', '$gender', '$age');");
					db_checkerrors($insert);
					// echo "Member $reg_no found!<br />\n";
									
				} else {
					
					// Handle unmatched members
					
					if ($reg_no == '') {
					
						// Guest member or otherwise unregistered member
						//echo "Guest member or no MSA number. <br />\n";
						
										
					} else {
						
						// Registered but unfinancial or otherwise unknown member
						//echo "Member $reg_no not found!<br />\n";
												
					}
					
					$insert = $GLOBALS['db']->query("INSERT INTO eprogram_athletes (meet_id, ath_no, team_no, firstname, surname, dob, msanumber, gender, age) VALUES ('$this->meetId', '$ath_no', '$team_no', '$first_name', '$last_name', '$dob', '$reg_no', '$gender', '$age');");
					db_checkerrors($insert);
					
				}
			
			}
			
		}
		
		// print_r($athleteArray);
		
		exec("mdb-export -H '$filePath' event > $eventCsv");
		
		$eventFile = fopen($eventCsv, "r");
		
		while (!feof($eventFile)) {
		
			$csvEntry = fgetcsv($eventFile);
				
			if (count($csvEntry) > 1) {
				
				$event_ptr = mysql_real_escape_string(trim($csvEntry[2]));
				$event_no = mysql_real_escape_string(trim($csvEntry[0]));
				$event_ltr = mysql_real_escape_string(trim($csvEntry[1]));
				
				// Check if event already exists
				$eventTest = $GLOBALS['db']->getRow("SELECT * FROM eprogram_events WHERE meet_id = '$this->meetId' AND event_ptr = '$event_ptr';");
				db_checkerrors($eventTest);
				
				if (isset($eventTest)) {

					// Already exists
					//echo "Event has already been dataloaded! <br />\n";
					continue;
					
				}
				
				$progNum = $event_no;
				$progSuf = $event_ltr;
				
			//	echo "Searching for $progNum - $progSuf\n";
				
				$eventId = $GLOBALS['db']->getOne("SELECT id FROM meet_events WHERE meet_id = '$this->meetId' AND prognumber = '$progNum' AND progsuffix = '$progSuf';");
				db_checkerrors($eventId);
				
				if ($eventId) {
				
					$insert2 = $GLOBALS['db']->query("INSERT INTO eprogram_events (meet_id, event_id, event_ptr) VALUES ('$this->meetId', '$eventId', '$event_ptr');");
					db_checkerrors($insert2);
					
					//echo " found<br />\n";
					
				} else {
					
					//echo " Error event $event_no not found!<br />\n";
					
				}
				
				
			}
			
		}
		
		exec("mdb-export -H '$filePath' team > $teamCsv");
		
		$teamFile = fopen($teamCsv, "r");
		
		while (!feof($teamFile)) {
		
			$csvEntry = fgetcsv($teamFile);
		
			if (count($csvEntry) > 1) {
		
				$team_no = mysql_real_escape_string(trim($csvEntry[0]));
				
				// Check if team has already been loaded
				$teamTest = $GLOBALS['db']->getRow("SELECT * FROM eprogram_teams WHERE meet_id = '$this->meetId' AND team_no = '$team_no';");
				db_checkerrors($teamTest);
				
				if (isset($teamTest)) {
					
					// Team already loaded
					//echo "Team already matched.<br />\n";
					continue;
					
				}
				
				$team_code = trim($csvEntry[3]);
				$team_name = trim($csvEntry[1]);
				
				$clubId = $GLOBALS['db']->getOne("SELECT id FROM clubs WHERE code = '$team_code';");
				db_checkerrors($clubId);
		
				if (! isset($clubId)) {
				
					// Add new club
					// $newClub = new Club();
					// $newClub->create($team_code, $team_name);
					
					$tempClubCode = $team_code;
					$tempClubName = $team_name;
					
					//echo "Club $team_code not found, created temporary!<Br />\n";
					
				} else {
					
					$tempClubCode = NULL;
					$tempClubName = NULL;
					
				}
				
				// Link club id to team_no in eprogram
				$insert1 = $GLOBALS['db']->query("INSERT INTO eprogram_teams (meet_id, club_id, team_no, clubcode, clubname) VALUES ('$this->meetId', '$clubId', '$team_no', '$tempClubCode', '$tempClubName');");
				db_checkerrors($insert1);
		
			}
				
		}

		exec("mdb-export -H '$filePath' entry > $entryCsv");
		
		$entryFile = fopen($entryCsv, "r");
		
		while (!feof($entryFile)) {
		
			$csvEntry = fgetcsv($entryFile);
		
			if (count($csvEntry) > 1) {
				
				$event_ptr = mysql_real_escape_string($csvEntry[0]);
				$ath_no = mysql_real_escape_string($csvEntry[1]);
				$heatnumber = mysql_real_escape_string($csvEntry[35]);
				$heatlane = mysql_real_escape_string($csvEntry[36]);
				$seedtime = mysql_real_escape_string($csvEntry[5]);
				$heatplace = mysql_real_escape_string($csvEntry[40]);
				$finaltime = mysql_real_escape_string($csvEntry[38]);
				$finalplace = mysql_real_escape_string($csvEntry[42]);
				$evscore = mysql_real_escape_string($csvEntry[12]);
				
				// Check if entry already recorded
				$entryTest = $GLOBALS['db']->getRow("SELECT * FROM eprogram_entry WHERE meet_id = '$this->meetId' AND event_ptr = '$event_ptr' AND ath_no = '$ath_no';");
				db_checkerrors($entryTest);
				
				if (isset($entryTest)) {
					
					// Check for updates
					$update4 = $GLOBALS['db']->query("UPDATE eprogram_entry SET heatnumber = '$heatnumber', lanenumber = '$heatlane', seedtime = '$seedtime', heatplace = '$heatplace', finalplace = '$finalplace', finaltime = '$finaltime', ev_score = '$evscore' WHERE meet_id = '$this->meetId' AND event_ptr = '$event_ptr' AND ath_no = '$ath_no';");
					db_checkerrors($update4);
					
					//echo "Updating $event_ptr for $ath_no.<br />";
					
				} else {
					
					$insert4 = $GLOBALS['db']->query("INSERT INTO eprogram_entry (meet_id, event_ptr, ath_no, heatnumber, lanenumber, seedtime, heatplace, finalplace, finaltime, ev_score) VALUES ('$this->meetId', '$event_ptr', '$ath_no', '$heatnumber', '$heatlane', '$seedtime', '$heatplace', '$finalplace', '$finaltime', '$evscore');");
					db_checkerrors($insert4);
					
					//echo "Inserting $event_ptr for $ath_no.<br />";
					
				}
				
			}
			
		}
		
		// Update heat numbers
		$eventList = $GLOBALS['db']->getAll("SELECT * FROM eprogram_events WHERE meet_id = '$this->meetId';");
		db_checkerrors($eventList);

		foreach ($eventList as $l) {
			
			$event_ptr = $l[2];
			
			$numIndHeats = $GLOBALS['db']->getOne("SELECT MAX(heatnumber) 
					FROM eprogram_entry WHERE meet_id = '$this->meetId' AND event_ptr = '$event_ptr'");
			db_checkerrors($numIndHeats);
					
			$numRelHeats = $GLOBALS['db']->getOne("SELECT MAX(heatnumber) FROM eprogram_relay 
					WHERE meet_id = '$this->meetId' AND event_ptr = '$event_ptr'");
			db_checkerrors($numRelHeats);
			
			$numTotalHeats = $numIndHeats + $numRelHeats;
			
			$numHeats = $GLOBALS['db']->query("UPDATE eprogram_events SET numheats = '$numTotalHeats' 
					WHERE meet_id = '$this->meetId' AND event_ptr = '$event_ptr';");
			db_checkerrors($numHeats);
			
			//echo "Updating number of heats for event_ptr $event_ptr to $numTotalHeats<br />\n";
			
		}
		
		
		exec("mdb-export -H '$filePath' relaynames > $relaynamesCsv");
		
		$relayNameFile = fopen($relaynamesCsv, "r");
		
		$deleteRelay = $GLOBALS['db']->query("DELETE FROM eprogram_relaynames
				WHERE meet_id = '$this->meetId';");
		db_checkerrors($deleteRelay);
		
		
		while (!feof($relayNameFile)) {
		
			$csvEntry = fgetcsv($relayNameFile);
			
			if (count($csvEntry) > 1) {
				
				$event_ptr = $csvEntry[0];
				$team_no = $csvEntry[1];
				$team_ltr = $csvEntry[2];
				$ath_no = $csvEntry[3];
				$pos_no = $csvEntry[4];
				$event_round = $csvEntry[5];
				$relay_no = $csvEntry[6];
				
				if ($event_round == 'F') {
					
					//echo "Found relay team $relay_no for event $event_ptr team member $pos_no is $ath_no.";
				
					$insert = $GLOBALS['db']->query("INSERT INTO eprogram_relaynames (meet_id, event_ptr, 
							relay_no, team_no, ath_no, pos_no, team_ltr) 
							VALUES ('$this->meetId', '$event_ptr', '$relay_no', '$team_no', '$ath_no', 
							'$pos_no', '$team_ltr');");
					db_checkerrors($insert);
						
					//echo "created.<br />\n";				
					
				}
			}
			
		}
		

		exec("mdb-export -H '$filePath' relay > $relayCsv");

		$relayFile = fopen($relayCsv, "r");

		$deleteRelay = $GLOBALS['db']->query("DELETE FROM eprogram_relay WHERE meet_id = '$this->meetId';");
		db_checkerrors($deleteRelay);
		
		while (!feof($relayFile)) {
		
			$csvEntry = fgetcsv($relayFile);
		
			if (count($csvEntry) > 1) {
		
				$event_ptr = $csvEntry[0];
				$relay_no = $csvEntry[1];
				$team_no = $csvEntry[2];
				$team_ltr = $csvEntry[3];
				$rel_age = $csvEntry[4];
				$rel_sex = $csvEntry[5];
				$seedtime = $csvEntry[9];
				$ev_score = $csvEntry[16];
				$fin_heat = $csvEntry[38];
				$fin_lane = $csvEntry[39];
				$fin_time = $csvEntry[41];
				$heatplace = $csvEntry[43];
				$finplace = $csvEntry[45];
				
				$insert = $GLOBALS['db']->query("INSERT INTO eprogram_relay (event_ptr, meet_id, relay_no, team_no, 
						team_ltr, rel_age, rel_sex, seedtime, heatnumber, lanenumber, finaltime, heatplace, finalplace, 
						ev_score) VALUES ('$event_ptr', '$this->meetId', '$relay_no', '$team_no', '$team_ltr', '$rel_age', '$rel_sex', 
						'$seedtime', '$fin_heat', '$fin_lane', '$fin_time',' $heatplace', '$finplace', '$ev_score');");
				db_checkerrors($insert);
					
				//echo "Added new relay<br />\n";
				
			}
				
		}

		// Check if all entries already exist in Meet Entry system
		$this->updateEntryManager();
		
	}
	
	// Updates entry manager to show the entries from the backup file rather than whatever was originally
	// entered by competitor.
	public function updateEntryManager() {
	
		// Get list of all athletes who are in at least one event
		$athList = $GLOBALS['db']->getAll("SELECT member_id, ath_no, team_no FROM eprogram_athletes WHERE meet_id = '$this->meetId' AND 
										ath_no IN (SELECT DISTINCT(ath_no) FROM eprogram_entry WHERE 
										meet_id = '$this->meetId');");
		db_checkerrors($athList);
		
		//echo "Updating entry manager... <br />\n";
		
		foreach ($athList as $l) {
		
			$memberId = $l[0];
			$ath_no = $l[1];
			$team_no = $l[2];
			$clubId = $GLOBALS['db']->getOne("SELECT club_id FROM eprogram_teams WHERE meet_id = '$this->meetId' AND
					team_no = '$team_no';");
			db_checkerrors($clubId);

			// Ignore non Swimming Management System members and guests
			if (isset($memberId)) {
				
				//echo "Creating Meet Entry for $memberId... \n";
			
				$nEntry = new MeetEntry($memberId, $clubId, $this->meetId);
				
				// Get list of events from eProgram
				$eProgEvents = $GLOBALS['db']->getAll("SELECT * FROM eprogram_entry, eprogram_events 
														WHERE eprogram_entry.meet_id = '$this->meetId'
														AND eprogram_events.meet_id = '$this->meetId'
														AND eprogram_events.event_ptr = eprogram_entry.event_ptr
														AND eprogram_entry.ath_no = '$ath_no';");
				db_checkerrors($eProgEvents);
								
				if ($nEntry->load()) {
				
					// Update existing entry
					$nEntry->setStatus(8);	// Set entry status to confirmed
					$nEntry->updateStatus();
					$entryManList = $nEntry->getArrayEventId();
					
					//print_r($entryManList);
					
					//echo "updating existing entry.<br />\n";
					
					foreach ($eProgEvents as $e) {
					
						$eventId = $e[11];
						$seedTime = $e[5];
						
						if (in_array($eventId, $entryManList)) {
						
							// The event currently being checked from eProgram was entered into via Entry Manager
							// mark this event as confirmed, and update seed time and status.
							$nEntry->updateEvent($eventId, $seedTime, 8); 
							
							//echo "update event $eventId entry<br />\n";
						
						} else {
						
							$nEntry->addEvent($eventId, $seedTime, 8);  // Add the event to the entry as confirmed
							
							//echo "adding event $eventId entry<br />\n";
						
						}
					
					}
					
				} else {
				
					// Create new entry
					
					//echo "creating new entry.<br />\n";
				
					foreach ($eProgEvents as $p) {
				
						$eventId = $p[11];
						$seedTime = $p[5];
						$nEntry->addEvent($eventId, $seedTime, 8);  // Add the event to the entry as confirmed
				
					}
					
					$entryId = $nEntry->create();
					$nEntry->setStatus(8);
				
				}
			
			}
		
		}
	
	}
	
	public function setMeet($id) {
		
		$this->meetId = $id;
		
	}
	
	public function setUrl($u) {
		
		$this->url = $u;
		
	}
	
	public function setFilename($f) {
		
		$this->filename = $f;
		
	}
	
	public function getFilename() {
		
		return $this->filename;
		
	}
	
	public function getUpdated() {
		
		return array($this->version, $this->updated);
		
	}
	
	public function setQldMemberOnly() {
		
		$this->qldMemberOnly = true;
		
	}
	
	public function outputProgram($method = 'byheat') {
		
// 		if ($method == 'byage') {
		
// 			// Age group selector
// 			echo "<form id=\"ageSelectorFrom\">\n";
// 			echo "<fieldset>\n";
// 			echo "<p>\n";
// 			echo "<label for=\"ageSelector\">\n";
// 			echo "Age Group:\n";
// 			echo "</label>\n";
		
// 			$ageGroupList = $GLOBALS['db']->getAll("SELECT * FROM age_groups WHERE age_groups.set = '1';");
// 			db_checkerrors($ageGroupList);
		
// 			echo "<select name=\"ageSelector\" id=\"ageSelector\">\n";
		
// 			echo "<option value=\"\">All</option>\n";
		
// 			foreach ($ageGroupList as $a) {
		
// 				$groupId = $a[0];
// 				$groupName = $a[5];
		
// 				echo "<option value=\"$groupId\">$groupName</option>\n";
		
// 			}
		
// 			echo "</select>\n";
		
// 			echo "<br />\n";
// 			echo "<input type=\"button\" id=\"ageFilterButton\" value=\"Select\" />\n";
		
// 			echo "</p>\n";
// 			echo "</fieldset>\n";
// 			echo "</form>\n";
		
// 		}
		
		// Get list of events
		$eventList = $GLOBALS['db']->getAll("SELECT * FROM eprogram_events WHERE meet_id = '$this->meetId' ORDER BY event_id;");
		db_checkerrors($eventList);
		
		foreach ($eventList as $e) {
			
			$eventId = $e[1];
			$event_ptr = $e[2];
			$eventNumHeats = $e[3];
						
			$eventDetails = $GLOBALS['db']->getRow("SELECT * FROM meet_events WHERE id = '$eventId';");
			db_checkerrors($eventDetails);
			
			$eventLegs = $eventDetails[4];
			$progNum = $eventDetails[7];
			$progSuf = $eventDetails[8];
			$eventName = $eventDetails[6];
			$eventType = $eventDetails[2];
			$eventDiscipline = $eventDetails[3];
			$eventDisciplineName = $GLOBALS['db']->getOne("SELECT discipline FROM event_disciplines WHERE id = '$eventDiscipline';");
			db_checkerrors($eventDisciplineName);
			$eventDistance = $eventDetails[5];
			$eventDistanceName = $GLOBALS['db']->getOne("SELECT distance FROM event_distances WHERE id = '$eventDistance';");
			db_checkerrors($eventDistanceName);
			$eventGender = $GLOBALS['db']->getOne("SELECT gender FROM event_types WHERE id = '$eventType';");
			db_checkerrors($eventGender);
			
			echo "<a href=\"#$progNum$progSuf\" onclick=\"displayDetails($eventId)\">\n";
			echo "<div class=\"eProgramTitle\" id=\"$progNum$progSuf\">Event $progNum$progSuf - $eventName ";
			
			if ($eventLegs > 1) {

				switch($eventGender) {
					
					case 1:
						echo "Men's ";
						break;
					case 2:
						echo "Women's ";
						break;
					case 3:
						echo "Mixed ";
						break;
											
				}
				
				echo $eventLegs . "x";
				
			}
			
			echo "$eventDistanceName $eventDisciplineName</div>\n";
			echo "</a>\n";
			
			echo "<div id=\"event_$eventId\" style=\"visibility: collapse; display: none;\">\n";
			
			if ($method == 'byheat') {
			
				for ($h = 1; $h <= $eventNumHeats; $h++) {
					
					echo "<h5>Event $progNum$progSuf $eventName ";
					
					switch($eventGender) {
							
						case 1:
							echo "Men's ";
							break;
						case 2:
							echo "Women's ";
							break;
						case 3:
							echo "Mixed ";
							break;
								
					}
					
					if ($eventLegs > 1) {
						
						echo $eventLegs . "x";
						
					}
					
					echo "$eventDistanceName $eventDisciplineName Heat $h</h5>\n";
					
					echo "<table border=\"0\" width=\"100%\">\n";
					
					echo "<thead class=\"list\">\n";
					echo "<tr>\n";
					echo "<th width=\"5%\">\n";
					echo "Lane\n";
					echo "</th>\n";
					echo "<th width=\"20%\">\n";
					echo "Name";
					
					if ($eventLegs > 1) {
						
						echo "s";
						
					}
					
					echo "\n";
					
					echo "</th>\n";
					echo "<th width=\"5%\">\n";
					echo "Club";
					echo "</th>\n";
					echo "<th width=\"5%\">\n";
					echo "Age\n";
					echo "</th>\n";
					echo "<th>\n";
					echo "Age Group\n";
					echo "</th>\n";
					echo "<th>\n";
					echo "Seed Time\n";
					echo "</th>\n";
					echo "<th>\n";
					echo "Heat Place\n";
					echo "</th>\n";
					echo "<th>\n";
					echo "Age Group Place\n";
					echo "</th>\n";
					echo "<th>\n";
					echo "Final Time\n";
					echo "</th>\n";
					echo "<th>\n";
					echo "Points\n";
					echo "</th>\n";
					echo "</tr>\n";
					echo "</thead>\n";
					
					if ($eventLegs == 1) {
	
						$heatEntries = $GLOBALS['db']->getAll("SELECT * FROM eprogram_entry WHERE meet_id = '$this->meetId' 
								AND event_ptr = '$event_ptr' AND heatnumber = '$h' ORDER BY lanenumber ASC;");
						db_checkerrors($heatEntries);
						
					} else {
						
						$heatEntries = $GLOBALS['db']->getAll("SELECT * FROM eprogram_relay 
								WHERE meet_id = '$this->meetId' AND event_ptr = '$event_ptr' AND heatnumber = '$h'
								ORDER BY lanenumber ASC;");
						db_checkerrors($heatEntries);
						
					}
					
					echo "<tbody class=\"list\">\n";
					
					foreach ($heatEntries as $t) {
						
						if ($eventLegs == 1) {
						
							$lane = $t[4];
							$ath_no = $t[2];
							
							$memberDetails = $GLOBALS['db']->getRow("SELECT * FROM eprogram_athletes WHERE meet_id = '$this->meetId' AND ath_no = '$ath_no';");
							db_checkerrors($memberDetails);
							
							$memberId = $memberDetails[1];
							
							if (isset($memberId)) {
									
								$memberObj = new Member();
								$memberObj->loadId($memberId);
								$swimmerName = $memberObj->getFullname();
								$swimmerAge = $memberObj->getAge();
								$swimmerAgeGroup = $memberObj->getAgeGroup();
									
							} else {
									
								$swimmerName = $memberDetails[4] . ' ' . $memberDetails[5];
								$swimmerDob = $memberDetails[6];
									
								// Get unknown swimmer age
								$dobDT = new DateTime($swimmerDob);
								$testDateDT = new DateTime();
									
								$ageInt = $dobDT->diff($testDateDT);
								$swimmerAge = $ageInt->format('%y');
									
								$genderCode = $memberDetails[8];
									
								$swimmerAgeGroup = $GLOBALS['db']->getOne("SELECT groupname FROM age_groups WHERE '$swimmerAge' >= min AND max >= '$swimmerAge' AND gender = '$genderCode';");
								db_checkerrors($swimmerAgeGroup);
									
							}
							
							$team_no = $memberDetails[3];
							$clubCode = $GLOBALS['db']->getOne("SELECT clubs.code FROM eprogram_teams, clubs WHERE eprogram_teams.meet_id = '$this->meetId' AND eprogram_teams.club_id = clubs.id AND eprogram_teams.team_no = '$team_no';");
							db_checkerrors($clubCode);
							
							$seedTime = $t[5];
							$heatplace = $t[6];
							$agegroupplace = $t[7];
							$finalTime = $t[8];
							$evscore = $t[9];
							
						} else {
							
							$lane = $t[9];
							$team_no = $t[3];
							$relay_no = $t[2];
							$seedTime = $t[7];
							$heatplace = $t[11];
							$agegroupplace = $t[12];
							$finalTime = $t[10];
							$evscore = $t[13];
							$swimmerName = '';
							$swimmerAge = '';
							
							$relayNames = $GLOBALS['db']->getAll("SELECT * FROM eprogram_relaynames 
									WHERE meet_id = '$this->meetId' AND relay_no = '$relay_no' AND
									event_ptr = '$event_ptr' ORDER BY pos_no ASC;");
							db_checkerrors($relayNames);
							
							foreach ($relayNames as $n) {

								$ath_no = $n[4];								
								$memberDetails = $GLOBALS['db']->getRow("SELECT * FROM eprogram_athletes 
										WHERE meet_id = '$this->meetId' AND ath_no = '$ath_no';");
								db_checkerrors($memberDetails);
									
								$memberId = $memberDetails[1];
								
								if (isset($memberId)) {
										
									$memberObj = new Member();
									$memberObj->loadId($memberId);
									$swimmerNameTemp = $memberObj->getFullname();
									$swimmerAgeTemp = $memberObj->getAge();
									$swimmerAgeGroupTemp = $memberObj->getAgeGroup();
										
								} else {
										
									$swimmerNameTemp = $memberDetails[4] . ' ' . $memberDetails[5];
									$swimmerDobTemp = $memberDetails[6];
										
									// Get unknown swimmer age
									$dobDT = new DateTime($swimmerDobTemp);
									$testDateDT = new DateTime();
										
									$ageInt = $dobDT->diff($testDateDT);
									$swimmerAgeTemp = $ageInt->format('%y');
										
									$genderCodeTemp = $memberDetails[8];
										
								}
								
								$swimmerName = $swimmerName . $swimmerNameTemp . ", ";
								$swimmerAge = $swimmerAge + $swimmerAgeTemp;
								
							}
							
							$swimmerName = substr($swimmerName, 0, (strlen($swimmerName) - 2));
							$teamDetails = $GLOBALS['db']->getRow("SELECT * FROM eprogram_teams 
									WHERE meet_id = '$this->meetId' AND team_no = '$team_no';");
							db_checkerrors($teamDetails);
							
							if ($teamDetails[1] == 0) {

								$clubCode = $teamDetails[3];
							
							} else {
								
								$clubDetails = new Club;
								$clubDetails->load($teamDetails[1]);
								$clubCode = $clubDetails->getCode();
								
							}
							
							$swimmerAgeGroup = $GLOBALS['db']->getOne("SELECT groupname FROM age_groups 
									WHERE age_groups.set = '1' AND swimmers = '4' AND '$swimmerAge' >= min AND max >= '$swimmerAge' 
									AND gender = '$eventGender';");
							db_checkerrors($swimmerAgeGroup);
							
						}
												
						if (!strpbrk($seedTime, '.')) {
							
							$seedTimeSecs = $seedTime;
							$seedTimeMs = "00";
							
						} else {
	
							list($seedTimeSecs, $seedTimeMs) = explode('.', $seedTime);
							
							if (strlen($seedTimeMs) == 1) {
								
								$seedTimeMs = $seedTimeMs . '0';
								
							}
							
						}
						
						$seedTimeDisp = floor($seedTimeSecs / 60) . ':' . sprintf("%02d", ($seedTimeSecs % 60)) . '.' . $seedTimeMs;
						
						if ($seedTimeDisp == "0:00.00") {
						
							$seedTimeDisp = "NT";
						
						}
						
						if (!strpbrk($finalTime, '.')) {
						
							$finalTimeSecs = $finalTime;
							$finalTimeMs = "00";
						
						} else {
						
							list($finalTimeSecs, $finalTimeMs) = explode('.', $finalTime);
							
							if (strlen($finalTimeMs) == 1) {
									
								$finalTimeMs = $finalTimeMs . '0';
									
							}
						
						}
							
						$finalTimeDisp = floor($finalTimeSecs / 60) . ':' . sprintf("%02d", ($finalTimeSecs % 60)) . '.' . $finalTimeMs;
					
						if ($heatplace == 0) {
							
							$notRun = 1;
							
						} else {
							
							$notRun = 0;
							
						}
						
						// Remove non qld entries
						
						if (isset($memberId)) {
						
							echo "<tr class=\"list\">\n";
							echo "<td>\n";
							echo "<div class=\"programCentre\">\n";
							echo "$lane\n";
							echo "</div>\n";
							echo "</td>\n";
							echo "<td>\n";
							
							if (isset($memberId) ) {
							
								echo "<a href=\"$this->url?id=$this->meetId&individualProg=1&member=$memberId\">\n";
								echo "$swimmerName\n";
								
							} else {
								
								echo "<a href=\"$this->url?id=$this->meetId&individualProg=1&ath_no=$ath_no\">\n";
								echo "$swimmerName\n";
								echo "</a>\n";
								
							}
							echo "</td>\n";
							echo "<td>\n";
							
							if ($clubCode == "") {
								
								// Must be out of state member, look up club code from team_no
								$clubCode = $GLOBALS['db']->getOne("SELECT clubcode FROM eprogram_teams WHERE 
										meet_id = ? AND team_no = ?", array($this->meetId, $team_no));
								db_checkerrors($clubCode);
								
							}
							
							echo "<a href=\"$this->url?id=$this->meetId&teamProg=1&team=$team_no\">\n";
							echo "$clubCode\n";
							echo "</a>\n";
							echo "</td>\n";
							echo "<td>\n";
							echo "<div class=\"programCentre\">\n";
							echo "$swimmerAge\n";
							echo "</div>\n";
							echo "</td>\n";
							echo "<td>\n";
							echo "$swimmerAgeGroup\n";
							echo "</td>\n";
							echo "<td>\n";
							echo "<div class=\"programTime\">\n";
							echo "$seedTimeDisp\n";
							echo "</div>";
							echo "</td>\n";
							echo "<td>\n";
							echo "<div class=\"programCentre\">\n";
							if ($notRun != 1) {
								echo "$heatplace\n";
							}					
							echo "</div>";
							echo "</td>\n";
							echo "<td>\n";
							echo "<div class=\"programCentre\">\n";
							if ($notRun != 1) {
								echo "$agegroupplace\n";
							}
							echo "</div>";
							echo "</td>\n";
							echo "<td class=\"programTime\">\n";
							echo "<div class=\"programTime\">\n";
							if ($notRun != 1) {
								echo "$finalTimeDisp\n";
							}
							echo "</div>\n";
							echo "</td>\n";
							echo "<td class=\"programCentre\">\n";
							if ($notRun != 1) {
								echo "$evscore\n";
							}
							echo "</td>\n";
							echo "</tr>\n";
							
						}
							
						
					}
					
					echo "</tbody>\n";
					
					echo "</table>\n";
				
				}
			}
			
			if ($method == 'byage') {
				
				$ageGroupList = $GLOBALS['db']->getAll("SELECT * FROM age_groups WHERE age_groups.set = '1'
						AND swimmers = '$eventLegs';");
				db_checkerrors($ageGroupList);
									
				foreach ($ageGroupList as $a) {
					
					$aId = $a[0];
					$min = $a[2];
					$max = $a[3];
					$gender = $a[4];
					
					switch($gender) {
						
						case 1: 
							$genderCode = 'M';
							break;
						case 2: 
							$genderCode = 'F';
							break;
						case 3:
							$genderCode = 'X';
							break;
						
					}
					
					
					$groupName = $a[5];
					
					if ($eventLegs == 1) {
					
						$ageGroupSwimmers = $GLOBALS['db']->getAll("SELECT * FROM eprogram_entry, eprogram_athletes 
							WHERE eprogram_athletes.ath_no = eprogram_entry.ath_no 
							AND eprogram_entry.meet_id = '$this->meetId' AND eprogram_athletes.meet_id = '$this->meetId' 
							AND eprogram_entry.event_ptr = '$event_ptr' AND eprogram_athletes.age >= '$min' 
							AND eprogram_athletes.age <= '$max' AND eprogram_athletes.gender = '$gender' 
							ORDER BY eprogram_entry.finalplace ASC;");
						db_checkerrors($ageGroupSwimmers);
						
					} else {
						
						$ageGroupSwimmers = $GLOBALS['db']->getAll("SELECT * FROM eprogram_relay
								WHERE meet_id = '$this->meetId' AND event_ptr = '$event_ptr' AND
								rel_age >= '$min' AND rel_age <= '$max' AND rel_sex = '$genderCode';");
						
					}
					
					if (count($ageGroupSwimmers) >= 1) {
						
						echo "<div id=\"agegroup_$aId\" class=\"agegroups\">\n";
						
						echo "<h4>$groupName</h4>";
					
						echo "<table border=\"0\" width=\"100%\">\n";
						
						echo "<thead class=\"list\">\n";
						echo "<tr>\n";
						echo "<th>\n";
						echo "Place";
						echo "</th>\n";
						echo "<th>\n";
						echo "Name";
						echo "</th>\n";
						echo "<th>\n";
						echo "Club";
						echo "</th>\n";
						echo "<th>\n";
						echo "Age";
						echo "</th>\n";
						echo "<th>\n";
						echo "Seed Time";
						echo "</th>\n";
						echo "<th>\n";
						echo "Final Time";
						echo "</th>\n";
						echo "<th>\n";
						echo "Points";
						echo "</th>\n";
						echo "</tr>\n";
						echo "</thead>\n";
						
						echo "<tbody>\n";
						
						foreach ($ageGroupSwimmers as $s) {
							
							if ($eventLegs == 1) {
							
								$ath_no = $s[2];
								$finalplace = $s[7];
								$seedTime = $s[5];
								$finalTime = $s[8];
								$points = $s[9];
								
								$memberDetails = $GLOBALS['db']->getRow("SELECT * FROM eprogram_athletes WHERE meet_id = '$this->meetId' AND ath_no = '$ath_no';");
								db_checkerrors($memberDetails);
									
								$memberId = $memberDetails[1];
									
								if (isset($memberId)) {
										
									$memberObj = new Member();
									$memberObj->loadId($memberId);
									$swimmerName = $memberObj->getFullname();
									$swimmerAge = $memberObj->getAge();
									$swimmerAgeGroup = $memberObj->getAgeGroup();
										
								} else {
										
									$swimmerName = $memberDetails[4] . ' ' . $memberDetails[5];
									$swimmerDob = $memberDetails[6];
										
									// Get unknown swimmer age
									$dobDT = new DateTime($swimmerDob);
									$testDateDT = new DateTime();
										
									$ageInt = $dobDT->diff($testDateDT);
									$swimmerAge = $ageInt->format('%y');
										
									$genderCode = $memberDetails[8];
										
									$swimmerAgeGroup = $GLOBALS['db']->getOne("SELECT groupname FROM age_groups 
											WHERE '$swimmerAge' >= min AND max >= '$swimmerAge' 
											AND gender = '$genderCode';");
									db_checkerrors($swimmerAgeGroup);
									
									
										
								}
								
								$team_no = $memberDetails[3];
								$clubCode = $GLOBALS['db']->getOne("SELECT clubs.code FROM eprogram_teams, clubs WHERE eprogram_teams.meet_id = '$this->meetId' AND eprogram_teams.club_id = clubs.id AND eprogram_teams.team_no = '$team_no';");
								db_checkerrors($clubCode);
							
							} else {
								
								$finalplace = $s[12];
								$seedTime = $s[7];
								$finalTime = $s[10];
								$points = $s[13];
								$team_no = $s[3];
								$relay_no = $s[2];
								$swimmerAge = '';
								$swimmerName = '';
								
								$relayNames = $GLOBALS['db']->getAll("SELECT * FROM eprogram_relaynames
										WHERE meet_id = '$this->meetId' AND relay_no = '$relay_no' AND
										event_ptr = '$event_ptr' ORDER BY pos_no ASC;");
								db_checkerrors($relayNames);
									
								foreach ($relayNames as $n) {
								
									$ath_no = $n[4];
									$memberDetails = $GLOBALS['db']->getRow("SELECT * FROM eprogram_athletes
											WHERE meet_id = '$this->meetId' AND ath_no = '$ath_no';");
											db_checkerrors($memberDetails);
												
									$memberId = $memberDetails[1];
								
									if (isset($memberId)) {
								
										$memberObj = new Member();
										$memberObj->loadId($memberId);
										$swimmerNameTemp = $memberObj->getFullname();
										$swimmerAgeTemp = $memberObj->getAge();
										$swimmerAgeGroupTemp = $memberObj->getAgeGroup();
								
									} else {
								
										$swimmerNameTemp = $memberDetails[4] . ' ' . $memberDetails[5];
										$swimmerDobTemp = $memberDetails[6];
								
										// Get unknown swimmer age
										$dobDT = new DateTime($swimmerDobTemp);
										$testDateDT = new DateTime();
								
										$ageInt = $dobDT->diff($testDateDT);
										$swimmerAgeTemp = $ageInt->format('%y');
								
										$genderCodeTemp = $memberDetails[8];
								
									}
								
									$swimmerName = $swimmerName . $swimmerNameTemp . ", ";
									$swimmerAge = $swimmerAge + $swimmerAgeTemp;
								
								}
												
								$swimmerName = substr($swimmerName, 0, (strlen($swimmerName) - 2));
								$teamDetails = $GLOBALS['db']->getRow("SELECT * FROM eprogram_teams
											WHERE meet_id = '$this->meetId' AND team_no = '$team_no';");
								db_checkerrors($teamDetails);
												
								if ($teamDetails[1] == 0) {
								
									$clubCode = $teamDetails[3];
												
								} else {
								
									$clubDetails = new Club;
									$clubDetails->load($teamDetails[1]);
									$clubCode = $clubDetails->getCode();
								
								}
								
								
							}
								
							if (!strpbrk($seedTime, '.')) {
									
								$seedTimeSecs = $seedTime;
								$seedTimeMs = "00";
									
							} else {
							
								list($seedTimeSecs, $seedTimeMs) = explode('.', $seedTime);
									
								if (strlen($seedTimeMs) == 1) {
							
									$seedTimeMs = $seedTimeMs . '0';
							
								}
									
							}
							
							$seedTimeDisp = floor($seedTimeSecs / 60) . ':' . sprintf("%02d", ($seedTimeSecs % 60)) . '.' . $seedTimeMs;
							
							if ($seedTimeDisp == "0:00.00") {
								
								$seedTimeDisp = "NT";
								
							}
							
							if (!strpbrk($finalTime, '.')) {
							
								$finalTimeSecs = $finalTime;
								$finalTimeMs = "00";
							
							} else {
							
								list($finalTimeSecs, $finalTimeMs) = explode('.', $finalTime);
									
								if (strlen($finalTimeMs) == 1) {
										
									$finalTimeMs = $finalTimeMs . '0';
										
								}
							
							}
								
							$finalTimeDisp = floor($finalTimeSecs / 60) . ':' . sprintf("%02d", ($finalTimeSecs % 60)) . '.' . $finalTimeMs;
							
							// Remove non qld swimmers
							
							if (isset($memberId)) {
								
							
								echo "<tr class=\"list\">\n";
								echo "<td>\n";
								echo "<div class=\"programCentre\">\n";
								if ($finalplace != 0) {
									echo "$finalplace\n";
								}
								echo "</div>\n";
								echo "</td>\n";
								echo "<td>\n";
								if (isset($memberId) ) {
							
									echo "<a href=\"$this->url?id=$this->meetId&individualProg=1&member=$memberId\">\n";
									echo "$swimmerName\n";
								
								} else {
								
									echo "<a href=\"$this->url?id=$this->meetId&individualProg=1&ath_no=$ath_no\">\n";
									echo "$swimmerName\n";
									echo "</a>\n";
								
								}
								echo "</td>\n";
								echo "<td>\n";
								
								if ($clubCode == "") {
										
									// Must be out of state member, look up club code from team_no
									$clubCode = $GLOBALS['db']->getOne("SELECT clubcode FROM eprogram_teams WHERE
										meet_id = ? AND team_no = ?", array($this->meetId, $team_no));
									db_checkerrors($clubCode);
										
								}
								
								echo "<a href=\"$this->url?id=$this->meetId&teamProg=1&team=$team_no\">\n";
								echo "$clubCode\n";
								echo "</a>";
								echo "</td>\n";
								echo "<td>\n";
								echo "<div class=\"programCentre\">\n";
								echo "$swimmerAge\n";
								echo "</div>\n";
								echo "</td>\n";
								echo "<td>\n";
								echo "<div class=\"programTime\">\n";
								echo "$seedTimeDisp\n";
								echo "</div>\n";
								echo "</td>\n";
								echo "<td>\n";
								echo "<div class=\"programTime\">\n";
								if ($finalplace != 0) {
									echo "$finalTimeDisp\n";
								}
								echo "</div>\n";
								echo "</td>\n";
								echo "<td>\n";
								echo "<div class=\"programCentre\">\n";
								if ($finalplace != 0) {
									echo "$points\n";
								}
								echo "</div>\n";
								echo "</td>\n";
								echo "</tr>\n";
							
							}
							
						}

						echo "</tbody>\n";
						echo "</table>\n";
						echo "</div>\n";
				
					}
					
				}
				
			}
			
			echo "</div>\n";
						
		}
		
	}
	
	public function outputTeamScores() {
		
		echo "<h4>Team Point Scores</h4>\n";
		
		$teamList = $GLOBALS['db']->getAll("SELECT * FROM eprogram_teams WHERE meet_id = '$this->meetId';");
		db_checkerrors($teamList);
		
		echo "<table border=\"0\" width=\"100%\">\n";
		echo "<thead class=\"list\">\n";
		echo "<tr>\n";
		echo "<th>\n";
		echo "Place\n";
		echo "</th>\n";
		echo "<th>\n";
		echo "Club\n";
		echo "</th>\n";
		echo "<th>\n";
		echo "Number of Swimmers\n";
		echo "</th>\n";
		echo "<th>\n";
		echo "Individual Points\n";
		echo "</th>\n";
		echo "<th>\n";
		echo "Relay Points\n";
		echo "</th>\n";
		echo "<th>\n";
		echo "Avg Points Each\n";
		echo "</th>\n";
		echo "<th>\n";
		echo "Total Points\n";
		echo "</th>\n";
		echo "</tr>\n";
		echo "</thead>";
		echo "<tbody class=\"list\">";
		
		$teamPointsTally;
		$totalSwimmers = 0;
		
		foreach ($teamList as $t) {
			
			$club_id = $t[1];
			$team_no = $t[2];
			
			if ($club_id != 0) {
			
				$clubName = $GLOBALS['db']->getOne("SELECT clubname FROM clubs WHERE id = '$club_id';");
				db_checkerrors($clubName);
				
			} else {
				
				$clubName = $t[4]; 
								
			}
			
			
			
			$numSwimmers = $GLOBALS['db']->getOne("SELECT count(ath_no) FROM eprogram_athletes WHERE meet_id = '$this->meetId' AND team_no = '$team_no' AND ath_no IN (SELECT ath_no FROM eprogram_entry WHERE meet_id = '$this->meetId');");
			db_checkerrors($numSwimmers);
			$totalSwimmers = $totalSwimmers + $numSwimmers;
			
			$indPoints = $GLOBALS['db']->getOne("SELECT sum(ev_score) FROM eprogram_entry WHERE meet_id = '$this->meetId' 
					AND ath_no IN (SELECT ath_no FROM eprogram_athletes WHERE meet_id = '$this->meetId' 
					AND team_no = '$team_no');");
			db_checkerrors($indPoints);
			
			$relayPoints = $GLOBALS['db']->getOne("SELECT sum(ev_score) FROM eprogram_relay WHERE meet_id = '$this->meetId' 
					AND team_no = '$team_no';");
			db_checkerrors($relayPoints);
			
			$totalPoints = $indPoints + $relayPoints;
			
			if ($numSwimmers > 0) {
				
				// Remove non-qld clubs
				
				if ($club_id != 0) {
				
					$teamPointsTally[] = array($club_id, $clubName, $numSwimmers, $indPoints, $relayPoints, $totalPoints, $team_no);
					
				}
			
			}
			
		}
		
		// Bubble sort on team points tally
		// Sort by points
		$p = 5;

		$swap = true;
		while ($swap == true) {
		
			$swap = false;
			for ($i = 0; $i < (count($teamPointsTally) - 1); $i++) {
			
				if ($teamPointsTally[$i][$p] < $teamPointsTally[$i+1][$p]) {
				
					$row1 = $teamPointsTally[$i];
					$row2 = $teamPointsTally[$i+1];
					$teamPointsTally[$i] = $row2;
					$teamPointsTally[$i+1] = $row1;
					$swap = true;
				
				}
				
				// If equal points, team with fewest members is first
				if ($teamPointsTally[$i][$p] == $teamPointsTally[$i+1][$p]) {
				
					$row1 = $teamPointsTally[$i];
					$row2 = $teamPointsTally[$i+1];
					
					if ($row1[2] > $row2[2]) {
					
						$teamPointsTally[$i] = $row2;
						$teamPointsTally[$i+1] = $row1;
						$swap = true;		
					}
				
				}
				
			}
		
		}
		
		$placeCount = 1;
		
		foreach ($teamPointsTally as $t) {
		
			$clubName = $t[1];
			$numSwimmers = $t[2];
			$indPoints = $t[3];
			$relayPoints = $t[4];
			$totalPoints = $t[5];
			$team_no = $t[6];
		
			echo "<tr class=\"list\">\n";
			echo "<td>\n";
			echo $placeCount;
			echo "</td>\n";
			echo "<td>\n";
			echo "<a href=\"$this->url?id=$this->meetId&teamProg=1&team=$team_no\">\n";
			echo "$clubName\n";
			echo "</a>\n";
			echo "</td>\n";
			echo "<td>\n";
			echo "$numSwimmers\n";
			echo "</td>\n";
			echo "<td>\n";
			echo "$indPoints\n";
			echo "</td>\n";
			echo "<td>\n";
			echo "$relayPoints\n";
			echo "</td>\n";
			echo "<td>\n";
			echo round(($totalPoints / $numSwimmers), 1);
			echo "</td>\n";
			echo "<td>\n";
			echo "$totalPoints\n";
			echo "</td>\n";
			echo "</tr>\n";
			
			$placeCount++;
		
		}

		echo "</tbody>\n";
		echo "</table>\n";
		
		echo "<p>\n";
		echo "<strong>Total Number of Competitors:</strong> \n";
		echo $totalSwimmers;
		echo "</p>\n";
		
	}
	
	public function outputIndividualProgram($memberId, $ath_no = 0) {
		
		//if ($memberId != 0) {
		
			echo "<h4>Individual Program</h4>\n";
		
			// Get Meet Date
			$meetDetails = new Meet();
			$meetDetails->loadMeet($this->meetId);
			$meetStart = $meetDetails->getStartDate();
			$meetFinish = $meetDetails->getEndDate();
			
			// Get the athlete Id for this member in this meet
			if ($memberId != 0) {
			
				$athDetails = $GLOBALS['db']->getRow("SELECT ath_no, team_no FROM eprogram_athletes 
					WHERE member_id = ? AND meet_id = ?;", array($memberId, $this->meetId));
				db_checkerrors($athDetails);
			
			} else {
				
				$athDetails = $GLOBALS['db']->getRow("SELECT ath_no, team_no FROM eprogram_athletes
					WHERE ath_no = ? AND meet_id = ?;", array($ath_no, $this->meetId));
				db_checkerrors($athDetails);
				
			}
			
			$ath_no = $athDetails[0];
			$team_no = $athDetails[1];
			
			$teamDetails = $GLOBALS['db']->getRow("SELECT * FROM eprogram_teams WHERE team_no = ? AND
					meet_id = ?;", array($team_no, $this->meetId));
			db_checkerrors($teamDetails);
			$clubId = $teamDetails[1];
			
			if ($clubId != 0) {
				
				$clubDetails = new Club();
				$clubDetails->load($clubId);
				$clubName = $clubDetails->getName();
				$clubCode = $clubDetails->getCode();
				
			} else {
				
				$clubName = $teamDetails[4];
				$clubCode = $teamDetails[3];
				
			}
			
			if ($memberId == 0) {
				
				$athInfo = $GLOBALS['db']->getRow("SELECT firstname, surname, dob, gender, msanumber FROM
						eprogram_athletes WHERE meet_id = ? AND ath_no = ?", array($this->meetId, $ath_no));
				db_checkerrors($athInfo);
				
				$memberDetails = new Member();
				$memberDetails->setFirstname($athInfo[0]);
				$memberDetails->setSurname($athInfo[1]);
				$memberDetails->setDob($athInfo[2]);
				$memberDetails->setGender($athInfo[3]);
				$memberDetails->setMSANumber($athInfo[4]);
				
			} else {

				$memberDetails = new Member();
				$memberDetails->loadId($memberId);
				
			}
			
			$memberName = $memberDetails->getFullname();
			$memberNum = $memberDetails->getMSANumber();
			$memberAge = $memberDetails->getAge($meetStart);
			$ageGroupText = $memberDetails->getAgeGroup($meetStart);
			$ageGroupId = $memberDetails->getAgeGroupId($meetStart);
			
			// Display Team and Athlete Details
			echo "<h5>Athlete Details</h5>\n";
			
			echo "<p>\n";
			echo "<label>Athlete Name: </label>$memberName<br />\n";
			echo "<label>Membership Number: </label>$memberNum<br />\n";
			echo "<label>Age: </label>$memberAge<br />\n";
			echo "<label>Age Group: </label>$ageGroupText<br />\n";
			echo "<label>Swimming For: </label><a href=\"$this->url?id=$this->meetId&teamProg=1&team=$team_no\">$clubCode - $clubName</a><br />\n";
			echo "</p>\n";
			
			echo "<h5>Age Group Competitors</h5>\n";
			
			// Get list of athletes same age and age group
			$athList = $GLOBALS['db']->getAll("SELECT eprogram_athletes.*, SUM(eprogram_entry.ev_score) as score
					FROM eprogram_athletes, eprogram_entry WHERE eprogram_athletes.age >= (SELECT min 
					FROM age_groups WHERE id = ?) AND eprogram_athletes.age <= (SELECT max FROM age_groups WHERE id = ?) AND
					eprogram_athletes.gender = (SELECT gender FROM age_groups WHERE id = ?) 
					AND eprogram_athletes.meet_id = ? AND eprogram_athletes.ath_no = eprogram_entry.ath_no AND
					eprogram_entry.meet_id = ? GROUP BY eprogram_entry.ath_no ORDER BY score DESC;", 
					array($ageGroupId, $ageGroupId, $ageGroupId, $this->meetId, $this->meetId));
			db_checkerrors($athList);
			
			echo "<table class=\"list\">\n";
			echo "<thead class=\"list\">\n";
			echo "<tr>\n";
			echo "<th>Name</th>\n";
			echo "<th>Age</th>\n";
			echo "<th colspan=\"2\">Club</th>\n";
			echo "<th>Points</th>\n";
			echo "</tr>\n";
			echo "</thead>\n";
			echo "<tbody class=\"list\">\n";
			
			foreach ($athList as $a) {
				
				$curAth = $a[2];
				$curTeam = $a[3];
				$curScore = $a[10];
				$curMemberId = $a[1];
				
				$curTeamDetails = $GLOBALS['db']->getRow("SELECT * FROM eprogram_teams WHERE team_no = ? AND
					meet_id = ?;", array($curTeam, $this->meetId));
				db_checkerrors($curTeamDetails);
				$curClubId = $curTeamDetails[1];
				
				if ($curClubId != 0) {
						
					$curClubDetails = new Club();
					$curClubDetails->load($curClubId);
					$curClubName = $curClubDetails->getName();
					$curClubCode = $curClubDetails->getCode();
						
				} else {
						
					$curClubName = $curTeamDetails[4];
					$curClubCode = $curTeamDetails[3];
						
				}
				
				if ($curMemberId != 0) {
				
					$curMemberDetails = new Member();
					$curMemberDetails->loadId($curMemberId);
					$curMemberName = $curMemberDetails->getFullname();
				
				} else {
					
					$curMemberName = $a[4] . ' ' . $a[5];
					
				}
				
				$curMemberAge = $a[9];
				
				// Get score for this member
				//$curScore = $GLOBALS['db']->getOne("SELECT sum(ev_score) FROM eprogram_entry WHERE ath_no = ? 
				//		AND meet_id = ?;", array($curAth, $this->meetId));
				//db_checkerrors($curScore);
				
				echo "<tr class=\"list\">\n";
				echo "<td>\n";
				$meetId = $this->meetId;
				
				if ($curMemberId != "") {
					
					echo "<a href=\"eprogramq.php?id=$meetId&member=$curMemberId&individualProg=1\">\n";
					
				} else {
					
					echo "<a href=\"eprogramq.php?id=$meetId&ath_no=$curAth&individualProg=1\">\n";
					
				}
				
				echo $curMemberName;
				echo "</a>\n";
				echo "</td>\n";
				echo "<td class=\"programCentre\">\n";
				echo $curMemberAge;
				echo "</td>\n";
				echo "<td>\n";
				echo "<a href=\"$this->url?id=$this->meetId&teamProg=1&team=$curTeam\">$curClubCode</a>";
				echo "</td>\n";
				echo "<td>\n";
				echo "<a href=\"$this->url?id=$this->meetId&teamProg=1&team=$curTeam\">\n";
				echo $curClubName;
				echo "</a>\n";
				echo "</td>\n";
				echo "<td class=\"programCentre\">\n";
				echo "$curScore";
				echo "</td>\n";
				echo "</tr>\n";
				
			}
			
			echo "</tbody>\n";
			echo "</table>\n";
			
			echo  "<h4>$memberName's Individual Events</h4>\n";
			
			// Get list of events this member is in 
			$eventList = $GLOBALS['db']->getAll("SELECT * FROM eprogram_events, eprogram_entry 
					WHERE eprogram_entry.ath_no = ? AND eprogram_entry.meet_id = ? AND eprogram_events.meet_id = ?
					AND eprogram_entry.event_ptr = eprogram_events.event_ptr ORDER BY eprogram_events.event_ptr;",
					array($ath_no, $this->meetId, $this->meetId));
			db_checkerrors($eventList);
			
			echo "<table class=\"list\">\n";
			echo "<thead class=\"list\">\n";
			echo "<tr>\n";
			echo "<th colspan=\"2\">Event</th>\n";
			echo "<th>Heat</th>\n";
			echo "<th>Lane</th>\n";
			echo "<th>Seed Time</th>\n";
			echo "<th>Final Time</th>\n";
			echo "<th>Heat Place</th>\n";
			echo "<th>Age Group Place</th>\n";
			echo "<th>Points</th>\n";
			echo "</thead>\n";
			echo "<tbody class=\"list\">\n";
			
			foreach ($eventList as $e) {
				
				$eventDetails = new MeetEvent();
				$eventDetails->load($e[1]);
				$heatnumber = $e[7];
				$numheats = $e[3];
				$lanenumber = $e[8];
				$seedtime = $e[9];
				$heatplace = $e[10];
				$finalplace = $e[11];
				$finaltime = $e[12];
				$ev_score = $e[13];
				
				echo "<tr class=\"list\">\n";
				echo "<td class=\"programCentre\">\n";
				echo $eventDetails->getProgNumber();
				echo "</td>\n";
				echo "<td>\n";
				echo $eventDetails->getShortDetails();
				echo "</td>\n";
				echo "<td class=\"programCentre\">\n";
				echo $heatnumber;
				echo " of ";
				echo $numheats;
				echo "</td>\n";
				echo "<td class=\"programCentre\">\n";
				echo $lanenumber;
				echo "</td>\n";
				echo "<td class=\"programCentre\">\n";
				echo sw_formatSecs($seedtime);
				echo "</td>\n";
				echo "<td class=\"programCentre\">\n";
				
				$formFinalTime = sw_formatSecs($finaltime);
				
				if ($formFinalTime == "NT") {
	
					// Handle did not swims
					if (strtotime($meetFinish . " 23:59:59") < time()) {
					
						//echo "DNS\n";
						
					}
					
				} else {
					
					echo $formFinalTime;
					
				}
				
				echo "</td>\n";
				echo "<td class=\"programCentre\">\n";
				
				if ($heatplace != 0) {
				
					echo $heatplace;
					
				}
				
				echo "</td>\n";
				echo "<td class=\"programCentre\">\n";
				
				if ($finalplace != 0) {
	
					echo $finalplace;
					
				}
				
				echo "</td>\n";
				echo "<td class=\"programCentre\">\n";
				
				if ($ev_score != 0) {
				
					echo $ev_score;
					
				}
				
				echo "</td>\n";
				echo "</tr>\n";
				
			}
			
			echo "</tbody>\n";
			echo "</table>\n";
			
		//}
				
	}
	
	public function outputMemberSelector($selectName, $selectId) {
		
		// Get a list of all members from this club
		$athList = $GLOBALS['db']->getAll("SELECT * FROM eprogram_athletes WHERE meet_id = ? 
				ORDER BY team_no;", array($this->meetId));
		db_checkerrors($athList);
		
		echo "<select name=\"$selectName\" id=\"$selectId\">\n";
		echo "<option></option>\n";
		
		foreach ($athList as $a) {
			
			$memId = $a[1];
			
			if ($a[4] != "") {
				
				$memName = $a[4] . ' ' . $a[5];
				
			} else {
				
				$memDetails = new Member();
				$memDetails->loadId($memId);
				$memName = $memDetails->getFullname();
				
			}
			
			echo "<option value=\"$memId\">$memName</a>\n";
			
		}
		
		echo "</select>\n";
		
	}
	
	public function outputTeamSelector($selectName, $selectId) {
		
		// Get a list of all teams
		$teamList = $GLOBALS['db']->getAll("SELECT * FROM eprogram_teams WHERE meet_id = ?",
				 array($this->meetId));
		db_checkerrors($teamList);
		
		echo "<select name=\"$selectName\" id=\"$selectId\">\n";
		echo "<option></option>\n";
		
				foreach ($teamList as $t) {
					
				$clubId = $t[1];
				$teamNo = $t[2];
					
				if ($t[1] == 0) {
		
					$clubName = $t[4];
		
				} else {
		
					$clubDetails = new Club();
					$clubDetails->load($clubId);
					$clubName = $clubDetails->getName();
					
					// Export only QLD Teams
					echo "<option value=\"$teamNo\">$clubName</a>\n";
		
				}
					
				// echo "<option value=\"$teamNo\">$clubName</a>\n";
					
		}
		
		echo "</select>\n";
		
	}
	
	public function outputTeamProgram($teamId) {
		
		if ($teamId != 0) {
		
			echo "<h4>Team Program</h4>\n";
			
			// Get Meet Date
			$meetDetails = new Meet();
			$meetDetails->loadMeet($this->meetId);
			$meetStart = $meetDetails->getStartDate();
			$meetFinish = $meetDetails->getEndDate();
			
			echo "<h4>Team Details</h4>\n";
			
			$teamDetails = $GLOBALS['db']->getRow("SELECT * FROM eprogram_teams WHERE team_no = ? AND 
					meet_id = ?", array($teamId, $this->meetId));
			db_checkerrors($teamDetails);
			
			if ($teamDetails[1] != 0) {
				
				$clubDetails = new Club();
				$clubDetails->load($teamDetails[1]);
				$clubCode = $clubDetails->getCode();
				$clubName = $clubDetails->getName();
				
			} else {
				
				$clubCode = $teamDetails[3];
				$clubName = $teamDetails[4];
				
			}
			
			// Get count of competitors for this team
			$numComp = $GLOBALS['db']->getOne("SELECT count(*) FROM eprogram_athletes WHERE meet_id = ? and 
					team_no = ?;", array($this->meetId, $teamId));
			db_checkerrors($numComp);
			
			echo "<p>\n";
			echo "<label>Team Name: </label>$clubName<br />\n";
			echo "<label>Club Code: </label>$clubCode<br />\n";
			echo "<label>Number of Competitors: </label>$numComp<br />\n";
			echo "</p>\n";
			
			// Get a list of athletes from this club
			// Get list of athletes same age and age group
			$athList = $GLOBALS['db']->getAll("SELECT eprogram_athletes.*, SUM(eprogram_entry.ev_score) as score
						, count(DISTINCT eprogram_entry.event_ptr) as numevents FROM eprogram_athletes, eprogram_entry WHERE eprogram_athletes.team_no = ?
						AND eprogram_athletes.meet_id = ? AND eprogram_athletes.ath_no = eprogram_entry.ath_no AND
						eprogram_entry.meet_id = ? GROUP BY eprogram_entry.ath_no ORDER BY score DESC;",
					array($teamId, $this->meetId, $this->meetId));
			db_checkerrors($athList);
			
			echo "<h5>Competitors</h5>\n";
			
			echo "<table class=\"list\">\n";
			echo "<thead class=\"list\">\n";
			echo "<tr>\n";
			echo "<th>Name</th>\n";
			echo "<th>Age Group</th>\n";
			echo "<th>Events</th>\n";
			echo "<th>Points</th>\n";
			echo "</tr>\n";
			echo "</thead>\n";
			echo "<tbody class=\"list\">\n";
				
			
			
			foreach ($athList as $a) {
			
				$curAth = $a[2];
				$curTeam = $a[3];
				$curScore = $a[10];
				$curMemberId = $a[1];
				$curMemberAge = $a[9];
			
				if ($curMemberId != 0) {
			
					$curMemberDetails = new Member();
					$curMemberDetails->loadId($curMemberId);
					$curMemberName = $curMemberDetails->getFullname();
					$curMemberAgeGroup = $curMemberDetails->getAgeGroup($meetStart);
			
				} else {
						
					$curMemberName = $a[4] . ' ' . $a[5];
					
					$curMemberAgeGroup = $GLOBALS['db']->getOne("SELECT groupname FROM age_groups WHERE
							age_groups.set = '1' AND min <= ? AND max >= ?;", array($curMemberAge, 
							$curMemberAge));
					db_checkerrors($curMemberAgeGroup);
						
				}
			
				
			
				// Get score for this member
				//$curScore = $GLOBALS['db']->getOne("SELECT sum(ev_score) FROM eprogram_entry WHERE ath_no = ?
				//		AND meet_id = ?;", array($curAth, $this->meetId));
				//db_checkerrors($curScore);
			
				echo "<tr class=\"list\">\n";
				echo "<td>\n";
				$meetId = $this->meetId;
				if ($curMemberId != "") {
					
					echo "<a href=\"eprogramq.php?id=$meetId&member=$curMemberId&individualProg=1\">\n";
					
				} else {
					
					echo "<a href=\"eprogramq.php?id=$meetId&ath_no=$curAth&individualProg=1\">\n";
					
				}
				echo $curMemberName;
				echo "</a>\n";
				echo "</td>\n";
				echo "<td>\n";
				echo $curMemberAgeGroup;
				echo "</td>\n";
				echo "<td class=\"programCentre\">\n";
				echo $a[11];
				echo "</td>\n";
				echo "<td class=\"programCentre\">\n";
				echo "$curScore";
				echo "</td>\n";
				echo "</tr>\n";
			
				}
										
			echo "</tbody>\n";
			echo "</table>\n";
			
			echo "<h4>Events</h4>\n";

			$eventList = $GLOBALS['db']->getAll("SELECT * FROM eprogram_events WHERE meet_id = ? 
					AND event_ptr IN 
					(SELECT event_ptr FROM eprogram_entry WHERE meet_id = ? AND ath_no IN 
					(SELECT ath_no FROM eprogram_teams WHERE team_no = ? AND meet_id = ?)) ORDER BY event_ptr;",
					array($this->meetId, $this->meetId, $teamId, $this->meetId));
			db_checkerrors($eventList);
			
			foreach ($eventList as $e) {
				
				$eventDetails = new MeetEvent();
				$eventDetails->load($e[1]);
				$event_ptr = $e[2];
				$numHeats = $e[3];
				
				$progNum = $eventDetails->getProgNumber();
				$eventName = $eventDetails->getName();
				$eventD = $eventDetails->getShortDetails();
				
				// Get list of all team entrants in this event
				$swimList = $GLOBALS['db']->getAll("SELECT * FROM eprogram_entry WHERE meet_id = ? AND
						ath_no IN (SELECT ath_no FROM eprogram_athletes WHERE team_no = ? and meet_id = ?) AND
						event_ptr = ? ORDER BY heatnumber, lanenumber", array($this->meetId, $teamId, $this->meetId, $event_ptr));
				db_checkerrors($swimList);
				
				if (count($swimList) > 0) {
				
					echo "<div class=\"eProgramTitle\" id=\"\">Event $progNum - $eventD $eventName</div>\n";
					
					echo "<table class=\"list\" width=\"100%\">\n";
					echo "<thead class=\"list\">\n";
					echo "<tr>\n";
					echo "<th width=\"5%\">\n";
					echo "Heat\n";
					echo "</th>\n";
					echo "<th width=\"5%\">\n";
					echo "Lane\n";
					echo "</th>\n";
					echo "<th width=\"20%\">\n";
					echo "Name\n";
					echo "</th>\n";
					echo "<th width=\"15%\">\n";
					echo "Age Group\n";
					echo "</th>\n";
					echo "<th>\n";
					echo "Seed Time\n";
					echo "</th>\n";
					echo "<th>\n";
					echo "Heat Place\n";
					echo "</th>\n";
					echo "<th>\n";
					echo "Age Group Place\n";
					echo "</th>\n";
					echo "<th>\n";
					echo "Final Time\n";
					echo "</th>\n";
					echo "<th>\n";
					echo "Points\n";
					echo "</th>\n";
					echo "</tr>";
					echo "</thead>\n";
					echo "<tbody class=\"list\">\n";
					
					foreach ($swimList as $s) {
						
						$ath_no = $s[2];
						$athDetails = $GLOBALS['db']->getRow("SELECT * FROM eprogram_athletes WHERE
								meet_id = ? AND ath_no = ?", array($this->meetId, $ath_no));
						db_checkerrors($athDetails);
						$curMemberAge = $athDetails[9];
						
						
						if ($athDetails[1] != 0) {
							
							$memberId = $athDetails[1];
							$memberDetails = new Member();
							$memberDetails->loadId($memberId);
							$memberName = $memberDetails->getFullname();
							$memberAgeGroup = $memberDetails->getAgeGroup($meetStart);
							
						} else {
							
							$memberName = $athDetails[4] . ' ' . $athDetails[5];
							
						}
								
						echo "<tr class=\"list\">\n";
						echo "<td class=\"programCentre\">\n";
						// Heat number of number of heats
						echo $s[3];
						echo " of ";
						echo $numHeats;
						echo "</td>\n";
						echo "<td class=\"programCentre\">\n";
						// Lane Number
						echo $s[4];
						echo "</td>\n";
						echo "<td>\n";
						if (isset($memberId)) {
							echo "<a href=\"$this->url?id=$this->meetId&member=$memberId&individualProg=1\">\n";
						}
						echo "$memberName";
						if (isset($memberId)) {
						echo "</a>\n";
						}
						echo "</td>\n";
						echo "<td>\n";
						
						$memberAgeGroup = $GLOBALS['db']->getOne("SELECT groupname FROM age_groups WHERE
								age_groups.set = '1' AND min <= ? AND max >= ?;", array($curMemberAge,
															$curMemberAge));
						db_checkerrors($memberAgeGroup);
						
						echo $memberAgeGroup;
						echo "</td>\n";
						echo "<td class=\"programTime\">\n";
						echo sw_formatSecs($s[5]);
						echo "</td>\n";
						echo "<td class=\"programCentre\">\n";
						if ($s[6] != 0) {
							
							echo $s[6];
							
						}
						echo "</td>\n";
						echo "<td class=\"programCentre\">\n";
						if ($s[7]) {
	
							echo $s[7];
							
						}
						echo "</td>\n";
						echo "<td class=\"programTime\">\n";
						$formFinal = sw_formatSecs($s[8]);
						
						if ($formFinal != "NT") {
						
							echo $formFinal;
						 
						} else {
							
							if (strtotime($meetFinish . " 23:59:59") < time()) {
								
								//echo "DNS";
								
							}
							
						}
						
						echo "</td>\n";
						
						echo "<td class=\"programCentre\">\n";
						if ($s[9] != 0) {
							echo $s[9];
						}
						echo "</td>\n";
						echo "</tr>\n";
						
					}
					
					echo "</tbody>\n";
					echo "</table>\n";
				
				}
				
			}
			
		}
			
	}
	
}


?>