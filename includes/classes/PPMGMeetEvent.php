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

        $insert = $GLOBALS['db']->query("INSERT INTO PPMG_meetevent (meet_year, meet_id, meet_event_id, 
                                        ppmg_name, ppmg_column) 
                                        VALUES (?, ? ,?, ?, ?)",
            array($this->year, $this->meetId, $this->meetEventId, $this->PPMGName, $this->PPMGcolumn));
        db_checkerrors($insert);

    }

}