<?php

class TMEntryFile {
	
	private $meetId;
	private $clubId;
	private $meetObj;
	private $entries;  // Array of MeetEntries

    private $event = "";      // Set if we just want one event

    private $relayOnly = "";
	
	private $filename;
	
	// Set Meet Id
	public function setMeet($m) {
		
		$this->meetId = intval($m);
		$this->meetObj = new Meet;
		$this->meetObj->loadMeet($this->meetId);
		
		// echo "Loaded " . $this->meetObj->getName();
		
	}

	public function setEvent($eventId) {
        $this->event = $eventId;
    }

    /**
     * @param string $relayOnly
     */
    public function setRelayOnly($relayOnly)
    {
        $this->relayOnly = $relayOnly;
    }

	public function setClub($c) {
		
		$this->clubId = $c;
		
	}
	
	public function getFileName() {
		
		return $this->filename;
		
	}
	
	public function createArchive() {
		
		// Delete an existing zip
		if (is_readable($GLOBALS['home_dir'] . '/masters-data/entries/' . $this->filename . ".zip")) {
			
			unlink($GLOBALS['home_dir'] . '/masters-data/entries/' . $this->filename . ".zip");
			
		}

		// Zip up data
		$zip = new ZipArchive();
		
		$zip->open($GLOBALS['home_dir'] . '/masters-data/entries/'. $this->filename . ".zip", ZipArchive::CREATE);
		
		if (is_readable(($GLOBALS['home_dir'] . '/masters-tmp/'. $this->filename . ".hy3"))) {
			
			$zip->addFile($GLOBALS['home_dir'] . '/masters-tmp/'. $this->filename . ".hy3", $this->filename . ".hy3");
			//echo "wrtie file";
				
		} else {
			
			// Can't read file
			//echo "can't write file";
			
		}
		
		$zip->close();
		
	}
	
	// Get entries 
	public function loadEntries() {
		
		if (isset($this->clubId)) {
			
			$entryList = $GLOBALS['db']->getAll("SELECT id FROM meet_entries
				WHERE meet_id = ? AND club_id = ? AND cancelled = 0;", array($this->meetId, $this->clubId));
			db_checkerrors($entryList);
			
		} else { 
		
			$entryList = $GLOBALS['db']->getAll("SELECT id FROM meet_entries 
				WHERE meet_id = ? AND cancelled = 0 ORDER BY club_id;", array($this->meetId));
			db_checkerrors($entryList);
			
		}
		
		foreach ($entryList as $e) {
			
			$eId = $e[0];
			$mEntry = new MeetEntry();
			$mEntry->loadId($eId);
			
			$entries[] = $mEntry;
			
		}
		
	}
	
	// Create HY3 File
	public function createHY3File() {
		
		if (isset($this->clubId)) {
			
			$clubDet = new Club();
			$clubDet->load($this->clubId);
			$clubCode = $clubDet->getCode();
			
			$tempFileName = $clubCode . "-Entries" . $this->meetId;
			
		} else {
		
			$tempFileName = "All-Entries" . $this->meetId;
			
		}
		
		$this->filename = $tempFileName;
		
		$entryCount = 1;
		
		if (is_writable($GLOBALS['home_dir'] . '/masters-tmp/')) {
			
			if (!$handle = fopen($GLOBALS['home_dir'] . '/masters-tmp/' . $tempFileName . '.hy3', "w")) {
				
				echo "Unable to write file!";
				return false;
				
			}
			
			//echo "<pre>\n";
		
			// Create A section
			$line = $this->HY3aSection();
			//echo $line;
			fwrite($handle, $line);
			
			// Create B section
			$line = $this->HY3bSection();
			//echo $line;
			fwrite($handle, $line);
			
			// Create C section
			if (isset($this->clubId)) {
			
				$clubList = $GLOBALS['db']->getAll("SELECT DISTINCT(club_id) FROM meet_entries
					WHERE meet_id = ? AND club_id = ?;", array($this->meetId, $this->clubId));
				
			} else {

				$clubList = $GLOBALS['db']->getAll("SELECT DISTINCT(club_id) FROM meet_entries
					WHERE meet_id = ?;", array($this->meetId));
				
			}
			db_checkerrors($clubList);
			
			// Initialise map of entrant numbers
			$mapEntrants = array();
			
			foreach ($clubList as $c) {
			
				// Create a Club section
				$cId = $c[0];
				$line = $this->HY3cSection($cId);
				//echo $line;
				fwrite($handle, $line);
				
				// Create individual entries
				$memberList = $GLOBALS['db']->getAll("SELECT DISTINCT(member_id) FROM meet_entries
						WHERE meet_id = ? AND club_id = ?", array($this->meetId, $cId));
				db_checkerrors($memberList);
				
				foreach ($memberList as $m) {
				
					$memId = $m[0];
					
					$memberDetails = new Member();
					$memberDetails->loadId($memId);
					
					$lineE = "D1" .  $memberDetails->getGender();
					$lineE = $lineE . str_pad($entryCount, 5, ' ', STR_PAD_LEFT);
					$lineE = $lineE . str_pad($memberDetails->getSurname(), 20);
					$lineE = $lineE . str_pad($memberDetails->getFirstname(), 41);
					$lineE = $lineE . str_pad($memberDetails->getMSANumber(), 19);
					$lineE = $lineE . date('mdY', strtotime($memberDetails->getDob()));
					
					// Update map for potential relay entries
					$mapEntrants["m" . $memId] = $entryCount;
										
					// Calculate date
					$dobDt = new DateTime($memberDetails->getDob());
					$ageUpDt = new DateTime($this->meetObj->getAgeUpDate());
					
					$ageInterval = $ageUpDt->diff($dobDt);
					$lineE = $lineE . str_pad($ageInterval->format('%Y'), 3, ' ', STR_PAD_LEFT);
					
					$lineE = $lineE . "                             ";
					
					$lineE = $lineE . $this->HY3checksum($lineE) . "\r\n";
					
					//echo $lineE;
					fwrite($handle, $lineE);
					
					
					// Output lines for this member's individual event entries
					$memEntry = new MeetEntry($memId, $cId, $this->meetId);
					$memEntry->load();
					
					$entryList = $memEntry->getEvents();
					//$entryList = array_reverse($entryList);

					
					if (count($entryList) > 0) {
					
						foreach ($entryList as $e) {

                            // Skip cancelled events
						    if ($e->getCancelled()) {
                                continue;
                            }

							// Check if this entry is a relay entry
							$eventId = $e->getEventId();

                            // Handle if if just exporting one event
                            if ($this->event != "") {
                                if ($eventId != $this->event) {
                                    continue;
                                }
                            }

                            // Handle if only exporting relays
                            if ($this->relaysOnly) {

                                // Get event details
                                $eventDetails = new MeetEvent();
                                $eventDetails->load($eventId);

                                if ($eventDetails->getLegs() == 1) {
                                    continue;
                                }

                            }
							
							$eventDet = new MeetEvent();
							$eventDet->load($eventId);
							
							if ($eventDet->getLegs() > 1) {
								
								continue;
									
							}
							
							$lineV = "E1" . $memberDetails->getGender();
							$lineV = $lineV . str_pad($entryCount, 5, ' ', STR_PAD_LEFT);
							$lineV = $lineV . str_pad(substr($memberDetails->getSurname(), 0, 5), 5) . "XX";
							
							// Get distance of event
							$eId = $e->getEventId();
							
							$eventDetails = new MeetEvent();
							$eventDetails->load($eId);
							
							$lineV = $lineV . str_pad($eventDetails->getDistanceMetres(), 6, ' ', STR_PAD_LEFT);
							$lineV = $lineV . $eventDetails->getHY3Discipline();
							$lineV = $lineV . "  0109      0.00";
							$lineV = $lineV . str_pad($eventDetails->getProgNumber(), 3, ' ', STR_PAD_LEFT);
							
							// Get course of first event
							$meetCourse = $this->meetObj->getMeetCourse();
							
							switch ($meetCourse) {
									
								case 'LCM':
									$courseLetter = "L";
									break;
								case 'SCM':
									$courseLetter = "S";
									break;
										
							}
							
							$lineV = $lineV . str_pad("0" . $courseLetter, 10, ' ', STR_PAD_LEFT);
							
							// Put in time
							$seedTime = number_format(floatval($e->getSeedTime()), 2, '.', '');
							$lineV = $lineV . str_pad($seedTime, 8, ' ', STR_PAD_LEFT) . $courseLetter;
							$lineV = $lineV . "                               ";
							
							if ($seedTime != "0.00") {
	
								$lineV = $lineV . "C";
	
							} else {
								
								$lineV = $lineV . " ";
								
							}
							
							$lineV = $lineV . "                                    ";
							
							$lineV = $lineV . $this->HY3checksum($lineV) . "\r\n";
		
							//echo $lineV;
							fwrite($handle, $lineV);
							
						}
						
					}
					
					$entryCount++;
					
				}
				
				// Handle club relay entries
				$relayEntries = $GLOBALS['db']->getAll("SELECT * FROM meet_entries_relays 
						WHERE meet_id = ? AND club_id = ?;", array($this->meetId, $cId));
				db_checkerrors($relayEntries);
				
				foreach ($relayEntries as $r) {
					
					$rId = $r[0];
					$relayLetter = $r[5];
					$ageGroupId = $r[6];
					$meetEventId = $r[3];
					$seedTime = $r[7];
					
					$clubDet = new Club();
					$clubDet->load($cId);
					$clubCode = $clubDet->getCode();
					
					// Get Event Details
					$eventDet = new MeetEvent();
					$eventDet->load($meetEventId);

                    // Handle if if just exporting one event
                    if ($this->event != "") {
                        if ($meetEventId != $this->event) {
                            continue;
                        }
                    }

					// Line start and club code
					$lineR = "";
					$lineR = "F1" . str_pad(trim($clubCode), 5, ' ') . $relayLetter . " ";
					
					$ageGroupMin = $GLOBALS['db']->getOne("SELECT min FROM age_groups WHERE id = ?;",
						array($ageGroupId));
					db_checkerrors($ageGroupMin);
					
					// Get the gender letter
					switch($eventDet->getGender()) {
					
						case 1:
							$genderLetter = "M";
							break;
						case 2:
							$genderLetter = "F";
							break;
						case 3:
							$genderLetter = "X";
							break;
					}
					
					$lineR = $lineR . str_pad($ageGroupMin, 3, ' ', STR_PAD_LEFT) . $genderLetter . $genderLetter . $genderLetter;
					
					$lineR = $lineR . str_pad(intval($eventDet->getDistanceMetres()) * intval($eventDet->getLegs()), 6, ' ', STR_PAD_LEFT);
					$lineR = $lineR . $eventDet->getHY3Discipline();
					$lineR = $lineR . "  0109      0.00";
					$lineR = $lineR . str_pad($eventDet->getProgNumber(), 3, ' ', STR_PAD_LEFT);
						
					// Get course of first event
					$meetCourse = $this->meetObj->getMeetCourse();
						
					switch ($meetCourse) {
							
						case 'LCM':
							$courseLetter = "L";
							break;
						case 'SCM':
							$courseLetter = "S";
							break;
					
					}
						
					$lineR = $lineR . str_pad("0" . $courseLetter, 10, ' ', STR_PAD_LEFT);
					
					// Put in time
					$seedTime = number_format(floatval($seedTime), 2, '.', '');
					$lineR = $lineR . str_pad($seedTime, 8, ' ', STR_PAD_LEFT) . $courseLetter;
					
					$lineR = str_pad($lineR, 128, ' ', STR_PAD_RIGHT);
					$lineR = $lineR . $this->HY3checksum($lineR) . "\r\n";
					
					fwrite($handle, $lineR);
					
					// Second line
					$lineR = "F3" . $genderLetter;
					
					// Get members of this relay
					$relayMembers = $GLOBALS['db']->getAll("SELECT * FROM meet_entries_relays_members 
							WHERE relay_team = ? ORDER BY leg ASC;", array($rId));
					db_checkerrors($relayMembers);
					
					foreach ($relayMembers as $m) {
						
						$mId = $m[2];
						$mLeg = $m[3];
						
						$mEntrant = $mapEntrants["m" . $mId];
						
						$memDet = new Member();
						$memDet->loadId($mId);
						
						$lineR = $lineR . str_pad($mEntrant, 5, ' ', STR_PAD_LEFT);
						$lineR = $lineR . str_pad(substr($memDet->getSurname(), 0, 5), 5) . "F";
						$lineR = $lineR . $mLeg . $memDet->getGender();
						
					}
					
					$lineR = str_pad($lineR , 128, ' ', STR_PAD_RIGHT);
					$lineR = $lineR . $this->HY3checksum($lineR) . "\r\n";

					//$lineR = print_r($mapEntrants, true);
					
					//echo $lineR;
					fwrite($handle, $lineR);
					
				}
				
			}
			
			//echo "</pre>\n";
			
		} else {
			
			echo "Unable to write hy3 file -";
			
		}
		
	}
	
	// Create HY3 A Section - File Header
	public function HY3aSection() {
		
		// First information
		$tmpLine = "A102Meet Entries             Hy-Tek, Ltd    Win-TM 6.0Ge  ";
		$tmpLine = $tmpLine . date('mdY');
		$tmpLine = $tmpLine . " ";
		$tmpLine = $tmpLine . str_pad(date('g:i A'), 8, ' ', STR_PAD_LEFT);
		$tmpLine = $tmpLine . "TEAM MANAGER Lite                                    ";
		
		return $tmpLine . $this->HY3checksum($tmpLine) . "\r\n";
		
	}
	
	// Create HY3 B Section - Meet Details
	public function HY3bSection() {
		
		$tmpLine = "B1" . str_pad(substr($this->meetObj->getName(), 0, 45), 45);
		$tmpLine = $tmpLine . str_pad(substr($this->meetObj->getLocation(), 0, 45), 45);
		
		$startDate = date('mdY', strtotime($this->meetObj->getStartDate()));	

		$endTs = $this->meetObj->getEndDate();
		
		if ($endTs == "0000-00-00") {
			
			$endDate = $startDate;
			
		} else {
		
			$endDate = date('mdY', strtotime($endTs));
			
		}
		
		$meetYear = date('Y', strtotime($this->meetObj->getStartDate()));
		$ageUpDate = "1231" . $meetYear;
		
		$tmpLine = $tmpLine. $startDate . $endDate . $ageUpDate . "            ";
		$tmpLine = $tmpLine . $this->HY3checksum($tmpLine) . "\r\n";
		
		$tmpLine2 = "B2                                                                                            ";
		$tmpLine2 = $tmpLine2 . "06";
		$tmpLine2 = $tmpLine2 . "  ";
		
		// Get course of first event
		$meetCourse = $this->meetObj->getMeetCourse();
		
		switch ($meetCourse) {
			
			case 'LCM': 
				$courseLetter = "L";
				break;
			case 'SCM':
				$courseLetter = "S";
				break;
			
		}
		
		$tmpLine2 = $tmpLine2 . $courseLetter;
		$tmpLine2 = $tmpLine2 . "   0.00";
		$tmpLine2 = $tmpLine2 . $courseLetter;
		$tmpLine2 = $tmpLine2 . "                     ";
		
		$tmpLine2 = $tmpLine2 . $this->HY3checksum($tmpLine2) . "\r\n";
		
		return $tmpLine . $tmpLine2;
		
	}
	
	// 
	public function HY3cSection($clubId) {
		
		$clubDetails = new Club();
		$clubDetails->load($clubId);
		$clubCode = trim($clubDetails->getCode());
		$clubName = substr(trim($clubDetails->getNameShortened()), 0, 30);

		$tmpLine = "C1" . str_pad($clubCode,5, ' ') . str_pad($clubName, 112, ' ') . str_pad("MAS", 9, ' ');
		$tmpLine = $tmpLine . $this->HY3checksum($tmpLine) . "\r\n";
		
		// Start Club line 2
		// Get club captain details
		$memberDetails = new Member();
		$memberDetails->loadId(26);
		
		$tmpLine2 = str_pad("C2", 104, ' ') . "AUS AUST                ";
		$tmpLine2 = $tmpLine2 . $this->HY3checksum($tmpLine2) . "\r\n";
		
		// Start Club line 3
		$tmpLine3 = str_pad("C3", 128, ' ');
		$tmpLine3 = $tmpLine3 . $this->HY3checksum($tmpLine3) . "\r\n";
		
		return $tmpLine . $tmpLine2 . $tmpLine3;
		
	}
	
	// HY3 Checksum
	public function HY3checksum($line) {
		
		$sumEven = 0;
		$sumOdd = 0;
		
		// Step through 128 characters taking the sums
		for ($i = 0; $i < 64; $i++) {
			
			$sumEven = $sumEven + ord(substr($line, ($i*2), 1));
			$sumOdd = $sumOdd + (ord(substr($line, (($i*2)+1), 1)) * 2);
			
		}
		
		$checkSum = intval((($sumEven + $sumOdd) / 21) + 205);
		$tens = intval(($checkSum / 10) % 10);
		$ones = intval($checkSum % 10);
		
		$retVal = $ones . $tens;
		
		return $retVal;
		
	}
	
}

?>