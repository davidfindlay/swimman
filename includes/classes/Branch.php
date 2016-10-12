<?php

class Branch {
	
	private $id;
	private $branchName;
	private $branchCode;
	
	// Loads a branch from the database
	public function load($id) {
		
		$branchData = $GLOBALS['db']->getRow("SELECT * FROM branches WHERE id = ?;", array($id));
		db_checkerrors($branchData);
		
		// Populate fields
		$this->id = $branchData[0];
		$this->branchCode = $branchData[1];
		$this->branchName = $branchData[2];
		
	}
	
	// Returns the value of Branch Name
	public function getBranchName() {
		
		return $this->branchName;
		
	}
	
	// Returns the value of Branch Code
	public function getBranchCode() {
		
		return $this->branchCode;
		
	}
	
	// Sets the value of Branch Name
	public function setBranchName($n) {
		
		$this->branchName = $n;
		
	}
	
	// Sets the value of Branch Code
	public function setBranchCode($c) {
	
		$this->branchCode = substr($c, 0, 3);
	
	}
	
	// Stores this branch as a new branch in the database
	public function store() {
		
		$branchInsert = $GLOBALS['db']->query("INSERT INTO branches (branchcode, branchname) 
				VALUES (?, ?);", array($this->branchCode, $this->branchName));
		db_checkerrors($branchInsert);
		
		return true;
		
	}
	
	// Updates an existing branch record in the database
	public function update() {
		
		$branchUpdate = $GLOBALS['db']->query("UPDATE branches SET branchcode = ?, branchname = ? 
				WHERE id = ?", array($this->branchCode, $this->branchName, $this->id));
		db_checkerrors($branchUpdate);
		
		return true;
		
	}
	
}

?>