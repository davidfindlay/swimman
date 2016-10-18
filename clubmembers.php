<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
checkLogin();

htmlHeaders("Club Members");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Club Members</h1>\n";

echo "<table width=\"100%\">\n";
echo "<thead class=\"list\">\n";
echo "<tr class=\"list\">\n";
echo "<th>Name</th>\n";
echo "<th>MSA Number</th>\n";
echo "<th>Membership Type</th>\n";
echo "<th>Financial End Date</th>\n";
echo "</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";

$clubId = intval($_GET['club']);

if (isset($_GET['start'])) {

    $start = intval($_GET['start']);

} else {

    $start = 0;

}


$interval = 20;

$membersCount = $GLOBALS['db']->getOne("SELECT count(*) FROM member, member_memberships 
		WHERE member.id = member_memberships.member_id 
		AND member_memberships.club_id = ?;", array($clubId));
db_checkerrors($membersCount);

$memberList = $GLOBALS['db']->getAll("SELECT * FROM member, member_memberships 
		WHERE member.id = member_memberships.member_id 
		AND member_memberships.club_id = ?
		ORDER BY member_memberships.enddate DESC, member.surname, member.firstname 
		LIMIT ? OFFSET ?;", array($clubId, $interval, $start));
db_checkerrors($memberList);

foreach ($memberList as $m) {
	
	$firstName = $m[3];
	$lastName = $m[2];
	$msaNumber = $m[1];
	$financialEndDate = $m[15];
	
	echo "<tr class=\"list\">\n";
	
	echo "<td>$firstName $lastName</td>\n";
	echo "<td>$msaNumber</td>\n";
	echo "<td></td>\n";
	echo "<td>";
	
	if (strtotime($financialEndDate) < time()) {
		
		echo "<span style=\"color: red;\">\n";
		echo $financialEndDate;
		echo "</span>\n";
		
	} else {
	
		echo $financialEndDate;
		
	}
	echo "</td>\n";
	
	echo "</tr>\n";
	
}

echo "</tbody>\n";
echo "</table>\n";

echo "<p>\n";

if ($start != 0) {

	$prev = $start - $interval;

	if ($prev < 0) {

		$prev = 0;

	}

	echo "<a href=\"clubmembers.php?club=$clubId&start=0\">First</a> \n";
	echo "<a href=\"clubmembers.php?club=$clubId&start=$prev\">Previous</a> \n";
		
}

$next = $start + $interval;
$last = $membersCount - $interval;
if ($next < $membersCount) {

	echo "<a href=\"clubmembers.php?club=$clubId&start=$next\">Next</a> \n";
	echo "<a href=\"clubmembers.php?club=$clubId&start=$last\">Last</a>\n";

}

echo "</p>\n";


echo "</div>\n";   // Main div

htmlFooters();

?>