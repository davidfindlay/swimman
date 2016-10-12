<?php

class RelayEntryMember {
	
	private $id;
	private $relayId;
	
	private $memberId;
	private $leg;
	
	public function __construct($i = 0) {
		
		// If id has been provided load it
		if ($i != 0) {
			
			$this->load($i);
			
		}
		
	}
	
	public function load($id) {
		
		$remDet = $GLOBALS['db']->getRow("SELECT * FROM meet_entries_relays_members 
				WHERE id = ?", array($id), DB_FETCHMODE_ASSOC);
		db_checkerrors($remDet);
		
		$this->id = $remDet['id'];
		$this->relayId = $remDet['relay_team'];
		$this->memberId = $remDet['member_id'];
		$this->leg = $remDet['leg'];
		
	}
	
	public function setRelayId($rId) {
		
		$this->relayId = $rId;
		
	}
	
	public function create() {
		
		$insert = $GLOBALS['db']->query("INSERT INTO meet_entries_relays_members (relay_team, member_id, 
				leg) VALUES (?, ?, ?);", array($this->relayId, $this->memberId, $this->leg));
		db_checkerrors($insert);
		
		$this->id = mysql_insert_id();
		
	}
	
	public function setMember($m) {
		
		$this->memberId = $m;
		
	}
	
	public function setLeg($l) {
		
		$this->leg = $l;
		
	}
	
	public function setRelay($r) {
		
		$this->relayId = $r;
		
	}
	
	public function getAge($dt) {
		
		$memberDetails = new Member();
		$memberDetails->loadId($this->memberId);
		return $memberDetails->getAge($dt);
		
	}
	
	public function getGender() {
		
		$memberDetails = new Member();
		$memberDetails->loadId($this->memberId);
		return $memberDetails->getGender();
		
	}
	
	public function getFullName() {
		
		$memberDetails = new Member();
		$memberDetails->loadId($this->memberId);
		return $memberDetails->getFullname();
	
	}
	
	// Delete the relay item
	public function delete() {
		
		// Check that relay has actually been created first
		if (isset($this->id)) {
			
			$delete = $GLOBALS['db']->query("DELETE FROM meet_entries_relays_members WHERE id = ?", 
				array($this->id));
			db_checkerrors($delete);
			
			return true;
			
		} else {
			
			return false; 
			
		}
		
	}
	
}