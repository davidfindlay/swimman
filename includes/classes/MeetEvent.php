<?php

class MeetEvent {
	
	private $id; 		// Database id
	private $meet_id; 	// Which meet is it in
	private $progNum;	// Program number e.g. 3
	private $progSuf; 	// Program number suffix, e.g b
	private $type;		// Type of event
	private $discipline;
	private $legs;		// If greater than 1 is a relay
	private $distance; 	// Distance per leg
	private $eventName; // Does this event have a name
	private $eventFee;	// Does this event have a fee on top of the meet fee
    private $deadline;  // Does this event have it's own deadline - MySQL date time format
	
	// Create this new event in database
	public function create() {
	
		// Check if MeetEvent has already been created
		$check = $GLOBALS['db']->getRow("SELECT * FROM meet_events WHERE meet_id = ? 
                  AND prognumber = ? AND progsuffix = ?;",
            array($this->meetId, $this->progNum, $this->progSuf));
		db_checkerrors($check);
		
		if (!isset($check)) {
		
			$insert = $GLOBALS['db']->query("INSERT INTO meet_events 
					(meet_id, type, discipline, legs, distance, eventname, prognumber, progsuffix, eventfee, deadline) 
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);",
                array($this->meet_id, $this->type, $this->discipline, $this->legs, $this->distance,
                    $this->eventName, $this->progNum, $this->progSuf, $this->eventFee, $this->deadline));
			db_checkerrors($insert);
		
			$this->id = mysql_insert_id();

			return $this->id;
			
		} else {
			
			return false;
			
		}
		
	}
	
	// Update this event in database
	public function update() {
		
		$update = $GLOBALS['db']->query("UPDATE meet_events SET prognumber = ?, 
              progsuffix = ?, 
              eventname = ?, 
              eventfee = ?,
              deadline = ?
              WHERE id = ?;",
            array($this->progNum, $this->progSuf, $this->eventName, $this->deadline, $this->id));
		db_checkerrors($update);
		
	}
	
	// Delete this event from database
	public function delete() {
		
		// Delete event group items featuring this one first
		$delete1 = $GLOBALS['db']->query("DELETE FROM meet_events_groups_items WHERE event_id = '$this->id';"); 
		db_checkerrors($delete1);
		
		// Delete entries second
		$delete2 = $GLOBALS['db']->query("DELETE FROM meet_events_entries WHERE event_id = '$this->id';");
		db_checkerrors($delete2);
		
		// Delete event
		$delete3 = $GLOBALS['db']->query("DELETE FROM meet_events WHERE id = '$this->id';");
		db_checkerrors($delete3);
		
	}
	
	// Load the event from the database
	public function load($eventId) {

	    $this->id = $eventId;
		
		$eventData = $GLOBALS['db']->getRow("SELECT * FROM meet_events WHERE id = ?;",
            array($this->id));
		db_checkerrors($eventData);
		
		$this->meet_id = $eventData[1];
		$this->progNum = $eventData[7];
		$this->progSuf = $eventData[8];
		$this->type = $eventData[2];
		$this->discipline = $eventData[3];
		$this->legs = $eventData[4];
		$this->distance = $eventData[5];
		$this->eventName = $eventData[6];
		$this->eventFee = $eventData[9];
		$this->deadline = $eventData[10];
		
		
	}
	
	// Update functions 
	public function setProgram($n, $s) {
		
		// TODO: add some validation here
		$this->progNum = $n;
		$this->progSuf = $s;		
		
	}
	
	public function setName($n) {
		
		$this->eventName = $n;		
		
	}
	
	public function getName() {
		
		return $this->eventName;
		
	}
	
	public function setFee($f) {
		
		$this->eventFee = $f;
		
	}
	
	public function setMeetId($id) {
		
		$this->meet_id = $id;
		
	}
	
	public function setType($t) {
		
		$this->type = $t;
		
	}
	
	public function setTypeName($tn) {
		
		$type = mysql_real_escape_string($tn);
		$typeId = $GLOBALS['db']->getOne("SELECT id FROM event_types WHERE typename = '$type';");
		db_checkerrors($typeId);
		
		$this->setType($typeId);
		
	}
	
	public function getType() {
		
		$typeName = $GLOBALS['db']->getOne("SELECT typename FROM event_types WHERE id = '$this->type';");
		db_checkerrors($typeName);
		
		return $typeName;
		
	}
	
	public function setLegs($l) {
	
		$this->legs = $l;
	
	}
	
	public function getLegs() {
		
		return $this->legs;
		
	}
	
	public function setDiscipline($d) {
	
		$this->discipline = $d;
	
	}
	
	public function setDisciplineName($dName) {
	
		$discName = mysql_real_escape_string($dName);
		$discId = $GLOBALS['db']->getOne("SELECT id FROM event_disciplines WHERE discipline = '$discName';");
		db_checkerrors($discId);
		
		$this->setDiscipline($discId);
	
	}
	
	public function setDistance($d) {
		
		$this->distance = $d;
		
	}
	
	public function setDistanceM($metres, $course) {
	
		$dMetres = mysql_real_escape_string($metres);
		$dCourse = mysql_real_escape_string($course);
		$distId = $GLOBALS['db']->getOne("SELECT id FROM event_distances WHERE metres = '$dMetres' AND course = '$dCourse';");
		db_checkerrors($distId);
		
		$this->setDistance($distId);
		
	}
	
	public function getDistanceMetres() {
		
		$metres = $GLOBALS['db']->getOne("SELECT metres FROM event_distances WHERE id = ?", array($this->distance));
		db_checkerrors($metres);
		
		return $metres;
		
	}
	
	// Returns the letter used in meet manager HY3 Files as a identifier for this stroke
	public function getHY3Discipline() {
		
		switch($this->discipline) {
			
			case 1: 
				return "A";
				break;
			case 2:
				return "C";
				break;
			case 3:
				return "D";
				break;
			case 4:
				return "B";
				break;
			case 5:
				return "E";	
				break;
			case 6:
				return "E";
				break;
			
		}
		
		return;
		
	}
	
	// Returns event program number
	public function getProgNumber() {
	
		return $this->progNum . strtoupper($this->progSuf);
	
	}
	
	// Gets event gender
	public function getGender() {
		
		$gender = 0;
		
		if (isset($this->type)) {
		
			$gender = $GLOBALS['db']->getOne("SELECT gender FROM event_types WHERE id = ?", $this->type);
			db_checkerrors($gender);
			
		}
		
		return $gender;
		
	}
	
	// Returns a short text detail of event
	public function getShortDetails() {
	
		$distName = $GLOBALS['db']->getOne("SELECT distance FROM event_distances WHERE id = '$this->distance';");
		db_checkerrors($distName);
		$strokeName = $GLOBALS['db']->getOne("SELECT discipline FROM event_disciplines WHERE id = '$this->discipline';");
		db_checkerrors($strokeName);

		if ($this->getGender() != 3) {
			
			if ($this->getGender() == 1) {
				
				$genderText = "Men's ";
				
			} else {
				
				$genderText = "Women's ";
				
			}
			
		} else {

		    if (($this->legs > 1)) {

		        $genderText = "Mixed ";

            } else {

                $genderText = "";

            }
			
		}
		
		if ($this->legs > 1) {
		
			return $this->legs . "x" . $distName . " $genderText" . $strokeName . " Relay";
		
		} else {
		
			return $distName . " " . $strokeName;
		
		}
	
	}
	
	// Returns the course of this event 
	public function getCourse() {
		
		$eventCourse = $GLOBALS['db']->getOne("SELECT course FROM event_distances WHERE id = ?",
			array($this->distance));
		db_checkerrors($eventCourse);
		
		return $eventCourse;
		
	}

    /**
     * @return discipline database id
     */
    public function getDiscipline()
    {
        return $this->discipline;
    }

    /**
     * @return mixed
     */
    public function getDeadline() {

        return $this->deadline;
    }

    /**
     * @param mixed $deadline
     */
    public function setDeadline($deadline) {

        // TODO check format
        $this->deadline = $deadline;

    }



}