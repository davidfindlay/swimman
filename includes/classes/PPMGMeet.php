<?php
require_once("../setup.php");
require_once("Meet.php");
require_once("MeetEvent.php");

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

    function __construct($year, $meetId) {
        $this->year = $year;
        $this->meetId = $meetId;

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

                        if (($event->getDistanceMetres() == $distance) && ($event->getDiscipline() == $stroke)) {

                            // We have a match, store it
                            $ppmgMeetEvent = new PPMGMeetEvent($this->year, $this->meetId, $e, $t, $colNo);
                            $ppmgMeetEvent->store();

                        }

                    }

                }
            }

            $colNo++;
        }

    }

}