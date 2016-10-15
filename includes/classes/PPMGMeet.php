<?php
require_once("includes/setup.php");
require_once("Meet.php");
require_once("MeetEvent.php");
require_once("PPMGEntry.php");

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


    function load($year) {
        $ppmgMeet = $GLOBALS['db']->getRow("SELECT * FROM PPMG_meets WHERE meet_year = ?", array($year));
        db_checkerrors($ppmgMeet);

        $this->year = $ppmgMeet[0];
        $this->meetId = $ppmgMeet[1];
        $this->datafile = $ppmgMeet[2];

        // Get an Entry Manager meet object
        $this->meetObj = new Meet();
        $this->meetObj->loadMeet($this->meetId);

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
}