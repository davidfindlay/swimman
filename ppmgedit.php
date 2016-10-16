<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15/10/2016
 * Time: 11:10 AM
 */

require_once("includes/setup.php");
require_once("includes/sidebar.php");

require_once("includes/classes/PPMGEntry.php");

checkLogin();

addlog("Access", "Accessed ppmgedit.php");

$account = intval($_GET['account']);

$entry = new PPMGEntry();
$entry->load($account);

if (isset($_POST['update'])) {

    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $dob = $_POST['dob'];
    $msaMember = $_POST['msaMember'];
    $msaId = $_POST['msaId'];
    $msaClubCode = $_POST['msaClubCode'];
    $overseasMastersClubCode = $_POST['overseasMastersClubCode'];

    $entry->setFirstName($firstName);
    $entry->setLastName($lastName);
    $entry->setDateOfBirth($dob);
    $entry->setMsaMember($msaMember);
    $entry->setMsaId($msaId);
    $entry->setMsaClubCode($msaClubCode);
    $entry->overseasMastersClubCode($overseasMastersClubCode);

    $entry->updateEdit();

}

htmlHeaders("Pan Pacific Masters Games");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Pan Pacific Masters Games - Edit Entry</h1>\n";

echo "<form method=\"post\">\n";

// Account Number
echo "<p>\n";
echo "<label>Account Number:</label>\n";
$accountNumber = $entry->getAccountNumber();
echo "$accountNumber\n";
echo "</p>\n";

// Date Registered
echo "<p>\n";
echo "<label>Date Registered:</label>\n";
$dateRegistered = $entry->getDateRegistered();
echo "$dateRegistered";
echo "</p>\n";

// First Name
echo "<p>\n";
echo "<label>First Name:</label>\n";
$firstName = $entry->getFirstName();
echo "<input type=\"text\" name=\"firstName\" value=\"$firstName\" size=\"40\" />\n";
echo "</p>\n";

// Last Name
echo "<p>\n";
echo "<label>Last Name:</label>\n";
$lastName = $entry->getLastName();
echo "<input type=\"text\" name=\"lastName\" value=\"$lastName\" size=\"40\" />\n";
echo "</p>\n";

// Gender
echo "<p>\n";
echo "<label>Gender:</label>\n";
$gender = $entry->getGender();
echo "$gender";
echo "</p>\n";

// Main Country
echo "<p>\n";
echo "<label>Main Country:</label>\n";
$mainCountry = $entry->getMainCountry();
echo "$mainCountry";
echo "</p>\n";

// Date of Birth
echo "<p>\n";
echo "<label>Date of Birth:</label>\n";
$dob = $entry->getDateOfBirth();
echo "<input type=\"text\" name=\"dob\" value=\"$dob\" size=\"20\" />\n";
echo "</p>\n";

// Age
echo "<p>\n";
echo "<label>Age:</label>\n";
$age = $entry->getAge();
echo "$age";
echo "</p>\n";

// Primary Contact Number
echo "<p>\n";
echo "<label>Primary Contact Number:</label>\n";
$primaryContactNumber = $entry->getPrimaryContactNumber();
echo "$primaryContactNumber";
echo "</p>\n";

// Secondary Contact Number
echo "<p>\n";
echo "<label>Secondary Contact Number:</label>\n";
$secondaryContactNumber = $entry->getSecondaryContactNumber();
echo "$secondaryContactNumber";
echo "</p>\n";

// Email
echo "<p>\n";
echo "<label>Email:</label>\n";
$email = $entry->getEmail();
echo "$email";
echo "</p>\n";

// Main State
echo "<p>\n";
echo "<label>Main State:</label>\n";
$mainState = $entry->getMainState();
echo "$mainState";
echo "</p>\n";

// Emergency Contact Name
echo "<p>\n";
echo "<label>Emergency Contact Name:</label>\n";
$emergencyContactName = $entry->getEmergencyContactName();
echo "$emergencyContactName";
echo "</p>\n";

// Emergency Contact Phone Number
echo "<p>\n";
echo "<label>Emergency Contact Phone:</label>\n";
$emergencyContactNumber = $entry->getEmergencyContactPhoneNumber();
echo "$emergencyContactNumber";
echo "</p>\n";

// Emergency Contact Relationship
echo "<p>\n";
echo "<label>Emergency Contact Relationship:</label>\n";
$emergencyContactRelationship = $entry->getEmergencyContactRelationship();
echo "$emergencyContactRelationship";
echo "</p>\n";

// Age Group
echo "<p>\n";
echo "<label>Age Group:</label>\n";
$ageGroup = $entry->getAgeGroup();
echo "$ageGroup";
echo "</p>\n";

// MSA Member
echo "<p>\n";
echo "<label>MSA Member:</label>\n";
$msaMember = $entry->getMsaMember();
echo "<input type=\"text\" name=\"msaMember\" value=\"$msaMember\" size=\"20\" />\n";
echo "</p>\n";

// MSA Id
echo "<p>\n";
echo "<label>MSA ID:</label>\n";
$msaId = $entry->getMsaId();
echo "<input type=\"text\" name=\"msaId\" value=\"$msaId\" size=\"20\" />\n";
echo "</p>\n";

// MSA Club Code
echo "<p>\n";
echo "<label>MSA Club Code:</label>\n";
$msaClubCode = $entry->getMsaClubCode();
echo "<input type=\"text\" name=\"msaClubCode\" value=\"$msaClubCode\" size=\"10\" />\n";
echo "</p>\n";

// Non Australian Masters Member
echo "<p>\n";
echo "<label>Non Australian Masters Member:</label>\n";
$nonAustralianMasters = $entry->getNonAustralianMasterMember();
echo "$nonAustralianMasters";
echo "</p>\n";

// Overseas Masters Member
echo "<p>\n";
echo "<label>Overseas Masters Member:</label>\n";
$overseasMasters = $entry->getOverseasMastersSwimmingMember();
echo "$overseasMasters";
echo "</p>\n";

// Overseas Masters Country
echo "<p>\n";
echo "<label>Overseas Masters Country:</label>\n";
$overseasMastersCountry = $entry->getOverseasMastersSwimmingCountry();
echo "$overseasMastersCountry";
echo "</p>\n";

// Overseas Masters Club Name
echo "<p>\n";
echo "<label>Overseas Masters Club Name:</label>\n";
$overseasMastersClubName = $entry->getOverseasMastersSwimmingClubName();
echo "$overseasMastersClubName";
echo "</p>\n";

// Overseas Masters Club Code
echo "<p>\n";
echo "<label>Overseas Masters Club Code:</label>\n";
$overseasMastersClubCode = $entry->getOverseasMastersSwimmingClubCode();
echo "<input type=\"text\" name=\"overseasMastersClubCode\" value=\"$overseasMastersClubCode\" size=\"10\" />\n";
echo "</p>\n";

// Disability
echo "<p>\n";
echo "<label>Disability:</label>\n";
$disability = $entry->getDisability();
echo "$disability";
echo "</p>\n";

// Member ID
echo "<p>\n";
echo "<label>Member ID:</label>\n";
$memberId = $entry->getMemberId();
echo "$memberId";
echo "</p>\n";

// Entry ID
echo "<p>\n";
echo "<label>Entry ID:</label>\n";
$entryId = $entry->getEntryId();
echo "$entryId";
echo "</p>\n";

// Status
echo "<p>\n";
echo "<label>Status:</label>\n";
$status = $entry->getStatus();
echo "$status";
echo "</p>\n";

echo "<p>\n";
echo "<input type=\"submit\" name=\"update\" value=\"Update Entry\" />\n";
echo "</p>\n";

echo "</form>\n";

echo "</div>\n";

htmlFooters();

?>