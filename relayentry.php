<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");
checkLogin();

addlog("Access", "Accessed relayentry.php");

htmlHeaders("Relay Entries");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Relay Entries</h1>\n";

echo "</div>\n";  // Main Div

htmlFooters();

?>
