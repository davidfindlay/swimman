<?php

/**
 * Created by PhpStorm.
 * User: david
 * Date: 11/10/2016
 * Time: 6:59 AM
 */
class PPMGEntryEvent
{

    private $accountNumber;     // reference to PPMG account id for linking to entry
    private $eventName;
    private $seedTime;

    function __construct($accountNumber, $eventName, $seedTime) {

        $this->accountNumber = $accountNumber;
        $this->eventName = $eventName;
        $this->seedTime = $seedTime;

    }

    /**
     * @return String event name as per PPMG CSV File
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @return String seed time as per PPMG CSV file format
     */
    public function getSeedTime()
    {
        return $this->seedTime;
    }



}