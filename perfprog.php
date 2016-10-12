<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
checkLogin();

// Check for adding of a new program
if (isset($_POST['submit'])) {
	
	if ($_POST['submit'] == "Add Program") {
		
		$nShortName = mysql_real_escape_string($_POST['shortName']);
		$nLongName = mysql_real_escape_string($_POST['longName']);
		$nAgeSet = 1;
		$nStartDate = mysql_real_escape_string($_POST['startDate']);
		$nEndDate = mysql_real_escape_string($_POST['endDate']);
		$nTimesRequired = mysql_real_escape_string($_POST['timesRequired']);
		$nStatus = mysql_real_escape_string($_POST['status']);
		
		$insert = $GLOBALS['db']->query("INSERT INTO performance_programs (shortname, longname,
				agegroupset, startdate, enddate, status, timesrequired) 
				VALUES ('$nShortName', '$nLongName', '$nAgeSet', '$nStartDate', '$nEndDate',
				'$nStatus', '$nTimesRequired');");
		db_checkerrors($insert);
		
	}
	
}

htmlHeaders("Performance Program Setup - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Performance Program Setup</h1>\n";

echo "<h2>Program List</h2>\n";

echo "<table class=\"list\">\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>Short Name</th>\n";
echo "<th>Long Name</th>\n";
echo "<th>Age Group Set</th>\n";
echo "<th>Start Date</th>\n";
echo "<th>End Date</th>\n";
echo "<th>Status</th>\n";
echo "<th>Times Required</th>\n";
echo "<th>Levels</th>\n";
echo "<th>Edit</th>\n";
echo "<th>Results</th>\n";
echo "</tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

$progList = $GLOBALS['db']->getAll("SELECT * FROM performance_programs ORDER BY startdate DESC;");
db_checkerrors($progList);

foreach ($progList as $p) {
	
	$pId = $p[0];
	$shortName = $p[1];
	$longName = $p[2];
	$ageGroupSet = $p[3];
	$startDate = $p[4];
	$endDate = $p[5];
	$status = $p[6];
	
	$statusText = $GLOBALS['db']->getOne("SELECT status FROM performance_programs_statuscodes
			WHERE id = '$status';");
	db_checkerrors($statusText);
	
	$timesRequired = $p[7];
	
	$levels = $GLOBALS['db']->getOne("SELECT count(*) FROM performance_programs_levels 
			WHERE perf_prog_id = '$pId';");
	db_checkerrors($levels);
	
	echo "<tr class=\"list\">\n";
	echo "<td>";
	echo $shortName;
	echo "</td>\n";
	echo "<td>";
	echo $longName;
	echo "</td>\n";
	echo "<td>";
	echo $ageGroupSet;
	echo "</td>\n";
	echo "<td>";
	echo $startDate;
	echo "</td>\n";
	echo "<td>";
	echo $endDate;
	echo "</td>\n";
	echo "<td>";
	echo $statusText;
	echo "</td>\n";
	echo "<td>";
	echo $timesRequired;
	echo "</td>\n";
	echo "<td>";
	echo $levels;
	echo "</td>\n";
	echo "<td>";
	echo "<a href=\"perfprogedit.php?id=$pId\">Details</a>\n";
	echo " ";
	echo "<a href=\"perfprogmeet.php?id=$pId\">Import Meet</a>\n";
	echo " ";
	echo "<a href=\"perfprogcopy.php?id=$pId\">Copy Existing Program</a>\n";
	echo "</td>\n";
	echo "<td>";
	echo "<a href=\"perfprogquals.php?id=$pId\">Qualifying Results</a>\n";
	echo " ";
	echo "<a href=\"perfprogresults.php?id=$pId\">Program Results</a>\n";
	echo "</td>\n";
	echo "</tr>\n";
	
}


echo "</tbody>\n";
echo "</table>\n";


echo "<h2>Add New Program</h2>\n";

echo "<form method=\"post\">\n";
echo "<p>\n";
echo "<label for=\"shortName\">Short Name:</label>\n";
echo "<input type=\"text\" name=\"shortName\" id=\"shortName\" /><br />\n";
echo "<label for=\"longName\">Long Name:</label>\n";
echo "<input type=\"text\" name=\"longName\" id=\"longName\" /><br />\n";

echo "<label for=\"ageSet\">Age Group Set:</label>\n";
echo "1<br />";

echo "<label for=\"startDate\">Start Date:</label>\n";
echo "<input type=\"text\" name=\"startDate\" id=\"startDate\" /><br />\n";
echo "<label for=\"endDate\">End Date:</label>\n";
echo "<input type=\"text\" name=\"endDate\" id=\"endDate\" /><br />\n";

echo "<label for=\"timesRequired\">Times Required:</label>\n";
echo "<input type=\"text\" name=\"timesRequired\" id=\"timesRequired\" /><br />\n";

echo "<label for=\"status\">Status:</label>\n";
echo "<select name=\"status\" id=\"status\">\n";

// Get list of statuses
$statusList = $GLOBALS['db']->getAll("SELECT * FROM performance_programs_statuscodes ORDER by sort;");
db_checkerrors($statusList);

foreach ($statusList as $s) {
	
	$statusId = $s[0];
	$statusText = $s[2];
	
	echo "<option value=\"$statusId\">$statusText</option>\n";
	
}

echo "</select><br />\n";
echo "</p>\n";
echo "<p>\n";
echo "<input type=\"submit\" name=\"submit\" value=\"Add Program\" />\n";
echo "</p>\n";

echo "</form>\n";

echo "</div>\n";   // Main div

htmlFooters();

?>