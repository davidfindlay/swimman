<?php
// Gets latest QLD Registration RE1 file from AUSSI results portal

require_once($_ENV['HOME'] . "/forum.mastersswimmingqld.org.au/swimman/includes/setup.php");
require_once($_ENV['HOME'] . "/forum.mastersswimmingqld.org.au/swimman/config.php");
require_once($_ENV['HOME'] . "/forum.mastersswimmingqld.org.au/swimman/includes/classes/Meet.php");

require_once("HTTP/Request2.php");
require_once("HTTP/Request2/CookieJar.php");

//require_once("libphp-phpmailer/class.phpmailer.php");

$quietmode = 1;

//$smtphost = "mail.woodypointcomms.com.au";

$portalURL = "http://www.portal.aussi.org.au/admin";
$re1dir = $GLOBALS['home_dir'] . "/masters-data/re1";
$re1file = "registrations.zip";

// Check if any meets require files
$today = date('Y-m-d');

$meetIds = $GLOBALS['db']->getAll("SELECT id FROM meet
		WHERE deadline != '0000-00-00' AND deadline < '$today' AND startdate > '$today';");
db_checkerrors($meetIds);

if ($quietmode != 1) {

	echo "Checking if update of RE1 File is needed...";
	
}

addlog("batch", "getre1.php executed");
$needToSend = 0;

foreach ($meetIds as $m) {

	$meetId = $m[0];

	$meetDetails = new Meet();
	$meetDetails->loadMeet($meetId);

	// Check if this meet has already been sent
	$dateSent = $GLOBALS['db']->getOne("SELECT re1sent FROM meet_jobs WHERE meet_id = '$meetId';");
	db_checkerrors($dateSent);

	if (!isset($dateSent)) {

		$needToSend = 1;
		
	}
	
}

if ($needToSend != 1) {
	
	// No update required
	if ($quietmode != 1) {
	
		echo "not required!\n";
		
	}
	
	//exit();
	
}

if ($quietmode != 1) {

	echo "update required!\n";
	
}

// Backup file
if ($quietmode != 1) {

	echo "Backing up old RE1 file...\n";
	
}

rename($re1dir . "/AUSSI_QLD_REG.RE1", $re1dir . "/AUSSI_QLD_REG.BAK");

$cookiejar = new HTTP_Request2_CookieJar();

if ($quietmode != 1) {

	echo "Stage 1 - Login\n";

}

// $prereq =& new HTTP_Request2($portalURL . "/index.html");
// $prereq->setCookieJar($cookiejar);
// try {
// 	echo $prereq->send()->getBody();
// } catch (HttpException $ex) {
// 	echo $ex;
// }

// Request 1 - Login
$req =& new HTTP_Request2($portalURL . "/pgm_login.php");
$req->setMethod(HTTP_Request2::METHOD_POST);
$username = $GLOBALS['resultsuser'];
$password = $GLOBALS['resultspass'];	
$req->setCookieJar($cookiejar);

// Add form data to request
$req->addPostParameter(array('NAME' => "$username", 'PASS' => "$password", 'Login' => 'Login'));
$req->setConfig("follow_redirects", "false");

try {
	$req1data = $req->send()->getBody();
} catch (HttpException $ex) {
	echo $ex;
}

// Request 1a - Pass JS test
if ($quietmode != 1) {

	echo "Stage 2 - JS Test\n";
	
}

$req1a =& new HTTP_Request2($portalURL . "/pgm_login.php");
$req1a->setMethod(HTTP_Request2::METHOD_POST);
$req1a->setCookieJar($cookiejar);

$portalname = $GLOBALS['resultsname'];

// Add form data to request
$req1a->addPostParameter(array('js' => 'on', 
		'__login' 		=> "$username", 
		'__name' 		=> "$portalname", 
		'__scope'		=> 'QLD',
		'__validation' 	=> '1',
		'__width' 		=> '1024'));
$req1a->setConfig("follow_redirects", "false");

try {
	$req1adata = $req1a->send()->getBody();
} catch (HttpException $ex) {
	echo $ex;
}

if ($quietmode != 1) {

	echo "Stage 3 - Create Updated RE1 File\n";
	
}

// Request 2 - Generate new RE1
$req2 =& new HTTP_Request2($portalURL . "/pgm_re1_export.php");
$req2->setMethod(HTTP_Request2::METHOD_POST);
$req2->setCookieJar($cookiejar);
$req2->addPostParameter(array('scope' => "QLD", 'memberyear' => "2013", 'action' => 'Create'));

try {
	$req2data = $req2->send()->getBody();
} catch (HttpException $ex) {
	echo $ex;
}

if ($quietmode != 1) {

	echo "Stage 4 - Download RE1 File\n";
	
}

// Request Download
$req3 =& new HTTP_Request2($portalURL . "/download_file.php?file=./export/QLD/AUSSI_QLD_REG.RE1&basename=AUSSI_QLD_REG.RE1");
$req3->setCookieJar($cookiejar);
try {
	$req3data = $req3->send()->getBody();
	file_put_contents($re1dir . "/AUSSI_QLD_REG.RE1", $req3data);
} catch (HttpException $ex) {
	echo $ex;
}

// Check RE1 File
if ($quietmode != 1) {

	echo "Stage 5 - Verify RE1 File\n";
	
}

// Open RE1 File for reading
$row = 1;
$error = 0;
if (($handle = fopen($re1dir . "/AUSSI_QLD_REG.RE1", "r")) !== FALSE) {
	
	while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
		
		// First Row Should equal a specific 
		if ($row == 1) {
			
			if ($data[0] != "AUSSI QLD Registration List") {
				
				$error = 1;
				break;
				
			}
			
			$re1date = $data[1];
			
		}
		
		$row++;
		
	}
	
}

$records = $row - 1;

// If error detected return to old RE1
if ($error == 1 || $records < 150) {
	
	// Delete new file, rename backup
	unlink($re1dir . "/AUSSI_QLD_REG.RE1");
	rename($re1dir . "/AUSSI_QLD_REG.BAK", $re1dir . "/AUSSI_QLD_REG.RE1");
	
	if ($quietmode != 1) {
	
		echo "RE1 File Update was unsuccessful, backup file restored.\n";
		echo "Sending email notification to recorder...";
		
	}
		
	// Send email to recorder
	// Send confirmation email to records
//	$mail2 = new PHPMailer();
	//$mail2->IsSMTP();
	//$mail2->SMTPDebug = 1;
	//$mail2->Host = $GLOBALS['smtphost'];
	//$mail2->SMTPSecure = "ssl";
	//$mail2->Port = 465;
	//$mail2->SMTPAuth = false;
	// $mail->Username = $GLOBALS['smtpuser'];
	// $mail->Password = $GLOBALS['smtppass'];
	
// 	$mail2->setFrom('recorder@mastersswimmingqld.org.au','MSQ RE1 Update Script');
// 	$mail2->addAddress('david@woodypointcomms.com.au', 'MSQ Branch Recorder');
// 	$mail2->Subject = "MSQ RE1 Update Failed";
// 	$mail2->Body = "Hi,\n\nThe RE1 File update by getre1.php was unsuccessful. "
// 					. "\n\nRegards, \n\nMSQ RE1 Update Script";
	
// 	$mail2->send();
	
	if ($quietmode != 1) {
	
		echo " sent.\n";
		
	}
	
	addlog("batch", "getre1.php failed");

} else {
	
	if ($quietmode != 1) {
	
	//	echo "RE1 File Update was successful.\n";
	//	echo "Sending email notification to recorder...";
		
	}
	
	// Send email to recorder
	// Send confirmation email to records
	//$mail2 = new PHPMailer();
	//$mail2->IsSMTP();
	//$mail2->SMTPDebug = 1;
	//$mail2->Host = $GLOBALS['smtphost'];
	//$mail2->SMTPSecure = "ssl";
	//$mail2->Port = 465;
	//$mail2->SMTPAuth = false;
	// $mail->Username = $GLOBALS['smtpuser'];
	// $mail->Password = $GLOBALS['smtppass'];
	
	//$mail2->setFrom('recorder@mastersswimmingqld.org.au','MSQ RE1 Update Script');
	//$mail2->addAddress('david@woodypointcomms.com.au', 'MSQ Branch Recorder');
	//$mail2->Subject = "MSQ RE1 Update Successful";
	//$mail2->Body = "Hi,\n\nThe RE1 File update by getre1.php was successful. "
	//		. "The new file is dated $re1date and contains $records registration records."
	//		. "\n\nRegards, \n\nMSQ RE1 Update Script";
	
	//$mail2->send();
	
	if ($quietmode != 1) {
	
	//	echo " sent.\n";
		
	}
	
	addlog("batch", "getre1.php successful");
}

// Zip up data
$zip = new ZipArchive();

// Check permissions are accessible
if (!is_writable($re1dir . '/'))
	exit("Cannot write to re1 directory");

if ($zip->open($re1dir . '/' . $re1file, ZipArchive::CREATE) !== TRUE) {
		
	exit("Cannot open $re1dir/$re1file.\n");
		
}

if (!is_readable(($re1dir . '/' . 'AUSSI_QLD_REG.RE1')))
	exit("Could not open " . $re1dir . '/' . 'AUSSI_QLD_REG.RE1');
$zip->addFile($re1dir . '/' . 'AUSSI_QLD_REG.RE1', 'AUSSI_QLD_REG.RE1');

if (!is_readable($re1dir . '/' . 'Importing RE1 file.pdf'))
	exit("Could not open " . $re1dir . '/' . 'Importing RE1 file.pdf');
$zip->addFile($re1dir . '/' . 'Importing RE1 file.pdf', 'Importing RE1 file.pdf');
$zip->close();

?>