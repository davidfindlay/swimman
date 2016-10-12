<?php
require_once("../includes/setup.php");
require_once("../includes/classes/Meet.php");
require_once("../includes/classes/MeetEntry.php");
require_once("../includes/classes/MeetEntryEvent.php");
require_once("../includes/classes/MeetSelector.php");
require_once("../includes/classes/MeetProgram.php");
require_once("../includes/classes/Member.php");
require_once("../includes/classes/Club.php");

// Check for file upload
$meetId = mysql_real_escape_string($_POST['meetId']);
$username = mysql_real_escape_string($_POST['username']);
$password = mysql_real_escape_string($_POST['password']);
addlog("Uploader", "Upload request received for $meetId by $username.");

$result = authenticate($username, $password);

if ($result) {

	$uploaddir = $GLOBALS['home_dir'] . '/masters-eprogram';
	$uploadfile = $uploaddir . '/' . basename($_FILES['userfile']['name']);
	$uploadname = $_FILES['userfile']['name'];
	
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
			
		$eProgram = new MeetProgram();
		$eProgram->setMeet($meetId);
		$eProgram->import($uploadname);
		
		addlog("Uploader", "eProgram upload completed");
		//echo "eProgram upload completed!";
	
	}

} else {
	addlog("Uploader", "Authentication failed for $username and $password.");
	echo "eProgram upload authentication failed!";
}
