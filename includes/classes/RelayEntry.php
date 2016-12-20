<?php

// Entry to a relay
class RelayEntry {
	
	private $id;
	private $meetId;
	private $clubId;
	private $meetEventId;
	private $seedTime;	// Seedtime in seconds
	private $teamName;
	private $letter; // Letter of team, A etc
	private $ageGroup;
	
	private $members;	// Array of RelayEntryMembers
	
	// Load relay by ID
	public function load($id) {
		
		$rDet = $GLOBALS['db']->getRow("SELECT * FROM meet_entries_relays 
				WHERE id = ?;", array($id), DB_FETCHMODE_ASSOC);
		db_checkerrors($rDet);
		
		$this->id = $rDet['id'];
		$this->meetId = $rDet['meet_id'];
		$this->meetEventId = $rDet['meetevent_id'];
		$this->seedTime = $rDet['seedtime'];
		$this->teamName = $rDet['teamname'];
		$this->letter = $rDet['letter'];
		$this->ageGroup = $rDet['agegroup'];
		
		// Load members
		$rMembers = $GLOBALS['db']->getAll("SELECT id FROM meet_entries_relays_members 
				WHERE relay_team = ? ORDER BY leg ASC", array($id));
		db_checkerrors($rMembers);
		
		foreach ($rMembers as $m) {
			
			$this->members[] = new RelayEntryMember($m[0]);
			
		}
		
	}
	
	// Create relay entry in DB
	public function create() {
		
		// Create the relay team
		$rInsert = $GLOBALS['db']->query("INSERT INTO meet_entries_relays (meet_id, club_id, 
				meetevent_id, teamname, letter, agegroup, seedtime) VALUES (?,?,?,?,?,?,?);",
				array ($this->meetId, $this->clubId, $this->meetEventId, $this->teamName, 
				$this->letter, $this->ageGroup, $this->seedTime));
		db_checkerrors($rInsert);
		
		$this->id = mysql_insert_id();
		
		// Add Members to the relay team
		if (count($this->members) > 0) {
			
			foreach ($this->members as $m) {
				
				// Set the relay ID associated with this relay
				$m->setRelayId($this->id);
				
				// Add the relay members to the db
				$m->create();
				
			}
			
		}

		addlog("RelayEntry.php", "Created relay entry $this->id");
		
	}
	
	// Set meet
	public function setMeet($m) {
		
		$this->meetId = $m;
		
	}
	
	// Set club
	public function setClub($c) {
	
		$this->clubId = $c;
	
	}
	
	// Set event
	public function setEvent($e) {
	
		$this->meetEventId = $e;
	
	}
	
	// Set letter
	public function setLetter($l) {
	
		$this->letter = $l;
	
	}
	
	// Set Seed Time
	public function setSeedTime($s) {
		
		$this->seedTime = $s;
		
	}

    /**
     * @return mixed
     */
    public function getTeamName()
    {
        return $this->teamName;
    }

    /**
     * @param mixed $teamName
     */
    public function setTeamName($teamName)
    {
        $this->teamName = $teamName;
    }


	
	// Add members
	public function addMember($position, $memberId) {
		
		$nMember = new RelayEntryMember();
		$nMember->setMember($memberId);
		$nMember->setLeg($position);
		
		$this->members[] = $nMember;
		
	}
	
	// Calculate Age Group
	public function calcAgeGroup() {
		
		// Meet Start Date
		$meetDetails = new Meet();
		$meetDetails->loadMeet($this->meetId);
		$meetStart = $meetDetails->getStartDate();
		
		// Count number of entrys
		$numMembers = count($this->members);
		
		// Get Total Age
		$cumAge = 0;
		$numM = 0;
		$numF = 0;
		
		foreach ($this->members as $m) {
			
			$cumAge = $cumAge + $m->getAge($meetStart);
			
			if ($m->getGender() == "M") {
				
				$numM++;
				
			}
			
			if ($m->getGender() == "F") {
			
				$numF++;
			
			}
			
		}
		
		if ($numM == 4) {
			
			$gender = 1;
			
		} elseif ($numF == 4) {
			
			$gender = 2;
			
		} else {
			
			$gender = 3;
			
		}
		
		// Find appropriate age group
		$ageId = $GLOBALS['db']->getOne("SELECT id FROM age_groups WHERE age_groups.set = 1 
				AND min <= ? AND max >= ? AND swimmers = ? AND gender = ? LIMIT 1;", 
				array($cumAge, $cumAge, $numMembers, $gender));
		db_checkerrors($ageId);
		
		$this->ageGroup = $ageId;
		
	}
	
	// Check if existing relay team has same letter
	public function checkLetter() {
		
		$letterCheck = $GLOBALS['db']->getAll("SELECT * FROM meet_entries_relays 
				WHERE meet_id = ? 
				AND club_id = ? 
				AND meetevent_id = ? 
				AND letter = ?
				AND agegroup = ?;",
				array($this->meetId, $this->clubId, $this->meetEventId, $this->letter, $this->ageGroup));
		db_checkerrors($letterCheck);
		
		if (count($letterCheck) > 0) {
			
			return false;
			
		} else {
			
			return true;
			
		}
		
	}

	// Get Next letter
    public function getNextLetter() {

        $firstLetter = ord("A");
        $letterAvailable = false;
        $curLetter = $firstLetter;

        while($letterAvailable != true) {

            $this->letter = chr($curLetter);

            if ($this->checkLetter() === false) {
                $curLetter++;
            } else {
                $letterAvailable = true;
            }

        }

    }
	
	// Get Program Number
	public function getProgNum() {
		
		$meetEventDetails = new MeetEvent();
		$meetEventDetails->load($this->meetEventId);
		return $meetEventDetails->getProgNumber();
		
	}
	
	// Get Relay Letter
	public function getLetter() {
		
		return $this->letter;
		
	}
	
	// Get Age Group Id
	public function getAgeGroup() {
		
		return $this->ageGroup;
		
	}
	
	// Set Age Group
	public function setAgeGroup($aid) {
		
		$this->ageGroup = $aid;
		
	}
	
	// Get Age Group Text Description
	public function getAgeGroupText() {
		
		$ageGroupDet = $GLOBALS['db']->getRow("SELECT * FROM age_groups WHERE id = ?;", 
				array($this->ageGroup));
		db_checkerrors($ageGroupDet);
		
		$ageText = $ageGroupDet[5];
		
		return $ageText;
		
	}
	
	public function getAgeGroupShort() {
		
		$ageGroupDet = $GLOBALS['db']->getRow("SELECT * FROM age_groups WHERE id = ?;", 
				array($this->ageGroup));
		db_checkerrors($ageGroupDet);
		
		if ($ageGroupDet[4] == 1) {
			
			$ageText = "M";
			
		} elseif ($ageGroupDet[4] == 2) {
			
			$ageText = "F";
			
		} else {
			
			$ageText = "X";
			
		}
		
		$ageText = $ageText . $ageGroupDet[2] . "-" . $ageGroupDet[3];
		return $ageText;
		
	}
	
	// Get Member List
	public function getMemberList() {
		
		$memList = "";
		$first = true;
		
		if (count($this->members) > 0) {
			
			foreach ($this->members as $m) {
				
				$genderLetter = $m->getGender();
				
				$mAge = "(" . $genderLetter . $m->getAge(date('Y-m-d')) . ")";
	
				if ($first == true) {
					
					$memList = $m->getFullName() . $mAge;
					$first = false;
					
				} else {
					
					$memList = $memList . ', ' . $m->getFullName() . $mAge;
					
				}
				
			}

		}

		return $memList;
		
	}
	
	// Get Seed Time
	public function getSeedTime() {
		
		return sw_formatSecs($this->seedTime);
		
	}
	
	// Delete a relay
	public function delete() {
		
		if (count($this->members) > 0) {
		
			foreach ($this->members as $m) {
					
				$m->delete();
					
			}
			
		}
		
		$delete = $GLOBALS['db']->query("DELETE FROM meet_entries_relays WHERE id = ?",
			array($this->id));
		db_checkerrors($delete);
		
		return true;
		
	}
	
	// Get Club ID
	public function getClub() {
		
		return $this->clubId;
		
	}
	
	// Get Meet ID
	public function getMeet() {
	
		return $this->meetId;
	
	}
	
	// Get Meet Event ID
	public function getMeetEvent() {
	
		return $this->meetEventId;
	
	}

	public function setClubCode($code) {

        $club = new Club();
        $club->load($code);
        $this->clubId = $club->getId();

    }
	
}