<?php
require_once("includes/setup.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEntry.php");
require_once("includes/classes/MeetEntryEvent.php");
require_once("includes/classes/MeetSelector.php");
require_once("includes/classes/MeetProgram.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Club.php");
checkLogin();

// Check for file upload
if (isset($_POST['importbackupfile'])) {

	$meetId = mysql_real_escape_string($_POST['meetId']);
	
	$uploaddir = $GLOBALS['home_dir'] . '/masters-eprogram';
	$uploadfile = $uploaddir . '/' . basename($_FILES['userfile']['name']);
	$uploadname = $_FILES['userfile']['name'];

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		
		$eProgram = new MeetProgram();
		$eProgram->setMeet($meetId);
		$eProgram->import($uploadname);

	}


}

htmlHeaders("Swimming Management System - Upload Backup File to eProgram");

echo "<div id=\"main\">\n";

echo "<h1>Upload Backup File to eProgram</h1>\n";

echo "<form enctype=\"multipart/form-data\" method=\"post\">\n";

echo "<p>\n";
echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"5000000\" />\n";
echo "<label>Meet: </label>\n";

$meetSel = new MeetSelector;
$meetSel->setName("meetId");
$meetSel->showDate();
$meetSel->output();

echo "<br />\n";

echo "<strong>Upload file: </strong> <input type=\"file\" name=\"userfile\" /><br />\n";
echo "<input type=\"submit\" name=\"importbackupfile\" value=\"Upload Backup File\" />\n";
echo "</p>\n";
echo "</form>\n";


echo "</div>\n";  // Main Div

htmlFooters();

?>