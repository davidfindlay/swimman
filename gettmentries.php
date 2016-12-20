<?php
require("includes/setup.php");
require("includes/classes/Member.php");
require("includes/classes/Meet.php");
require("includes/classes/Club.php");
require("includes/classes/MeetEvent.php");
require("includes/classes/MeetEntry.php");
require("includes/classes/MeetEntryEvent.php");
require("includes/classes/TMEntryFile.php");


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
