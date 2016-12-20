<?php

// JSON Web Service
// Returns a list of Meets available for viewing via eProgram

require_once("../includes/setup.php");
require_once("../includes/classes/Meet.php");
require_once("../includes/classes/Club.php");
require_once("../includes/classes/Member.php");
require_once("../includes/classes/RelayEntry.php");
require_once("../includes/classes/RelayEntryMember.php");

checkLogin();

// Set up the Associative Array fetch mode
//$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);

$meetId = $_POST['meetId'];
$eventId = $_POST['eventId'];
$newTeamClub = $_POST['newTeamClub'];
$newTeamName = $_POST['newTeamName'];
$newTeamSwimmer1 = $_POST['newTeamSwimmer1'];
$newTeamSwimmer2 = $_POST['newTeamSwimmer2'];
$newTeamSwimmer3 = $_POST['newTeamSwimmer3'];
$newTeamSwimmer4 = $_POST['newTeamSwimmer4'];

addlog("createrelay.php", "Received Relay Entry message for meet $meetId event $eventId");

$relayEntry = new RelayEntry();
$relayEntry->setMeet($meetId);
$relayEntry->setEvent($eventId);

if($newTeamClub == "") {

    $relayEntry->setClubCode("UNAT");

} else {

    $relayEntry->setClub($newTeamClub);

}

$relayEntry->setTeamName($newTeamName);
$relayEntry->addMember(1, $newTeamSwimmer1);
$relayEntry->addMember(2, $newTeamSwimmer2);
$relayEntry->addMember(3, $newTeamSwimmer3);
$relayEntry->addMember(4, $newTeamSwimmer4);
$relayEntry->calcAgeGroup();
$relayEntry->getNextLetter();

//print_r($relayEntry);

$relayEntry->create();

// Send JSON Response
header('Content-type: application/json');
$callback = filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_SPECIAL_CHARS);

if (isset($_GET['callback'])) {

    echo $callback . '(' . json_encode(true) . ');';

} else {

    echo json_encode(true);

}

?>
