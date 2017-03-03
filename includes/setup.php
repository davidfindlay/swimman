<?php

// Start session
if (session_id() == '') {
	session_start();		// fix for joomla components
}

// Database include
require_once ("DB.php");
require_once ("config.php");
require_once ("array_column_impl.php");

require_once ($_SERVER['DOCUMENT_ROOT'] . "/swimman/vendor/autoload.php");

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

//require_once ("../includes/Zebra_Form/Zebra_Form.php");
global $db;

setlocale(LC_MONETARY, 'en_AU');

database_connect();

date_default_timezone_set('Australia/Brisbane');
// set_magic_quotes_runtime(false);

// Authentication logger
$GLOBALS['authLog'] = new \Monolog\Logger('auth');
$GLOBALS['authLog']->pushProcessor(new \Monolog\Processor\WebProcessor);
$GLOBALS['authLog']->pushHandler(new StreamHandler($GLOBALS['log_dir'] . 'auth.log', $GLOBALS['log_level']));


if (isset($_GET['logout'])) {
	
	$logOutUser = $_SESSION['swuname'];
	
	unset($_SESSION['swuid']);
	unset($_SESSION['swuname']);
	
	$expire = time() -3600;
	setcookie('swuname', "", $expire);
	setcookie('swpass', "", $expire);
			
	session_destroy();

    $GLOBALS['authLog']->info("Log out: $logOutUser");
	
	header("Location: login.php");
	
}

function siteURL() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'].'/';
    return $protocol.$domainName;
}

define( 'SITE_URL', siteURL() );

function htmlHeaders($pageTitle) {
	
    ?>
    
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "DTD/xhtml1-frameset.dtd">
	<html>
	<head>
	<meta name="viewport" content="width=device-width">
	<link rel="stylesheet" type="text/css" href="style/screen.css">
	<link rel="stylesheet" href="style/jquery.sidr.light.css" />
    <link rel="stylesheet" href="style/jquery-ui.min.css" />
    <link rel="stylesheet" href="style/jquery-ui.structure.min.css" />
    <link rel="stylesheet" href="style/jquery-ui.theme.min.css" />
	<link rel="stylesheet" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
        <link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.4/css/buttons.dataTables.min.css" />

	<script src="/swimman/includes/jquery-3.1.1.min.js"></script>
	<script src="/swimman/includes/jquery-ui.min.js"></script>
    <script src="/swimman/includes/combobox.js"></script>
	<script src="/swimman/includes/jquery.sidr.min.js"></script>
    <script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
        <script src="//cdn.datatables.net/buttons/1.2.4/js/dataTables.buttons.min.js"></script>
        <script src="//cdn.datatables.net/buttons/1.2.4/js/buttons.flash.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
        <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
        <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
        <script src="//cdn.datatables.net/buttons/1.2.4/js/buttons.html5.min.js"></script>
        <script src="//cdn.datatables.net/buttons/1.2.4/js/buttons.print.min.js"></script>

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>


        <title><?php echo $pageTitle; ?></title>
	
	</head>
	
	<body>
	
	<div id="headerbar">
	
	<div id="mobile-menu-button">
	<a id="responsive-menu-button" href="#sidr-main">&#9776;</a>
	</div>
	
	<h1>Swimming Management System</h1>
	
	</div>
	
	<?php
	
}

function htmlFooters() {
	
	echo "</body>\n";
	echo "</html>\n";
	
}



// Connect to the database and pass back object
function database_connect() {
	
	$dbuser = $GLOBALS['dbuser'];
	$dbpass = $GLOBALS['dbpass'];
	$dbhost = $GLOBALS['dbhost'];
	$dbport = $GLOBALS['dbport'];
	$dbname = $GLOBALS['dbname'];
	
 	$dsn = "mysql://$dbuser:$dbpass@$dbhost:$dbport/$dbname";

	$GLOBALS['db'] = DB::connect($dsn);
	db_checkerrors($GLOBALS['db']);
	
	// Set correct timezone for all operations
	$result = $GLOBALS['db']->query("SET time_zone = '+10:00';");
	db_checkerrors($result);

}

function database_close() {

	$GLOBALS['db']->disconnect();

}

function db_checkerrors($var) {

	if (DB::isError($var)) {
		
		echo 'Standard Message: ' . $var->getMessage() . "<br />\n";
    	echo 'DBMS/User Message: ' . $var->getUserInfo() . "<br />\n";
    	echo 'DBMS/Debug Message: ' . $var->getDebugInfo() . "<br />\n";
	
		$message = $var->getDebugInfo();
		$backTrace = debug_backtrace();
		$callingFunction = $backTrace[0]['function'];

        $dbLog = new \Monolog\Logger('db');
        $dbLog->pushHandler(new StreamHandler($GLOBALS['log_dir'] . 'db.log', $GLOBALS['log_level']));
        $dbLog->critical("SQL error in $callingFunction: $message");

		echo "Oops. An error has occured. This incident has been logged and we apologise for the
			inconvenience. <a href=\"/\">Click here to return home.</a>";
		
		$GLOBALS['db']->disconnect();

		exit;
	}
	
}

// Check for user
function checkLogin() {

	if ( ! isset($_SESSION['swuname'])) {

		$result = false;
		
		// Check for cookies
		if ( ! isset($_COOKIE['swuname'] ) ) {

			// Go to login page
			header("Location: login.php");

		} else {

			// Verify cookie data to log user in

			$username = $_COOKIE['swuname'];
			$password = $_COOKIE['swpass'];

			$result = authenticate($username, $password);

            $GLOBALS['authLog']->info("Log in via cookie: $username");

		}
		
		if ($result == false) {
			
			// Go to login page
			header("Location: login.php");
			
		}
	}

}

function authenticate($username, $password) {

	$uname = $username;
	$pass = $password;

	// Does the user exist in our database?
	$row = $GLOBALS['db']->getRow("SELECT * FROM users WHERE username = ?;",
        array($uname));
	db_checkerrors($row);
	
	if (!$row) {
		
		return (false);
		
	} else {
		
		$uid = $row[0];
		$uname = $row[2];
		$dbpass = $row[3];
		$hashparts = explode(':', $dbpass);
		
		$userhash = md5($pass . $hashparts[1]);
		
		if ($userhash == $hashparts[0]) {
			
			$_SESSION['swuid'] = $uid;
			$_SESSION['swuname'] = $uname;
			
			return (true);
			
		} else {

			$jdbuser = $GLOBALS['jdbuser'];
			$jdbpass = $GLOBALS['jdbpass'];
			$jdbhost = $GLOBALS['jdbhost'];
			$jdbport = $GLOBALS['jdbport'];
			$jdbname = $GLOBALS['jdbname'];
			
			// Authentication failed. Perhaps user has updated username and password. Synchronise with Joomla
			$joomla_dsn = "mysql://$jdbuser:$jdbpass@$jdbhost:$jdbport/$jdbname";
			
			$GLOBALS['jdb'] =& DB::connect($joomla_dsn);
			db_checkerrors($GLOBALS['jdb']);
			
			$joomla_user = $GLOBALS['jdb']->getRow("SELECT * FROM j_users WHERE username = '$uname';");
			db_checkerrors($joomla_user);
			
			$jpass = $joomla_user[4];
			$jhashparts = explode(':', $jpass);
			
			$juserhash = md5($pass . $jhashparts[1]);
			
			if ($juserhash == $jhashparts[0]) {
				
				// We have a match, sync password hash, return true to authentication
				
				$insert1 = $GLOBALS['db']->query("UPDATE users SET passwordhash = '$jpass' WHERE id = '$uid';");
				db_checkerrors($insert1);
				
				$_SESSION['swuid'] = $uid;
				$_SESSION['swuname'] = $uname;

                $GLOBALS['authLog']->info("User $uname has updated password in Joomla, synchronised");
								
				return (true);
				
			}
			
		}
		
		
	}
	
}

function sw_createAddress($address1, $address2, $suburb, $state, $country, $postcode) {
	
	// Get country ID
	$countryId = sw_findCountry($country);
	
	// Get state ID
	$stateId = sw_findState($state);
	
	// Get suburb ID or add it if it doesn't yet exist
	$suburbId = sw_findAddSuburb($suburb, $stateId);
	
	$address1 = sw_uniformNames($address1);
	$address2 = sw_uniformNames($address2);
	
	// Search for existing address the same
	$addressId = $GLOBALS['db']->getOne("SELECT id FROM addresses WHERE address1 = '$address1' AND address2 = '$address2' AND suburb = '$suburbId' AND postcode = '$postcode';");
	db_checkerrors($addressId);
	
	if (!isset($addressId)) {
	
		$insert1 = $GLOBALS['db']->query("INSERT INTO addresses (address1, address2, suburb, state, country, postcode) VALUES ('$address1', '$address2', '$suburbId', '$stateId', '$countryId', '$postcode');");
		db_checkerrors($insert1);
		$addressId = mysql_insert_id();
		
	}
	
	return ($addressId);
	
}

// Finds and returns country id
function sw_findCountry($countryName) {
	
	$countryAbbrev = strtoupper($countryName);
	$countryName = sw_uniformNames($countryName);
	
	$countryId = $GLOBALS['db']->getOne("SELECT id FROM countries WHERE abbrev='$countryAbbrev' OR countryname='$countryName';");
	db_checkerrors($countryId);
	
	if (isset($countryId)) {
		
		return($countryId);
		
	} else {
		
		return (false);	
		
	}
		
}

// Finds and returns state id
function sw_findState($stateName) {
	
	$stateAbbrev = strtoupper($stateName);
	$stateName = sw_uniformNames($stateName);
	
	$stateId = $GLOBALS['db']->getOne("SELECT id FROM states WHERE abbrev='$stateAbbrev' OR statename='$stateName';");
	db_checkerrors($stateId);
	
	if (isset($stateId)) {
		
		return($stateId);
		
	} else {
		
		return (false);	
		
	}
		
}

// Finds and returns state id or creates a new suburb in the db if not found
function sw_findAddSuburb($suburbName, $stateId) {
	
	$suburbName = sw_uniformNames($suburbName);
	
	$suburbId = $GLOBALS['db']->getOne("SELECT id FROM suburbs WHERE state_id = '$stateId' AND suburbname = '$suburbName';");
	db_checkerrors($suburbId);
	
	if (isset($suburbId)) {
		
		return($suburbId);
		
	} else {
		
		// Suburb not found, add it
		$suburbInsert = $GLOBALS['db']->query("INSERT INTO suburbs (state_id, suburbname) VALUES ('$stateId', '$suburbName');");
		db_checkerrors($suburbInsert);
		
		$suburbId = mysql_insert_id();
		
		return($suburbId);
		
	}
		
}

// Parse and add phone numbers
function sw_addPhone($number, $type) {
	
	// Default country code
	$countryCode = 61;
	$areaCode = '';
	
	$number = trim($number);
	
	// Area code is in 
	if (substr($number, 0, 1) == "(") {
		
		$areaCode = substr($number, 1, 2);
		
		$number = ltrim(substr_replace($number, '', 4));
		
	}
	
	if (substr($number, 2, 1) == ' ') {
		
		$areaCode = substr($number, 0, 2);
		$number = ltrim(substr_replace($number, '', 2));
		
	}
	
	// Remove spaces
	$number = str_replace(' ', '', $number);
	
	if (strlen($number) == 9) {
		
		$number = '0' . $number;
		
	}
	
	// Check if number exists
	$numberId = $GLOBALS['db']->getOne("SELECT id FROM phones WHERE phonetype = '$type' AND countrycode = '$countryCode' AND areacode = '$areaCode' AND phonenumber = '$number';");
	db_checkerrors($numberId);
	
	if (!isset($numberId)) {
	
		$insert1 = $GLOBALS['db']->query("INSERT INTO phones (phonetype, countrycode, areacode, phonenumber) VALUES ('$type', '$countryCode', '$areaCode', '$number');");
		db_checkerrors($insert1);
		
		$numberId = mysql_insert_id();
	
	}
	
	return($numberId);
	
}

// Inserts New Email address if not existing, returns Id if not
function sw_addEmail($address, $type) {
	
	$emailId = $GLOBALS['db']->getOne("SELECT id FROM emails WHERE email_type = '$type' AND address = '$address';");
	db_checkerrors($emailId);
	
	if (!isset($emailId)) {
		
		$insert1 = $GLOBALS['db']->query("INSERT INTO emails (email_type, address) VALUES ('$type', '$address');");
		db_checkerrors($insert1);

		$emailId = mysql_insert_id();
		
	}
	
	return($emailId);
	
}

// Turns names in any capitalisation format to a uniform standard e.g. NORTH LAKES to North Lakes
function sw_uniformNames($nameIn) {
	
    $nameIn = ucwords(strtolower($nameIn));

    foreach (array('-', '\'') as $delimiter) {
    	
      if (strpos($nameIn, $delimiter) !== false) {
      	
        $nameIn = implode($delimiter, array_map('ucfirst', explode($delimiter, $nameIn)));
        
      }
      
    }
    
    return $nameIn;

}

// Determines which age group a member fits in
// Test Date defines the date at which the age group test is being applied
// Test date format is YYYY-MM-DD
// Age groupt set is teh set of age groups to use
function ageGroup($dob, $gender, $testDate, $set) {
	
	// Determine age at 31/12 of year of the test date
	$testyear = substr($testDate, 0, 4);
	$lastDay = $testyear . '-12-31';
	
	$dobDT = new DateTime($dob);
	$testDateDT = new DateTime($lastDay);
	
	$ageInt = $dobDT->diff($testDateDT);
	$age = $ageInt->format('%y');
	
	$ageGroup = $GLOBALS['db']->getOne("SELECT id FROM age_groups WHERE '$age' >= min AND max >= '$age' AND gender = '$gender';");
	db_checkerrors($ageGroup);
	
	return $ageGroup;
	
}

// Cretea a log item
function addlog($logName, $shortText, $longText = '', $jUser = '') {

	$logN = $logName;
	$shortT = $shortText;
	$longT = $longText;

	// Does log name already exist?
	$logId = $GLOBALS['db']->getOne("SELECT id FROM log_type WHERE logname = ?;", array($logN));
	//db_checkerrors($logId);

	if (!isset($logId)) {

		$insert1 = $GLOBALS['db']->query("INSERT INTO log_type (logname) VALUES (?);",
				array($logN));
		//db_checkerrors($insert1);

		$logId = mysql_insert_id();

	}

	if (isset($_SESSION['swuid'])) {

		$adminUser = $_SESSION['swuid'];

	} else {

		$adminUser = '';

	}

	if ($jUser != '') {

		// Look up the Member ID linked to this jUser
		$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite 
				WHERE joomla_uid = ?;", array($jUser));
		//db_checkerrors($memberId);

	} else {

		$memberId = '';

	}

	$insert = $GLOBALS['db']->query("INSERT INTO log (log_type, adminuser, member, juser, short, text) 
		VALUES (?, ?, ?, ?, ?, ?);",
		array($logId, $adminUser, $memberId, $jUser, $shortT, $longT));
	//db_checkerrors($insert);
	
}

function sw_formatSecs($secTime) {
	
	if (!strpbrk($secTime, '.')) {
			
		$secTimeSecs = $secTime;
		$secTimeMs = "00";
			
	} else {
			
		list($secTimeSecs, $secTimeMs) = explode('.', $secTime);
			
		if (strlen($secTimeMs) == 1) {
				
			$secTimeMs = $secTimeMs . '0';
				
		}
			
	}
		
	$secTimeDisp = floor($secTimeSecs / 60) . ':' . sprintf("%02d", ($secTimeSecs % 60)) . '.' . $secTimeMs;
		
	if ($secTimeDisp == "0:00.00") {
	
		$secTimeDisp = "NT";
	
	}
	
	return $secTimeDisp;
	
}

function sw_timeToSecs($formTime) {
	
	// Reformat time result into seconds
	if (strpos($formTime, ':') !== FALSE) {
	
		$stArray = explode(':', $formTime);
	
		if (count($stArray) == 3) {
	
			$secResult = (floatval($stArray[0]) * 60 * 60) + (floatval($stArray[1]) * 60) +
			floatval($stArray[2]);
	
		} else {
				
			$secResult = (floatval($stArray[0]) * 60) + floatval($stArray[1]);
	
		}
	
	} else {
	
		$secResult = floatval($formTime);
	
	}
	
	return $secResult;
	
}

/**
 * Creates a proper name case
 * TODO: make this better
 */
function titleCase($string) {

    $string = ucwords(strtolower($string));

    foreach (array('-', '\'', 'Mc', ) as $delimiter) {
        if (strpos($string, $delimiter)!==false) {
            $string =implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
        }
    }

    $string = preg_replace("/De\s/", "de ", $string);
    $string = preg_replace("/Der\s/", "der ", $string);

    return $string;

}

?>