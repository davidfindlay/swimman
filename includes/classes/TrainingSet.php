<?php

class TrainingSet {
	
	private $id;
	private $author;
	private $dateCreated;
	private $title;
	private $description;
	private $published;
	private $type;
	
	// Array of Training Set Items
	private $items;
	
	// Creates or updates set in database
	public function store() {
		
		if ($this->id == '') {
			
			$this->create();
			
			echo "Create set...\n";
			
		} else {
			
			$this->update();
			
		}
		
	}
	
	// Mutators
	public function setAuthor($a) {
		
		$this->author = $a;
		
	}
	
	public function setTitle($t) {
		
		$this->title = $t;
		
	}
	
	public function setDesc($d) {
		
		$this->description = $d;
		
	}
	
	public function publish() {
		
		$this->published = 1;
		
	}
	
	public function unpublish() {
		
		$this->published = 0;
		
	}
	
	public function setType($t) {
		
		$this->type = $t;
		
	}
	
	// Add item to a position in array - if position is not sent, adds to end
	public function addItem($nItem, $position = '') {
	
		if ($position == '') {
			
			$this->items[] = $nItem;
			
		} else {
			
			// Splice new item in at the right position
			array_splice($this->items, $position, 0, $nItem);
			
		}
	
	}
	
	// Remove item from array by it's position in the array
	public function removeItemByPos($position) {

		unset($this->items, $position);
		array_splice($this->items, count($this->items));
	
	}
	
	// Create Set
	private function create() {
		
		// Prepare query to insert data
		$sth = $GLOBALS['db']->prepare('INSERT INTO training_sets (author, datecreated, title, description, published, type) 
				VALUES (?, curdate(), ?, ?, ?, ?);');
		$data = array($this->author, $this->title, $this->description, $this->published, 
			$this->type);
		
		// Execute query and check for errors
		$ret = $GLOBALS['db']->execute($sth, $data);
		db_checkerrors($ret);
		
		// Get Training Set ID and set it in class
		$this->id = mysql_insert_id();  // TODO: find a better way
		
		echo "created.\n";
		
	}
	
	// Update Set in Database
	private function update() {
		
		$sth = $GLOBALS['db']->prepare('UPDATE training_sets SET author = ?, title = ?, description = ?, published = ?, 
			type = ? WHERE id = ?;');
		$data = array($this->author, $this->title, $this-description, $this->published, 
			$this->type, $this->id);
		
		$GLOBALS['db']->execute($sth, $data);
		db_checkerrors($sth);
		
	}
	
	// Load a set from database
	public function load($s) {

		if ($s != '') {
			
			$ret = $GLOBALS['db']->getRow("SELECT * FROM training_sets WHERE id = $s");
			db_checkerrors($ret);
			
			// Load data from row
			$this->id = $s;
			$this->author = $ret[1];
			$this->dateCreated = $ret[2];
			$this->title = $ret[3];
			$this->description = $ret[4];
			$this->pubished = $ret[5];
			$this->type = $ret[6];
			
			// Get all events and create an array of Training Set Items
			$ret = $GLOBALS['db']->getAll('SELECT * FROM training_sets_items WHERE training_set = ? ORDER BY order;', $ret);
			
			foreach ($ret as $i) {
				
				$this->items[] = new TrainingSetItem();
				$this->items[count($this->items - 1)]->setRow($i);	// Load a row worth of data
				
			}
			
			
		}
		
	}
	
	// Load a row from database 
	public function setRow($r) {
		
		$this->id = $r[0];
		$this->author = $r[1];
		$this->dateCreated = $r[2];
		$this->title = $r[3];
		$this->description = $r[4];
		$this->pubished = $r[5];
		$this->type = $r[6];
		
	}
	
	// Load events
	public function loadEvents() {
		
		// Get all events and create an array of Training Set Items
		$ret = $GLOBALS['db']->getAll('SELECT * FROM training_sets_items WHERE training_set = ? ORDER BY order;', $ret);
			
		foreach ($ret as $i) {
		
			$this->items[] = new TrainingSetItem();
			$this->items[count($this-items - 1)].setRow($i);	// Load a row worth of data
		
		}
		
	}
	
	// Return id
	public function getId() {
		
		return $this->id;
		
	}
	
	// Return true if published
	public function getPublished() {
		
		if ($this->published == 1) {
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
	
	// Return author name
	public function getAuthor() {
		
		return $this->author;
		
	}
	
	// Return title
	public function getTitle() {
		
		return $this->title;
		
	}
	
	// Returns type id
	public function getType() {
		
		return $this->type;
		
	}
	
	// Return type text label
	public function getTypeText() {
		
		$typeText = $GLOBALS['db']->getOne('SELECT typename FROM training_set_types WHERE id = ?;', $this->type);
		return $typeText;
		
	}
	
	// Return date set was created
	public function getCreated() {
		
		return $this->dateCreated;
		
	}
	
	// Return description
	public function getDescription() {
		
		return $this->description;
		
	}
	
}