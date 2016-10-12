<?php

// JSON Web Service
// Returns a list of Meets available for viewing via eProgram

require_once("../includes/setup.php");
require_once("../includes/classes/Meet.php");

// Set up the Associative Array fetch mode
$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);

// Define request 
$sql = "SELECT id, CONCAT(DATE_FORMAT(startdate, '%e/%c/%Y'), ' - ', meetname) AS name
		FROM meet
		ORDER BY startdate DESC;";

// Make request and check for errors
$meetsAvailable = $GLOBALS['db']->getAll($sql);
db_checkerrors($meetsAvailable);

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {

	header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
	header('Access-Control-Allow-Credentials: true');

}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		header("Access-Control-Allow-Methods: GET, OPTIONS");

	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
		header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

}

// Send JSON Response
header('Content-type: application/json');
$callback = filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_SPECIAL_CHARS);

if (isset($_GET['callback'])) {

	echo $callback . '(' . json_encode($meetsAvailable) . ');';
	
} else {
	
	echo json_encode($meetsAvailable);
	
}

?>
