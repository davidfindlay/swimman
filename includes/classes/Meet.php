<?php

// Meet class

class Meet {
	
	private $id;
	private $meetName;
	private $startDate;
	private $endDate;
	private $deadline;
	private $contactName;
	private $contactEmail;
	private $contactPhone;
	private $meetFee;
	private $mealFee;
	private $location;
	private $status;
	private $maxEvents;
	private $mealsIncluded;
	
	private $events;	// MeetEvents array object
	
	public function loadMeet($m) {
		
		$this->id = $m;
		
		$meetData = $GLOBALS['db']->getRow("SELECT * FROM meet WHERE id = '$this->id';");
		db_checkerrors($meetData);
		
		$this->meetName = $meetData[1];
		$this->startDate = $meetData[2];
		$this->endDate = $meetData[3];
		$this->deadline = $meetData[4];
		$this->contactName = $meetData[5];
		$this->contactEmail = $meetData[6];
		$this->contactPhone = $meetData[7];
		$this->meetFee = $meetData[8];
		$this->mealFee = $meetData[9];
		$this->location = $meetData[10];
		$this->status = $meetData[11];		
		$this->maxEvents = $meetData[12];
		$this->mealsIncluded = $meetData[13];
		
		// Get list of event ids
		$eventIds = $GLOBALS['db']->getAll("SELECT * FROM meet_events WHERE meet_id = '$this->id' 
				ORDER BY prognumber, progsuffix;");
		db_checkerrors($eventIds);
		
		foreach ($eventIds as $e) {

			$this->events[] = $e[0];
			
		}
			
	}
	
	// Creates meet
	public function create() {
		
		$meetName = mysql_real_escape_string($this->meetName);
		$startDate = mysql_real_escape_string($this->startDate);
		$endDate = mysql_real_escape_string($this->endDate);
		$deadline = mysql_real_escape_string($this->deadline);
		$contactName = mysql_real_escape_string($this->contactName);
		
		if (isset($this->contactEmail)) {
		
			$newContactEmailId = sw_addEmail(mysql_real_escape_string($this->contactEmail), 10);
			
		} 
			
		if (isset($this->contactPhone)) {

			$newContactPhoneId = sw_addPhone(mysql_real_escape_string($this->contactPhone), 10);
			
		}
		
		$meetFee = mysql_real_escape_string($this->meetFee);
		$mealFee = mysql_real_escape_string($this->mealFee);
		$location = mysql_real_escape_string($this->location);
		$status = mysql_real_escape_string($this->status);
		$maxEvents = mysql_real_escape_string($this->maxEvents);
		$mealincluded = mysql_real_escape_string($this->mealsIncluded);
	
		$insert = $GLOBALS['db']->query("INSERT INTO meet (meetname, startdate, enddate, deadline, contactname, contactemail, contactphone, meetfee, mealfee, location, status, maxevents, mealsincluded) VALUES ('$meetName', '$startDate', '$endDate', '$deadline', '$contactName', '$newContactEmailId', '$newContactPhoneId', '$meetFee', '$mealfee', '$location', '$status', '$maxEvents', '$mealsIncluded');");
		db_checkerrors($insert);
		
		$meetId = mysql_insert_id();
		$this->id = $meetId;
		
	}
	
	// Sets the name
	public function setName($n) {
		
		$this->meetName = $n;
		
	}
	
	// Set the dates
	public function setDates($start, $end) {
		
		$this->startDate = $start;
		$this->endDate = $end;
		
	}
	
	// Set deadline
	public function setDeadline($d) {
		
		$this->deadline = $d;
		
	}
	
	// Set meet fee
	public function setMeetFee($f) {
		
		$this->meetFee = $f;
		
	}
	
	// Get Meet fee
	public function getMeetFee() {
		
		return $this->meetFee;
		
	}
	
	// Set meal fee
	public function setMealFee($f) {
		
		$this->mealFee = $f;
		
	}
	
	// Set location
	public function setLocation($l) {
		
		$this->location = $l;
		
	}
	
	// Set Contact Phone 
	public function setContactPhone($p) {
		
		$this->contactPhone = sw_addPhone($p, 10);
		
	}
	
	// Set Contact Email
	public function setContactEmail($e) {
	
		$this->contactEmail = sw_addEmail($e, 10);
	
	}
	
	// Set Maximum number of events
	public function setMaxEvents($m) {
		
		if ($m > 0) {

			$this->maxEvents = $m;
			
		} else {
			
			$this->maxEvents = 0; 
			
		}
		
		
	}
	
	// Get Meet Name
	public function getName() {
		
		return $this->meetName;
		
	}
	
	// Get Start Date
	public function getStartDate() {
		
		return $this->startDate;
		
	}
	
	// Get End Date
	public function getEndDate() {
		
		return $this->endDate;
		
	}
	
	// Get Status text
	public function getStatus() {
	
		$statusText = $GLOBALS['db']->getOne("SELECT label FROM meet_entry_status_codes WHERE id = '$this->status';");
		db_checkerrors($statusText);
		
		return $statusText;
	
	}
	
	// Publishes meet
	public function publish() {
		
		$status = $GLOBALS['db']->query("UPDATE meet SET status = 1 WHERE id = '$this->id';");
		db_checkerrors($status);
		
		return true;
		
	}
	
	// Unpublishes meet
	public function unpublish() {
		
		$status = $GLOBALS['db']->query("UPDATE meet SET status = 0 WHERE id = '$this->id';");
		db_checkerrors($status);
		
		return true;
		
	}
	
	// Deletes meet
	public function delete() {
		
		// Delete event groups first
		$delete1 = $GLOBALS['db']->query("DELETE FROM meet_events_groups_items WHERE group_id IN (SELECT id FROM meet_events_groups WHERE meet_id = '$this->id');");
		db_checkerrors($delete1);
		
		$delete2 = $GLOBALS['db']->query("DELETE FROM meet_events_groups WHERE meet_id = '$this->id';");
		db_checkerrors($delete2);
		
		// Delete entries second
		$delete3 = $GLOBALS['db']->query("DELETE FROM meet_events_entries WHERE meet_entry_id IN (SELECT id FROM meet_entries WHERE meet_id = '$this->id');");
		db_checkerrors($delete3);
		
		$delete4 = $GLOBALS['db']->query("DELETE FROM meet_entry_statuses WHERE entry_id IN (SELECT id FROM meet_entries WHERE meet_id = '$this->id');");
		db_checkerrors($delete4);
		
		// Relay entries
		$delete5 = $GLOBALS['db']->query("DELETE FROM meet_entries_relays WHERE meet_id = '$this->id';");
		db_checkerrors($delete5);
		
		// Check ins
		$delete6 = $GLOBALS['db']->query("DELETE FROM meet_check_in WHERE meet_id = '$this->id';");
		db_checkerrors($delete6);
		
		// Delete actual meet item
		$delete7 = $GLOBALS['db']->query("DELETE FROM meet WHERE id = '$this->id';");
		db_checkerrors($delete7);
		
		return true;
		
	}
	
	public function getMax() {
	
		return $this->maxEvents;
	
	
	}
	
	public function getEventList() {
	
		return $this->events;
	
	}
	
	public function getContactName() {

		return $this->contactName;
	
	}
	
	public function doneJob($jobCol) {
		
		$rowExists = $GLOBALS['db']->getRow("SELECT meet_id FROM meet_jobs WHERE meet_id = '$this->id';");
		db_checkerrors($rowExists);
		
		if (isset($rowExists)) {
			
			$update = $GLOBALS['db']->query("UPDATE meet_jobs SET $jobCol = NOW() 
					WHERE meet_id = '$this->id';");
			db_checkerrors($update);
			
		} else {
			
			$insert = $GLOBALS['db']->query("INSERT INTO meet_jobs (meet_id, $jobCol) 
					VALUES ('$this->id', NOW());");
			db_checkerrors($insert);
			
		}
		
	}
	
	public function getContactEmail() {
		
		$email = $GLOBALS['db']->getOne("SELECT address FROM emails WHERE id = '$this->contactEmail';");
		db_checkerrors($email);
		
		return $email;
		
	}
	
	public function getDays() {

		// TODO: finish this
		
		return 1;
		
	}
	
	public function getLocation() {
		
		return $this->location;
		
	}
	
	public function getMealFee() {

		return $this->mealFee;
		
	}
	
	public function getRulesText() {
		
		$textRules = $GLOBALS['db']->getAll("SELECT DISTINCT(meet_rules.rule) FROM meet_rules, meet_rules_groups
				WHERE meet_rules.id = meet_rules_groups.rule_id AND meet_events_groups_id IN
				(SELECT id FROM meet_events_groups WHERE meet_id = '$this->id');");
		db_checkerrors($textRules);
		
		$rulesFormated = "";
		
		foreach ($textRules as $r) {
						
			$rulesFormated = $rulesFormated . $r[0] . "<br />\n";
			
		}
		
		return $rulesFormated;
		
	}
	
	public function getMealsIncluded() {
		
		if (!isset($this->mealsIncluded))
			return 0;
		
		return $this->mealsIncluded;
		
	}
	
	// Returns the course of the first event
	public function getMeetCourse() {
		
		if (isset($this->events) && is_array($this->events)) {
			
			$firstEvent = new MeetEvent();
			$firstEvent->load($this->events[0]);
			
			$meetCourse = $firstEvent->getCourse();
			
		}
		
		return $meetCourse;
		
	}
	
	// Returns the age up date of the meet
	public function getAgeUpDate() {
		
		$meetYear = date('Y', strtotime($this->startDate));
		
		return $meetYear . '-12-31';
		
	}
}


?>