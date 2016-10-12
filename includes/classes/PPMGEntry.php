<?php

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

    private $events; // Events array

}