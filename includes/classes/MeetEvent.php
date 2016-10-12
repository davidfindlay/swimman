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
	
	// Create this new event in database
	public function create() {
		
		$meetId = mysql_real_escape_string($this->meet_id);
		$progNum = mysql_real_escape_string($this->progNum);
		$progSuf = mysql_real_escape_string($this->progSuf);
		$type = mysql_real_escape_string($this->type);
		$discipline = mysql_real_escape_string($this->discipline);
		$legs = mysql_real_escape_string($this->legs);
		$distance = mysql_real_escape_string($this->distance);
		$eventName = mysql_real_escape_string($this->eventName);
		$eventFee = mysql_real_escape_string($this->eventFee);
	
		// Check if MeetEvent has already been created
		$check = $GLOBALS['db']->getRow("SELECT * FROM meet_events WHERE meet_id = '$meetId' AND prognumber = '$progNum' AND progsuffix = '$progSuf';");
		db_checkerrors($check);
		
		if (!isset($check)) {
		
			$insert = $GLOBALS['db']->query("INSERT INTO meet_events 
					(meet_id, type, discipline, legs, distance, eventname, prognumber, progsuffix, eventfee) 
					VALUES ('$meetId', '$type', '$discipline', '$legs', '$distance', '$eventName', '$progNum', '$progSuf', 
					'$eventFee');");
			db_checkerrors($insert);
		
			$this->id = mysql_insert_id();
		
			// echo "INSERT INTO meet_events (meet_id, type, discipline, legs, distance, eventname, prognumber, progsuffix, eventfee) VALUES ('$meetId', '$type', '$discipline', '$legs', '$distance', '$eventName', '$progNum', '$progSuf', '$eventFee')";
		
			return $this->id;
			
		} else {
			
			return false;
			
		}
		
	}
	
	// Update this event in database
	public function update() {
		
		$progNum = mysql_real_escape_string($this->progNum);
		$progSuf = mysql_real_escape_string($this->progSuf);
		$eventName = mysql_real_escape_string($this->eventName);
		$eventFee = mysql_real_escape_string($this->eventFee);
		
		$update = $GLOBALS['db']->query("UPDATE meet_events SET prognumber = '$progNum', progsuffix = '$progSuf', eventname = '$eventName', eventfee = '$eventfee' WHERE id = '$this->id';");
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
		
		$this->id = mysql_real_escape_string($eventId);
		
		$eventData = $GLOBALS['db']->getRow("SELECT * FROM meet_events WHERE id = '$this->id';");
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
		
		$gender = $this->getGender();
		if ($gender != 3) {
			
			if ($gender == 1) {
				
				$genderText = "Mens' ";
				
			} else {
				
				$genderText = "Womens' ";
				
			}
			
		} else {
			
			$genderText = "";
			
		}
		
		if ($this->legs > 1) {
		
			return $this->legs . "x" . $distName . " $genderText" . $strokeName;
		
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


}