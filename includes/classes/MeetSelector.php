<?php

class MeetSelector {
	
	private $selected;
	private $selectName;
	private $published;	// If set to 1 only show published meets
	private $available; // If set only show events that are prior to the club recorder's days prior
	private $daysprior; // Days prior to official deadline that club recorder requires entries
	private $startDate = "2000-01-01"; // Only show meets after this date
	private $showDate;
	
	// Set HTML Select Name
	public function setName($n) {
		
		$this->selectName = $n;
		
	}
	
	// Set Start Date
	public function setStartDate($d) {

		$this->startDate = $d;

	}
	
	// Preselect by Meet Id
	public function selected($i) {
		
		$this->selected = $i;
		
	}
	
	public function publishedOnly() {
		
		$this->published = 1;
		
	}

	public function availableOnly() {

		$this->available = 1;

	}
	
	public function showAll() {
		
		$this->published = 0;
		
	}
	
	public function showDate() {
		
		$this->showDate = 1;
		
	}
	
	// Output list of meets
	public function output() {
		
		if ($this->published == 1) {
			
			$published = "status = 1";
			
		} else {
			
			$published = 1;
			
		}

		if ($this->available == 1) {

			$available = "curdate() <= deadline";

		} else {

			$available = "1";

		}
		
		$dateClause = "startdate > " . $this->startDate;
		
		$query = "SELECT * FROM meet WHERE $published AND $available AND $dateClause ORDER BY startdate ASC;";
		$meetList = $GLOBALS['db']->getAll($query);
		db_checkerrors($meetList);
		
		echo "<select name=\"$this->selectName\">\n";
		
		foreach ($meetList as $m) {
			
			$mId = $m[0];
			$mName = $m[1];
			
			echo "<option value=\"$mId\"";
			
			if ($mId == $this->selected) {
				
				echo " selected=\"selected\"";
				
			} 
			
			echo ">\n";
			
			echo $mName;
			
			if ($this->showDate == 1) {
			
				$mDate = date('d/m/Y', strtotime($m[2]));
				
				echo " -  $mDate";
			
			}
			
			echo "</option>\n";
			
		}
		
		echo "</select>\n";
		
	}
	
}


?>