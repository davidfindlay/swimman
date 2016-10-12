<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Member.php");
checkLogin();

htmlHeaders("Member List - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Member List</h1>\n";

echo "<table width=\"100%\">\n";
echo "<thead class=\"list\">\n";
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
echo "Status\n";
echo "</th>\n";
echo "<th>\n";
echo "Controls";
echo "</th>\n";
echo "</tr>\n";
echo "</thead>\n";

// Get list of members
$memberList = $GLOBALS['db']->getAll("SELECT id FROM member ORDER BY number;");
db_checkerrors($memberList);

echo "<tbody class=\"list\">\n";

foreach ($memberList as $m) {
	
	$mId = $m[0];
	
	$curMem = new Member();
	$curMem->loadId($mId);
	
	$msaNumber = $curMem->getMSANumber();
	$surname = $curMem->getSurname();
	$firstname = $curMem->getFirstname();
	$sex = $curMem->getGender();
	$dob = $curMem->getDob();
	
		
	echo "<tr class=\"list\">\n";
	echo "<td>\n";
	echo "$msaNumber\n";
	echo "</td>\n";
	
	echo "<td>\n";
	
	$memberClubs = $GLOBALS['db']->getAll("SELECT DISTINCT (member_memberships.club_id), clubs.clubname, 
			clubs.code FROM member_memberships, clubs WHERE member_memberships.club_id = clubs.id
			AND member_memberships.member_id = '$mId' ORDER BY member_memberships.enddate DESC;");
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
	echo $sex;	
	echo "</td>\n";
	
	echo "<td>\n";
	echo "$dob\n";
	echo "</td>\n";
	
	echo "<td>\n";
	
	$clubRun = 0;
	foreach ($memberClubs as $c) {

		$cId = $c[0];
		
		if ($clubRun > 0) 
			echo "<br />\n";
		
		echo $curMem->getMembershipStatusText($cId);
		
		$clubRun++;
						
	}
	
	echo "</td>\n";
	
	echo "<td>\n";
	echo "<a href=\"meetentry.php?member=$mId\">Create Meet Entry</a>\n";
	echo " | ";
	echo "<a href=\"memberdetails.php?member=$mId\">Details</a>\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	
}

echo "</tbody>\n";
echo "</table>\n";

echo "</div>\n";   // Main div

htmlFooters();

?>