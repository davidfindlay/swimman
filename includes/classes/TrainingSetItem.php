<?php

class TrainingSetItem {
	
	private $id;
	private $training_set;
	private $reps;
	private $discipline;
	private $distance;
	private $shortInstruction;
	private $longInstruction;
	private $restTime;
	private $cycleTime;
	private $order;
	
	// Mutators
	public function setReps($r) {
		
		$this->reps = intval($r);
		
	}
	
	public function setDiscipline($d) {
		
		$this->sdiscipline = intval($d);
		
	}
	
	public function setdistance($d) {
		
		$this->distance = intval($d);
		
	}
	
	public function setInstructions($s, $l = '') {
		
		$this->shortInstruction = $s;
		$this->longInstruction = $l;
		
	}
	
	public function setRestSecs($s) {
		
		$this->restTime = $s;
		
	}
	
	public function setCycleSecs($s) {
		
		$this->cycleTime = $c;
		
	}
	
	public function setRestForm($s) {
	
		$this->restTime = sw_timeToSecs($s);
	
	}
	
	public function setCycleForm($s) {
	
		$this->cycleTime = sw_timeToSecs($c);
	
	}
	
	// Accessors
	public function getId() {
		
		return $this->id;
		
	}
	
	public function getReps() {
	
		return $this-reps;
	
	}
	
	public function getDiscipline() {
	
		return $this->discipline;
	
	}
	
	public function getDistance() {
	
		return $this->distance;
	
	}
	
	public function getShortInstruction() {
	
		return $this->shortInstruction;
	
	}
	
	public function getLongInstruction() {
	
		return $this->longInstruction;
	
	}
	
	public function getRestSecs() {
	
		return $this->restTime;
	
	}
	
	public function getCycleSecs() {
	
		return $this->cycleTime;
	
	}
	
	public function getRestForm() {
	
		return sw_formatSecs($this->restTime);
	
	}
	
	public function getCycleForm() {
	
		return sw_formatSecs($this->cycleTime);
	
	}
	
	public function store() {
		
		if ($this->id == '') {
				
			$this->create();
				
		} else {
				
			$this->update();
				
		}
		
	}
	
	private function create() {
		
		$sth = $GLOBALS['db']->prepare('INSERT INTO training_sets_items (training_set, reps, discipline, 
				distance, short_instruction, long_instruction, rest_time, cycle_time) 
				VALUES (?, ?, ?, ?, ?, ?, ?, ?);');
		$data = array($this->training_set, $this->reps, $this->discipline, $this->distance, 
				$this->shortInstruction, $this->longInstruction, $this->restTime, $this->cycleTime);
		
		$GLOBALS['db']->execute($sth, $data);
		db_checkerrors($sth);
		
	}
	
	private function update() {
		
		$sth = $GLOBALS['db']->prepare('UPDATE training_sets_items SET reps = ?, discipline = ?, 
				distance = ?, short_instruction = ?, long_instruction = ?, rest_time = ?, 
				cycle_time = ? WHERE id = ?');
		$data = array($this->reps, $this->discipline, $this->distance, $this->shortInstruction, 
				$this->longInstruction, $this->restTime, $this->cycleTime);
		
		$GLOBALS['db']->execute($sth, $data);
		db_checkerrors($sth);
		
	}
	
	// Loads a row of data output from a query of the table
	public function setRow($r) {
		
		$this->id = $r[0];
		$this->training_set = $r[1];
		$this->reps = $r[2];
		$this->discipline = $r[3];
		$this->distance = $r[4];
		$this->shortInstruction = $r[5];
		$this->longInstruction = $r[6];
		$this->restTime = $r[7];
		$this->cycleTime = $r[8];
		
	}
	
}