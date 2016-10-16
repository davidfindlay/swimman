<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Member.php");
checkLogin();

$memberId = $_GET['member'];
$memberDetails = new Member();
$memberDetails->loadId($memberId);

htmlHeaders("Member List - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Member Details</h1>\n";
//$memberDetails->getQRCode();
$msa = $memberDetails->getMSANumber();

$memberFullName = $memberDetails->getFullname();
echo "<h2>$memberFullName</h2>\n";

echo "<img src=\"temp/qrcode-$msa.png\" alt=\"QR Code\" id=\"qrcode\" />\n";
echo "<p>\n";
echo "<label>Member ID:</label>\n";
echo $memberId;
echo "<br />\n";
echo "<label>Surname:</label>\n";
echo $memberDetails->getSurname();
echo "<br />\n";
echo "<label>First Name:</label>\n";
echo $memberDetails->getFirstname();
echo "<br />\n";
echo "<label>MSA Number:</label>\n";
echo $memberDetails->getMSANumber();
echo "<br />\n";
echo "<label>Date of Birth:</label>\n";
echo $memberDetails->getDob();
echo "<br />\n";
echo "<label>Gender:</label>\n";
echo $memberDetails->getGender();
echo "<br />\n";

echo "<h2>Membership Details</h2>\n";

$memberships = $GLOBALS['db']->getAll("SELECT clubs.clubname, clubs.code, membership_types.typename,
  membership_statuses.desc, member_memberships.startdate, member_memberships.enddate 
  FROM clubs, member_memberships, membership_types, membership_statuses 
  WHERE member_id = ?
  AND member_memberships.club_id = clubs.id
  AND member_memberships.type = membership_types.id
  AND member_memberships.status = membership_statuses.id
  ORDER BY enddate;",
    array($memberId));
db_checkerrors($memberships);

echo "<table>\n";
echo "<thead>\n";
echo "<tr>\n";
echo "<th>Club</th>\n";
echo "<th>Type</th>\n";
echo "<th>Status</th>\n";
echo "<th>Start</th>\n";
echo "<th>End</th>\n";
echo "</tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

foreach ($memberships as $m) {

    echo "<tr>\n";
    echo "<td>\n";
    echo $m[0] . "(" . $m[1] . ")";
    echo "</td>\n";
    echo "<td>\n";
    echo $m[2];
    echo "</td>\n";
    echo "<td>\n";
    echo $m[3];
    echo "</td>\n";
    echo "<td>\n";
    echo $m[4];
    echo "</td>\n";
    echo "<td>\n";
    echo $m[5];
    echo "</td>\n";
    echo "</tr>\n";

}

echo "</tbody>\n";

echo "</table>\n";

echo "</p>\n";


echo "</div>\n";   // Main div

htmlFooters();

?>