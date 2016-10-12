<?php
require_once("includes/setup.php");
require_once("includes/classes/Member.php");
checkLogin();

$memberId = $_GET['member'];
$memberDetails = new Member();
$memberDetails->loadId($memberId);

htmlHeaders("Member List - Swimming Management System");

echo "<div id=\"main\">\n";

echo "<h1>Member Details</h1>\n";
$memberDetails->getQRCode();
$msa = $memberDetails->getMSANumber();

$memberFullName = $memberDetails->getFullname();
echo "<h2>$memberFullName</h2>\n";

echo "<img src=\"temp/qrcode-$msa.png\" alt=\"QR Code\" id=\"qrcode\" />\n";
echo "<p>\n";
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


echo "</p>\n";


echo "</div>\n";   // Main div

htmlFooters();

?>