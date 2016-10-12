<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Meet.php");
checkLogin();

htmlHeaders("Swimming Management System - Meet List");

sidebarMenu();

addlog("Access", "Accessed dashboard.php");

echo "<div id=\"main\">\n";

echo "<h1>Dashboard</h1>\n";

echo "<table border=\"0\">\n";
echo "<thead>\n";

echo "</thead>\n";
echo "<tbody>\n";
echo "<tr>\n";
echo "<th>\n";
echo "Total Current Members: ";
echo "</th>\n";
echo "<td>\n";

$numMembers = $GLOBALS['db']->getOne("SELECT count( DISTINCT number )
									FROM `member` , member_memberships
									WHERE member.id = member_memberships.member_id
									AND member_memberships.startdate <= CURDATE( )
									AND member_memberships.enddate >= CURDATE( ) ");
db_checkerrors($numMembers);

echo $numMembers;

echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<th>\n";
echo "Previous Year Members: ";
echo "</th>\n";
echo "<td>\n";

$numMembers = $GLOBALS['db']->getOne("SELECT count( DISTINCT number )
									FROM `member` , member_memberships
									WHERE member.id = member_memberships.member_id
									AND member_memberships.startdate <= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
									AND member_memberships.enddate >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) ");
db_checkerrors($numMembers);

echo $numMembers;

echo "</td>\n";
echo "</tr>\n";

echo "</tbody>\n";

echo "</table>\n";

echo 'Current PHP version: ' . phpversion();

echo "</div>\n";  // Main Div

htmlFooters();

?>
