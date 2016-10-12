<?php

class MeetEntryEvent {

	private $id;
	private $eventId;
	private $entryId;
	private $memberId;
	private $relayId;
	private $leg;
	
	private $seedtime;
	private $status;
	private $statusStr;
	
	public function __construct($member = 0, $event = 0, $seed = 0) {
	
		$this->memberId = mysql_real_escape_string($member);
		$this->eventId = mysql_real_escape_string($event);
		$this->seedtime = mysql_real_escape_string($seed);
		
	}
	
	public function setEntryId($entryId) {
	
		$this->entryId = mysql_real_escape_string($entryId);
	
	}
	
	public function setRow($tableRow) {
	
		$this->id = $tableRow[0];
		$this->eventId = $tableRow[2];
		$this->entryId = $tableRow[1];
		$this->memberId = $tableRow[3];
		$this->relayId = $tableRow[4];
		$this->leg = $tableRow[5];
		$this->seedtime = $tableRow[6];
	
	}
	
	public function loadStatus() {
	
		$status = $GLOBALS['db']->getRow("SELECT meet_events_entries_statuses.status, meet_entry_status_codes.label 
										FROM meet_events_entries_statuses, meet_entry_status_codes WHERE 
										meet_events_entries_statuses.status = meet_entry_status_codes.id AND
										meet_events_entries_statuses.meet_event_entries_id = '$this->id'
										ORDER BY meet_events_entries_statuses.changed DESC LIMIT 1;");
		db_checkerrors($status);
		
		$this->status = $status[0];
		$this->statusStr = $status[1];
	
	}
	
	public function getStatus() {
	
		return $this->status;
	
	}
	
	public function getStatusText() {
	
		return $this->statusStr;
	
	}
	
	public function setStatus($status) {
	
		$this->status = mysql_real_escape_string($status);
	
		return true;
	}
	
	public function getId() {
	
		return $this->id;
	
	}
	
	public function getEventId() {
		
		return $this->eventId;
		
	}
	
	public function getSeedTime() {
	
		return $this->seedtime;
		
	}
	
	public function setSeedTime($seedtime) {
	
		$this->seedtime = mysql_real_escape_string($seedtime);
		
	}
	
	public function loadEntryId($entryId, $eventId) {
	
		$entry = mysql_real_escape_string($entryId);
		$event = mysql_real_escape_string($eventId);
		
		$eventEntryDetails = $GLOBALS['db']->getRow("SELECT * FROM meet_events_entries WHERE meet_entry_id = '$entry'
													AND event_id = '$event';");
		db_checkerrors($eventEntryDetails);
		
		$this->id = $eventEntryDetails[0];
		$this->entryId = $eventEntryDetails[1];
		$this->eventId = $eventEntryDetails[2];
		$this->memberId = $eventEntryDetails[3];
		$this->relayId = $eventEntryDetails[4];
		$this->leg = $eventEntryDetails[5];
		$this->seedtime = $eventEntryDetails[6];
	
	}
	
	public function create() {
	
		$insert = $GLOBALS['db']->query("INSERT INTO meet_events_entries (meet_entry_id, event_id, member_id, 
											relay_id, leg, seedtime) VALUES ('$this->entryId', '$this->eventId', 
											'$this->memberId', '$this->relayId', '$this->leg', '$this->seedtime');");
		db_checkerrors($insert);
		
		$this->id = mysql_insert_id();
		
		$insert = $GLOBALS['db']->query("INSERT INTO meet_events_entries_statuses (meet_event_entries_id, status)
				VALUES ('$this->id', '$this->status');");
		db_checkerrors($insert);
					
	}
	
	public function update() {

		$update = $GLOBALS['db']->query("UPDATE meet_events_entries SET seedtime = '$this->seedtime' 
				WHERE id = '$this->id';");
		db_checkerrors($update);
		
		// echo "update the seedtime for " . $this->id . " to " . $this->seedtime . " now!";

		$curStatus = $GLOBALS['db']->getOne("SELECT status FROM meet_events_entries_statuses WHERE id = '$this->id';");
		db_checkerrors($curStatus);
		
		if ($this->status != $curStatus) {
		
			$insert = $GLOBALS['db']->query("INSERT INTO meet_events_entries_statuses (meet_event_entries_id, status)
				VALUES ('$this->id', '$this->status');");
			db_checkerrors($insert);
			
		}
		
	
	}
	
	// Deletes all traces of Event Entry
	public function delete() {
		
		$delete1 = $GLOBALS['db']->query("DELETE FROM meet_events_entries_statuses 
				WHERE meet_event_entries_id = '$this->id';");
		db_checkerrors($delete1);
		
		$delete2 = $GLOBALS['db']->query("DELETE FROM meet_events_entries WHERE id = '$this->id';");
		db_checkerrors($delete2);
		
	}
	
	// Get Event Number
	public function getEventNum() {
		
		$eventId = $this->eventId;
		
		$eventNum = $GLOBALS['db']->getRow("SELECT prognumber, progsuffix FROM meet_events 
				WHERE id = '$eventId';");
		db_checkerrors($eventNum);
		
		return $eventNum[0] . $eventNum[1];
		
	}
	
	// Check if the event is cancelled
	public function getCancelled() {
		
		$cancelled = $GLOBALS['db']->getOne("SELECT cancelled FROM meet_entry_status_codes
				WHERE id = ?;", array($this->status));
		db_checkerrors($cancelled);
		
		if ($cancelled == 1) {
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
	
}