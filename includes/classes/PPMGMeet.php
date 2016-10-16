<?php
require_once("includes/setup.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");
require_once("includes/classes/MeetEntry.php");
require_once("includes/classes/MeetEntryEvent.php");
require_once("includes/classes/PPMGEntry.php");
require_once("includes/classes/PPMGMeetEvent.php");
require_once("includes/classes/PPMGEntryEvent.php");

/**
 * Created by PhpStorm.
 * User: david
 * Date: 11/10/2016
 * Time: 7:07 AM
 */
class PPMGMeet
{

    private $year;      // PPMG Meet Year
    private $meetId;    // Swimman Meet Id
    private $datafile;  // PPMG Data File

    private $meetObj;

    private $events;    // Array of PPMGMeetEvents
    private $entries;   // Array of PPMGEntries


    function load($year) {

        $ppmgMeet = $GLOBALS['db']->getRow("SELECT * FROM PPMG_meets WHERE meet_year = ?", array($year));
        db_checkerrors($ppmgMeet);

        $this->year = $ppmgMeet[0];
        $this->meetId = $ppmgMeet[1];
        $this->datafile = $ppmgMeet[2];

        // Get an Entry Manager meet object
        $this->meetObj = new Meet();
        $this->meetObj->loadMeet($this->meetId);

        $ppmgMeetEvents = $GLOBALS['db']->getAll("SELECT * FROM PPMG_meetevent WHERE meet_year = ?",
            array($year));
        db_checkerrors($ppmgMeetEvents);

        foreach ($ppmgMeetEvents as $e) {

            $this->events[] = new PPMGMeetEvent($e[1], $e[2], $e[3], $e[4], $e[5]);

        }

    }

    /**
     * Loads the PPMG Data File, finds the event columns and matches them
     * to entry manager events for this meet
     */
    function matchEvents() {

        // Open Datafile CSV
        $uploaddir = $GLOBALS['home_dir'] . '/masters-data/';
        $csvFile = fopen ( $uploaddir . $this->datafile, "r" );

        // Find columns
        $titleLine[] = array();
        while(count($titleLine) <= 1) {

            $titleLine = fgetcsv ( $csvFile );

        }

        // Set through the titleline to find
        $colNo = 0;
        foreach ($titleLine as $t) {

            // Match event selection columns
            if (!preg_match('/Nominated Time/i', $t)) {

                // Look for swimming event name
                if (preg_match('/Freestyle/i', $t)) {
                    $stroke = 1;    // Event Manager Freestyle
                }

                if (preg_match('/Breaststroke/i', $t)) {
                    $stroke = 2;    // Event Manager Breaststroke
                }

                if (preg_match('/Butterfly/i', $t)) {
                    $stroke = 3;    // Event Manager Butterfly
                }

                if (preg_match('/Backstroke/i', $t)) {
                    $stroke = 4;    // Event Manager Backstroke
                }

                if (preg_match('/Individual Medley/i', $t)) {
                    $stroke = 5;    // Event Manager Individual Medley
                }

                // Get distance
                if (preg_match('/\d{2,3}m/', $t)) {
                    preg_match('/\d{2,3}/', $t, $distanceArr);
                    $distance = $distanceArr[0];

                    // Found an event so now search through this meet's events
                    // to find a matching event;
                    $emEvents = $this->meetObj->getEventList();

                    foreach ($emEvents as $e) {

                        // Load the meet event
                        $event = new MeetEvent();
                        $event->load($e);

                        // Exclude relays from matching
                        if ($event->getLegs() == 1) {

                            if (($event->getDistanceMetres() == $distance) && ($event->getDiscipline() == $stroke)) {

                                // We have a match, store it
                                $ppmgMeetEvent = new PPMGMeetEvent($this->year, $this->meetId, $e, $t, $colNo);
                                $ppmgMeetEvent->store();

                            }

                        }

                    }

                }
            }

            $colNo++;
        }

        fclose($csvFile);

    }

    /**
     * Loads the PPMG Data File and Creates PPMGEntry Objects
     */
    function matchMembers() {

        // Open Datafile CSV
        $uploaddir = $GLOBALS['home_dir'] . '/masters-data/';
        $csvFile = fopen ( $uploaddir . $this->datafile, "r" );

        // Get title line as discard it
        $titleLine = fgetcsv ( $csvFile );

        // Step through all records
        while ($entryData = fgetcsv($csvFile)) {

            $entry = new PPMGEntry();

            // Check entry doesn't already exist
            if (!$entry->load($entryData[0])) {

                $entry->setAccountNumber($entryData[0]);
                $entry->setDateRegistered($entryData[1]);
                $entry->setRecordType($entryData[2]);
                $entry->setFirstName($entryData[3]);
                $entry->setLastName($entryData[4]);
                $entry->setGender($entryData[5]);
                $entry->setMainCountry($entryData[6]);
                $entry->setDateOfBirth($entryData[7]);
                $entry->setAge($entryData[8]);
                $entry->setPrimaryContactNumber($entryData[9]);
                $entry->setSecondaryContactNumber($entryData[10]);
                $entry->setEmail($entryData[11]);
                $entry->setMainState($entryData[12]);
                $entry->setEmergencyContactName($entryData[13]);
                $entry->setEmergencyContactPhoneNumber($entryData[14]);
                $entry->setEmergencyContactRelationship($entryData[15]);
                $entry->setAgeGroup($entryData[16]);
                $entry->setMsaMember($entryData[53]);
                $entry->setNonAustralianMasterMember($entryData[54]);
                $entry->setMsaId($entryData[55]);
                $entry->setMsaClubCode($entryData[56]);
                $entry->setOverseasMastersSwimmingMember($entryData[57]);
                $entry->setOverseasMastersSwimmingCountry($entryData[58]);
                $entry->setOverseasMastersSwimmingClubName($entryData[59]);
                $entry->setOverseasMastersSwimmingClubCode($entryData[60]);
                $entry->setDisability($entryData[61]);

                $entry->findEntryManagerMember();

                $entry->store();

            } else {

                // Entry already exists

                // Match up member
                $entry->findEntryManagerMember();

                $entry->updateMemberEntry();

            }

        }

        fclose($csvFile);
    }

    public function createEntries() {

        // Open Datafile CSV
        $uploaddir = $GLOBALS['home_dir'] . '/masters-data/';
        $csvFile = fopen ( $uploaddir . $this->datafile, "r" );

        // Get title line as discard it
        $titleLine = fgetcsv ( $csvFile );

        // Step through all records
        while ($entryData = fgetcsv($csvFile)) {

            $entry = new PPMGEntry();

            // Check entry doesn't already exist
            if ($entry->load($entryData[0])) {

                // Check we have an member link
                if ($entry->getMemberId() != "") {

                    $clubCode = '';

                    if ($entry->getMsaClubCode() != "") {
                        $clubCode = $entry->getMsaClubCode();
                    } elseif ($entry->getOverseasMastersSwimmingClubCode() != "") {
                        $clubCode = $entry->getOverseasMastersSwimmingClubCode();
                    } else {
                        $clubCode = "UNAT";
                    }

                    $club = new Club();
                    $club->load($clubCode);

                    $meetEntry = new MeetEntry($entry->getMemberId(), $club->getId(), $this->meetId);

                    // Step through the events
                    foreach ($this->events as $e) {

                        $selectionCol = $e->getPPMGcolumn();
                        $timeCol = $e->getPPMGcolumn() + 1;

                        if (strcasecmp($entryData[$selectionCol], "Yes") == 0) {

                            // Entry includes this event
                            $meetEntry->addEvent($e->getMeetEventId(), sw_timeToSecs($entryData[$timeCol]));

                            // Store the PPMG event
                            $ppmgEntryEvent = new PPMGEntryEvent($entry->getAccountNumber(),
                                $entryData[$selectionCol],$entryData[$timeCol]);

                            $ppmgEntryEvent->store();


                        }

                    }

                    // Create the entry
                    $meetEntry->create();
                    $emEntryId = $meetEntry->getId();

                    // Store the entry and event entry id
                    $entry->setEntryId($emEntryId);
                    $entry->updateEntryId($this->meetId);

                    // Update status
                    $meetEntry->setStatus(2);
                    $meetEntry->setEventStatuses(2);

                    $meetEntry->updateStatus();
                    $meetEntry->updateEventStatuses();

                }

            }

        }

        fclose($csvFile);

    }
}