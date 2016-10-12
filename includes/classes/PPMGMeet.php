<?php

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

    private $events;    // Array of PPMGMeetEvents

    function __construct($year, $meetId) {
        $this->year = $year;
        $this->meetId = $meetId;
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


    }

}