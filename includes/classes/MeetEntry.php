<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/ConfirmationEmail.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/SlackNotification.php');

class MeetEntry {

	private $id;
	private $memberId;
	private $entrantName;
	private $clubId;
	private $meetId;
	private $ageGroupId;
	private $meals;
    private $massages;
    private $programs;
	private $medical;
	private $cost;
	private $notes;
	private $status = 0;
	private $cancelled = false;

	private $mealFees;

	private $events;
	private $meet;

	private $logger;

	public function __construct($member = '', $club = '', $meet = '') {
	
		$this->memberId = intval($member);
		$this->clubId = intval($club);
		$this->meetId = intval($meet);

        $this->meet = new Meet();
        $this->meet->loadMeet($this->meetId);

        $this->logger = new \Monolog\Logger('MeetEntry');
        $this->logger->pushProcessor(new \Monolog\Processor\WebProcessor);
        $this->logger->pushHandler(new StreamHandler($GLOBALS['log_dir'] . 'meetentry.log', $GLOBALS['log_level']));

    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
	
	// Used for Entry Checker, not stored in database
	public function setEntrantName($eName) {
		
		$this->entrantName = $eName;
		
	}
	
	public function getEntrantName() {
		
		return $this->entrantName;
		
	}

	/**
     * Loads a meet entry, finding it by it's id
     *
     * @param $eId integer Meet Entry Id
     * @return boolean true if successfully loaded, false otherwise
     */
	public function loadId($eId) {
		
		$this->id = intval($eId);
		$entryDetails = $GLOBALS['db']->getRow("SELECT * FROM meet_entries WHERE id = '$this->id';");
		db_checkerrors($entryDetails);

        return $this->populateValues($entryDetails);
		
	}

    /**
     * Loads a meet entry, finding it by the meet id, club id and member id passed
     * to the constructor
     *
     * @return boolean true if successfully loaded, false otherwise
     */
	public function load() {
	
		$entryDetails = $GLOBALS['db']->getRow("SELECT * FROM meet_entries WHERE meet_id = '$this->meetId'
												AND club_id = '$this->clubId'
												AND member_id = '$this->memberId';");
		db_checkerrors($entryDetails);

        return $this->populateValues($entryDetails);
	
	}

    /**
     * Takes a row from the meet_entries database table and populates it's values into
     * the class
     *
     * @param $entryDetails array row as retrieved from the database
     *
     * @return boolean true if successfully loaded, false otherwise
     */
	private function populateValues($entryDetails) {

        if (!isset($entryDetails)) {

            $this->status = 0;
            return false;

        } else {

            $this->memberId = $entryDetails[2];
            $this->clubId = $entryDetails[8];
            $this->meetId = $entryDetails[1];
            $this->id = $entryDetails[0];
            $this->meals = $entryDetails[4];
            $this->medical = $entryDetails[5];
            $this->cost = $entryDetails[6];
            $this->notes = $entryDetails[7];

            $this->massages = $entryDetails[10];
            $this->programs = $entryDetails[11];

            $eventEntries = $GLOBALS['db']->getAll("SELECT * FROM meet_events_entries WHERE meet_entry_id = '$this->id'
					ORDER BY id DESC;");
            db_checkerrors($eventEntries);

            foreach ($eventEntries as $e) {

                $this->events[] = new MeetEntryEvent;
                $eventsIndex = count($this->events) - 1;
                $this->events[$eventsIndex]->setRow($e);
                $this->events[$eventsIndex]->loadStatus();

            }

            // Store a copy of the Meet object in the class, so we don't have to repeatedly load it
            $this->meet = new Meet();
            $this->meet->loadMeet($this->meetId);



            return true;

        }

    }
	
	// Inserts Meet Entry into system and returns entry id number
	public function create() {
	
		$memDetails = new Member;
		$memDetails->loadId($this->memberId);	
		$this->ageGroupId = $memDetails->getAgeGroupId();
		
		$this->calcCost(); // Calculate and update entry cost before creating
		
		// Prevent null entries
		if (isset($this->meetId) && ($this->meetId != 0)) {
		
			$insert = $GLOBALS['db']->query("INSERT INTO meet_entries (meet_id, member_id, 
					age_group_id, club_id, meals, medical, cost, notes, massages, programs) VALUES
					(?, ?, ?, ?, ?, ?, ?, ?, ?, ?);",
                array($this->meetId, $this->memberId, $this->ageGroupId, $this->clubId,
                    $this->meals, $this->medical, $this->cost, $this->notes, $this->massages,
                    $this->programs));
			db_checkerrors($insert);
			
			$this->id = mysql_insert_id();
			
			$this->updateStatus();
			
			if (count($this->events) > 0) {
			
				foreach ($this->events as $e) {
				
					$e->setEntryId($this->id);
					$e->create();
				
				}
				
			}

            $clubDetails = new Club();
            $clubDetails->load($this->clubId);

			$message = "New entry to " . $this->meet->getName() . " by " . $memDetails->getFullname() . " for "
                . $clubDetails->getName() . ".";

            $slack = new SlackNotification();
            $slack->setMessage($message);
            $slack->send();

            addlog("MeetEntry.php", "Created Entry", "Created entry $this->id for meet $this->meetId", "");

            return true;
			
		} else {
			
			addlog("MeetEntry.php", "MeetId Unset!", "meetId was 0 when trying to insert meet entry");

			return false;
			
		}
	
	}
	
	public function addEvent($eventId, $seedtime, $status = 0) {
	
		$this->events[] = new MeetEntryEvent($this->memberId, $eventId, $seedtime);
		$newIndex = sizeof($this->events) - 1;
		$this->events[$newIndex]->setEntryId($this->id);
		
		// echo "Status = $status<br />\n";
		
		if ($status != 0 ) {
			
			$this->events[$newIndex]->setStatus($status);
			
		}
		
	}
	
	// Update event
	public function updateEvent($eventId, $seedTime, $status = 0) {
		
		//echo "update event fucntion $eventId<br />\n";
	
		foreach ($this->events as $e) {
			
			//echo "e id = ";
			//echo $e->getEventId;
			//echo "<br />\n";
		
			if ($e->getEventId() == $eventId) {
			
				$e->setSeedTime($seedTime);
				
				// echo "Status = $status<br />\n";
				
				if ($status != 0) {
					
					$e->setStatus($status);
					
				}
			
			}
			
			$e->update();
		
		}
	
	}
	
	public function checkMeetGroups() {
		
		$meetId = mysql_real_escape_string($this->meetId);		
		$eventGroups = $GLOBALS['db']->getAll("SELECT * FROM meet_events_groups WHERE meet_id = '$meetId';");
		db_checkerrors($eventGroups);
		
		$failGroups = '';
		$eventList = $this->getArrayEventId();
		
		foreach ($eventGroups as $g) {
			
			$gid = $g[0];
			$maxInGroup = $g[2];
			$inThisGroup = 0;
			
			$eventGroupItems = $GLOBALS['db']->getAll("SELECT * FROM meet_events_groups_items WHERE group_id = '$gid';");
			db_checkerrors($eventGroupItems);
			
			foreach ($eventGroupItems as $e) {
				
				$gEid = $e[2];
				
				if (in_array($gEid, $eventList)) {
					
					$inThisGroup++;
					
				}
				
			}
			
			if ($inThisGroup > $maxInGroup) {
				
				$failGroups[] = $gid;

			}
			
		}
		
		if ($failGroups == '') {
			
			return true;
			
		} else {
			
			return $failGroups;
			
		}
		
	}
	
	public function getArrayEventId() {
		
		$eventArray = array();
		
			if (isset($this->events)) {
			
			foreach ($this->events as $v) {
				
				$eventArray[] = $v->getEventId();
				
			}
			
		}
		
		return $eventArray;
		
	}
	
	public function getNumEntries() {
	
		$count = 0;
		
		if (isset($this->events)) {
	
			foreach($this->events as $e) {
				
				$eventId = $e->getEventId();
				
				// Check that the entry isn't cancelled
				if (!$e->getCancelled()) {
				
					$eventDet = new MeetEvent();
					$eventDet->load($eventId);
								
					if ($eventDet->getLegs() == 1) {
						
						$count++;
						
					}
					
				}

			}
			
			return $count;
			
		} else {
		
			return 0;
			
		}
	
	}
	
	// Sets the status of the Meet Entry
	public function setStatus($status) {
	
		$this->status = $status;
		
		// Check if this status cancels the entry
		$cancelled = $GLOBALS['db']->getOne("SELECT cancelled FROM meet_entry_status_codes WHERE id = ?", 
			array($status));
		db_checkerrors($cancelled);
		
		if (intval($cancelled) == 1) {
			
			$this->cancelled = true;
			
		} else {
			
			$this->cancelled = false;
			
		}
	
	}
	
	// Applies status change to database
	public function updateStatus() {
		
		$insert = $GLOBALS['db']->query("INSERT INTO meet_entry_statuses (entry_id, code) VALUES
				('$this->id', '$this->status');");
		db_checkerrors($insert);
		
		if (isset($this->cancelled)) {
		
			$updateCan = $GLOBALS['db']->query("UPDATE meet_entries SET cancelled = ? WHERE id = ?",
				array($this->cancelled, $this->id));
			db_checkerrors($updateCan);
			
		}

		$this->calcCost();

		// As status has been updated, send a confirmation

        if ($this->status == 2) {

            $emailConfirm = new ConfirmationEmail();
            $emailConfirm->setEntryId($this->id);
            $emailConfirm->setMeetId($this->meetId);
            $emailConfirm->setMemberId($this->memberId);
            $emailConfirm->send();

        }
		
	}
	
	// Steps through each event in the entry and sets the status of the event entry
	public function setEventStatuses($status) {
		
		foreach ($this->events as $e) {

            if (!$e->getCancelled()) {

                $e->setStatus($status);

            }
		
		}
		
	}

	public function updateEventStatuses() {

	    foreach ($this->events as $e) {

	        $e->update();

        }

    }
	
	public function getStatus() {
		
		$status = $GLOBALS['db']->getOne("SELECT meet_entry_status_codes.label FROM meet_entry_status_codes, 
				meet_entry_statuses WHERE meet_entry_status_codes.id = meet_entry_statuses.code AND 
				entry_id = '$this->id' ORDER BY meet_entry_statuses.id DESC LIMIT 1;");
		db_checkerrors($status);
		
		return $status;			
		
	}
	
	public function getStatusDesc() {
		
		$statusDesc = $GLOBALS['db']->getOne("SELECT meet_entry_status_codes.description FROM meet_entry_status_codes,
				meet_entry_statuses WHERE meet_entry_status_codes.id = meet_entry_statuses.code AND
				entry_id = '$this->id' ORDER BY meet_entry_statuses.id DESC LIMIT 1;");
		db_checkerrors($statusDesc);
		
		return $statusDesc;
		
	}
	
	public function getEvents() {
		
		return $this->events;
		
	}

    /**
     * Calculates and returns the total event fees for this entry.
     *
     * @return float the total event fees for this entry
     */
	public function calcEventFees() {

		$feeTotal = 0;
		
		if (isset($this->events)) {
		
			foreach($this->events as $e) {

                // Add cost of events if not cancelled
                if (!$e->getCancelled()) {

                    $eventId = $e->getEventId();

                    // Don't charge for relay entries as part of the individual entry
                    $meetEvent = new MeetEvent();
                    $meetEvent->load($eventId);
                    $eventLegs = $meetEvent->getLegs();

                    if ($eventLegs == 1) {

                        $fee = $GLOBALS['db']->getOne("SELECT eventfee FROM meet_events WHERE id = ?;",
                            array($eventId));
                        db_checkerrors($fee);

                        $feeTotal += floatval($fee);

                    }

                }
				
			}
			
		}
		
		return $feeTotal;
	}

    /**
     * Calculates and returns the fee for meals in this entry,
     * equal to the number of meals multiplied by fee per meal.
     *
     * @return float the total fee for meals ordered in this entry
     */
    public function calcMealFees() {

        $mealFees = $this->meet->getMealFee() * $this->meals;

        return $mealFees;

    }

    /**
     * Calculates and returns the fee for massages in this entry,
     * equal to the number of massages multiplied by fee per massage.
     *
     * @return float the total fee for massages ordered in this entry
     */
    public function calcMassageFees() {

        $massageFees = $this->meet->getMassageFee() * $this->massages;

        return $massageFees;

    }

    /**
     * Calculates and returns the fee for programs in this entry,
     * equal to the number of programs multiplied by fee per programs.
     *
     * @return float the total fee for programs ordered in this entry
     */
    public function calcProgramFees() {

        $programFees = $this->meet->getProgramFee() * $this->programs;

        return $programFees;

    }

    /**
     * Calculates and returns the total cost for this entry.
     * Calculated value is stored in the object and database on update.
     *
     * @return float the total cost of this entry
     */
	public function calcCost() {
		
		$meetDet = new Meet();
		$meetDet->loadMeet($this->meetId);
		
		$this->cost = $this->meet->getMeetFee() +
            $this->calcMealFees() +
            $this->calcMassageFees() +
            $this->calcProgramFees() +
            $this->calcEventFees();

		return $this->cost;
		
	}

	
	// Set number of meals
	public function setNumMeals($num) {
		
		$this->meals = intval($num);
		
	}
	
	// Get number of meals
	public function getNumMeals() {
		
		return $this->meals;
		
	}
	
	// Set medical certificate
	public function setMedical($m) {
		
		$this->medical = mysql_real_escape_string($m);
		
	}
	
	// Get Medical certificate
	public function getMedical() {
		
		return $this->medical;
		
	}
	
	// Set notes
	public function setNotes($n) {
		
		$this->notes = mysql_real_escape_string($n);
		
	}
	
	// Get notes
	public function getNotes() {
		
		return $this->notes;
		
	}
	
	// Completely delete entry
	public function delete() {
		
		// Delete events
		if (isset($this->events)) {
		
			foreach($this->events as $e) {
		
				$e->delete();
				
			}
			
		}
		
		// Delete entry status
		$delete1 = $GLOBALS['db']->query("DELETE FROM meet_entry_statuses WHERE entry_id = '$this->id';");
		db_checkerrors($delete1);
		
		// Delete entry
		$delete2 = $GLOBALS['db']->query("DELETE FROM meet_entries WHERE id = '$this->id';");
		db_checkerrors($delete2);

        // Delete payments
        $delete3 = $GLOBALS['db']->query("DELETE FROM meet_entry_payments WHERE entry_id = ?;",
            array($this->id));
        db_checkerrors($delete3);
		
	}
	
	// Gets the amount that's been paid so far for this entry
	public function getPaid() {

		$paidAmount = $GLOBALS['db']->getOne("SELECT sum(amount) FROM meet_entry_payments 
				WHERE entry_id = '$this->id';");
		db_checkerrors($paidAmount);

        if ($paidAmount == "") {
            $paidAmount = 0;
        }

		return $paidAmount;
	
	}

    /**
     * @return mixed
     */
    public function getCost()
    {
        return $this->cost;
    }
	
	// Receive payment
	public function makePayment($amount, $method, $comment = "") {

        if ($this->id == "") {

            addlog("Entry Manager", "Unable to accept payment", "Unable to accept payment due to no entry id!");
            return false;

        }

        if ($this->memberId == "") {

            addlog("Entry Manager", "Unable to accept payment", "Unable to accept payment due to no member id!");
            return false;

        }

        // Changed from <= 0 so that we can handle refunds
        if ($amount == 0) {

            addlog("Entry manager", "Unable to accept payment", "Can't accept a zero payment!");
            return false;

        }

		$payCol = $method;
		$paid = $amount;
		
		$method = $GLOBALS['db']->getOne("SELECT id FROM payment_types 
				WHERE colname = ?;", array($method));
		db_checkerrors($method);

        // Update cost
        $this->calcCost();

		// Don't accept payment if already fully paid
		if ($this->getCost() >= $this->getPaid()) {
		
			$query = $GLOBALS['db']->query("INSERT INTO meet_entry_payments (entry_id, member_id, 
				amount, method, comment) VALUES (?, ?, ?, ?, ?);",
				array($this->id, $this->memberId, $paid, $method, $comment));
			db_checkerrors($query);
		
		}

		//addlog("test", "calcCost = " . $this->getCost() . " getPaid = " . $this->getPaid());
		
		// If full amount has been paid, update status
		if ($this->getCost() <= $this->getPaid()) {
			
			$this->setStatus(2);		// Change status to Accepted
			$this->updateStatus();
			
			foreach ($this->events as $e) {

                // Don't record cancelled events as Accepted
                if (!$e->getCancelled()) {

                    $eventId = $e->getEventId();
                    $eventDet = new MeetEvent();
                    $eventDet->load($eventId);

                    // Mark relays as Pending
                    // Mark individual events as Accepted
                    if ($eventDet->getLegs() > 1) {

                        $e->setStatus(1);
                        $e->update();

                    } else {

                        $e->setStatus(2);
                        $e->update();

                    }

                }
				
			}

            addlog("Entry Manager", "Payment Received", "Member $this->memberId paid $paid for $this->id - status updated to Accepted");
			
		} else {
            addlog("Entry Manager", "Payment Received", "Member $this->memberId paid $paid for $this->id - status not updated due money still owed");
        }
		
	}
	
	public function cancel() {
		
		// Update status to 11 and commit
		$this->setStatus(11);
		$this->updateStatus();
		
		// Set statuses of each event in entry
		if (isset($this->events)) {
			
			foreach ($this->events as $e) {
			
				$eventId = $e->getEventId();
				$eventDet = new MeetEvent();
			
				// Update status and commit
				$e->setStatus(11);
				$e->update();
			
			}
			
		}
			
	}
	
	public function getMemberId() {
		
		return $this->memberId;
		
	}
	
	public function getMeetId() {
		
		return $this->meetId;
		
	}
	
	public function getClubId() {
		
		return $this->clubId;
		
	}
	
	// Checks to see if the member has any previous entries for this meet
	public function checkConflicting() {
		
		$otherEntries = $GLOBALS['db']->getAll("SELECT * FROM meet_entries WHERE member_id = '$this->memberId'
				AND meet_id = '$this->meetId';");
		db_checkerrors($otherEntries);
		
		if (count($otherEntries) > 0) {
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
	
	// Updates the existing entry with these details 
	public function updateExisting() {
		
		$existingId = $GLOBALS['db']->getOne("SELECT id FROM meet_entries 
				WHERE member_id = '$this->memberId'
				AND meet_id = '$this->meetId';");
		db_checkerrors($existingId);
		
		$existEntry = new MeetEntry();
		$existEntry->loadId($existingId);
		
		$existEntry->setStatus(5);
		$existEntry->updateStatus();
		$existEntry->updateEvents($this->getEvents(), 5, 11);

        // Update extras
        //addlog("test", "meals = " . $this->meals . " massages = " . $this->massages);
        $existEntry->setNumMeals($this->meals);
        $existEntry->setMassages($this->massages);
        $existEntry->setPrograms($this->programs);

        $existEntry->updateExtras();

        // Update club if necessary
        if ($existEntry->getClubId() != $this->getClubId()) {

            $existEntry->setClubId($this->clubId);
            $existEntry->updateClub();

        }

        // Calculate and store the new cost
        $existEntry->calcCost();

        return $existingId;
		
	}

    /**
     * @param string $clubId
     */
    public function setClubId($clubId)
    {
        $this->clubId = $clubId;
    }

	public function updateClub() {

        $update = $GLOBALS['db']->query("UPDATE meet_entries SET club_id = ? WHERE id = ?",
            array($this->clubId, $this->id));
        db_checkerrors($update);

        addlog("Meet Entry", "Update club", "Update club to $this->clubId for $this->id.");

    }
	
	// Gets a list of all events entered
	public function getEventList() {
		
		$eventList = "";
		
		if (count($this->events) > 0) {
		
			foreach ($this->events as $v) {
				
				if ($eventList == "") {
					
					$eventList = $v->getEventNum();
					
				} else {
				
					$eventList = $eventList . ', ' . $v->getEventNum();
					
				}
				
			}
			
		}
		
		return $eventList;
		
	}
	
	public function update($foundStatus, $notFoundStatus) {
		
		// Load the existing entry
		$existingEntry = new MeetEntry();
		$existId = $GLOBALS['db']->getOne("SELECT id FROM meet_entries WHERE member_id = '$this->memberId'
				AND meet_id = '$this->meetId' LIMIT 1;");
		db_checkerrors($existId);
		$existingEntry->loadId($existId);

		$existingEntry->updateEvents($existingEntry->events, $foundStatus, $notFoundStatus);
        $existingEntry->calcCost();
		
		addlog("Meet Entry", "Updated entry $existId");
		
		//print_r($this->events);
		
		$update = $GLOBALS['db']->query("UPDATE meet_entries SET meals = ?, medical = ?, cost = ?, 
				notes = ?, massages = ? 
				WHERE id = ?", array($this->meals, $this->medical, $this->cost, $this->notes, $this->massages,
				$this->id));
		db_checkerrors($update);
		
	}
	
	// Updates events based on events array
	public function updateEvents($eventsArray, $foundStatus, $notFoundStatus) {
		
		// Set status of events that are found
		foreach ($this->events as $tEvents) {
			
			$eventId = $tEvents->getEventId();
			$found = 0;
			
			foreach ($eventsArray as $nEvents) {
				
				$nId = $nEvents->getEventId();
				
				if (($nId == $eventId) && (!$nEvents->getCancelled())) {
					
					$tEvents->setStatus($foundStatus);
					$na = $nEvents->getId();
					$nSeed = $nEvents->getSeedTime();
					$tEvents->setSeedTime($nSeed);
					$tEvents->update();
					
					addlog("Meet Entry", "Updated event $nId entry $na with seed time $nSeed");
					
					$found = 1;
					
				}
				
			}
			
			// Update the ones that aren't found
			if ($found == 0) {
				
				$tEvents->setStatus($notFoundStatus);
				$tEvents->update();
				
			}
			
		}
		
		// Set status of events that are not found
		foreach ($eventsArray as $nEvents) {
			
			//echo $this->entrantName;
			
			$newEvent = $nEvents->getEventId();
			//echo "<br />newEvent = $newEvent<br />\n";
			$newExists = 0;
			
			foreach ($this->events as $tEvents) {
				
				//echo $tEvents->getEventId() . "<br />\n";
				
				if ($newEvent == $tEvents->getEventId()) {
					
					$newExists = 1;
					
					//echo "new Exists!";
					
				}
				
			}
			
			if ($newExists == 0) {
				
				$this->addEvent($newEvent, $nEvents->getSeedTime(), $foundStatus);
				$this->events[count($this->events) - 1]->create();
				
				//echo "add event $newEvent $foundStatus";
				//echo count($this->events);
				
			}
			
		}

		// Calculate and store the updated cost
		$this->calcCost();
		
	}

	public function updateExtras() {

        $update = $GLOBALS['db']->query("UPDATE meet_entries SET meals = ?, massages = ?, programs = ? WHERE id = ?;",
            array($this->meals, $this->massages, $this->programs, $this->id));
        db_checkerrors($update);

        addlog("Meet Entry", "Update extras", "Updated meals to " . $this->meals . " and massages to " .
            $this->massages . " and programs to " . $this->programs . " for " . $this->id . ".");

    }

    /**
     * Recalculates and updates the entry cost
     */
    public function updateCost() {

        $this->calcCost();

        $update = $GLOBALS['db']->query("UPDATE meet_entries SET cost = ? WHERE id = ?;",
            array($this->cost, $this->id));
        db_checkerrors($update);

        addlog("Meet Entry", "Updated cost", "Updated cost to $" . number_format($this->cost, 2) . " for " . $this->id);

    }
	
	// Checks if this entry is cancelled
	public function isCancelled() {
		
		return $this->cancelled;
		
	}

    /**
     * @return mixed
     */
    public function getMassages()
    {
        return $this->massages;
    }

    /**
     * @param mixed $massages
     */
    public function setMassages($massages)
    {
        $this->massages = $massages;
    }

    /**
     * @return mixed
     */
    public function getPrograms() {

        return $this->programs;
    }

    /**
     * @param mixed $programs
     */
    public function setPrograms($programs) {

        $this->programs = $programs;
    }



}

