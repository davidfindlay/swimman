<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");
require_once("includes/classes/MeetEntry.php");
require_once("includes/classes/MeetEntryEvent.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Club.php");
checkLogin();

$meetId = 0;

if (isset($_GET['meet'])) {

    $meetId = $_GET['meet'];

}

htmlHeaders("Swimming Management System - Enter a Meet");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Meet Entry Integrity Check</h1>\n";



if ($meetId != 0) {

    echo "<h3>$meetName</h3>\n";

} else {

    echo "<p>No meet selected!</p>\n";

}