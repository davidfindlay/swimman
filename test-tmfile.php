<?php

require("includes/setup.php");
require("includes/classes/Member.php");
require("includes/classes/Meet.php");
require("includes/classes/Club.php");
require("includes/classes/MeetEvent.php");
require("includes/classes/MeetEntry.php");
require("includes/classes/MeetEntryEvent.php");
require("includes/classes/TMEntryFile.php");

$testTMFile = new TMEntryFile();
$testTMFile->setMeet(37);

$testTMFile->createHY3File();