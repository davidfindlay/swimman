<?php

// JSON Web Service
// Returns a list of Meets available for viewing via eProgram

require_once("../includes/setup.php");
require_once("../includes/classes/Meet.php");

// Set up the Associative Array fetch mode
$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);

// Allow filtering by year
$dateClause = '';

if (isset($_GET['year'])) {
	
	$requestYear = intval($_GET['year']);
	
	// Requested Year must be greater than 2012 and less than the current year + 1 
	if (($requestYear > 2012) && ($requestYear < (intval(date('Y')) + 1))) {
		
		$dateClause = "AND a.startdate > '$requestYear-01-01' AND a.startdate < '$requestYear-12-31' ";
		
	} else {
		
		header("msq.eprogram.statuscode: 9001");
		exit();
		
	}
	
}

// Define request 
$sql = "SELECT a.id, a.meetname, a.startdate, a.enddate, b.updated
		FROM meet as a, meet_programs as b
		WHERE a.id = b.meet_id
		$dateClause
		ORDER BY a.startdate DESC;";

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
