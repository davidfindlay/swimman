<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
checkLogin();

htmlHeaders("Branch List - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Branch List</h1>\n";

$branchList = $GLOBALS['db']->getAll("SELECT * FROM branches;");
db_checkerrors($branchList);

foreach ($branchList as $l) {
	
	
	
}

echo "</div>\n";   // Main div

htmlFooters();

?>