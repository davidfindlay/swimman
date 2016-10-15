<?php

require_once("Member.php");
require_once("Club.php");

/**
 * Created by PhpStorm.
 * User: david
 * Date: 11/10/2016
 * Time: 6:49 AM
 */
class PPMGEntry
{

    // Representation of all fields in PPMG Entry CSV File
    private $accountNumber;
    private $dateRegistered;
    private $recordType;
    private $firstName;
    private $lastName;
    private $gender;
    private $mainCountry;
    private $dateOfBirth;
    private $age;
    private $primaryContactNumber;
    private $secondaryContactNumber;
    private $email;
    private $mainState;
    private $emergencyContactName;
    private $emergencyContactPhoneNumber;
    private $emergencyContactRelationship;
    private $ageGroup;

    private $msaMember;
    private $msaId;
    private $msaClubCode;

    private $nonAustralianMasterMember;

    private $overseasMastersSwimmingMember;
    private $overseasMastersSwimmingCountry;
    private $overseasMastersSwimmingClubName;
    private $overseasMastersSwimmingClubCode;

    private $disability;

    private $entry_id;
    private $member_id;

    private $status;

    private $events; // Events array

    public function load($account) {

        $entry = $GLOBALS['db']->getRow("SELECT * FROM PPMG_entry WHERE account_number = ?;", array($account));
        db_checkerrors($entry);

        if (count($entry) >= 28) {

            $this->accountNumber = $entry[0];
            $this->dateRegistered = $entry[1];
            $this->recordType = $entry[2];
            $this->firstName = $entry[3];
            $this->lastName = $entry[4];
            $this->gender = $entry[5];
            $this->mainCountry = $entry[6];
            $this->dateOfBirth = $entry[7];
            $this->age = $entry[8];
            $this->primaryContactNumber = $entry[9];
            $this->secondaryContactNumber = $entry[10];
            $this->email = $entry[11];
            $this->mainState = $entry[12];
            $this->emergencyContactName = $entry[13];
            $this->emergencyContactPhoneNumber = $entry[14];
            $this->emergencyContactRelationship = $entry[15];
            $this->ageGroup = $entry[16];
            $this->msaMember = $entry[17];
            $this->msaId = $entry[18];
            $this->msaClubCode = $entry[19];
            $this->nonAustralianMasterMember = $entry[20];
            $this->overseasMastersSwimmingMember = $entry[21];
            $this->overseasMastersSwimmingCountry = $entry[22];
            $this->overseasMastersSwimmingClubName = $entry[23];
            $this->overseasMastersSwimmingClubCode = $entry[24];
            $this->disability = $entry[25];
            $this->entry_id = $entry[26];
            $this->member_id = $entry[27];
            $this->status = $entry[28];

            return true;

        } else {

            // Entry not found
            return false;

        }

    }

    public function store() {
        $insert = $GLOBALS['db']->query("INSERT INTO PPMG_entry (
                              account_number,
                              date_registered,
                              record_type,
                              first_name,
                              last_name,
                              gender,
                              main_country,
                              dob,
                              age,
                              primary_contact_num,
                              secondary_contact_num,
                              email,
                              main_state,
                              emergency_contact_name,
                              emergency_contact_phone,
                              emergency_contact_relation,
                              age_group,
                              msa_member,
                              msa_id,
                              msa_club_code,
                              non_aus_master,
                              overseas_masters_member,
                              overseas_masters_country,
                              overseas_masters_club,
                              overseas_masters_clubcode,
                              disability,
                              member_id,
                              entry_id,
                              status ) 
                              VALUES (
                              ?, ?, ?, ?, ?,
                              ?, ?, ?, ?, ?,
                              ?, ?, ?, ?, ?,
                              ?, ?, ?, ?, ?,
                              ?, ?, ?, ?, ?,
                              ?, ?, ?, ? );",
            array($this->accountNumber,
                $this->dateRegistered,
            $this->recordType,
            $this->firstName,
            $this->lastName,
            $this->gender,
            $this->mainCountry,
            $this->dateOfBirth,
            $this->age,
            $this->primaryContactNumber,
            $this->secondaryContactNumber,
            $this->email,
            $this->mainState,
            $this->emergencyContactName,
            $this->emergencyContactPhoneNumber,
            $this->emergencyContactRelationship,
            $this->ageGroup,
            $this->msaMember,
            $this->msaId,
            $this->msaClubCode,
            $this->nonAustralianMasterMember,
            $this->overseasMastersSwimmingMember,
            $this->overseasMastersSwimmingCountry,
            $this->overseasMastersSwimmingClubName,
            $this->overseasMastersSwimmingClubCode,
            $this->disability,
                $this->member_id,
            $this->entry_id,
                $this->status)
        );

        db_checkerrors($insert);

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
    public function getDateRegistered()
    {
        return $this->dateRegistered;
    }

    /**
     * @param mixed $dateRegistered
     */
    public function setDateRegistered($dateRegistered)
    {
        $this->dateRegistered = $dateRegistered;
    }

    /**
     * @return mixed
     */
    public function getRecordType()
    {
        return $this->recordType;
    }

    /**
     * @param mixed $recordType
     */
    public function setRecordType($recordType)
    {
        $this->recordType = $recordType;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getMainCountry()
    {
        return $this->mainCountry;
    }

    /**
     * @param mixed $mainCountry
     */
    public function setMainCountry($mainCountry)
    {
        $this->mainCountry = $mainCountry;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param mixed $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * @return mixed
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param mixed $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * @return mixed
     */
    public function getPrimaryContactNumber()
    {
        return $this->primaryContactNumber;
    }

    /**
     * @param mixed $primaryContactNumber
     */
    public function setPrimaryContactNumber($primaryContactNumber)
    {
        $this->primaryContactNumber = $primaryContactNumber;
    }

    /**
     * @return mixed
     */
    public function getSecondaryContactNumber()
    {
        return $this->secondaryContactNumber;
    }

    /**
     * @param mixed $secondaryContactNumber
     */
    public function setSecondaryContactNumber($secondaryContactNumber)
    {
        $this->secondaryContactNumber = $secondaryContactNumber;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getMainState()
    {
        return $this->mainState;
    }

    /**
     * @param mixed $mainState
     */
    public function setMainState($mainState)
    {
        $this->mainState = $mainState;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactName()
    {
        return $this->emergencyContactName;
    }

    /**
     * @param mixed $emergencyContactName
     */
    public function setEmergencyContactName($emergencyContactName)
    {
        $this->emergencyContactName = $emergencyContactName;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactPhoneNumber()
    {
        return $this->emergencyContactPhoneNumber;
    }

    /**
     * @param mixed $emergencyContactPhoneNumber
     */
    public function setEmergencyContactPhoneNumber($emergencyContactPhoneNumber)
    {
        $this->emergencyContactPhoneNumber = $emergencyContactPhoneNumber;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactRelationship()
    {
        return $this->emergencyContactRelationship;
    }

    /**
     * @param mixed $emergencyContactRelationship
     */
    public function setEmergencyContactRelationship($emergencyContactRelationship)
    {
        $this->emergencyContactRelationship = $emergencyContactRelationship;
    }

    /**
     * @return mixed
     */
    public function getAgeGroup()
    {
        return $this->ageGroup;
    }

    /**
     * @param mixed $ageGroup
     */
    public function setAgeGroup($ageGroup)
    {
        $this->ageGroup = $ageGroup;
    }

    /**
     * @return mixed
     */
    public function getMsaMember()
    {
        return $this->msaMember;
    }

    /**
     * @param mixed $msaMember
     */
    public function setMsaMember($msaMember)
    {
        $this->msaMember = $msaMember;
    }

    /**
     * @return mixed
     */
    public function getMsaId()
    {
        return $this->msaId;
    }

    /**
     * @param mixed $msaId
     */
    public function setMsaId($msaId)
    {
        $this->msaId = $msaId;
    }

    /**
     * @return mixed
     */
    public function getMsaClubCode()
    {
        return $this->msaClubCode;
    }

    /**
     * @param mixed $msaClubCode
     */
    public function setMsaClubCode($msaClubCode)
    {
        $this->msaClubCode = $msaClubCode;
    }

    /**
     * @return mixed
     */
    public function getNonAustralianMasterMember()
    {
        return $this->nonAustralianMasterMember;
    }

    /**
     * @param mixed $nonAustralianMasterMember
     */
    public function setNonAustralianMasterMember($nonAustralianMasterMember)
    {
        $this->nonAustralianMasterMember = $nonAustralianMasterMember;
    }

    /**
     * @return mixed
     */
    public function getOverseasMastersSwimmingMember()
    {
        return $this->overseasMastersSwimmingMember;
    }

    /**
     * @param mixed $overseasMastersSwimmingMember
     */
    public function setOverseasMastersSwimmingMember($overseasMastersSwimmingMember)
    {
        $this->overseasMastersSwimmingMember = $overseasMastersSwimmingMember;
    }

    /**
     * @return mixed
     */
    public function getOverseasMastersSwimmingCountry()
    {
        return $this->overseasMastersSwimmingCountry;
    }

    /**
     * @param mixed $overseasMastersSwimmingCountry
     */
    public function setOverseasMastersSwimmingCountry($overseasMastersSwimmingCountry)
    {
        $this->overseasMastersSwimmingCountry = $overseasMastersSwimmingCountry;
    }

    /**
     * @return mixed
     */
    public function getOverseasMastersSwimmingClubName()
    {
        return $this->overseasMastersSwimmingClubName;
    }

    /**
     * @param mixed $overseasMastersSwimmingClubName
     */
    public function setOverseasMastersSwimmingClubName($overseasMastersSwimmingClubName)
    {
        $this->overseasMastersSwimmingClubName = $overseasMastersSwimmingClubName;
    }

    /**
     * @return mixed
     */
    public function getOverseasMastersSwimmingClubCode()
    {
        return $this->overseasMastersSwimmingClubCode;
    }

    /**
     * @param mixed $overseasMastersSwimmingClubCode
     */
    public function setOverseasMastersSwimmingClubCode($overseasMastersSwimmingClubCode)
    {
        $this->overseasMastersSwimmingClubCode = $overseasMastersSwimmingClubCode;
    }

    /**
     * @return mixed
     */
    public function getDisability()
    {
        return $this->disability;
    }

    /**
     * @param mixed $disability
     */
    public function setDisability($disability)
    {
        $this->disability = $disability;
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
    public function getMemberId()
    {
        return $this->member_id;
    }

    /**
     * @param mixed $member_id
     */
    public function setMemberId($member_id)
    {
        $this->member_id = $member_id;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Matches a member in Entry Manager
     */
    public function findEntryManagerMember() {

        $emMember = new Member();

        if (strcasecmp($this->msaMember, "Yes") == 0) {

            // Person claims to be member of MSA
            if ($emMember->loadNumber($this->msaId)) {

                // Get their Entry Manager member Id
                $memberId = $emMember->getId();

                // Check that date of birth matches
                $dob = $emMember->getDob();

                if ($dob == date('Y-m-d', strtotime($this->dateOfBirth))) {

                    // Date of birth matches so lets assume this is the correct member

                    // Check supplied club is correct
                    $msqClub = new Club();

                    if ($msqClub->load($this->msaClubCode)) {

                        $this->member_id = $memberId;
                        $this->status = "MSQ Member";

                    } else {

                        $this->status = "MSQ Member, club incorrect";

                    }

                } else {

                    $this->status = "MSQ Member - DOB incorrect";

                }

            } else {

                // Possibly interstate member
                // create a membership for them
                $dob = date('Y-m-d', strtotime($this->dateOfBirth));
                $gender = 0;

                if ($this->gender == "Male") {
                    $gender = 1;
                } else {
                    $gender = 2;
                }

                $insert = $GLOBALS['db']->query("INSERT INTO member (number, firstname, surname, dob, gender)
                                              VALUES (?,?,?,?,?);", array(
                                                  $this->msaId,
                    ucwords($this->firstName),
                    ucwords($this->lastName),
                    $dob,
                    $gender
                ));
                db_checkerrors($insert);

                $interstateClub = new Club();

                if ($interstateClub->load($this->msaClubCode)) {

                    $emMember->loadNumber($this->msaId);
                    $this->member_id = $emMember->getId();
                    $emMember->applyMembership(20, $this->msaClubCode);

                    $this->status = "MSA member created";

                } else {

                    $this->status = "MSA member, club incorrect";

                }

            }

            return true;

        } else {

            // We need to create an Entry Manager member for this person
            if (strcasecmp($this->overseasMastersSwimmingMember, "Yes") == 0) {

                // Overseas Masters Member
                $this->createOverseasMember();

                return true;

            } else {

                // Non masters member
                $this->createPPMGmember();

                return true;

            }

            return false;

        }

    }

    /**
     * Creates a guest membership for Pan Pacific Masters Games for this entry
     * this is for non-masters members
     *
     */
    public function createPPMGmember() {

        $dob = date('Y-m-d', strtotime($this->dateOfBirth));

        $existingCheck = $GLOBALS['db']->getOne("SELECT id FROM member WHERE 
              firstname = ? AND surname = ? and dob = ?;",
            array($this->firstName, $this->lastName, $dob));
        db_checkerrors($existingCheck);

        if ($existingCheck == "") {

            $newMember = new Member();
            $newMember->setFirstname(ucwords($this->firstName));
            $newMember->setSurname(ucwords($this->lastName));
            $newMember->setDob($dob);

            if (strcasecmp($this->gender, "Male") == 0) {

                $newMember->setGender('M');

            } else {

                $newMember->setGender('F');

            }

            $newMember->setMSANumber("P" . $this->accountNumber);

            $newMember->store();
            $newMember->applyMembership(20, 'UNAT');

            $this->status = "Created PPMG Unattached Member";

        } else {

            $this->status = "Existing PPMG Unattached Member";

        }

    }

    public function createOverseasMember() {

        $dob = date('Y-m-d', strtotime($this->dateOfBirth));

        $existingCheck = $GLOBALS['db']->getOne("SELECT id FROM member WHERE 
              firstname = ? AND surname = ? and dob = ?;",
            array($this->firstName, $this->lastName, $dob));
        db_checkerrors($existingCheck);

        if ($existingCheck == "") {

            $newMember = new Member();
            $newMember->setFirstname(ucwords($this->firstName));
            $newMember->setSurname(ucwords($this->lastName));
            $newMember->setDob(date('Y-m-d', strtotime($this->dateOfBirth)));

            if (strcasecmp($this->gender, "Male") == 0) {

                $newMember->setGender('M');

            } else {

                $newMember->setGender('F');

            }

            $newMember->setMSANumber("P" . $this->accountNumber);

            $newMember->store();

            $clubDetails = new Club();

            if (!$clubDetails->load($this->overseasMastersSwimmingClubCode)) {

                $clubId = $GLOBALS['db']->getOne("SELECT id FROM clubs WHERE clubname = ?",
                    array($this->overseasMastersSwimmingClubName));
                db_checkerrors($clubId);

                if ($clubId == "") {

                    $clubDetails->create($this->overseasMastersSwimmingClubCode, $this->overseasMastersSwimmingClubName);

                }

            }

            $newMember->applyMembership(20, $this->overseasMastersSwimmingClubCode);

            $this->status = "Created PPMG Overseas Member";

        } else {

            $this->status = "Existing PPMG Overseas Member";

        }
    }

    /**
     * Provides backend function to update record based on edits from the Swimman interface
     */
    public function updateEdit() {

        $update = $GLOBALS['db']->query("UPDATE PPMG_entry 
            SET first_name = ?,
            last_name = ?,
            dob = ?,
            msa_member = ?,
            msa_id = ?,
            msa_club_code = ?
            WHERE account_number = ?;",
            array($this->firstName,
                $this->lastName,
                $this->dateOfBirth,
                $this->msaMember,
                $this->msaId,
                $this->msaClubCode,
                $this->accountNumber));
        db_checkerrors($update);

    }

    public function updateMemberEntry() {

        $update = $GLOBALS['db']->query("UPDATE PPMG_entry
            SET member_id = ?,
            status = ?
            WHERE account_number = ?;",
            array($this->member_id,
                $this->status,
                $this->accountNumber));
        db_checkerrors($update);

    }

}