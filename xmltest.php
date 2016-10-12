<?php

// Database include
require_once ("DB.php");
require_once("config.php");

// CORS Header
header("Access-Control-Allow-Origin: *");

function db_checkerrors($var) {

	if (DB::isError($var)) {
		echo 'Standard Message: ' . $var->getMessage() . "<br />\n";
		echo 'DBMS/User Message: ' . $var->getUserInfo() . "<br />\n";
		echo 'DBMS/Debug Message: ' . $var->getDebugInfo() . "<br />\n";
		// $uid = $_SESSION['uid'];
		// $message = mysql_real_escape_string($var->getDebugInfo());
		// $insert = $GLOBALS['db']->query("INSERT INTO log_debug (uid, datetime, message) VALUES ('$uid', now(), '$message');");

		//	echo "Oops. An error has occured. This incident has been logged and we apologise for the inconvenience.";

		$GLOBALS['db']->disconnect();

		exit;
	}

}

global $db;

setlocale(LC_MONETARY, 'en_AU');

$dbuser = $GLOBALS['dbuser'];
$dbpass = $GLOBALS['dbpass'];
$dbhost = $GLOBALS['dbhost'];
$dbport = $GLOBALS['dbport'];
$dbname = $GLOBALS['dbname'];
	
 $dsn = "mysql://$dbuser:$dbpass@$dbhost:$dbport/$dbname";

$GLOBALS['db'] =& DB::connect($dsn);
db_checkerrors($GLOBALS['db']);
	
// Set correct timezone for all operations
$result = $GLOBALS['db']->query("SET time_zone = '+10:00';");
db_checkerrors($result);

date_default_timezone_set('Australia/Brisbane');

require_once("includes/classes/MeetProgramMobile.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");

$action = $_GET['action'];

header('Content-type: text/xml');
echo "";

if ($action == "getheat") {

	$meetId = intval($_GET['meet']);
	$event = mysql_real_escape_string($_GET['event']);
	$heat = intval($_GET['heat']);

	$meetProgram = new MeetProgramMobile();
	$meetProgram->setMeet($meetId);
	$meetProgram->load();

	echo $meetProgram->getHeat($event, $heat);

}

if ($action == "getagegroup") {
	
	$meetId = intval($_GET['meet']);
	$event = mysql_real_escape_string($_GET['event']);
	$ageGroup = intval($_GET['agegroup']);
	
	$meetProgram = new MeetProgramMobile();
	$meetProgram->setMeet($meetId);
	$meetProgram->load();
	
	echo $meetProgram->getAgeGroup($event, $ageGroup);
	
}

if ($action == "getagegrouplist") {
	
	$meetId = intval($_GET['meet']);
	$event = $_GET['event'];
	
	$meetProgram = new MeetProgramMobile();
	$meetProgram->setMeet($meetId);
	$meetProgram->load();
	
	echo $meetProgram->getAgeGroupList($event);
	
}

if ($action == "getmeetlist") {
	
	$meetProgram = new MeetProgramMobile();
	
	echo $meetProgram->getMeetList();
	
}

if ($action == "geteventlist") {

	$meetProgram = new MeetProgramMobile();
	$meet = $_GET['meet'];
	
	echo $meetProgram->getEventList($meet);

}

if ($action == "getheatlist") {

	$meetProgram = new MeetProgramMobile();
	$meetProgram->setMeet(intval($_GET['meet']));
	$event = $_GET['event'];

	echo $meetProgram->getHeatList($event);

}


?>