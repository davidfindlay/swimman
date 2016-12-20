<?php

require_once("Club.php");
require_once("Member.php");

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

    private $memberShipType;

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
    }

    /**
     * @return mixed
     */
    public function getMemberShipType()
    {
        return $this->memberShipType;
    }/**
     * @param mixed $memberShipType
     */
    public function setMemberShipType($memberShipType)
    {

        $memberShipTypeId = $GLOBALS['db']->getOne("SELECT id FROM membership_types WHERE id = ?;",
            $memberShipType);

        if (isset($memberShipTypeId)) {

            $this->memberShipType = $memberShipType;
            return true;

        } else {

            return false;

        }

    }

    // RE1 Data File

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

    /**
     * Update names
     */
    public function updateDetails() {

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

        // Step through lines
        while($member = fgetcsv($csvFile, 250, ';')) {

            $msaNumber = $member[0];
            $surname = titleCase($member[1]);
            $firstname = titleCase($member[2]);
            $initial = $member[3];
            $gender = $member[4];

            list($dobMon, $dobDay, $dobYear) = explode('/', $member[5]);
            $dob = $dobYear . "-" . $dobMon . "-" . $dobDay;

            $clubcode = $member[6];
            $clubname = $member[7];

            $memberDetails = new Member();

            if ($memberDetails->loadNumber($msaNumber)) {

                if ($memberDetails->getFirstname() != $firstname) {

                    // Update first name
                    $memberDetails->setFirstname($firstname);
                    addlog("RE1 Import", "First Name Updated", "First Name updated for $msaNumber to $firstname");

                }

                if ($memberDetails->getSurname() != $surname) {

                    // Update last name
                    $memberDetails->setSurname($surname);
                    addlog("RE1 Import", "Surname Updated", "Surname updated for $msaNumber to $surname");

                }

                if ($memberDetails->getDob() != $dob) {

                    // Update Date of Birth
                    $memberDetails->setDob($dob);
                    addlog("RE1 Import", "Date of Birth Updated", "Date of Birth updated for $msaNumber to $dob");

                }

                $memberDetails->updateDetails();

            } else {

                // Create new member
                $memberDetails->setMSANumber($msaNumber);
                $memberDetails->setFirstname($firstname);
                $memberDetails->setSurname($surname);
                $memberDetails->setOtherNames('');
                $memberDetails->setDob($dob);
                $memberDetails->setGender($gender);

                if ($memberDetails->store()) {

                    addlog("RE1 Import", "Imported Member", "Imported $firstname $surname($msaNumber) $gender $dob.");

                }

            }

            // Apply membership details
            $club = new Club();
            $club->load($clubcode);
            $clubId = $club->getId();

            if ($this->memberShipType != "") {

                $memberDetails->applyMembership($this->memberShipType, $clubId);
                addlog("RE1 Import", "Applied Membership", "$firstname $surname($msaNumber) member of club $clubcode");

            }

        }

        fclose($csvFile);

    }
}