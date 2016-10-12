<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
checkLogin();

// Handle form
if (isset($_POST['updateClubsSubmit'])) {

	if (isset($_POST['clubSelect'])) {
	
		foreach ($_POST['clubSelect'] as $c) {
		
			$clubId = mysql_real_escape_string($c);
			
			if (isset($_POST['regionId'])) {
			
				// Put club into region
				$regionId = mysql_real_escape_string($_POST['regionId']);
				
				$update = $GLOBALS['db']->query("UPDATE clubs SET region = '$regionId' WHERE id = '$clubId';");
				db_checkerrors($update);
			
			}
			
			if (isset($_POST['changeCase'])) {
			
				// Change club name to sentance case
				$curName = $GLOBALS['db']->getOne("SELECT clubname FROM clubs WHERE id = '$clubId';");
				db_checkerrors($curName);
				
				$newName = ucWords(strtolower($curName));
				$newName = str_replace('Aussi', 'AUSSI', $newName);
				
				$update = $GLOBALS['db']->query("UPDATE clubs SET clubname = '$newName' WHERE id = '$clubId';");
				db_checkerrors($update);
			
			}
		
		}
	
	}

}

htmlHeaders("Club List - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Club List</h1>\n";

echo "<form method=\"post\">\n";

echo "<div id=\"clubs\">\n";



echo "<table width=\"100%\">\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>\n";
echo "</th>\n";
echo "<th>\n";
echo "Branch\n";
echo "</th>\n";
echo "<th>\n";
echo "Region\n";
echo "</th>\n";
echo "<th>\n";
echo "Code\n";
echo "</th>\n";
echo "<th>\n";
echo "Name\n";
echo "</th>\n";
echo "<th>\n";
echo "Roles\n";
echo "</th>\n";
echo "<th>\n";
echo "Members\n";
echo "</th>\n";
echo "<th>\n";
echo "\n";
echo "</th>\n";
echo "</tr>\n";
echo "</thead>\n";

echo "<tbody class=\"list\">\n";

// Get club list
$clubList = $GLOBALS['db']->getAll("SELECT * FROM clubs ORDER BY region, code;");
db_checkerrors($clubList);

foreach ($clubList as $c) {
	
	$clubId = $c[0];
	$regionId = $c[4];
	$code = $c[1];
	$clubName = $c[2];
	$regionDetails = $GLOBALS['db']->getRow("SELECT * FROM branch_regions WHERE id = '$regionId';");
	db_checkerrors($regionDetails);
	$regionName = $regionDetails[2];
	$branchId = $regionDetails[1];
	$branchCode = $GLOBALS['db']->getOne("SELECT branchcode FROM branches WHERE id = '$branchId';");
	db_checkerrors($branchCode);
	$numMembers = $GLOBALS['db']->getOne("SELECT count(*) FROM member_memberships WHERE club_id = '$clubId' AND startdate <= NOW() AND enddate >= NOW();");
	db_checkerrors($numMembers);
	$clubRolesNum = $GLOBALS['db']->getOne("SELECT count(*) FROM club_roles WHERE club_id = ?;", array($clubId));
	db_checkerrors($clubRolesNum);
	
	echo "<tr class=\"list\">\n";
	
	echo "<td>\n";
	echo "<input type=\"checkbox\" id=\"clubSelect_$clubId\" name=\"clubSelect[]\" value=\"$clubId\" />\n";
	echo "</td>\n";
	
	echo "<td class=\"branch\">\n";
	echo "$branchCode\n";
	echo "</td>\n";
	
	echo "<td class=\"region\">\n";
	echo "$regionName\n";
	echo "</td>\n";
	
	echo "<td class=\"code\">\n";
	echo "$code($clubId)\n";
	echo "</td>\n";
	
	echo "<td class=\"clubname\">\n";
	echo "$clubName\n";
	echo "</td>\n";
	
	echo "<td class=\"short\">\n";
	if ($clubRolesNum > 0) {
		echo "$clubRolesNum\n";
	}
	echo "</td>\n";
	
	echo "<td class=\"short\">\n";
	if ($numMembers > 0) {
		echo $numMembers;
	}
	echo "</td>\n";
	
	echo "<td>\n";
	echo "<a href=\"clubsettings.php?club=$clubId\"><img src=\"images/edit.png\" alt=\"Edit\" /></a>\n";
	echo "<a href=\"clubroles.php?club=$clubId\"><img src=\"images/roles.png\" alt=\"Rolls\" /></a>\n";
	echo "<a href=\"clubmembers.php?club=$clubId\"><img src=\"images/members.png\" alt=\"Member List\" /></a>\n";
	echo "</td>\n";
		
	echo "</tr>\n";
	
}

echo "</tbody>\n";

echo "</table>\n";

echo "<div align=\"right\">\n";
echo "<a href=\"clubadd.php\"><img src=\"images/edit.png\" alt=\"Add\" />Add a Club</a>\n";
echo "</div>\n";
echo "<fieldset>\n";
echo "<p>\n";
echo "<label for=\"regionId\">Add Selection to Region:</label>\n";
echo "<select name=\"regionId\" id=\"regionId\">\n";

echo "<option value=\"\"></option>\n";

// Get list of regions
$branchesList = $GLOBALS['db']->getAll("SELECT * FROM branches ORDER BY branchname;");
db_checkerrors($branchesList);

foreach ($branchesList as $b) {

	$bId = $b[0];
	$bCode = $b[1];

	echo "<optgroup label=\"$bCode\">\n";

	$regionList = $GLOBALS['db']->getAll("SELECT * FROM branch_regions WHERE branch = '$bId';");
	db_checkerrors($regionList);

	foreach ($regionList as $r) {

		$rId = $r[0];
		$rBranch = $r[1];
		$rName = $r[2];
	
		echo "<option value=\"$rId\">$rName</option>\n";

	}

	
}
echo "</select>\n";
echo "<br />\n";
echo "<label for=\"changeCase\">Change to Sentance Case: </label>\n";
echo "<input type=\"checkbox\" name=\"changeCase\" id=\"changeCase\" />\n";
echo "</p>\n";
echo "<p>\n";
echo "<input type=\"submit\" name=\"updateClubsSubmit\" id=\"updateClubsSubmit\" value=\"Update\" />\n";
echo "<input type=\"reset\" value=\"Reset Selection\" />\n";
echo "</p>\n";
echo "</fieldset>\n";
echo "</form>\n";

echo "</div>\n";

echo "</div>\n";   // Main div

htmlFooters();

?>