<?php

// JSON Web Service
// Returns a list of Meets available for viewing via eProgram

require_once("../includes/setup.php");
require_once("../includes/classes/Meet.php");
require_once("../includes/classes/Member.php");
require_once("../includes/classes/RelayEntry.php");
require_once("../includes/classes/RelayEntryMember.php");

checkLogin();

// Set up the Associative Array fetch mode
//$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);

$teamId = $_POST['teamId'];

addlog("deleterelay.php", "Received Relay Delete message for meet $meetId event $eventId");

//print_r($relayEntry);

$relayTeam = new RelayEntry();
$relayTeam->load($teamId);
$relayTeam->delete();

// Send JSON Response
header('Content-type: application/json');
$callback = filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_SPECIAL_CHARS);

if (isset($_GET['callback'])) {

    echo $callback . '(' . json_encode(true) . ');';

} else {

    echo json_encode(true);

}

?>
