<?php

class Club {
	
	private $id;
	private $code;
	private $clubname;
	private $region;
	private $branch;

	public function load($cId) {
		
		$clubId = mysql_real_escape_string($cId);
		
		if (ctype_digit($clubId) || is_numeric($clubId)) {
		
			$clubDetails = $GLOBALS['db']->getRow("SELECT * FROM clubs WHERE id = '$clubId';");
			db_checkerrors($clubDetails);
			
		} else {
			
			$clubDetails = $GLOBALS['db']->getRow("SELECT * FROM clubs WHERE code = '$clubId';");
			db_checkerrors($clubDetails);
						
		} 
		
		if (isset($clubDetails)) {
		
			$this->id = $clubDetails[0];
			$this->code = $clubDetails[1];
			$this->clubname = $clubDetails[2];
			$this->region = $clubDetails[4];
			
			return true;

		} else {
			
			return false;
			
		}
		
	}
	
	public function create($newCode, $newName, $newRegion = NULL) {
		
		$this->code = mysql_real_escape_string($newCode);
		$this->clubname = mysql_real_escape_string($newName);
		$this->region = mysql_real_escape_string($newRegion);
		
		$insert = $GLOBALS['db']->query("INSERT INTO clubs (code, clubname, region) VALUES ('$this->code', '$this->clubname', '$this->region');");
		db_checkerrors($insert);
		
		$this->id = mysql_insert_id();
		
		return $this->id;
		
	}
	
	public function getId() {
		
		return $this->id;
		
	}
	
	public function getName() {
		
		return $this->clubname;
		
	}
	
	public function getNameShortened() {
	
		$shortenedName = $this->clubname;
		$shortenedName = str_replace(" Club", "", $shortenedName);
		$shortenedName = str_replace(" Swimming", "", $shortenedName);
		$shortenedName = str_replace(" Swim", "", $shortenedName);
		$shortenedName = str_replace(" AUSSI", "", $shortenedName);
		$shortenedName = str_replace(" Inc", "", $shortenedName);
		$shortenedName = trim($shortenedName);
		
		return $shortenedName;
	
	}
	
	public function getCode() {
		
		return $this->code;
		
	}
	
}

?>