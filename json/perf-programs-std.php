<?php
require_once("../includes/setup.php");

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

// Set up the Associative Array fetch mode
$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);

$perf_prog_id = $_GET['id'];
$stroke = $_GET['stroke'];
$distance = $_GET['distance'];
$course = $_GET['course'];


if (isset($_GET['age'])) {

	$ageGroup = $_GET['age'];
	
	
	
}

$standards = $GLOBALS['db']->getAll("SELECT * FROM p")

// Send JSON Response
header('Content-type: application/json');
$callback = filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_SPECIAL_CHARS);

if (isset($_GET['callback'])) {

	echo $callback . '(' . json_encode($perfProgsAvailable) . ');';

} else {

	echo json_encode($perfProgsAvailable);

}

?>