<?php
require_once("includes/setup.php");
checkLogin();

// Is there a submitted form?
if (isset($_POST['importusersubmit'])) {
	
	$import_user = mysql_real_escape_string($_POST['importuser']);

	$jdbuser = $GLOBALS['jdbuser'];
	$jdbpass = $GLOBALS['jdbpass'];
	$jdbhost = $GLOBALS['jdbhost'];
	$jdbport = $GLOBALS['jdbport'];
	$jdbname = $GLOBALS['jdbname'];
	
	$joomla_dsn = "mysql://$jdbuser:$jdbpass@$jdbhost:$jdbport/$jdbname";
	$GLOBALS['jdb'] =& DB::connect($joomla_dsn);
	db_checkerrors($GLOBALS['jdb']);

	// Get the user details from Joomla	
	$joomla_user = $GLOBALS['jdb']->getRow("SELECT * FROM j_users WHERE username = '$import_user';");
	db_checkerrors($joomla_user);
	
	print_r($joomla_user);
	
	if (isset($joomla_user)) {
	
		$j_uid = $joomla_user[0];
		$newRealname = $joomla_user[1];
		$newUsername = $joomla_user[2];
		$newPasshash = $joomla_user[4];
	
		// Check for associated member number and date of birth
		$msanumber1 = $GLOBALS['jdb']->getOne("SELECT profile_value FROM j_user_profiles WHERE user_id = '$j_uid' AND profile_key = 'profile.msanumber';");
		db_checkerrors($msanumber1);
	
		$msanumber = trim($msanumber1, chr(34));
	
		$dob1 = $GLOBALS['jdb']->getOne("SELECT profile_value FROM j_user_profiles WHERE user_id = '$j_uid' AND profile_key = 'profile.dob';");
		db_checkerrors($dob1);
	
		$dob = trim($dob1, chr(34));
	
		// 	Datamatch Membership database
		if (($msanumber != '') && ($dob != '')) {
		
			$msaMember = $GLOBALS['db']->getRow("SELECT * FROM members WHERE number = '$msanumber' AND dob = '$dob';");
			db_checkerrors($msaMember);
		
			$memberId = $msaMember[0];
		
		} else {
		
			$memberId = 0;
		
		}
	
		$successString = "Imported user <i>$newUsername</i> with MSA Number <i>$memberId</i>.\n";
	
		$insert1 = $GLOBALS['db']->query("INSERT INTO users (member, username, passwordhash) VALUES ('$memberId', '$newUsername', '$newPasshash');");
		db_checkerrors($insert1);
	
	} else {
		
		$successString = "Unknown user <i>$import_user</i>\n";
	}	
		
}

htmlHeaders("Swimming Management System - Import Joomla User");

sidebarMenu();

echo "<div id=\"main\">\n";


?>

<h1>Swimming Management System</h1>
<h2>Import Joomla User</h2>

<form method="post">
<?php 

if (isset($successString)) {

	echo "<p>\n";
	echo $successString;
	echo "</p>\n";

}

?>

<p>
<strong>Username: </strong> <input type="text" name="importuser" /><br />
<input type="submit" name="importusersubmit" value="Import User" />

</p>
</form>

<?php 

echo "</div>\n"; // main div

htmlFooters();


?>