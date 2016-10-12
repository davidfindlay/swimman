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

require_once("/home/masters/swimman/includes/setup.php");
require_once("/home/masters/swimman/includes/classes/Meet.php");

require_once("libphp-phpmailer/class.phpmailer.php");

$quietmode = 0;

$smtphost = "mail.woodypointcomms.com.au";
$recordsdir = "/home/masters-data/records";
$recordsfile = "Records.zip";
$recordstext = "recordsemail.txt";

$sendDate = new DateTime();
$sendDate->add(new DateInterval('P4D'));
$sendDateString = $sendDate->format('Y-m-d');
$today = date('Y-m-d');

$meetIds = $GLOBALS['db']->getAll("SELECT id FROM meet 
		WHERE startdate >= '$today' AND startdate <= '$sendDateString';");

if ($quietmode != 1) {

	echo "Checking for Records file sends due...\n";
	echo "Checking between $sendDateString\n";
	
}

foreach ($meetIds as $m) {
	
	$meetId = $m[0];
	
	$meetDetails = new Meet();
	$meetDetails->loadMeet($meetId);
	
	if ($quietmode != 1) {

		echo $meetDetails->getName() . "...";
		
	}
	
	// Check if this meet has already been sent
	$dateSent = $GLOBALS['db']->getOne("SELECT recsent FROM meet_jobs WHERE meet_id = '$meetId';");
	db_checkerrors($dateSent);
	
	if (!isset($dateSent)) {
		
		// Check to see what course the meet is SCM or LCM
		$course = $GLOBALS['db']->getOne("SELECT course FROM event_distances 
				WHERE id = (SELECT distance FROM meet_events WHERE meet_id = '$meetId' LIMIT 1);");
		db_checkerrors($course);
		
		if ($course == "") {
			
			exit("\nCould not determine if meet is LCM or SCM.\n");
			
		}
		
		$mail = new PHPMailer();
		//$mail->IsSMTP();
		//$mail->SMTPDebug = 1;
		//$mail->Host = $GLOBALS['smtphost'];
		//$mail->SMTPSecure = "ssl";
		//$mail->Port = 465;
		//$mail->SMTPAuth = true;
		//$mail->Username = $GLOBALS['smtpuser'];
		//$mail->Password = $GLOBALS['smtppass'];
		
		$mail->setFrom('recorder@mastersswimmingqld.org.au','MSQ Branch Recorder');
		
		$recorderEmail = $meetDetails->getContactEmail();
		
		$mail->addAddress($meetDetails->getContactEmail(), $meetDetails->getContactName());
		$mail->Subject = "MSQ Records File";
		$mail->Body = file_get_contents($recordsdir . '/' . $recordstext);
		
		if ($recorderEmail == '')
			exit("Email address not set, unable to send.");
		
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
		
		if (!is_readable($recordsdir . '/' . $recordsfile))
			exit("Records ZIP file not created!");
		
		$mail->addAttachment($recordsdir . '/' . $recordsfile);
		
		if (!$mail->send()) {
			
			exit("Could not send email: " . $mail->ErrorInfo . "\n");
			
		}
		
		// unlink($recordsdir . '/' . $recordsfile);
		
		// Set job as done in table meet_jobs
		$meetDetails->doneJob('recsent');
		
		// Send confirmation email to records
		$mail2 = new PHPMailer();
		//$mail2->IsSMTP();
		//$mail2->SMTPDebug = 1;
		//$mail2->Host = $GLOBALS['smtphost'];
		//$mail2->SMTPSecure = "ssl";
		//$mail2->Port = 465;
		//$mail2->SMTPAuth = true;
		//$mail2->Username = $GLOBALS['smtpuser'];
		//$mail2->Password = $GLOBALS['smtppass'];
		
		$mail2->setFrom('recorder@mastersswimmingqld.org.au','MSQ Records Email Script');
		$mail2->addAddress('david@woodypointcomms.com.au', 'MSQ Branch Recorder');
		$mail2->Subject = "MSQ Records Sent to " . $meetDetails->getName();
		$mail2->Body = "Hi,\n\nThe $course records files have been sent to " 
				. $meetDetails->getContactName() . " <" . $meetDetails->getContactEmail() . ">" 
				. " by sendrecords.php.\n\nRegards, \n\nMSQ Records Email Script";
		
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