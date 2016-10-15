<?php

//require_once("phpqrcode/qrlib.php");

class Member {
	
	private $id;
	private $number;
	private $surname;
	private $firstname;
	private $othernames;
	private $dob;
	private $gender;
	private $address;
	private $postal;
	private $club;
	private $type;
	private $enddate;
	
	private $nominees;
	
	// Load Member by database ID
	public function loadId($memberId) {
		
		$this->id = $memberId;
		
		$memberDetails = $GLOBALS['db']->getRow("SELECT * FROM member 
				WHERE id = ?;", array($this->id));
		db_checkerrors($memberDetails);
		
		$this->number = $memberDetails[1];
		$this->surname = $memberDetails[2];
		$this->firstname = $memberDetails[3];
		$this->othernames = $memberDetails[4];
		$this->dob = $memberDetails[5];
		
		if ($memberDetails[6] == 1) {
			$this->gender = "M";
		} elseif ($memberDetails[6] == 2) {
			$this->gender = "F";			
		}
		
		$this->address = $memberDetails[7];
		$this->postal = $memberDetails[8];
		
		$memberClubs = $GLOBALS['db']->getAll("SELECT * FROM member_memberships 
					WHERE member_id = ?
					GROUP BY club_id;", array($this->id));
		db_checkerrors($memberClubs);

		foreach ($memberClubs as $c) {
			
			$this->club[] = $c[2];
			
		}
		
		$memberNominees = $GLOBALS['db']->getAll("SELECT * FROM member_access 
				WHERE member = ? 
				AND startdate <= now() AND enddate = '0000-00-00' OR enddate > now();",
				array($this->id));
		db_checkerrors($memberNominees);
		
		$this->nominees = $memberNominees;
		
	}
	
	// Load Member by MSA Number
	public function loadNumber($memberNumber) {
		
		$this->number = intval($memberNumber);
		
		$memberDetails = $GLOBALS['db']->getRow("SELECT * FROM member WHERE number = ?;",
			array($this->number));
		db_checkerrors($memberDetails);

		// Check if member can be found
		if (count($memberDetails) > 1) {
		
			$this->id = $memberDetails[0];
			$this->number = $memberDetails[1];
			$this->surname = $memberDetails[2];
			$this->firstname = $memberDetails[3];
			$this->othernames = $memberDetails[4];
			$this->dob = $memberDetails[5];
			
			if ($memberDetails[6] == 1) {
				$this->gender = "M";
			} else {
				$this->gender = "F";
			}
			
			$this->address = $memberDetails[7];
			$this->postal = $memberDetails[8];
			
			$memberClubs = $GLOBALS['db']->getAll("SELECT * FROM member_memberships 
					WHERE member_id = '$this->id'
					GROUP BY club_id;");
			db_checkerrors($memberClubs);

			foreach ($memberClubs as $c) {
					
				$this->club[] = $c[2];
									
			}
		
			$memberNominees = $GLOBALS['db']->getAll("SELECT * FROM member_access WHERE member = '$this->id'
					AND startdate <= now() AND enddate = '0000-00-00' OR enddate > now();");
			db_checkerrors($memberNominees);
			
			$this->nominees = $memberNominees;
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
	
	// Create new member 
	public function create($newNumber, $newSurname, $newFirstname, $newOthernames, $newDob, $newGender) {
		
		$this->number = mysql_real_escape_string($newNumber);
		$this->surname = mysql_real_escape_string($newSurname);
		$this->firstname = mysql_real_escape_string($newFirstname);
		$this->othernames = mysql_real_escape_string($newOthernames);
		$this->dob = mysql_real_escape_string($newDob);
		$this->gender = mysql_real_escape_string($newGender);
		
		if ($this->gender == 'M') {
			
			$genderNum = 1;
			
		} else {
			
			$genderNum = 2;
			
		}
		
		$insert = $GLOBALS['db']->query("INSERT INTO member (number, surname, firstname, othernames, 
				dob, gender) VALUES ('$this->number', '$this->surname', '$this->firstname', 
				'$this->othernames', '$this->dob', '$genderNum');");
		db_checkerrors($insert);
		
		$this->id = mysql_insert_id();
		
		return true;
		
	}

	public function store() {

        if ($this->gender == 'M') {

            $genderNum = 1;

        } else {

            $genderNum = 2;

        }

        $insert = $GLOBALS['db']->query("INSERT INTO member (number, surname, firstname, othernames, 
				dob, gender) VALUES ('$this->number', '$this->surname', '$this->firstname', 
				'$this->othernames', '$this->dob', '$genderNum');");
        db_checkerrors($insert);

        $this->id = mysql_insert_id();

        return true;

    }
	
	// Apply a membership to this member
	public function applyMembership($newType, $club, $newStartDate = null) {
		
		// Check if member already has membership of this type
		$type = mysql_real_escape_string($newType);

		$clubData = new Club();
		$clubData->load($club);
		$clubId = $clubData->getId();

        // Check a club has been found
        if ($clubId == 0) {
            return false;
        }
		
		if (isset($newStartDate)) {
			
			$startDate = mysql_real_escape_string($newStartDate);
			$startDateTime = new DateTime($startDate);
			
		}
		
		$existingCheck = $GLOBALS['db']->getRow("SELECT * FROM member_memberships 
				WHERE member_id = '$this->id' AND type = '$type' AND club_id = '$clubId';");
		db_checkerrors($existingCheck);
		
		if (isset($existingCheck)) {
			
			// Already a member holding this type
			return false;
			
		} else {
			
			// Apply membership information
			$typeInfo = $GLOBALS['db']->getRow("SELECT * FROM membership_types WHERE id = '$type';");
			db_checkerrors($typeInfo);
			
			$typeStartDate = $typeInfo[2];
			$endDate = $typeInfo[3];
			$status = $typeInfo[6];
			
			if ($endDate == NULL) {
				
				$months = $typeInfo[4];
				$weeks = $typeInfo[5];
				
				if (isset($months)) {
					
					// End date is start date plus this many months
					$endDateObj = $startDateTime->add(new DateInterval('P1M'));
					
					$endDateTime = new DateTime($endDateObj->format('Y-m-d'));
					$endDate = $endDateTime->format('Y-m-d');
					
				}
				
				if (isset($weeks)) {
					
					// End date is start date plus this many weeks
					$endDateObj = $startDateTime->add(new DateInterval('P4W'));
					
					$endDateTime = new DateTime($endDateObj->format('Y-m-d'));
					$endDate = $endDateTime->format('Y-m-d');
					
				}
				
				$insert = $GLOBALS['db']->query("INSERT INTO member_memberships (member_id, club_id, type, status, startdate, enddate) VALUES ('$this->id', '$clubId', '$type', '$status', '$startDate', '$endDate');");
				db_checkerrors($insert);
		
			} else {

				$insert = $GLOBALS['db']->query("INSERT INTO member_memberships (member_id, club_id, type, status, startdate, enddate) VALUES ('$this->id', '$clubId', '$type', '$status', '$typeStartDate', '$endDate');");
				db_checkerrors($insert);

			}

			$membershipId = mysql_insert_id();
			
			return true;
						
		}
		
	}
	
	// Retrieves financial status of member
	public function getMembershipStatus($clubId, $targetDate = '') {
		
		$endDate = $this->getMembershipEnd($clubId);
		
		// Check if today is before this end date TODO check start date too
		$endDateTime = new DateTime($endDate . "23:59:59");
		
		if (!isset($targetDate)) {
		
			$target = new DateTime();
		
		} else {
		
			$target = new DateTime($targetDate);
		
		}
		
		if ($endDateTime >= $target) {
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
	
	public function getMembershipStatusText($clubId, $targetDate = '') {
	
		if ($this->getMembershipStatus($clubId, $targetDate)) {
		
			$statusText = $GLOBALS['db']->getOne("SELECT membership_statuses.desc
												FROM membership_statuses
												WHERE id
												IN (
												SELECT STATUS FROM member_memberships
												WHERE member_id = '$this->id'
												AND club_id = '$clubId'
												AND startdate <= CURDATE()
												AND enddate >= CURDATE( )
												);");
			db_checkerrors($statusText);
		
			return $statusText;
			
		
		} else {
		
			return "Unfinancial";
			
		}
	
	}
	
	// Retreives financial end date of member
	public function getMembershipEnd($clubId) {
		
		$endDate = $GLOBALS['db']->getOne("SELECT enddate FROM member_memberships 
				WHERE member_id = '$this->id' AND club_id = '$clubId' ORDER BY enddate DESC;");
		db_checkerrors($endDate);
		
		return $endDate;
		
	}
	
	// Find member by First Name, Last Name and DOB (YYYY-MM-DD)
	public function find($f, $l, $d, $c) {
	
		$firstname = $f;
		$lastname = $l;
		$dob = $d;
		$clubId = $c;
		
		if (($firstname != '') && ($lastname != '') && ($dob != '') && ($clubId != '')) {
			
			$mId = $GLOBALS['db']->getOne("SELECT member.id FROM member, member_memberships 
					WHERE member.id = member_memberships.member_id 
					AND member_memberships.club_id = ? 
					AND member.firstname = ? 
					AND member.surname = ? 
					AND member.dob = ?
					ORDER BY member_memberships.enddate DESC
					LIMIT 1;", array($clubId, $firstname, $lastname, $dob));
			db_checkerrors($mId);
			
			//echo "found $firstname $lastname<br />\n";
					
		}

		if (($firstname == '') && ($lastname != '') && ($dob != '') && ($clubId != '')) {
		
			$mId = $GLOBALS['db']->getOne("SELECT member.id FROM member, member_memberships 
					WHERE member.id = member_memberships.member_id 
					AND member_memberships.club_id = ? 
					AND member.surname = ? 
					AND member.dob = ?
					ORDER BY member_memberships.enddate DESC
					LIMIT 1;", array($clubId, $lastname, $dob));
			db_checkerrors($mId);
			
			// echo "Firstname excluded search $lastname - $dob - $clubId<br />\n";
				
		}
		
		if (($firstname != '') && ($lastname != '') && ($dob == '') && ($clubId != '')) {
		
			$mId = $GLOBALS['db']->getOne("SELECT member.id FROM member, member_memberships 
					WHERE member.id = member_memberships.member_id 
					AND member_memberships.club_id = ? 
					AND member.firstname = ? 
					AND member.surname = ?
					ORDER BY member_memberships.enddate DESC
					LIMIT 1;", array($clubId, $firstname, $lastname));
			db_checkerrors($mId);
			
			// echo "DOB excluded search $firstname - $lastname - $clubId<br />\n";
		
		}
		
		if (isset($mId)) {
		
			$this->loadId($mId);
			return $mId;
		
		} else {
			
			return false ;
		
		}
		
	}
	
	public function getId() {
	
		return $this->id;
	
	}
	
	public function getMSANumber() {
		
		return $this->number;

	}
	
	public function setMSANumber($m) {
		
		$this->number = $m;
		
	}
	
	public function getFullname() {
		
		return $this->firstname . ' ' . $this->surname;
		
	}
	
	public function getSurname() {
	
		return $this->surname;
	
	}
	
	public function setSurname($nSurname) {
	
		$this->surname = $nSurname;
	
	}
	
	public function getFirstname() {
	
		return $this->firstname;
	
	}
	
	public function setFirstname($nFirst) {
	
		$this->firstname = $nFirst;
	
	}
		
	public function getDob() {
	
		return $this->dob;
	
	}
	
	public function setDob($nDob) {
		
		$this->dob = $nDob;
		
	}
	
	public function getGender() {
	
		return $this->gender;
	
	}
	
	public function setGender($genderId) {
		
		$this->gender = $genderId;
		
	}
	
	// Updates main member details, firstname, lastname, dob
	public function updateDetails() {
		
		$sth = $GLOBALS['db']->prepare("UPDATE member SET surname = ?, firstname = ?, 
				dob = ?, gender = ? WHERE id = ?;");
		
		if ($this->gender == 'M') {
			
			$genderNum = 1;
			
		} elseif ($this->gender == 'F') {
			
			$genderNum = 2;
			
		}
		
		$update = $GLOBALS['db']->execute($sth, array($this->surname, $this->firstname, $this->dob, $genderNum, $this->id));
		db_checkerrors($update);
		
	}
	
	public function getClubIds() {
	
		return $this->club;
	
	}
	
	public function getAge($testDate = '0') {
		
		if ($testDate == '0') {
			
			$testDate = date('Y-m-d');
			
		}
		
		$testyear = substr($testDate, 0, 4);
		$lastDay = $testyear . '-12-31';
		
		$dobDT = new DateTime($this->dob);
		$testDateDT = new DateTime($lastDay);
		
		$ageInt = $dobDT->diff($testDateDT);
		$age = $ageInt->format('%y');
		
		return $age;
		
	}
	
	public function getAgeGroup($testDate = '0') {
		
		$age = $this->getAge($testDate);
		
		if ($this->gender == 'M') {
			
			$genderCode = 1;
			
		} else {
			
			$genderCode = 2;
			
		}
		
		$ageGroup = $GLOBALS['db']->getOne("SELECT groupname FROM age_groups 
				WHERE '$age' >= min AND max >= '$age' AND gender = '$genderCode'
				AND age_groups.set = '1' AND swimmers = '1';");
		db_checkerrors($ageGroup);
		
		return $ageGroup;
		
	}
	
	public function getAgeGroupId($testDate = '0') {
	
		$age = $this->getAge($testDate);
	
		if ($this->gender == 'M') {
				
			$genderCode = 1;
				
		} else {
				
			$genderCode = 2;
				
		}
	
		$ageGroupId = $GLOBALS['db']->getOne("SELECT id FROM age_groups 
				WHERE '$age' >= min AND max >= '$age' AND gender = '$genderCode'
				AND age_groups.set = '1' AND swimmers = '1';");
		db_checkerrors($ageGroupId);
	
		return $ageGroupId;
	
	}
	
	// Checks if the member has a particular role
	public function checkRole($c, $r) {
		
		$clubId = mysql_real_escape_string($c);
		$roleId = mysql_real_escape_string($r);
		
		$hasRole = $GLOBALS['db']->getRow("SELECT * FROM club_roles 
				WHERE member_id = '$this->id' AND club_id = '$clubId' AND role_id = '$roleId';");
		db_checkerrors($hasRole);
		
		if (count($hasRole) < 1) {
			
			return false;
			
		} else {
			
			return true;
			
		}
		
	}
	
	// Get QR SVG
	public function getQRCode() {

		$qrCode = $this->number . "\n";
		$qrCode = $qrCode . $this->surname . "\n";
		$qrCode = $qrCode . $this->firstname . "\n";
		$qrCode = $qrCode . $this->dob . "\n";
		
		if ($this->gender == "M") {
			$sex = "Male";
		} else {
			$sex = "Female";
		}
		
		$qrCode = $qrCode . $sex . "\n";
		// $qrCode = $qrCode . "www.mastersswimmingqld.org.au";
		
		$msa = $this->number;
		
		QRCode::png($qrCode, "/home/masters/swimman/temp/qrcode-$msa.png", QR_ECLEVEL_L, 4);
		
		return;
		
	}
	
	// Add Nominee
	public function addNominee($member, $endDate = null) {
		
		// Check that Member ID exists
		$existsCheck = $GLOBALS['db']->getOne("SELECT id FROM member WHERE id = '$member';");
		db_checkerrors($existsCheck);
		
		if (isset($existsCheck)) {
			
			if ($endDate == null) {
				
				$endDate = "";
				
			}
			
			$nomAdd = $GLOBALS['db']->query("INSERT INTO member_access(member, nominee, startdate, enddate) 
					VALUES ('$this->id', '$member', now(), '$endDate');");
			db_checkerrors($nomAdd);
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
	
	// Remove nominee
	public function delNominee($nominee) {
		
		// Check nominee exists
		$nomArrId = $GLOBALS['db']->getOne("SELECT id FROM member_access WHERE member = '$this->id' 
				AND nominee = '$nominee' ORDER BY startdate DESC LIMIT 1;");
		db_checkerrors($nomArrId);
		
		if (count($nomArrId) > 0) {
			
			$update = $GLOBALS['db']->query("UPDATE member_access SET enddate = now() 
					WHERE member = '$this->id' AND nominee = '$nominee';");
			db_checkerrors($update);
			
			return true;
			
		} else {
			
			return false;
			
		}
		
		return false;
		
	}
	
	// Get list of nominees
	public function getNominees() {
		
		return $this->nominees;
		
	}
	
	// Checks if a member has a current nominee arrangement
	public function hasNominee($nomId) {
		
		$nomArr = $GLOBALS['db']->getRow("SELECT id FROM member_access WHERE member = '$this->id'
				AND nominee = '$nomId' AND startdate <= curdate() AND enddate > curdate()
				 ORDER BY startdate DESC LIMIT 1;");
		db_checkerrors($nomArr);
		
		if (count($nomArr) > 0) {
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
	
	// Returns a list of clubs this member has access to
	public function getAccess() {
		
		$clubList = array();
		
		foreach ($this->club as $c) {
			
			// If member has role 1 or 2 then add them to the list
			if ($this->checkRole($c, 1) == true || $this->checkRole($c, 2) == true) {
				
				$clubList[] = $c;
				
			}
			
		}
		
		return $clubList;
		
	}
	
	// Returns email address of member
	public function getEmail() {
		
		$emailAddress = $GLOBALS['db']->getOne("SELECT address FROM emails WHERE id = 
				(SELECT email_id FROM member_emails WHERE member_id = '$this->id' LIMIT 1);");
		db_checkerrors($emailAddress);
		
		return $emailAddress;
		
	}
	
	// Links a Joomla User
	public function linkJUser($juserId, $jUser) {
		
		// Check not currently linked to anyone
		$curCheck = $GLOBALS['db']->getRow("SELECT * FROM member_msqsite 
				WHERE member_id = ? 
				AND joomla_uid = ?;",
				array($this->id, $juserId));
		db_checkerrors($curCheck);
		
		if (count($curCheck) > 0) {
			
			$this->unlinkJUser($juserId);
			
		}
		
		$insert = $GLOBALS['db']->query("INSERT INTO member_msqsite 
				(member_id, joomla_uid, joomla_user) 
				VALUES (?, ?, ?);",
				array($this->id, $juserId, $jUser));
		db_checkerrors($insert);
		
		addlog("Joomla", "Member $this->id linked to joomla user $juserId");
		
		// Add to MSQ Members Group
		$msqMemGroup = 9;
		$msqClubGroup = 19;
		$msqMeetGroup = 20;
		
		// Connect to joomla database
		$jdbuser = $GLOBALS['jdbuser'];
		$jdbpass = $GLOBALS['jdbpass'];
		$jdbhost = $GLOBALS['jdbhost'];
		$jdbport = $GLOBALS['jdbport'];
		$jdbname = $GLOBALS['jdbname'];
		$dsn = "mysql://$jdbuser:$jdbpass@$jdbhost:$jdbport/$jdbname";
		$jdb =& DB::connect($dsn);
		db_checkerrors($jdb);
		
		// Set correct timezone for all operations
		$result = $jdb->query("SET time_zone = '+10:00';");
		db_checkerrors($result);
		
		$checkIfLink = $jdb->getRow("SELECT * FROM j_user_usergroup_map WHERE user_id = '$juserId' 
				AND group_id = '$msqMemGroup';");
		db_checkerrors($checkIfLink);
		
		if (count($checkIfLink) == 0) {
			
			$insert = $jdb->query("INSERT INTO j_user_usergroup_map (user_id, group_id) VALUES ('$juserId', 
					'$msqMemGroup');");
			db_checkerrors($insert);
			
			addlog("Joomla", "Member $this->id added to Joomla MSQ Members group");
			
		}
		
		$jdb->disconnect();
		
		return;
		
	}
	
	// Unlinks a Joomla User
	public function unlinkJUser($juserId) {
		
		$delete = $GLOBALS['db']->query("DELETE FROM member_msqsite WHERE member_id = '$this->id'
				AND joomla_uid = '$juserId';");
		db_checkerrors($delete);
		
		addlog("Joomla", "Member $this->id unlinked from joomla user $juserId");
		
		// Remove from group if in group
		$msqMemGroup = 9;
		$msqClubGroup = 19;
		$msqMeetGroup = 20;
		
		// Connect to joomla database
		$jdbuser = $GLOBALS['jdbuser'];
		$jdbpass = $GLOBALS['jdbpass'];
		$jdbhost = $GLOBALS['jdbhost'];
		$jdbport = $GLOBALS['jdbport'];
		$jdbname = $GLOBALS['jdbname'];
		$dsn = "mysql://$jdbuser:$jdbpass@$jdbhost:$jdbport/$jdbname";
		$jdb =& DB::connect($dsn);
		db_checkerrors($jdb);
		
		// Set correct timezone for all operations
		$result = $jdb->query("SET time_zone = '+10:00';");
		db_checkerrors($result);
		
		$delete = $jdb->query("DELETE FROM j_user_usergroup_map WHERE user_id = '$juserId' AND
				group_id = '$msqMemGroup';");
		db_checkerrors($delete);

		$delete2 = $jdb->query("DELETE FROM j_user_usergroup_map WHERE user_id = '$juserId' AND
				group_id = '$msqClubGroup';");
		db_checkerrors($delete2);
		
		$delete3 = $jdb->query("DELETE FROM j_user_usergroup_map WHERE user_id = '$juserId' AND
				group_id = '$msqMeetGroup';");
		db_checkerrors($delete3);
		
		addlog("Joomla", "Member $this->id removed from Joomla MSQ Members groups");
		
		
		//$jdb->disconnect();
		
	}
	
	// Returns id's of members this members is a nominee for
	public function isNominee() {
		
		$n = array();
		
		// Get list
		$noms = $GLOBALS['db']->getAll("SELECT id FROM member WHERE id IN
			(SELECT member from member_access
			WHERE nominee = '$this->id' AND startdate <= curdate() AND (enddate > curdate() 
			OR enddate = '0000-00-00'))
			ORDER BY surname, firstname;");
		db_checkerrors($noms);
		
		$retArray = array();
		
		foreach ($noms as $n) {
			
			$retArray[] = $n;
			
		}
		
		return $n;
		
	}
	
}

?>