<?php
// Gets latest Records file from AUSSI results portal

require_once($_ENV['HOME'] . "/forum.mastersswimmingqld.org.au/swimman/includes/setup.php");
require_once($_ENV['HOME'] . "/forum.mastersswimmingqld.org.au/swimman/config.php");
require_once($_ENV['HOME'] . "/forum.mastersswimmingqld.org.au/swimman/includes/classes/Meet.php");

require_once("HTTP/Request2.php");
require_once("HTTP/Request2/CookieJar.php");

//require_once("libphp-phpmailer/class.phpmailer.php");

$quietmode = 1;

$smtphost = "mail.woodypointcomms.com.au";

$portalURL = "http://www.portal.aussi.org.au/admin";
$recordsdir = $GLOBALS['home_dir'] . "/masters-data/records";
$needToSend = 0;

addlog("batch", "getrecords.php executed");

$sendDate = new DateTime();
$sendDate->add(new DateInterval('P10D'));
$sendDateString = $sendDate->format('Y-m-d');
$today = date('Y-m-d');

$meetIds = $GLOBALS['db']->getAll("SELECT id FROM meet 
		WHERE startdate >= '$today' AND startdate <= '$sendDateString';");
db_checkerrors($meetIds);

if ($quietmode != 1) {

	echo "Checking if updates of records are needed...";

}
	
foreach ($meetIds as $m) {
	
	$meetId = $m[0];
	
	$meetDetails = new Meet();
	$meetDetails->loadMeet($meetId);
	
	// Check if this meet has already been sent
	$dateSent = $GLOBALS['db']->getOne("SELECT recsent FROM meet_jobs WHERE meet_id = '$meetId';");
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

	echo "Backing up old Records files...\n";
	
}

copy($recordsdir . "/IBRA.LCM", $recordsdir . "/backup/IBRA.LCM");
copy($recordsdir . "/IBRA.SCM", $recordsdir . "/backup/IBRA.SCM");
copy($recordsdir . "/INAT.LCM", $recordsdir . "/backup/INAT.LCM");
copy($recordsdir . "/INAT.SCM", $recordsdir . "/backup/INAT.SCM");
copy($recordsdir . "/IWRL.LCM", $recordsdir . "/backup/IWRL.LCM");
copy($recordsdir . "/IWRL.SCM", $recordsdir . "/backup/IWRL.SCM");

copy($recordsdir . "/RBRA.LCM", $recordsdir . "/backup/RBRA.LCM");
copy($recordsdir . "/RBRA.SCM", $recordsdir . "/backup/RBRA.SCM");
copy($recordsdir . "/RNAT.LCM", $recordsdir . "/backup/RNAT.LCM");
copy($recordsdir . "/RNAT.SCM", $recordsdir . "/backup/RNAT.SCM");
copy($recordsdir . "/RWRL.LCM", $recordsdir . "/backup/RWRL.LCM");
copy($recordsdir . "/RWRL.SCM", $recordsdir . "/backup/RWRL.SCM");

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

	echo "Stage 3a - Create Updated Individual Files\n";
	
}

// Request 2 - Generate new Individual files
$req2 =& new HTTP_Request2($portalURL . "/pgm_rec_export.php");
$req2->setMethod(HTTP_Request2::METHOD_POST);
$req2->setCookieJar($cookiejar);
$req2->addPostParameter(array('scope' => "QLD", 'type' => "0", 'action' => 'Create'));

try {
	$req2data = $req2->send()->getBody();
} catch (HttpException $ex) {
	echo $ex;
}

if ($quietmode != 1) {

	echo "Stage 3b - Create Updated Individual Files\n";
	
}


// Request 2a - Generate new Relay files
$req2a =& new HTTP_Request2($portalURL . "/pgm_rec_export.php");
$req2a->setMethod(HTTP_Request2::METHOD_POST);
$req2a->setCookieJar($cookiejar);
$req2a->addPostParameter(array('scope' => "QLD", 'type' => "1", 'action' => 'Create'));

try {
	$req2adata = $req2a->send()->getBody();
} catch (HttpException $ex) {
	echo $ex;
}

if ($quietmode != 1) {

	echo "Stage 4 - Download Records Files\n";
	
}

// Request Downloads
$req3 =& new HTTP_Request2($portalURL . "/export/QLD/IBRA.LCM");
$req3->setCookieJar($cookiejar);
try {
	$req3data = $req3->send()->getBody();
	file_put_contents($recordsdir . "/IBRA.LCM", $req3data);
} catch (HttpException $ex) {
	echo $ex;
}

$req3a =& new HTTP_Request2($portalURL . "/export/QLD/IBRA.SCM");
$req3a->setCookieJar($cookiejar);
try {
	$req3adata = $req3a->send()->getBody();
	file_put_contents($recordsdir . "/IBRA.SCM", $req3adata);
} catch (HttpException $ex) {
	echo $ex;
}

$req3b =& new HTTP_Request2($portalURL . "/export/QLD/INAT.LCM");
$req3b->setCookieJar($cookiejar);
try {
	$req3bdata = $req3b->send()->getBody();
	file_put_contents($recordsdir . "/INAT.LCM", $req3bdata);
} catch (HttpException $ex) {
	echo $ex;
}

$req3c =& new HTTP_Request2($portalURL . "/export/QLD/INAT.SCM");
$req3c->setCookieJar($cookiejar);
try {
	$req3cdata = $req3c->send()->getBody();
	file_put_contents($recordsdir . "/INAT.SCM", $req3cdata);
} catch (HttpException $ex) {
	echo $ex;
}

$req3d =& new HTTP_Request2($portalURL . "/export/QLD/IWRL.LCM");
$req3d->setCookieJar($cookiejar);
try {
	$req3ddata = $req3d->send()->getBody();
	file_put_contents($recordsdir . "/IWRL.LCM", $req3ddata);
} catch (HttpException $ex) {
	echo $ex;
}

$req3e =& new HTTP_Request2($portalURL . "/export/QLD/IWRL.SCM");
$req3e->setCookieJar($cookiejar);
try {
	$req3edata = $req3e->send()->getBody();
	file_put_contents($recordsdir . "/IWRL.SCM", $req3edata);
} catch (HttpException $ex) {
	echo $ex;
}

$req3f =& new HTTP_Request2($portalURL . "/export/QLD/RBRA.LCM");
$req3f->setCookieJar($cookiejar);
try {
	$req3fdata = $req3f->send()->getBody();
	file_put_contents($recordsdir . "/RBRA.LCM", $req3fdata);
} catch (HttpException $ex) {
	echo $ex;
}

$req3g =& new HTTP_Request2($portalURL . "/export/QLD/RBRA.SCM");
$req3g->setCookieJar($cookiejar);
try {
	$req3gdata = $req3g->send()->getBody();
	file_put_contents($recordsdir . "/RBRA.SCM", $req3gdata);
} catch (HttpException $ex) {
	echo $ex;
}

$req3h =& new HTTP_Request2($portalURL . "/export/QLD/RNAT.LCM");
$req3h->setCookieJar($cookiejar);
try {
	$req3hdata = $req3h->send()->getBody();
	file_put_contents($recordsdir . "/RNAT.LCM", $req3hdata);
} catch (HttpException $ex) {
	echo $ex;
}

$req3i =& new HTTP_Request2($portalURL . "/export/QLD/RNAT.SCM");
$req3i->setCookieJar($cookiejar);
try {
	$req3idata = $req3i->send()->getBody();
	file_put_contents($recordsdir . "/RNAT.SCM", $req3idata);
} catch (HttpException $ex) {
	echo $ex;
}

$req3j =& new HTTP_Request2($portalURL . "/export/QLD/RWRL.LCM");
$req3j->setCookieJar($cookiejar);
try {
	$req3jdata = $req3j->send()->getBody();
	file_put_contents($recordsdir . "/RWRL.LCM", $req3jdata);
} catch (HttpException $ex) {
	echo $ex;
}

$req3k =& new HTTP_Request2($portalURL . "/export/QLD/RWRL.SCM");
$req3k->setCookieJar($cookiejar);
try {
	$req3kdata = $req3k->send()->getBody();
	file_put_contents($recordsdir . "/RWRL.SCM", $req3kdata);
} catch (HttpException $ex) {
	echo $ex;
}

// Check Records File
if ($quietmode != 1) {

	echo "Stage 5 - TODO: Verify Records Files\n";
	
}

// ZIP up the files
zipUpRecords("LCM");
zipUpRecords("SCM");

// Open records File for reading
$error = 0;

// If error detected return to old records
if ($error == 1) {

	// TODO: Create verification for records files

} else {
	
	if ($quietmode != 1) {

//		echo "Records File Updates were successful.\n";
	//	echo "Sending email notification to recorder...";
		
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
	
//	$mail2->setFrom('recorder@mastersswimmingqld.org.au','MSQ Records Update Script');
//	$mail2->addAddress('david@woodypointcomms.com.au', 'MSQ Branch Recorder');
//	$mail2->Subject = "MSQ Records Update Successful";
//	$mail2->Body = "Hi,\n\nThe Records Files update by getrecords.php was successful. "
//			. "\n\nRegards, \n\nMSQ Records Update Script";
	
//	$mail2->send();
	
	if ($quietmode != 1) {
	
//		echo " sent.\n";
		
	}
	
	addlog("batch", "getrecords.php successful");
}

function zipUpRecords($course) {
	
	$recordsdir = $GLOBALS['home_dir'] . "/masters-data/records";
	$recordsfile = "records-$course.zip";
	
	// Zip up data
	$zip = new ZipArchive();
	
	// Check permissions are accessible
	if (!is_writable($recordsdir . '/'. $recordstext))
		exit("Cannot write to records directory");
	
	if ($zip->open($recordsdir . '/' . $recordsfile, ZipArchive::CREATE) != TRUE) {
			
		exit("Cannot open $recordsdir/$recordsfile.\n");
			
	}
	
	if (!is_readable($recordsdir . '/' . "IBRA.$course"))
		exit("Cannot open" . $recordsdir . '/' . "IBRA.$course");
	$zip->addFile($recordsdir . '/' . "IBRA.$course", "IBRA.$course");
	
	if (!is_readable($recordsdir . '/' . "INAT.$course"))
		exit("Cannot open" . $recordsdir . '/' . "INAT.$course");
	$zip->addFile($recordsdir . '/' . "INAT.$course", "INAT.$course");
	
	if (!is_readable($recordsdir . '/' . "IWRL.$course"))
		exit("Cannot open" . $recordsdir . '/' . "IWRL.$course");
	$zip->addFile($recordsdir . '/' . "IWRL.$course", "IWRL.$course");
	
	if (!is_readable($recordsdir . '/' . "RBRA.$course"))
		exit("Cannot open" . $recordsdir . '/' . "RBRA.$course");
	$zip->addFile($recordsdir . '/' . "RBRA.$course", "RBRA.$course");
	
	if (!is_readable($recordsdir . '/' . "RNAT.$course"))
		exit("Cannot open" . $recordsdir . '/' . "RNAT.$course");
	$zip->addFile($recordsdir . '/' . "RNAT.$course", "RNAT.$course");
	
	if (!is_readable($recordsdir . '/' . "RWRL.$course"))
		exit("Cannot open" . $recordsdir . '/' . "RWRL.$course");
	$zip->addFile($recordsdir . '/' . "RWRL.$course", "RWRL.$course");
	
	if (!is_readable($recordsdir . '/' . "MSQ-Records Procedure Help File.pdf"))
		exit("Cannot open" . $recordsdir . '/' . "MSQ-Records Procedure Help File.pdf");
	$zip->addFile($recordsdir . '/' . 'MSQ-Records Procedure Help File.pdf', 'MSQ-Records Procedure Help File.pdf');
	
	echo $zip->getStatusString();
	
	$zip->close();
	
}

?>