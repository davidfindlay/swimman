<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/imgfunctions.php");

checkLogin();

addlog("Access", "Accessed importmembers.php");

// Check for file upload
if (isset($_POST['importmemberssubmit'])) {
	
	$uploaddir = $GLOBALS['home_dir'] . '/masters-data/img';

	// Check if datafile has a zip extension
    $fileparts = pathinfo($_FILES["userfile"]["name"]);
    $fileext = $fileparts['extension'];

	$uploadfile = $uploaddir . '/' . 'imgmembers.xls';
	
	addlog("IMG Sync", "Manual Upload IMG Database", "IMG Database has been manually uploaded via web form.");


	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {

	    if ($fileext == "zip") {

	        unzipUpload();

        }
		
		$uploadStatus = parseImg();
    	
	} else {
		
    	$uploadStatus = "Unable to read upload file!\n";
    	
	}
	
}

if (isset($_POST['autoupdate'])) {
	
	getImg();
	$uploadStatus = parseImg();
	
}


htmlHeaders("Import IMG Members");

sidebarMenu();

echo "<div id=\"main\">\n";

?>

<h2>Import IMG Members</h2>

<h3>Upload Membership File:</h3>
<form enctype="multipart/form-data" method="post">
<p>
<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
<label>Membership CSV File: </label> <input type="file" name="userfile" />
</p>
<p>
<label> </label><input type="submit" name="importmemberssubmit" value="Import IMG Member Data" />
</p>
</form>


<h3>Automated Membership Update:</h3>
<form method="post">

<input type="submit" name="autoupdate" value="Automatically Update IMG Member Data" />

</form>

<?php 

if (isset($uploadStatus)) {
	
	echo "<h3>Update Report</h3>\n";

	echo "<p>\n";
	echo $uploadStatus;
	echo "</p>\n";
	
	
	
} else {

	echo "<h3>Last Update Details</h3>\n";
	
	$logs = $GLOBALS['db']->getAll("SELECT * FROM log, log_type WHERE log_type.logname = 'IMG Sync'
			AND log.log_type = log_type.id ORDER BY time DESC LIMIT 2;");
	db_checkerrors($logs);
	
	foreach ($logs as $l) {

		$logTime = $l[2];
		$logText = $l[7];
		
		echo "<p><strong>$logTime:</strong> $logText</p>\n";

	}

}

?>

<?php 

echo "</div>\n"; // main div

htmlFooters();


?>