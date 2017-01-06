<?php

require_once("setup.php");
require_once("config.php");
require_once("html2csv.php");

require_once("classes/Club.php");
require_once("classes/Member.php");

require_once("HTTP/Request2.php");
require_once("HTTP/Request2/CookieJar.php");

//require_once("libphp-phpmailer/class.phpmailer.php");

function getImg() {
	
	$quietmode = 1;
	
	$smtphost = "mail.quadrahosting.com.au";
	
	$portalURL = "https://console.sportstg.com/";
	$imgdir = $GLOBALS['home_dir'] . "/masters-data/img";
	

	
	
	if ($quietmode != 1) {
	
		echo "Updating Membership Database from IMG...";
	
	}
	
	addlog("batch", "getimg.php executed");
	
	$cookiejar = new HTTP_Request2_CookieJar();
	
	if ($quietmode != 1) {
	
		echo "Stage 1 - Login\n";
	
	}
	
	// Request 1 - Login
	$req = new HTTP_Request2($portalURL);
	$req->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
	));
	try {
		$prereq1data = $req->send()->getBody();
	} catch (HttpException $ex) {
		echo $ex;
	}
	
	$req = new HTTP_Request2($portalURL . "/login/index.cfm");
	$req->setMethod(HTTP_Request2::METHOD_POST);
	$username = $GLOBALS['imguser'];
	$password = $GLOBALS['imgpass'];
	$req->setCookieJar($cookiejar);
	
	// Add form data to request
	$req->addPostParameter(array('fuseaction' => 'Process_Validate_Login', 'Username' => "$username", 'Password' => "$password", 'Login' => 'Login'));
	$req->setConfig("follow_redirects", "false");
	
	$req->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
	));
	
	try {
		$req1data = $req->send()->getBody();
	
	} catch (HttpException $ex) {
		echo $ex;
	}
	
	// Request 2 - Get to Members Section
	if ($quietmode != 1) {
	
		echo "Stage 2 - Get to Members Section\n";
	
	}
	
	$req2 = new HTTP_Request2($portalURL . "/level2members/index.cfm?fuseaction=display_landing");
	$req2->setMethod(HTTP_Request2::METHOD_POST);
	$req2->setCookieJar($cookiejar);
	
	$req2->setConfig("follow_redirects", "false");
	
	$req2->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
	));
	
	try {
		$req2data = $req2->send()->getBody();
	} catch (HttpException $ex) {
		echo $ex;
	}
	
	// Request 3 - Get to Members Export Section
	if ($quietmode != 1) {
	
		echo "Stage 3 - Get to Members Export Page\n";
	
	}
	
	$req2 = new HTTP_Request2($portalURL . "/level2membersexport/index.cfm?fuseaction=display_landing");
	$req2->setMethod(HTTP_Request2::METHOD_POST);
	$req2->setCookieJar($cookiejar);
	
	$req2->setConfig("follow_redirects", "false");
	
	$req2->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
	));
	
	try {
		$req2data = $req2->send()->getBody();
	} catch (HttpException $ex) {
		echo $ex;
	}
	
	// Request 4 - Get to Members Export All Members
	if ($quietmode != 1) {
	
		echo "Stage 4 - Get to Members Export All Members Page\n";
	
	}
	
	$req2 = new HTTP_Request2($portalURL . "/level2membersexport/index.cfm?fuseaction=display_all");
	$req2->setMethod(HTTP_Request2::METHOD_POST);
	$req2->setCookieJar($cookiejar);
	
	$req2->setConfig("follow_redirects", "false");
	
	$req2->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
	));
	
	try {
		$req2data = $req2->send()->getBody();
	} catch (HttpException $ex) {
		echo $ex;
	}
	
	// Request 5 - Set details
	if ($quietmode != 1) {
	
		echo "Stage 5 - Set Details\n";
	
	}
	
	$req = new HTTP_Request2($portalURL . "/level2membersexport/index.cfm");
	$req->setMethod(HTTP_Request2::METHOD_POST);
	$username = $GLOBALS['imguser'];
	$password = $GLOBALS['imgpass'];
	$req->setCookieJar($cookiejar);
	
	// Add form data to request
	$req->addPostParameter(array('fuseaction' => 'display_all', 'TemplateID' => "0", 'Tier3Selection' => "", 'MemberListingStatus' => '2', 'MemberListingFinancialStatus' => '1'));
	$req->setConfig("follow_redirects", "false");
	
	$req->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
	));
	
	try {
		$req1data = $req->send()->getBody();
	
	} catch (HttpException $ex) {
		echo $ex;
	}
	
	// Request 6 - confirm request
	if ($quietmode != 1) {
	
		echo "Stage 6 - Confirm request and get file\n";
	
	}
	
	$req = new HTTP_Request2($portalURL . "/level2membersexport/index.cfm");
	$req->setMethod(HTTP_Request2::METHOD_POST);
	$username = $GLOBALS['imguser'];
	$password = $GLOBALS['imgpass'];
	$req->setCookieJar($cookiejar);
	
	// Add form data to request
	$req->addPostParameter(array('fuseaction' => 'Display_All_Export', 'TemplateID' => "0", 'Tier3Selection' => "", 'MemberListingStatus' => '2', 'MemberListingFinancialStatus' => '1'));
	$req->setConfig("follow_redirects", "true");
	
	$req->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
	));
	
	try {
		$req1data = $req->send()->getBody();
		file_put_contents($imgdir . "/imgmembers.xls", $req1data);
	} catch (HttpException $ex) {
		echo $ex;
	}
	
	addlog("IMG Sync", "Downloaded IMG Database", "Downloaded new IMG membership database.");
	
	
}

/**
 * If called, attempts to unzip the upload file before parsing
 */
function unzipUpload() {

    $uploaddir = $GLOBALS['home_dir'] . '/masters-data/img';
    $path = $uploaddir . '/' . 'imgmembers.xls';

    // Unzip the file
    $fileinfo = "";
    $zip = new ZipArchive;
    if ($zip->open($path) === true) {

        for($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $fileinfo = pathinfo($filename);

            copy("zip://".$path."#".$filename, "$uploaddir" . '/' . $fileinfo['basename']);
        }
        $zip->close();
    }

    // Rename to original path
    rename($uploaddir . '/' . $fileinfo['basename'], $path);

}


function parseImg() {

	$uploaddir = $GLOBALS['home_dir'] . '/masters-data/img';
	$uploadfile = $uploaddir . '/' . 'imgmembers.xls';
	$tempCsvFile = $uploaddir . '/' . 'temp.csv';

	$uploadStatus = "<p>Opening CSV File for import of users.</p>\n<p>\n";

	$htmlFile = fopen ( $uploadfile, "r" );
	$tempCsv = fopen ( $tempCsvFile, "w" );

	$statsExisting = 0;
	$statsNew = 0;
	$statsUpdated = 0;

	// Set to handle mac line endings
	ini_set ( "auto_detect_line_endings", true );

	// Read in file
	html2csv($uploadfile, $tempCsvFile);

	// Open Temporary CSV
	$csvFile = fopen ( $tempCsvFile, "r" );

	// Find columns
	$titleLine[] = array();
	while(count($titleLine) <= 1) {
		
		$titleLine = fgetcsv ( $csvFile );
		
	}
	
	$colNo = 0;	

	foreach ( $titleLine as $t ) {

		switch ($t) {

			case 'First Name' :
				$firstNameCol = $colNo;
				break;
			case 'Other Names' :
				$otherNamesCol = $colNo;
				break;
			case 'Last Name' :
				$lastNameCol = $colNo;
				break;
			case 'DOB' :
				$dobCol = $colNo;
				break;
			case 'Gender' :
				$genderCol = $colNo;
				break;
			case 'Financial Date' :
				$financialDateCol = $colNo;
				break;
			case 'Status' :
				$statusCol = $colNo;
				break;
			case 'Member Type' :
				$memberTypeCol = $colNo;
				break;
			case 'Subscriptions' :
				$subscriptionsCol = $colNo;
				break;
			case 'Member Number' :
				$memberNumberCol = $colNo;
				break;
			case 'Club' :
				$clubNameCol = $colNo;
				break;
			case 'Club Code' :
				$clubCodeCol = $colNo;
				break;
		}

		$colNo ++;
	}

	while ( ! feof ( $csvFile ) ) {

		$csvEntry = fgetcsv ( $csvFile );

		if (($csvEntry [0] !== 'Title') && (count ( $csvEntry ) > 2)) {

            // Required data set
			$msaNumber = $csvEntry [$memberNumberCol];
            $firstName = $csvEntry [$firstNameCol];
            $otherNames = '';
            $surname = $csvEntry [$lastNameCol];

            $dob = $csvEntry [$dobCol]; // Format 23-Aug-1983
            $gender = $csvEntry [$genderCol];
            $financialEndDate = $csvEntry [$financialDateCol]; // Format 23-Aug-1983
            $financialEndDate = str_replace("-", " ", $financialEndDate); // Handle dashes
            $clubName = $csvEntry [$clubNameCol];
            $clubCode = $csvEntry [$clubCodeCol];
				
			// Validate club code
			$clubObj = new Club();
			if (! $clubObj->load ( $clubCode )) {

				$clubObj->create ( $clubCode, $clubName );
			}
				
			$clubId = $clubObj->getId ();
				
			// Check member doesn't already exist
			$hasMSANumber = false;
				
			if (preg_match ( '/\d{6}/', $msaNumber )) {

				$hasMSANumber = true;
			}

            // Create Member object
            $memberObj = new Member();
				
			if ($hasMSANumber && $memberObj->loadNumber( $msaNumber )) {

				// Member already exists, update
				// $uploadStatus = $uploadStatus . "Member $msaNumber $firstName $surname already exists, updating. Financial end date = $financialEndDate<br />\n";

				$statsExisting ++;

				// Update surname
				if (titleCase($surname) != $memberObj->getSurname()) {
						
					$memberObj->setSurname ( $surname );
					$uploadStatus = $uploadStatus . "Updated Surname for $msaNumber.<br />\n";

				}

				// Update first name
				if (titleCase($firstName) != $memberObj->getFirstname()) {
						
					$memberObj->setFirstname ( $firstName );
					$uploadStatus = $uploadStatus . "Updated Firstname for $msaNumber.<br />\n";

				}

				// Update DOB
				$dobFormated = date ( 'Y-m-d', strtotime ( $dob ) );
				if ($dobFormated != $memberObj->getDob()) {
						
					$memberObj->setDob ( $dobFormated );
					$uploadStatus = $uploadStatus . "Updated Date of Birth for $msaNumber.<br />\n";

				}

				// Create Member
				if ($gender == "Male") {
					$gender = 'M';
				} else {
					$gender = 'F';
				}

				$existGender = $memberObj->getGender();

				if ($gender != $existGender) {
						
					$memberObj->setGender ( $gender );
					$uploadStatus = $uploadStatus . "Updated Gender for $msaNumber from $existGender to $gender.<br />\n";
				}

				$memberObj->updateDetails ();

				if ($financialEndDate == "31 Dec 2016") {

					if ($memberObj->applyMembership ( 17, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;

					} else {

					}
				} elseif ($financialEndDate == "31 Dec 2017") {

                    if ($memberObj->applyMembership ( 18, $clubCode )) {

                        $uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
                        $statsUpdated ++;
                    } else {

                        //
                    }
                }

				unset ( $memberObj );

			} elseif ($hasMSANumber) {

				// Member doesn't exist
				$uploadStatus = $uploadStatus . "Member $msaNumber $firstName $surname: Doesn't exist, creating. <br />\n";
				$statsNew ++;

				// Create Member
				if ($gender == "Male") {
					$gender = 'M';
				} else {
					$gender = 'F';
				}

				// Fix date of birth
				$dobFormated = date ( 'Y-m-d', strtotime ( $dob ) );

                // Changing to using full object oriented system
                $memberObj->create($msaNumber, $surname, $firstName, $otherNames, $dobFormated, $gender);

				if ($financialEndDate == "31 Dec 2016") {
						
					if ($memberObj->applyMembership ( 17, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					} else {

						//
					}
				} elseif ($financialEndDate == "31 Dec 2017") {

                    if ($memberObj->applyMembership ( 18, $clubCode )) {

                        $uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
                        $statsUpdated ++;
                    } else {

                        //
                    }
                }

				unset ( $memberObj );
			}
				
			unset ( $clubObj );
		}
	}




	$uploadStatus = $uploadStatus . "<p>\n";
	$uploadStatus = $uploadStatus . "<table>\n";
	$uploadStatus = $uploadStatus . "<tr>\n";
	$uploadStatus = $uploadStatus . "<td>\n";
	$uploadStatus = $uploadStatus . "Existing Members:\n";
	$uploadStatus = $uploadStatus . "</td>\n";
	$uploadStatus = $uploadStatus . "<td>\n";
	$uploadStatus = $uploadStatus . "$statsExisting\n";
	$uploadStatus = $uploadStatus . "</td>\n";
	$uploadStatus = $uploadStatus . "</tr>\n";
	$uploadStatus = $uploadStatus . "<tr>\n";
	$uploadStatus = $uploadStatus . "<td>\n";
	$uploadStatus = $uploadStatus . "New Members:\n";
	$uploadStatus = $uploadStatus . "</td>\n";
	$uploadStatus = $uploadStatus . "<td>\n";
	$uploadStatus = $uploadStatus . "$statsNew\n";
	$uploadStatus = $uploadStatus . "</td>\n";
	$uploadStatus = $uploadStatus . "</tr>\n";
	$uploadStatus = $uploadStatus . "<tr>\n";
	$uploadStatus = $uploadStatus . "<td>\n";
	$uploadStatus = $uploadStatus . "Renewed Members:\n";
	$uploadStatus = $uploadStatus . "</td>\n";
	$uploadStatus = $uploadStatus . "<td>\n";
	$uploadStatus = $uploadStatus . "$statsUpdated\n";
	$uploadStatus = $uploadStatus . "</td>\n";
	$uploadStatus = $uploadStatus . "</tr>\n";
	$uploadStatus = $uploadStatus . "</p>\n";

	addlog("IMG Sync", "Parsed IMG Membership File", "Membership file contained $statsExisting existing members, $statsNew new members, $statsUpdated renewed members.");

	return $uploadStatus;

}
