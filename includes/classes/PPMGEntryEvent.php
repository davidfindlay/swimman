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
    private $entry_id;
    private $entry_event_id;

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

    /**
     * @param mixed $eventName
     */
    public function setEventName($eventName)
    {
        $this->eventName = $eventName;
    }

    /**
     * @param mixed $seedTime
     */
    public function setSeedTime($seedTime)
    {
        $this->seedTime = $seedTime;
    }

    /**
     * @return mixed
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param mixed $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return mixed
     */
    public function getEntryId()
    {
        return $this->entry_id;
    }

    /**
     * @param mixed $entry_id
     */
    public function setEntryId($entry_id)
    {
        $this->entry_id = $entry_id;
    }

    /**
     * @return mixed
     */
    public function getEntryEventId()
    {
        return $this->entry_event_id;
    }

    /**
     * @param mixed $entry_event_id
     */
    public function setEntryEventId($entry_event_id)
    {
        $this->entry_event_id = $entry_event_id;
    }



    public function store() {

        $insert = $GLOBALS['db']->query("INSERT INTO PPMG_entry (account_number, event_name, seed_time,
          entry_id, entry_event_id) VALUES(?, ?, ?, ?, ?);");
        db_checkerrors($insert);

        $this->id = $GLOBALS['db']->query("SELECT LAST_INSERT_ID();");

    }

}