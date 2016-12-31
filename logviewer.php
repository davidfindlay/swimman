<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");
checkLogin();

htmlHeaders("Swimming Management System - Log Viewer");

sidebarMenu();

addlog("Access", "Accessed logviewer.php");

echo "<div id=\"main\">\n";

echo "<h1>Log Viewer</h1>\n";

// Get list of log types
$log_types = $GLOBALS['db']->getAll("SELECT * FROM log_type ORDER by logname;");
db_checkerrors($log_types);

// Default period last 3 days
$psPeriod = "last3";
if (isset($_POST['psPeriod'])) {
	
	$psPeriod = $_POST['psPeriod'];
	
}

echo "<form method=\"post\">\n";

echo "<fieldset>\n";
echo "<p>\n";
echo "<label for=\"psPeriod\">Preset Date/Time Range</label>\n";
echo "<select name=\"psPeriod\">\n";
echo "<option value=\"last3\"";

if ($psPeriod == "last3") {
	
	echo " selected=\"selected\"";
	
}

echo ">Last 3 Days</option>\n";
echo "<option value=\"last7\"";

if ($psPeriod == "last7") {

	echo " selected=\"selected\"";

}

echo ">Last 7 Days</option>\n";
echo "<option value=\"last30\"";

if ($psPeriod == "last30") {

	echo " selected=\"selected\"";

}

echo ">Last 30 Days</option>\n";
echo "<option value=\"all\"";

if ($psPeriod == "all") {

	echo " selected=\"selected\"";

}

echo ">All</option>\n";
echo "</select>\n";
echo "</p>\n";
echo "<p>\n";
echo "<label for=\"logname\">Log Name:</label>\n";
echo "<select name=\"logname\" id=\"logname\">\n";

echo "<option value=\"\"></option>\n";

foreach ($log_types as $t) {

	$tId = $t[0];
	$tName = $t[1];
	
	if ((isset($_POST['logname'])) && ($_POST['logname'] == $tId)) {
	
		echo "<option value=\"$tId\" selected=\"selected\">$tName</option>\n";
		
	} else {
	
		echo "<option value=\"$tId\">$tName</option>\n";
		
	}

}

echo "</select>\n";

echo "</p>\n";
echo "<p>\n";
echo "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Filter\" />\n";
echo "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Refresh\" />\n";
echo "</p>\n";
echo "</fieldset>\n";
echo "</form>\n";

switch ($psPeriod) {
	
	case 'last3': 
		$periodClause = " AND time > DATE_SUB(NOW(), INTERVAL 3 DAY) ";
		break;
	case 'last7':
		$periodClause = " AND time > DATE_SUB(NOW(), INTERVAL 7 DAY) ";
		break;
	case 'last30':
		$periodClause = " AND time > DATE_SUB(NOW(), INTERVAL 30 DAY) ";
		break;
	case 'all':
		$periodClause = "";
		break;	
}

if (isset($_POST['logname'])) {

	if ($_POST['logname'] != "") {
		
		$logId = mysql_real_escape_string($_POST['logname']);

		$logs = $GLOBALS['db']->getAll("SELECT * FROM log, log_type WHERE log.log_type = '$logId' 
			AND log.log_type = log_type.id $periodClause ORDER BY time DESC, log.id DESC;");
		db_checkerrors($logs);
	
	} else {
		
		$logs = $GLOBALS['db']->getAll("SELECT * FROM log, log_type
				WHERE log.log_type = log_type.id $periodClause ORDER BY time DESC, log.id DESC;");
		db_checkerrors($logs);
		
	}

} else {

	$logs = $GLOBALS['db']->getAll("SELECT * FROM log, log_type 
			WHERE log.log_type = log_type.id $periodClause ORDER BY time DESC, log.id DESC;");
	db_checkerrors($logs);

}

echo "<table width=\"100%\" class=\"list\">\n";

echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>\n";
echo "Timestamp\n";
echo "</th>\n";
echo "<th>\n";
echo "Log Name\n";
echo "</th>\n";
echo "<th>\n";
echo "Who\n";
echo "</th>\n";
echo "<th>\n";
echo "Short\n";
echo "</th>\n";
echo "<th>\n";
echo "Text\n";
echo "</th>\n";
echo "</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";

foreach ($logs as $l) {

	$lId = $l[0];
	$lTime = $l[2];
	$lName = $l[9];
	$lAdminUser = $l[3];
	$lMember = $l[4];
	$lJuser = $l[5];
	$lShort = $l[6];
	$lText = $l[7];

	echo "<tr class=\"list\">\n";
	
	echo "<td>\n";
	echo "$lTime - $lId\n";
	echo "</td>\n";
	
	echo "<td>\n";
	echo $lName;
	echo "</td>\n";
	
	echo "<td>\n";
	
	if ($lAdminUser != 0) {
	
		$lAdminUserName = $GLOBALS['db']->getOne("SELECT username FROM users WHERE id = '$lAdminUser';");
		db_checkerrors($lAdminUserName);
	
		echo "<img src=\"images/admin.png\" alt=\"admin\" />$lAdminUserName\n";
	
	} else {
	
		$jUserName = $GLOBALS['db']->getOne("SELECT joomla_user FROM member_msqsite 
				WHERE joomla_uid = '$lJuser';");
		db_checkerrors($jUserName);
		
		echo "<img src=\"images/members.png\" alt=\"member\" />$jUserName\n";
	
	}
	
	echo "</td>\n";
	
	echo "<td>\n";
	echo $lShort;
	echo "</td>\n";
	
	echo "<td>\n";
	echo $lText;
	echo "</td>\n";
	
	echo "</tr>\n";
	
}

echo "</tbody>\n";

echo "</table>\n";
echo "</div>\n";  // Main Div

htmlFooters();

?>