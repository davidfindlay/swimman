<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/imgfunctions.php");

checkLogin();

addlog("Access", "Accessed importre1.php");

if (isset($_POST['importmemberssubmit'])) {

	$uploaddir = $GLOBALS['home_dir'] . '/masters-data/';
	$filename = basename($_FILES["userfile"]["name"]);

	addlog("Clubs", "Upload RE1 File", "RE1 Database has been uploaded.");

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploaddir. basename($_FILES["userfile"]["name"]))) {

		$uploadStatus = "Successfully uploaded.";

		$re1 = new RE1File();
		$re1->setDatafile($filename);
		$re1->updateDetails();

	} else {

		$uploadStatus = "Unable to read upload file!\n";

	}

}

htmlHeaders("Import RE1 Members");

sidebarMenu();

echo "<div id=\"main\">\n";

?>

<h2>Import RE1 Members</h2>

<h3>Upload Membership File:</h3>
<form enctype="multipart/form-data" method="post">
<p>
<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
<label>Membership CSV File: </label> <input type="file" name="userfile" />
</p>
<p>
<label> </label><input type="submit" name="importmemberssubmit" value="Import RE1 Member Data" />
</p>
</form>

?>

<?php 

echo "</div>\n"; // main div

htmlFooters();


?>