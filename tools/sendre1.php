<?php
// Command line script to check if there are any meets that require an RE1
// Registration file sent today
// 
// Script should be set to run as a cron job each night at 2am. If a meet
// has passed it's entry deadline and table meet_jobs column re1sent shows 
// false the script will zip up the current RE1 and instruction sheet from
// /home/masters-data/re1.
//
// 

require_once($_SERVER['DOCUMENT_ROOT'] . "/swimman/includes/setup.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/swimman/includes/classes/Meet.php");

//require_once("libphp-phpmailer/class.phpmailer.php");

$quietmode = 1;

$smtphost = "mail.woodypointcomms.com.au";
$re1dir = $GLOBALS['home_dir'] . "/masters-data/re1";
$re1file = "Registrations.zip";
$re1text = "re1email.txt";

$today = date('Y-m-d');

$meetIds = $GLOBALS['db']->getAll("SELECT id FROM meet 
		WHERE deadline != '0000-00-00' AND deadline < '$today' AND startdate > '$today';");
db_checkerrors($meetIds);

if ($quietmode != 1) {

	echo "Checking for RE1 file sends due...\n";
	
}

foreach ($meetIds as $m) {
	
	$meetId = $m[0];
	
	$meetDetails = new Meet();
	$meetDetails->loadMeet($meetId);
	
	if ($quietmode != 1) {
	
		echo $meetDetails->getName() . "...";
		
	}
	
	// Check if this meet has already been sent
	$dateSent = $GLOBALS['db']->getOne("SELECT re1sent FROM meet_jobs WHERE meet_id = '$meetId';");
	db_checkerrors($dateSent);
	
	if (!isset($dateSent)) {
		
	//	$mail = new PHPMailer();
		//$mail->IsSMTP();
		//$mail->SMTPDebug = 1;
		//$mail->Host = $GLOBALS['smtphost'];
		//$mail->SMTPSecure = "ssl";
		//$mail->Port = 465;
		//$mail->SMTPAuth = false;
		// $mail->Username = $GLOBALS['smtpuser'];
		// $mail->Password = $GLOBALS['smtppass'];
		
//		$mail->setFrom('recorder@mastersswimmingqld.org.au','MSQ Branch Recorder');
		$recorderEmail = $meetDetails->getContactEmail();
		
		if ($recorderEmail == '') 
			exit("Email address not set, unable to send.");
		
//		$mail->addAddress($meetDetails->getContactEmail(), $meetDetails->getContactName());
//		$mail->Subject = "MSQ Registration File";
//		$mail->Body = file_get_contents($re1dir . '/' . $re1text);
		
		// Zip up data
		$zip = new ZipArchive();
		
		// Check permissions are accessible
		if (!is_writable($re1dir . '/'. $re1text))
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
		
		if (!is_readable($re1dir . '/' . $re1file)) 
			exit("RE1 Zip file not created!");
		
		$mail->addAttachment($re1dir . '/' . $re1file);
		
		if (!$mail->send()) {
			
			exit("Could not send email: " . $mail->ErrorInfo . "\n");
			
		}
		
		unlink($re1dir . '/' . $re1file);
		
		// Set job as done in table meet_jobs
		$meetDetails->doneJob('re1sent');
		
		// Send confirmation email to records
		$mail2 = new PHPMailer();
		//$mail2->IsSMTP();
		//$mail2->SMTPDebug = 1;
		//$mail2->Host = $GLOBALS['smtphost'];
		//$mail2->SMTPSecure = "ssl";
		//$mail2->Port = 465;
		//$mail2->SMTPAuth = false;
		// $mail->Username = $GLOBALS['smtpuser'];
		// $mail->Password = $GLOBALS['smtppass'];
		
		$mail2->setFrom('recorder@mastersswimmingqld.org.au','MSQ RE1 Email Script');
		$mail2->addAddress('david@woodypointcomms.com.au', 'MSQ Branch Recorder');
		$mail2->Subject = "MSQ RE1 Sent to " . $meetDetails->getName();
		$mail2->Body = "Hi,\n\nThe RE1 File has been sent to " 
				. $meetDetails->getContactName() . " <" . $meetDetails->getContactEmail() . ">" 
				. " by sendre1.php.\n\nRegards, \n\nMSQ RE1 Email Script";
		
		$mail2->send();
		
		if ($quietmode != 1) {
		
			echo " sent.\n";
			
		}
		
	} else {
		
		if ($quietmode != 1) {
		
			echo " previously sent.\n";
			
		}
		
	}
	
}