<?php

class MeetProgramMobile {
	
	private $meetId;
	private $filename;
	private $updated;
	private $version;
	private $uploaddir;
	private $url;
	
	function __construct() {
		
		$uploaddir = $GLOBALS['home_dir'] . '/masters-eprogram/';
		
	}
	
	public function load() {

		$getProgram = $GLOBALS['db']->getRow("SELECT * FROM meet_programs 
				WHERE meet_id = ?;", array($this->meetId));
		db_checkerrors($getProgram);
		
		if (isset($getProgram)) {
				
			$this->filename = $getProgram[1];
			$this->updated = $getProgram[2];
			$this->version = $getProgram[3];
			
			return true;
				
		} else {

			return false;
		}
		
		
	} 
	
	// Returns true if a Meet Program has been uploaded for this meet,
	// Returns false if no program has been uploaded yet
	public function exists() {
		
		$progExists = $GLOBALS['db']->getRow("SELECT * FROM meet_programs 
				WHERE meet_id = ?;", array($this->meetId));
		db_checkerrors($progExists);
		
		if (count($progExists) > 0) {
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
	
	public function setMeet($id) {
		
		$this->meetId = $id;
		
	}
	
	public function setUrl($u) {
		
		$this->url = $u;
		
	}
	
	public function getFilename() {
		
		return $this->filename;
		
	}
	
	public function getUpdated() {
		
		return array($this->version, $this->updated);
		
	}
	
	// Get list of meets
	public function getMeetList() {
		
		/* create a dom document with encoding utf8 */
		$domtree = new DOMDocument('1.0', 'UTF-8');
		$domtree->formatOutput = true;
		
		/* create the root element of the xml tree */
		$xmlRoot = $domtree->createElement("meetlist");
		/* append it to the document created */
		$xmlRoot = $domtree->appendChild($xmlRoot);
		
		$meetsAvailable = $GLOBALS['db']->getAll("SELECT a.id, a.meetname, a.startdate, a.enddate, 
												b.updated 
												FROM meet as a, meet_programs as b 
												WHERE a.id = b.meet_id 
												ORDER BY a.startdate DESC;");
		db_checkerrors($meetsAvailable);
		
		foreach ($meetsAvailable as $m) {
			
			$mId = $m[0];
			$mName = $m[1];
			$mStartDt = $m[2];
			$mEndDt = $m[3];
			list($mUpdatedDate, $mUpdatedTime) = explode(' ', $m[4]);
			
			$currentMeet = $domtree->createElement("meet");
			$currentMeet->setAttribute("id", $mId);
			$xmlRoot->appendChild($currentMeet);
			
			// Shorten Names
			$mName = str_replace("Short Course", "", $mName);
			$mName = str_replace("Long Course", "", $mName);
			$mName = str_replace("Indoor", "", $mName);
			$mName = str_replace("Swim Meet", "", $mName);
			$mName = str_replace("Meet", "", $mName);
			
			$cMeetName = $domtree->createElement("meetname", $mName);
			$currentMeet->appendChild($cMeetName);
			
			$cStartDate = $domtree->createElement("startdate", $mStartDt);
			$currentMeet->appendChild($cStartDate);
			
			$cEndDate = $domtree->createElement("enddate", $mEndDt);
			$currentMeet->appendChild($cEndDate);
			
			$cUpdatedTag = $domtree->createElement("updated");
			$currentMeet->appendChild($cUpdatedTag);
			
			$cUpdateDate = $domtree->createElement("date", trim($mUpdatedDate));
			$cUpdatedTag->appendChild($cUpdateDate);
			
			$cUpdateTime = $domtree->createElement("time", trim($mUpdatedTime));
			$cUpdatedTag->appendChild($cUpdateTime);
			
		}
		
		$xmlData = $domtree->saveXML();
		return $xmlData;
		
	}
	
	// Get list of events in meet
	public function getEventList($meetId) {
		
		/* create a dom document with encoding utf8 */
		$domtree = new DOMDocument('1.0', 'UTF-8');
		$domtree->formatOutput = true;
		
		/* create the root element of the xml tree */
		$xmlRoot = $domtree->createElement("xml");
		/* append it to the document created */
		$xmlRoot = $domtree->appendChild($xmlRoot);
		
		$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);
		$eventList = $GLOBALS['db']->getAll("SELECT a.id, a.prognumber, a.progsuffix, b.typename, c.discipline, c.abrev, a.legs, d.distance, d.metres, d.course
				FROM meet_events as a, event_types as b, event_disciplines as c, event_distances as d 
				WHERE a.meet_id = ? 
				AND a.type = b.id
				AND a.discipline = c.id
				AND a.distance = d.id 
				ORDER BY a.prognumber, a.progsuffix;", array($meetId));
		db_checkerrors($eventList);
		$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ORDERED);
		
		foreach ($eventList as $e) {
			
			$eId = $e['id'];
			$eNumber = $e['prognumber'] . $e['progsuffix'];
			$eType = $e['typename'];
			$eDisc = $e['discipline'];
			
			$eventRoot = $domtree->createElement("event");
			$eventRoot->setAttribute("id", $eId);
			$xmlRoot->appendChild($eventRoot);
			
			$progNum = $domtree->createElement("number", $eNumber);
			$eventRoot->appendChild($progNum);
			
			$typeTag = $domtree->createElement("type", $eType);
			$eventRoot->appendChild($typeTag);
			
			$discTag = $domtree->createElement("discipline", $eDisc);
			$eventRoot->appendChild($discTag);
			
			$discAbrevTag = $domtree->createElement("disciplineabrev", $e['abrev']);
			$eventRoot->appendChild($discAbrevTag);
			
			$legsTag = $domtree->createElement("legs", $e['legs']);
			$eventRoot->appendChild($legsTag);
			
			$distTag = $domtree->createElement("distancetext", $e['distance']);
			$eventRoot->appendChild($distTag);
			
			$metresTag = $domtree->createElement("metres", $e['metres']);
			$eventRoot->appendChild($metresTag);
			
			$courseTag = $domtree->createElement("course", $e['course']);
			$eventRoot->appendChild($courseTag);
			
		}
		
		$xmlData = $domtree->saveXML();
		return $xmlData;
		
	}
	
	// Get list of heats
	public function getHeatList($eventNumber) {

		if (preg_match('/[a-zA-Z]/', $eventNumber)) {
		
			list ($progNumber, $progSuffix) = preg_split('#(?<=\d)(?=[a-z])#i', $eventNumber);
			
		} else {

			$progNumber = $eventNumber;
			$progSuffix = "";
			
		}
		
		//$progNumber = $eventNumber;
		
		/* create a dom document with encoding utf8 */
		$domtree = new DOMDocument('1.0', 'UTF-8');
		$domtree->formatOutput = true;
		
		/* create the root element of the xml tree */
		$xmlRoot = $domtree->createElement("xml");
		/* append it to the document created */
		$xmlRoot = $domtree->appendChild($xmlRoot);
		
		$meetId = mysql_real_escape_string($this->meetId);
		$eventNumber = mysql_real_escape_string($eventNumber);
		
		$legs = $GLOBALS['db']->getOne("SELECT legs FROM meet_events 
				WHERE meet_id = ");
		
		$heatnumbers = $GLOBALS['db']->getAll("SELECT DISTINCT(a.heatnumber)
			FROM eprogram_entry as a, eprogram_events as b, meet_events as c
			WHERE a.meet_id = $meetId
			AND a.meet_id = b.meet_id
			AND a.meet_id = c.meet_id
			AND a.event_ptr = b.event_ptr
			AND b.event_id = c.id
			AND c.prognumber = $eventNumber
			AND a.heatnumber != 0
			ORDER BY a.heatnumber;");
		db_checkerrors($heatnumbers);
		
		// array($this->meetId, $progNumber, $progSuffix)
		
		foreach ($heatnumbers as $h) {
			
			$heatNum = $h[0];
			
			$currentHeat = $domtree->createElement("heat");
			$currentHeat->setAttribute("event", $eventNumber);
			$currentHeat->setAttribute("number", $heatNum);
			$xmlRoot->appendChild($currentHeat);
			
		}
		
		$xmlData = $domtree->saveXML();
		return $xmlData;
		
	}
	
	// Get Age Group List
	public function getAgeGroupList($eventNumber) {
		
		/* create a dom document with encoding utf8 */
		$domtree = new DOMDocument('1.0', 'UTF-8');
		$domtree->formatOutput = true;
		
		/* create the root element of the xml tree */
		$xmlRoot = $domtree->createElement("xml");
		/* append it to the document created */
		$xmlRoot = $domtree->appendChild($xmlRoot);
		
		if (preg_match('/[a-zA-Z]/', $eventNumber)) {
		
			list ($progNumber, $progSuffix) = preg_split('#(?<=\d)(?=[a-z])#i', $eventNumber);
			
		} else {

			$progNumber = $eventNumber;
			$progSuffix = "";
			
		}
		
		$ageGroups = $GLOBALS['db']->getAll("SELECT id, groupname FROM `age_groups` 
WHERE age_groups.set = 1 
AND id IN
(SELECT DISTINCT(age_groups.id) FROM age_groups, eprogram_athletes, eprogram_entry, eprogram_events, meet_events
WHERE eprogram_entry.meet_id = ?
AND eprogram_athletes.meet_id = eprogram_entry.meet_id
AND eprogram_events.meet_id = eprogram_entry.meet_id
AND eprogram_entry.ath_no = eprogram_athletes.ath_no
AND eprogram_events.event_ptr = eprogram_entry.event_ptr
AND eprogram_events.event_id = meet_events.id
AND meet_events.prognumber = ?
AND meet_events.progsuffix = ?
AND age_groups.min <= eprogram_athletes.age
AND age_groups.max >= eprogram_athletes.age
AND age_groups.swimmers = 1
);", array($this->meetId, $progNumber, $progSuffix));
		db_checkerrors($ageGroups);
		
		$ageGroupsTag = $domtree->createElement("agegroups");
		$xmlRoot->appendChild($ageGroupsTag);
		
		foreach ($ageGroups as $a) {
			
			$aId = $a[0];
			$aName = $a[1];
			
			$ageGroupTag = $domtree->createElement("agegroup");
			$ageGroupsTag->appendChild($ageGroupTag);
			
			$aGroupIdTag = $domtree->createElement("id", $aId);
			$ageGroupTag->appendChild($aGroupIdTag);
			
			$aGroupNameTag = $domtree->createElement("name", $aName);
			$ageGroupTag->appendChild($aGroupNameTag);
			
		}
		
		$xmlData = $domtree->saveXML();
		return $xmlData;
		
	}
	
	// Get Age Group Results
	public function getAgeGroup($eventNumber, $ageGroupId) {
		
		/* create a dom document with encoding utf8 */
		$domtree = new DOMDocument('1.0', 'UTF-8');
		$domtree->formatOutput = true;
		
		/* create the root element of the xml tree */
		$xmlRoot = $domtree->createElement("xml");
		/* append it to the document created */
		$xmlRoot = $domtree->appendChild($xmlRoot);
		
		if (preg_match('/[a-zA-Z]/', $eventNumber)) {
		
			list ($progNumber, $progSuffix) = preg_split('#(?<=\d)(?=[a-z])#i', $eventNumber);
			
		} else {

			$progNumber = $eventNumber;
			$progSuffix = "";
			
		}
		
		$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);
		$ageGroupResults = $GLOBALS['db']->getAll("SELECT CONCAT(IF(c.firstname IS NULL, f.firstname, c.firstname), ' ', IF(c.surname IS NULL, f.surname, c.surname)) as swimmer,
a.heatnumber, a.lanenumber,
IF(g.clubcode = '', h.code, g.clubcode) as clubcode,
IF(g.clubname = '', h.clubname, g.clubname) as clubname,
IF(c.gender = 1, 'M', 'F') as sex,
c.age as age,
CONCAT(IF(c.gender = 1, 'M', 'F'), e.min, '-', e.max) as agegroup,
e.groupname as agegrouptext,
MSQTime(a.seedtime) as seedtime, 
IF(a.heatplace = 0, '', a.heatplace) as heatplace, 
IF(a.finalplace = 0, '', a.finalplace) as finalplace, 
MSQTime(a.finaltime) as finaltime, 
IF(a.ev_score = 0, '', a.ev_score) as points
FROM eprogram_entry as a, eprogram_events as b, 
eprogram_athletes AS c LEFT OUTER JOIN member AS f on c.member_id = f.id, 
meet_events as d, age_groups as e,
eprogram_teams as g LEFT OUTER JOIN clubs as h on g.club_id = h.id
WHERE a.meet_id = ?
AND a.meet_id = b.meet_id
AND a.meet_id = g.meet_id
AND a.event_ptr = b.event_ptr
AND b.event_id = d.id
AND c.meet_id = a.meet_id
AND c.team_no = g.team_no
AND c.ath_no = a.ath_no
AND d.prognumber = ?
AND d.progsuffix = ?
AND c.age >= e.min 
AND c.age <= e.max
AND c.gender = e.gender
AND e.id = ? 
ORDER BY finalplace ASC;", array($this->meetId, $progNumber, $progSuffix, $ageGroupId));
		db_checkerrors($ageGroupResults);
		
		$ageGroupDetails = $GLOBALS['db']->getRow("SELECT * FROM age_groups WHERE id = ?;", array($ageGroupId));
		db_checkerrors($ageGroupDetails);
		
		$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ORDERED);
		
		$eventResultsTag = $domtree->createElement("event");
		$eventResultsTag->setAttribute("number", $eventNumber);
		$xmlRoot->appendChild($eventResultsTag);
		
		$ageGroupTag = $domtree->createElement("agegroup");
		$ageGroupTag->setAttribute("id", $ageGroupId);
		$ageGroupTag->setAttribute("name", $ageGroupDetails['groupname']);
		$xmlRoot->appendChild($ageGroupTag);
		
		foreach($ageGroupResults as $r) {
		
			$entryTag = $domtree->createElement("entry");
			$ageGroupTag->appendChild($entryTag);
			
			$heatTag = $domtree->createElement("heat", $r['heatnumber']);
			$entryTag->appendChild($heatTag);
			
			$laneTag = $domtree->createElement("lane", $r['lanenumber']);
			$entryTag->appendChild($laneTag);
			
			$swimmerTag = $domtree->createElement("swimmer");
			$entryTag->appendChild($swimmerTag);
			
			$swimmerNameTag = $domtree->createElement("fullname", ucwords(strtolower($r['swimmer'])));
			$swimmerTag->appendChild($swimmerNameTag);
				
			$swimmerSexTag = $domtree->createElement("gender", $r['sex']);
			$swimmerTag->appendChild($swimmerSexTag);
				
			$swimmerAgeTag = $domtree->createElement("age", $r['age']);
			$swimmerTag->appendChild($swimmerAgeTag);
				
			$swimmerAgeGroupTag = $domtree->createElement("agegroup", $r['agegroup']);
			$swimmerTag->appendChild($swimmerAgeGroupTag);
			
			$swimmerAgeGroupTextTag = $domtree->createElement("agegroupdesc", $r['agegrouptext']);
			$swimmerTag->appendChild($swimmerAgeGroupTextTag);
				
			$swimmerClubCodeTag = $domtree->createElement("clubcode", $r['clubcode']);
			$swimmerTag->appendChild($swimmerClubCodeTag);
				
			$procClubName = ucwords(strtolower($r['clubname']));
				
			// Get rid of masters from club name
			$trunSpot = strpos($procClubName, "Masters");
				
			if ($trunSpot != 0) {
					
				$procClubName = substr($procClubName, 0, $trunSpot);
					
			}
				
			// Get rid of Aussi
			$trunSpot = strpos($procClubName, "Aussi");
			
			if ($trunSpot != 0) {
					
				$procClubName = substr($procClubName, 0, $trunSpot);
					
			}
				
			// Get rid of Swimm
			$trunSpot = strpos($procClubName, "Swimm");
				
			if ($trunSpot != 0) {
					
				$procClubName = substr($procClubName, 0, $trunSpot);
					
			}
			
			$swimmerClubNameTag = $domtree->createElement("clubname", $procClubName);
			$swimmerTag->appendChild($swimmerClubNameTag);
				
			$swimmerSeedTag = $domtree->createElement("seedtime", $r['seedtime']);
			$entryTag->appendChild($swimmerSeedTag);
				
			if ($r['finaltime'] != '') {
			
				$swimmerResultsTag = $domtree->createElement("results");
				$entryTag->appendChild($swimmerResultsTag);
			
				$heatPlaceTag = $domtree->createElement("heatplace", $r['heatplace']);
				$swimmerResultsTag->appendChild($heatPlaceTag);
			
				$agePlaceTag = $domtree->createElement("ageplace", $r['finalplace']);
				$swimmerResultsTag->appendChild($agePlaceTag);
			
				$finalTimeTag = $domtree->createElement("finaltime", $r['finaltime']);
				$swimmerResultsTag->appendChild($finalTimeTag);
			
				$pointsTag = $domtree->createElement("points", $r['points']);
				$swimmerResultsTag->appendChild($pointsTag);
			
			}
		}
		
		$xmlData = $domtree->saveXML();
		return $xmlData;
		
	}
	
	// Returns XML containing heat details
	public function getHeat($eventNumber, $heatNumber) {
		
		/* create a dom document with encoding utf8 */
   		$domtree = new DOMDocument('1.0', 'UTF-8');
   		$domtree->formatOutput = true;

    	/* create the root element of the xml tree */
    	$xmlRoot = $domtree->createElement("xml");
    	/* append it to the document created */
    	$xmlRoot = $domtree->appendChild($xmlRoot);
    	
    	// Create heat
    	$currentHeat = $domtree->createElement("heat");
		$currentHeat->setAttribute("event", $eventNumber);
		$currentHeat->setAttribute("number", $heatNumber);
		
		// Get the number of 
		
		$currentHeat->setAttribute("numheats", $numHeats);
		$xmlRoot->appendChild($currentHeat);
		
		// Get next heat and previous heat
		if (preg_match('/[a-zA-Z]/', $eventNumber)) {
		
			list ($progNumber, $progSuffix) = preg_split('#(?<=\d)(?=[a-z])#i', $eventNumber);
			
		} else {

			$progNumber = $eventNumber;
			$progSuffix = "";
			
		}
		
		//echo "meetId = '$this->meetId' prognumber = '$progNumber' progsuffix = '$progSuffix' heatnumber = '$heatNumber'\n";
		
		// Find heat details
		$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);
		$heatEntries = $GLOBALS['db']->getAll("SELECT a.heatnumber as heat, a.lanenumber as lane, 
CONCAT(IF(b.firstname IS NULL, c.firstname, b.firstname), ' ', IF(b.surname IS NULL, c.surname, b.surname)) as swimmer,
IF(e.clubcode = '', f.code, e.clubcode) as clubcode,
IF(e.clubname = '', f.clubname, e.clubname) as clubname,
IF(c.gender = 1, 'M', 'F') as sex,
c.age as age,
CONCAT(IF(c.gender = 1, 'M', 'F'), d.min, '-', d.max) as agegroup,
d.groupname as agegrouptext,
MSQTime(a.seedtime) as seedtime, 
IF(a.heatplace = 0, '', a.heatplace) as heatplace, 
IF(a.finalplace = 0, '', a.finalplace) as finalplace, 
MSQTime(a.finaltime) as finaltime, 
IF(a.ev_score = 0, '', a.ev_score) as points
FROM eprogram_entry as a, eprogram_teams as e left outer join clubs as f on e.club_id = f.id, age_groups as d, eprogram_athletes as c left outer join member as b on c.member_id = b.id
WHERE a.meet_id = ?
AND a.meet_id = c.meet_id
AND a.meet_id = e.meet_id
AND a.ath_no = c.ath_no
AND c.team_no = e.team_no
AND d.set = 1 AND d.min <= c.age AND d.max >= c.age AND d.gender = c.gender
AND d.swimmers = (SELECT legs 
      	FROM meet_events 
        WHERE meet_id = ? 
        AND prognumber = ? 
        AND progsuffix = ?
		LIMIT 1) 
AND a.event_ptr = 
	(SELECT event_ptr 
     FROM eprogram_events 
     WHERE event_id = 
     	(SELECT id 
      	FROM meet_events 
        WHERE meet_id = ? 
        AND prognumber = ? 
        AND progsuffix = ?
		LIMIT 1) 
     LIMIT 1)
AND a.heatnumber = ? 
ORDER BY a.lanenumber ASC" 
		, array($this->meetId, $this->meetId, $progNumber, $progSuffix, 
				$this->meetId, $progNumber, $progSuffix, $heatNumber));
		db_checkerrors($heatEntries);
		$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ORDERED);
		
		//print_r($heatEntries);
		
		foreach ($heatEntries as $h) {
			
			$currentLane = $h['lane'];
			$currentSwimmer = $h['swimmer'];
			$currentClubCode = $h['clubcode'];
			$currentClubName = $h['clubname'];
			$currentSex = $h['sex'];
			$currentAge = $h['age'];
			$currentAgeGroup = $h['agegroup'];
			$currentAgeGroupText = $h['agegrouptext'];
			$currentSeedTime = $h['seedtime'];
			$currentHeatPlace = $h['heatplace'];
			$currentFinalPlace = $h['finalplace'];
			$currentFinalTime = $h['finaltime'];
			$currentPoints = $h['points'];
			
			$currentEntry = $domtree->createElement("entry");
			$currentHeat->appendChild($currentEntry);
			
			$laneTag = $domtree->createElement("lane", $currentLane);
			$currentEntry->appendChild($laneTag);
			
			// Create Swimmer subsection
			$swimmerTag = $domtree->createElement("swimmer");
			$currentEntry->appendChild($swimmerTag);
			
			$swimmerNameTag = $domtree->createElement("fullname", ucwords(strtolower($currentSwimmer)));
			$swimmerTag->appendChild($swimmerNameTag);
			
			$swimmerSexTag = $domtree->createElement("gender", $currentSex);
			$swimmerTag->appendChild($swimmerSexTag);
			
			$swimmerAgeTag = $domtree->createElement("age", $currentAge);
			$swimmerTag->appendChild($swimmerAgeTag);
			
			$swimmerAgeGroupTag = $domtree->createElement("agegroup", $currentAgeGroup);
			$swimmerTag->appendChild($swimmerAgeGroupTag);

			$swimmerAgeGroupTextTag = $domtree->createElement("agegroupdesc", $currentAgeGroupText);
			$swimmerTag->appendChild($swimmerAgeGroupTextTag);
			
			$swimmerClubCodeTag = $domtree->createElement("clubcode", $h['clubcode']);
			$swimmerTag->appendChild($swimmerClubCodeTag);
			
			$procClubName = ucwords(strtolower($h['clubname']));
			
			// Get rid of masters from club name
			$trunSpot = strpos($procClubName, "Masters");
			
			if ($trunSpot != 0) {
			
				$procClubName = substr($procClubName, 0, $trunSpot);
			
			}	
			
			// Get rid of Aussi
			$trunSpot = strpos($procClubName, "Aussi");
				
			if ($trunSpot != 0) {
					
				$procClubName = substr($procClubName, 0, $trunSpot);
					
			}
			
			// Get rid of Swimm
			$trunSpot = strpos($procClubName, "Swimm");
			
			if ($trunSpot != 0) {
					
				$procClubName = substr($procClubName, 0, $trunSpot);
					
			}
				
			$swimmerClubNameTag = $domtree->createElement("clubname", htmlentities($procClubName));
			$swimmerTag->appendChild($swimmerClubNameTag);
			
			$swimmerSeedTag = $domtree->createElement("seedtime", htmlentities($currentSeedTime));
			$currentEntry->appendChild($swimmerSeedTag);
			
			if ($currentFinalTime != '') {
				
				$swimmerResultsTag = $domtree->createElement("results");
				$currentEntry->appendChild($swimmerResultsTag);
				
				$heatPlaceTag = $domtree->createElement("heatplace", $currentHeatPlace);
				$swimmerResultsTag->appendChild($heatPlaceTag);
				
				$agePlaceTag = $domtree->createElement("ageplace", $currentFinalPlace);
				$swimmerResultsTag->appendChild($agePlaceTag);
				
				$finalTimeTag = $domtree->createElement("finaltime", $currentFinalTime);
				$swimmerResultsTag->appendChild($finalTimeTag);
				
				$pointsTag = $domtree->createElement("points", $currentPoints);
				$swimmerResultsTag->appendChild($pointsTag);
				
			}
			
		}
		
		$xmlData = $domtree->saveXML();
		return $xmlData;
		
	}
	
}


?>