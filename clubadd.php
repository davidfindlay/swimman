<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
checkLogin();

// Cancel without updates
if (isset($_POST['cancel'])) {
	
	header("Location: clubs.php");
	
}

if (isset($_POST['addclubSubmit'])) {
	
	$clubcode = mysql_real_escape_string($_POST['clubcode']);
	$clubname = mysql_real_escape_string($_POST['clubname']);
	$clubregionId = mysql_real_escape_string($_POST['clubregion']);
	$clubaddress1 = mysql_real_escape_string($_POST['clubaddress1']);
	$clubaddress2 = mysql_real_escape_string($_POST['clubaddress2']);
	$clubsuburb = mysql_real_escape_string($_POST['clubsuburb']);
	$clubstate = mysql_real_escape_string($_POST['clubstate']);
	$clubpostcode = mysql_real_escape_string($_POST['clubpostcode']);
	
	// Create address - use default country Australia
	$addressId = sw_createAddress($clubaddress1, $clubaddress2, $clubsuburb, $clubstate, 1, $clubpostcode);
	
	$clubInsert = $GLOBALS['db']->query("INSERT INTO clubs (code, clubname, postal, region) VALUES ('$clubcode', '$clubname', '$addressId', '$clubregionId');");
	db_checkerrors($clubInsert);
	
	header("Location: clubs.php");
	
}


htmlHeaders("Add a Club - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Add a Club</h1>\n";

echo "<form method=\"post\">\n";

echo "<p>\n";
echo "<label>Club Code: </label>\n";
echo "<input type=\"text\" name=\"clubcode\" size=\"3\"><br />\n";
echo "<label>Name: </label>\n";
echo "<input type=\"text\" name=\"clubname\" size=\"40\"><br />\n";

echo "<label>Region: </label>\n";
echo "<select name=\"clubregion\">\n";

// Get list of regions
$regionList = $GLOBALS['db']->getAll("SELECT branch_regions.id, branches.branchcode, branch_regions.regionname FROM branch_regions, branches WHERE branches.id = branch_regions.branch ORDER BY branch_regions.id;");
db_checkerrors($regionList);

foreach ($regionList as $r) {
	
	$regionId = $r[0];
	$regionBranch = $r[1];
	$regionName = $r[2];
	
	echo "<option value=\"$regionId\">$regionBranch - $regionName</option>\n";
	
}

echo "</select><br />\n";

echo "<label>Postal Address:</label><br />\n";
echo "<label>Address 1: </label>\n";
echo "<input type=\"text\" name=\"clubaddress1\" size=\"40\"><br />\n";
echo "<label>Address 2: </label>\n";
echo "<input type=\"text\" name=\"clubaddress2\" size=\"40\"><br />\n";
echo "<label>Suburb: </label>\n";
echo "<input type=\"text\" name=\"clubsuburb\" size=\"40\"><br />\n";

echo "<label>State: </label>\n";
echo "<select name=\"clubstate\">\n";

// Get list of regions
$stateList = $GLOBALS['db']->getAll("SELECT * FROM states;");
db_checkerrors($stateList);

foreach ($stateList as $s) {
	
	$stateId = $s[0];
	$stateName = $s[3];
	
	echo "<option value=\"$stateName\">$stateName</option>\n";
	
}

echo "</select><br />\n";

echo "<label>Postcode: </label>\n";
echo "<input type=\"text\" name=\"clubpostcode\" size=\"4\"><br />\n";

echo "<input type=\"submit\" name=\"addclubSubmit\" value=\"Add Club\"> \n";
echo "<input type=\"submit\" name=\"cancel\" value=\"Cancel\">\n";

echo "</p>\n";

echo "</form>\n";

echo "</div>\n";   // Main div

htmlFooters();

?>