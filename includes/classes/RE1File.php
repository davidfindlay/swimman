<?php

require_once("Club.php");

/**
 * Created by PhpStorm.
 * User: david
 * Date: 14/10/2016
 * Time: 7:49 PM
 */
class RE1File
{
    private $datafile;
    private $data;      // Storage for data file

    /**
     * @return mixed
     */
    public function getDatafile()
    {
        return $this->datafile;
    }

    /**
     * @param mixed $datafile
     */
    public function setDatafile($datafile)
    {
        $this->datafile = $datafile;
    }  // RE1 Data File

    /**
     * Import clubs function
     */
    public function importClubs() {

        // Open Datafile CSV
        $uploaddir = $GLOBALS['home_dir'] . '/masters-data/';
        $csvFile = fopen ( $uploaddir . $this->datafile, "r" );

        // Check first line
        $titleLine = fgetcsv ( $csvFile );

        if (!preg_match("/AUSSI/", $titleLine[0])) {

            fclose($csvFile);
            addLog("RE1 Import", "Invalid RE1 File", "First line does not contain AUSSI!");
            return false;

        }

        $clubCheck = new Club();

        // Step through lines looking for clubs
        while($member = fgetcsv($csvFile, 250, ';')) {

            $msaNumber = $member[0];
            $surname = $member[1];
            $firstname = $member[2];
            $initial = $member[3];
            $gender = $member[4];
            $dob = $member[5];
            $clubcode = $member[6];
            $clubname = $member[7];

            // Check if club exists
            if (! $clubCheck->load($clubcode)) {

                $clubCheck->create($clubcode, $clubname);

            }

        }

        fclose($csvFile);

    }

}