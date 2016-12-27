<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
checkLogin();

if (isset($_POST['linksubmit'])) {
	
	// Link selected members to club
	$linkClubId = intval($_POST['linkclub']);
	
	foreach ($_POST['linkmembers'] as $l) {
		
		// Insert row to link member to club
		$memberId = intval($l);
		
		// Check if this member is already linked to this club
		$linked = $GLOBALS['db']->getOne("SELECT id FROM member_clubs WHERE member_id = '$memberId' AND club_id = '$linkClubId';");
		db_checkerrors($linked);

		if ($linked == '') {		
			
			$insert = $GLOBALS['db']->query("INSERT INTO member_clubs (member_id, club_id) VALUES ('$memberId', '$linkClubId');");
			db_checkerrors($insert);
			
		} else {

			
		}

			
	}
	
}

htmlHeaders("Club List - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Link Members to Clubs</h1>\n";

// Display a drop box of clubs
echo "<form method=\"post\">\n";

$clubId = mysql_real_escape_string($_GET['club']);

$clubList = $GLOBALS['db']->getAll("SELECT id, clubname FROM clubs;");
db_checkerrors($clubList);

echo "<label>Club: </label>\n";
echo "<select name=\"linkclub\">\n";

foreach ($clubList as $c) {
	
	$cId = $c[0];
	$cName =$c[1];
	
	if ($clubId == $cId) {
		
		echo "<option value=\"$cId\" selected=\"selected\">$cName</option>\n";
		
	} else {
		
		echo "<option value=\"$cId\">$cName</option>\n";
		
	}
	
}

echo "</select><br />\n";

echo "<label>Select Members: </label><br />\n";
echo "<table width=\"100%\">\n";

echo "<tr>\n";
echo "<th>\n";
echo "ID\n";
echo "</th>\n";
echo "<th>\n";
echo "Club\n";
echo "</th>\n";
echo "<th>\n";
echo "Surname\n";
echo "</th>\n";
echo "<th>\n";
echo "Firstname\n";
echo "</th>\n";
echo "<th>\n";
echo "Sex\n";
echo "</th>\n";
echo "<th>\n";
echo "DOB\n";
echo "</th>\n";
echo "<th>\n";
echo "Controls";
echo "</th>\n";
echo "</tr>\n";

// Get list of members
$memberList = $GLOBALS['db']->getAll("SELECT * FROM member ORDER BY number;");
db_checkerrors($memberList);

foreach ($memberList as $m) {

	$mId = $m[0];
	$msaNumber = $m[1];
	$surname = $m[2];
	$firstname = $m[3];
	$sex = $m[6];
	$dob = $m[5];

	echo "<tr>\n";
	echo "<td>\n";
	echo "$msaNumber\n";
	echo "</td>\n";

	echo "<td>\n";
	$memberClubs = $GLOBALS['db']->getAll("SELECT member_clubs.club_id, clubs.clubname, clubs.code FROM member_clubs, clubs WHERE member_clubs.club_id = clubs.id AND member_clubs.member_id = '$mId';");
	db_checkerrors($memberClubs);
	
	foreach ($memberClubs as $c) {
		
		$cId = $c[0];
		$cName= $c[1];
		$cCode = $c[2];
		
		echo "$cCode<br />\n";
		
	}
	echo "</td>\n";

	echo "<td>\n";
	echo "$surname\n";
	echo "</td>\n";

	echo "<td>\n";
	echo "$firstname\n";
	echo "</td>\n";

	echo "<td>\n";
	if ($sex == 1) {

		echo "M\n";

	} else {

		echo "F\n";

	}

	echo "</td>\n";

	echo "<td>\n";
	echo "$dob\n";
	echo "</td>\n";

	echo "<td>\n";
	echo "<input type=\"checkbox\" name=\"linkmembers[]\" value=\"$mId\" /><label>Member</label>\n";
	echo "</td>\n";

	echo "</tr>\n";

}


echo "</table>\n";

echo "<input type=\"submit\" name=\"linksubmit\" value=\"Link Members\" />";
echo "</form>\n";

echo "</div>\n";   // Main div

htmlFooters();

?>