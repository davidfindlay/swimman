<?php
require_once("includes/setup.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/Club.php");
require_once("includes/classes/MeetEvent.php");
require_once("includes/classes/MeetEntry.php");
require_once("includes/classes/MeetEntryEvent.php");
require_once("includes/classes/TMEntryFile.php");


// Get TM Entries
if (isset($_GET['meet'])) {

	$meetId = $_GET['meet'];
	
}

if (isset($_GET['club'])) {

	$clubId = $_GET['club'];
	
}

$tmEntries = new TMEntryFile();
$tmEntries->setMeet($meetId);

if (isset($_GET['event'])) {

	$eventId = $_GET['event'];
	$tmEntries->setEvents($eventId);

}

// Create entry file for 

if (isset($clubId)) {
	
	//echo "Export only club entries for $clubId\n";
	
	$tmEntries->setClub($clubId);
	
} 

$tmEntries->loadEntries();
$tmEntries->createHY3File();

$tmEntries->createArchive();

header("Location: /entries/" . $tmEntries->getFileName() . ".zip");

?>
