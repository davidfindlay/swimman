<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/ConfirmationEmail.php");

checkLogin();

htmlHeaders("Email Test");

sidebarMenu();

echo "<div id=\"main\">\n";

?>

<h2>Email Test</h2>


<?php 

$emailTest = new ConfirmationEmail();
$emailTest->setEntryId(5265);
$emailTest->setMeetId(112);
$emailTest->setMemberId(26);
$emailTest->send();


echo "</div>\n"; // main div

htmlFooters();


?>