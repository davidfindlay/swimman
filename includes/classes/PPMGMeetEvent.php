<?php

require_once("includes/setup.php");

/**
 * Created by PhpStorm.
 * User: david
 * Date: 11/10/2016
 * Time: 7:11 AM
 */
class PPMGMeetEvent
{
    private $id;
    private $year;
    private $meetId;
    private $meetEventId;
    private $PPMGName;
    private $PPMGcolumn;

    /**
     * PPMGMeetEvent constructor.
     * @param $year
     * @param $meetId
     * @param $meetEventId
     * @param $PPMGName
     */
    public function __construct($year, $meetId, $meetEventId, $PPMGName, $PPMGcolumn)
    {
        $this->year = $year;
        $this->meetId = $meetId;
        $this->meetEventId = $meetEventId;
        $this->PPMGName = $PPMGName;
        $this->PPMGcolumn = $PPMGcolumn;
    }

    // Stores this event in the database
    public function store() {

        // Check the event doesn't already exist
        $existingCheck = $GLOBALS['db']->getRow("SELECT * FROM PPMG_meetevent 
            WHERE meet_year = ? AND meet_id = ? and meet_event_id = ?;",
            array($this->year, $this->meetId, $this->meetEventId));
        db_checkerrors($existingCheck);

        if (count($existingCheck) == 0) {

            $insert = $GLOBALS['db']->query("INSERT INTO PPMG_meetevent (meet_year, meet_id, meet_event_id, 
                                        ppmg_name, ppmg_column) 
                                        VALUES (?, ? ,?, ?, ?)",
                array($this->year, $this->meetId, $this->meetEventId, $this->PPMGName, $this->PPMGcolumn));
            db_checkerrors($insert);

            return true;

        } else {

            return false;

        }

    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return mixed
     */
    public function getMeetId()
    {
        return $this->meetId;
    }

    /**
     * @param mixed $meetId
     */
    public function setMeetId($meetId)
    {
        $this->meetId = $meetId;
    }

    /**
     * @return mixed
     */
    public function getMeetEventId()
    {
        return $this->meetEventId;
    }

    /**
     * @param mixed $meetEventId
     */
    public function setMeetEventId($meetEventId)
    {
        $this->meetEventId = $meetEventId;
    }

    /**
     * @return mixed
     */
    public function getPPMGName()
    {
        return $this->PPMGName;
    }

    /**
     * @param mixed $PPMGName
     */
    public function setPPMGName($PPMGName)
    {
        $this->PPMGName = $PPMGName;
    }

    /**
     * @return mixed
     */
    public function getPPMGcolumn()
    {
        return $this->PPMGcolumn;
    }

    /**
     * @param mixed $PPMGcolumn
     */
    public function setPPMGcolumn($PPMGcolumn)
    {
        $this->PPMGcolumn = $PPMGcolumn;
    }



}