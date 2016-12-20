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
	
	$GLOBALS['imguser'] = "dfindlay";
	$GLOBALS['imgpass'] = "findlayd";
	
	
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

function parseImg() {

	$uploaddir = $GLOBALS['home_dir'] . '/masters-data/img';
	$uploadfile = $uploaddir . '/' . 'imgmembers.xls';
	$tempCsvFile = $uploaddir . '/' . 'temp.csv';

	$uploadStatus = "<p>Opening CSV File for import of users.</p>\n<p>\n";

	$htmlFile = fopen ( $uploadfile, "r" );
	$tempCsv = fopen ( $tempCsvFile, "w" );

	$secondClaimCsv = '';
	$statsExisting = 0;
	$statsNew = 0;
	$statsNew2 = 0;
	$statsUpdated = 0;
	$statsUpdated2 = 0;

	// Set to handle mac line endings
	ini_set ( "auto_detect_line_endings", true );

	// Read in file
	html2csv($uploadfile, $tempCsvFile);
	
// 	$table = fread ( $htmlFile, filesize ( $uploadfile ) );
// 	$table = str_replace ( "\r", "", $table );
// 	$table = str_replace ( "<table border=\"1\">", "", $table );
// 	$table = str_replace ( "<tr valign=\"top\">", "", $table );
// 	$table = str_replace ( "</table>", "", $table );
// 	$table = str_replace ( ",", "", $table );

// 	$csv = array ();

// 	$tableArray = explode ( "\n", $table );
// 	$curRow = "";
	
// 	//print_r($tableArray);
	
// 	foreach ( $tableArray as $row ) {

// 		$row = trim ( $row );

// 		// Add the cell to the row
// 		if ($row != "</tr>" && $row != "") {
				
// 			$row = str_replace ( "<td valign=\"top\">", "", $row );
// 			$row = str_replace ( "</td>", "", $row );
// 			$row = trim ( $row );
				
// 			$curRow = $curRow . $row . ",";
// 		}

// 		if ($row == "</tr>") {
				
// 			$curRow = rtrim ( $curRow, "," );
// 			$csv [] = $curRow;
// 			$curRow = "";
// 		}
// 	}

// 	$csvStr = implode ( "\n", $csv );

// 	fwrite ( $tempCsv, $csvStr );
// 	fclose ( $tempCsv );

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
				
			case 'Title' :
				$titleCol = $colNo;
				break;
			case 'First Name' :
				$firstNameCol = $colNo;
				break;
			case 'Initial' :
				$initialCol = $colNo;
				break;
			case 'Other Names' :
				$otherNamesCol = $colNo;
				break;
			case 'Last Name' :
				$lastNameCol = $colNo;
				break;
			case 'Company Name' :
				$companyNameCol = $colNo;
				break;
			case 'Address1' :
				$address1Col = $colNo;
				break;
			case 'Address2' :
				$address2Col = $colNo;
				break;
			case 'Suburb' :
				$suburbCol = $colNo;
				break;
			case 'State' :
				$stateCol = $colNo;
				break;
			case 'Postcode' :
				$postcodeCol = $colNo;
				break;
			case 'Country' :
				$countryCol = $colNo;
				break;
			case 'Email Address' :
				$emailAddressCol = $colNo;
				break;
			case 'Business' :
				$businessCol = $colNo;
				break;
			case 'Direct' :
				$directCol = $colNo;
				break;
			case 'Private' :
				$privateCol = $colNo;
				break;
			case 'Mobile' :
				$mobileCol = $colNo;
				break;
			case 'Facsimile' :
				$faxCol = $colNo;
				break;
			case 'Emergency Contact Person' :
				$emergencyContactCol = $colNo;
				break;
			case 'Emergency Contact Number' :
				$emergencyNumberCol = $colNo;
				break;
			case 'DOB' :
				$dobCol = $colNo;
				break;
			case 'Gender' :
				$genderCol = $colNo;
				break;
			case 'Occupation' :
				$occuptationCol = $colNo;
				break;
			case 'UserName' :
				$usernameCol = $colNo;
				break;
			case 'Password' :
				$passwordCol = $colNo;
				break;
			case 'Directory' :
				$directoryCol = $colNo;
				break;
			case 'Mailing List' :
				$mailingListCol = $colNo;
				break;
			case 'Financial Date' :
				$financialDateCol = $colNo;
				break;
			case 'Status' :
				$statusCol = $colNo;
				break;
			case 'Add Date' :
				$addDateCol = $colNo;
				break;
			case 'Edit Date' :
				$editDateCol = $colNo;
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
			case 'Coaching / Technical ID' :
				$techIdCol = $colNo;
				break;
			case 'Club' :
				$clubNameCol = $colNo;
				break;
			case 'Club Code' :
				$clubCodeCol = $colNo;
				break;
			case 'Primary' :
				$primaryCol = $colNo;
				break;
			case 'Alternate Email Address' :
				$alternateEmailAddressCol = $colNo;
				break;
		}

		$colNo ++;
	}

	// Create Array to store member Id and club ids
	$arrMemberTrackPrim = array ();
	$arrMemberTrackSec = array ();

	while ( ! feof ( $csvFile ) ) {

		$csvEntry = fgetcsv ( $csvFile );

		if (($csvEntry [0] !== 'Title') && (count ( $csvEntry ) > 2)) {
			// Refactored to dataload Full IMG dataset
			$msaNumber = mysql_real_escape_string ( $csvEntry [$memberNumberCol] );
			
			$firstName = mysql_real_escape_string ( $csvEntry [$firstNameCol] );
			$otherNames = mysql_real_escape_string ( $csvEntry [$otherNamesCol] );
			$surname = mysql_real_escape_string ( $csvEntry [$lastNameCol] );
			$address1 = mysql_real_escape_string ( $csvEntry [$address1Col] );
			$address2 = mysql_real_escape_string ( $csvEntry [$address2Col] );
			$suburb = mysql_real_escape_string ( $csvEntry [$suburbCol] );
			$state = mysql_real_escape_string ( $csvEntry [$stateCol] );
			$postcode = mysql_real_escape_string ( $csvEntry [$postcodeCol] );
			$country = mysql_real_escape_string ( $csvEntry [$countryCol] );
			$email = mysql_real_escape_string ( $csvEntry [$emailAddressCol] );
			$businessPhone = mysql_real_escape_string ( $csvEntry [$businessCol] );
			$directPhone = mysql_real_escape_string ( $csvEntry [$directCol] );
			$privatePhone = mysql_real_escape_string ( $csvEntry [$privateCol] );
			$mobilePhone = mysql_real_escape_string ( $csvEntry [$mobileCol] );
			$faxPhone = mysql_real_escape_string ( $csvEntry [$faxCol] );
			$emergName = mysql_real_escape_string ( $csvEntry [$emergencyContactCol] );
			$emergPhone = mysql_real_escape_string ( $csvEntry [$emergencyNumberCol] );
			$dob = mysql_real_escape_string ( $csvEntry [$dobCol] ); // Format 23-Aug-1983
			$gender = mysql_real_escape_string ( $csvEntry [$genderCol] );
			$subscriptionType = mysql_real_escape_string ( $csvEntry [$subscriptionsCol] );
			$financialEndDate = mysql_real_escape_string ( $csvEntry [$financialDateCol] ); // Format 23-Aug-1983
			$memberType = mysql_real_escape_string ( $csvEntry [$memberTypeCol] );
			$clubName = mysql_real_escape_string ( $csvEntry [$clubNameCol] );
			$clubCode = mysql_real_escape_string ( $csvEntry [$clubCodeCol] );
				
			if ($memberType == "Second Claim") {

				$secondClaimCsv [] = $csvEntry;
				continue;
			}
				
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
				
			$memberId = $GLOBALS ['db']->getOne ( "SELECT id FROM member WHERE number = '$msaNumber';" );
			db_checkerrors ( $memberId );
				
			if (isset ( $memberId ) && $hasMSANumber) {

				// Member already exists, update
				// $uploadStatus = $uploadStatus . "Member $msaNumber $firstName $surname already exists, updating. Financial end date = $financialEndDate<br />\n";

				$statsExisting ++;

				// Create Member object
				$memberObj = new Member ();
				$memberObj->loadId ( $memberId );

				// Update surname
				if ($surname != mysql_real_escape_string ( $memberObj->getSurname () )) {
						
					$memberObj->setSurname ( $surname );
					$uploadStatus = $uploadStatus . "Updated Surname for $msaNumber.<br />\n";
				}

				// Update first name
				if ($firstName != mysql_real_escape_string ( $memberObj->getFirstname () )) {
						
					$memberObj->setFirstname ( $firstName );
					$uploadStatus = $uploadStatus . "Updated Firstname for $msaNumber.<br />\n";
				}

				// Update DOB
				$dobFormated = date ( 'Y-m-d', strtotime ( $dob ) );
				if ($dobFormated != mysql_real_escape_string ( $memberObj->getDob () )) {
						
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

				// First check for any change of name, dob, gender
				// $memberCurDetails = $GLOBALS['db']->getRow("SELECT * FROM members WHERE number = '$msaNumber';");
				// db_checkerrors($memberCurDetails);
				if (preg_match ( '/HCC/', $subscriptionType ) && $financialEndDate == "31 Dec 2013") {
						
					if ($memberObj->applyMembership ( 5, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					}
				} elseif ($financialEndDate == "31 Dec 2013") {
						
					if ($memberObj->applyMembership ( 4, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					}
				} elseif (preg_match ( '/HCC/', $subscriptionType ) && $financialEndDate == "31 Dec 2014") {
						
					// echo "detected 2014hcc\n";
						
					if ($memberObj->applyMembership ( 9, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					} else {

						// echo "Unable to apply membership";
					}
				} elseif ($financialEndDate == "31 Dec 2014") {
						
					if ($memberObj->applyMembership ( 10, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					} else {

						//
					}
				} elseif (preg_match ( '/HCC/', $subscriptionType ) && $financialEndDate == "31 Dec 2015") {
						
					// echo "detected 2014hcc\n";
						
					if ($memberObj->applyMembership ( 14, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					} else {

						// echo "Unable to apply membership";
					}
				} elseif ($financialEndDate == "31 Dec 2015") {
						
					if ($memberObj->applyMembership ( 15, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					} else {

						//
					}
				} elseif (preg_match ( '/HCC/', $subscriptionType ) && $financialEndDate == "31 Dec 2016") {
						
					// echo "detected 2014hcc\n";
						
					if ($memberObj->applyMembership ( 16, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					} else {

						// echo "Unable to apply membership";
					}
				} elseif ($financialEndDate == "31 Dec 2016") {

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

				// Create an address first
				$addressId = sw_createAddress ( $address1, $address2, $suburb, $state, $country, $postcode );

				// Create Member
				if ($gender == "Male") {
					$gender = 1;
				} else {
					$gender = 2;
				}

				// Fix date of birth
				$dobFormated = date ( 'Y-m-d', strtotime ( $dob ) );

				$memberInsert = $GLOBALS ['db']->query ( "INSERT INTO member (number, surname, firstname, othernames, dob, gender, address) VALUES ('$msaNumber', '$surname', '$firstName', '$otherNames', '$dobFormated', '$gender', '$addressId');" );
				db_checkerrors ( $memberInsert );

				$memberId = mysql_insert_id ();

				// Changing to using full object oriented system
				$memberObj = new Member ();
				$memberObj->loadId ( $memberId );

				// Create Email address
				$emailInsert = $GLOBALS ['db']->query ( "INSERT INTO emails (email_type, address) VALUES ('1', '$email');" );
				db_checkerrors ( $emailInsert );

				$emailId = mysql_insert_id ();

				$emailMapInsert = $GLOBALS ['db']->query ( "INSERT INTO member_emails (member_id, email_id) VALUES ('$memberId', '$emailId');" );
				db_checkerrors ( $emailMapInsert );

				// Create Emergency Contacts
				if (strpos ( $emergName, ' ' )) {
						
					list ( $emergFirstName, $emergSurname ) = explode ( ' ', $emergName );
				} else {
						
					$emergFirstName = $emergName;
					$emergSurname = '';
				}

				$phoneId = sw_addPhone ( $emergPhone, 7 );

				$emergContactInsert = $GLOBALS ['db']->query ( "INSERT INTO member_emerg (member_id, surname, firstname) VALUES ('$memberId', '$emergSurname', '$emergFirstName');" );
				db_checkerrors ( $emergContactInsert );
				$emergContactId = mysql_insert_id ();

				$emergContactPhoneInsert = $GLOBALS ['db']->query ( "INSERT INTO member_emerg_phones (member_emerg_id, phone_id) VALUES ('$emergContactId', '$phoneId');" );
				db_checkerrors ( $emergContactPhoneInsert );

				// Create Phone numbers
				if ($businessPhone != '') {
						
					$businessPhoneId = sw_addPhone ( $businessPhone, 8 );
					$businessPhoneInsert = $GLOBALS ['db']->query ( "INSERT INTO member_phones (member_id, phone_id) VALUES ('$memberId', '$businessPhoneId');" );
					db_checkerrors ( $businessPhoneInsert );
				}

				if ($directPhone != '') {
						
					$directPhoneId = sw_addPhone ( $directPhone, 7 );
					$directPhoneInsert = $GLOBALS ['db']->query ( "INSERT INTO member_phones (member_id, phone_id) VALUES ('$memberId', '$directPhoneId');" );
				}

				if ($privatePhone != '') {
						
					$privatePhoneId = sw_addPhone ( $privatePhone, 6 );
					$privatePhoneInsert = $GLOBALS ['db']->query ( "INSERT INTO member_phones (member_id, phone_id) VALUES ('$memberId', '$privatePhoneId');" );
					db_checkerrors ( $privatePhoneInsert );
				}

				if ($mobilePhone != '') {
						
					$mobilePhoneId = sw_addPhone ( $mobilePhone, 2 );
					$mobilePhoneInsert = $GLOBALS ['db']->query ( "INSERT INTO member_phones (member_id, phone_id) VALUES ('$memberId', '$mobilePhoneId');" );
					db_checkerrors ( $mobilePhoneInsert );
				}

				if ($faxPhone != '') {
						
					$faxPhoneId = sw_addPhone ( $faxPhone, 5 );
					$faxPhoneInsert = $GLOBALS ['db']->query ( "INSERT INTO member_phones (member_id, phone_id) VALUES ('$memberId', '$faxPhoneId');" );
					db_checkerrors ( $faxPhoneInsert );
				}

				// Membership details
				// Create Member object
				$memberObj = new Member ();
				$memberObj->loadId ( $memberId );

				// First check for any change of name, dob, gender
				// $memberCurDetails = $GLOBALS['db']->getRow("SELECT * FROM members WHERE number = '$msaNumber';");
				// db_checkerrors($memberCurDetails);

				if (preg_match ( '/HCC/', $subscriptionType ) && $financialEndDate == "31-Dec-2013") {
						
					$memberObj->applyMembership ( 5, $clubCode );
				} elseif ($financialEndDate == "31-Dec-2013") {
						
					$memberObj->applyMembership ( 4, $clubCode );
				} elseif (preg_match ( '/HCC/', $subscriptionType ) && $financialEndDate == "31 Dec 2014") {
						
					if ($memberObj->applyMembership ( 9, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					}
				} elseif (preg_match ( '/HCC/', $subscriptionType ) && $financialEndDate == "31 Dec 2015") {
						
					if ($memberObj->applyMembership ( 14, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					}
				} elseif ($financialEndDate == "31 Dec 2014") {
						
					if ($memberObj->applyMembership ( 10, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					}
				} elseif ($financialEndDate == "31 Dec 2015") {
						
					if ($memberObj->applyMembership ( 15, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					}
				} elseif (preg_match ( '/HCC/', $subscriptionType ) && $financialEndDate == "31 Dec 2016") {
						
					// echo "detected 2014hcc\n";
						
					if ($memberObj->applyMembership ( 16, $clubCode )) {

						$uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
						$statsUpdated ++;
					} else {

						// echo "Unable to apply membership";
					}
				} elseif ($financialEndDate == "31 Dec 2016") {
						
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
				
			$arrMemberTrackPrim ["$memberId"] = $clubId;
				
			unset ( $clubObj );
		}
	}

	// Process second claims
    if (isset($secondClaimCsv)) {
        foreach ($secondClaimCsv as $csvEntry) {

            $msaNumber = mysql_real_escape_string($csvEntry [$memberNumberCol]);
            $firstName = mysql_real_escape_string($csvEntry [$firstNameCol]);
            $otherNames = mysql_real_escape_string($csvEntry [$otherNamesCol]);
            $surname = mysql_real_escape_string($csvEntry [$lastNameCol]);
            $address1 = mysql_real_escape_string($csvEntry [$address1Col]);
            $address2 = mysql_real_escape_string($csvEntry [$address2Col]);
            $suburb = mysql_real_escape_string($csvEntry [$suburbCol]);
            $state = mysql_real_escape_string($csvEntry [$stateCol]);
            $postcode = mysql_real_escape_string($csvEntry [$postcodeCol]);
            $country = mysql_real_escape_string($csvEntry [$countryCol]);
            $email = mysql_real_escape_string($csvEntry [$emailAddressCol]);
            $businessPhone = mysql_real_escape_string($csvEntry [$businessCol]);
            $directPhone = mysql_real_escape_string($csvEntry [$directCol]);
            $privatePhone = mysql_real_escape_string($csvEntry [$privateCol]);
            $mobilePhone = mysql_real_escape_string($csvEntry [$mobileCol]);
            $faxPhone = mysql_real_escape_string($csvEntry [$faxCol]);
            $emergName = mysql_real_escape_string($csvEntry [$emergencyContactCol]);
            $emergPhone = mysql_real_escape_string($csvEntry [$emergencyNumberCol]);
            $dob = mysql_real_escape_string($csvEntry [$dobCol]); // Format 23-Aug-1983
            $gender = mysql_real_escape_string($csvEntry [$genderCol]);
            $subscriptionType = mysql_real_escape_string($csvEntry [$subscriptionsCol]);
            $financialEndDate = mysql_real_escape_string($csvEntry [$financialDateCol]); // Format 23-Aug-1983
            $memberType = mysql_real_escape_string($csvEntry [$memberTypeCol]);
            $clubName = mysql_real_escape_string($csvEntry [$clubNameCol]);
            $clubCode = mysql_real_escape_string($csvEntry [$clubCodeCol]);

            $clubObj = new Club ();
            if (!$clubObj->load($clubCode)) {

                $clubObj->create($clubCode, $clubName);
            }

            $clubId = $clubObj->getId();

            $hasMSANumber = false;

            if (preg_match('/\d{6}/', $msaNumber)) {

                $hasMSANumber = true;
            }

            $memberId = $GLOBALS ['db']->getOne("SELECT id FROM member WHERE number = '$msaNumber';");
            db_checkerrors($memberId);

            if (isset ($memberId) && $hasMSANumber) {

                // Member already exists, update
                // $uploadStatus = $uploadStatus . "Member $msaNumber $firstName $surname already exists, adding second claim membership. Financial end date = $financialEndDate<br />\n";

                // Create Member object
                $memberObj = new Member ();
                $memberObj->loadId($memberId);

                if ($financialEndDate == "31 Dec 2013") {

                    if ($memberObj->applyMembership(8, $clubCode)) {

                        $uploadStatus = $uploadStatus . "Member $msaNumber $firstName $surname: Updating/adding second claim membership. Club = $clubCode. Financial End Date = $financialEndDate<br />\n";
                        $statsUpdated2++;
                    }
                } elseif ($financialEndDate == "31 Dec 2014") {

                    if ($memberObj->applyMembership(13, $clubCode)) {

                        $uploadStatus = $uploadStatus . "Member $msaNumber $firstName $surname: Updating/adding second claim membership. Club = $clubCode. Financial End Date = $financialEndDate<br />\n";
                        $statsUpdated2++;
                    }
                } elseif ($financialEndDate == "31 Dec 2015") {

                    if ($memberObj->applyMembership(13, $clubCode)) {

                        $uploadStatus = $uploadStatus . "Member $msaNumber $firstName $surname: Updating/adding second claim membership. Club = $clubCode. Financial End Date = $financialEndDate<br />\n";
                        $statsUpdated2++;
                    }
                } elseif (preg_match('/HCC/', $subscriptionType) && $financialEndDate == "31 Dec 2016") {

                    // echo "detected 2014hcc\n";

                    if ($memberObj->applyMembership(16, $clubCode)) {

                        $uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
                        $statsUpdated++;
                    } else {

                        // echo "Unable to apply membership";
                    }
                } elseif ($financialEndDate == "31 Dec 2016") {

                    if ($memberObj->applyMembership(17, $clubCode)) {

                        $uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
                        $statsUpdated++;
                    } else {

                        //
                    }
                }

                unset ($memberObj);
            } elseif ($hasMSANumber) {

                // Member doesn't exist - from another branch!
                $uploadStatus = $uploadStatus . "Member $msaNumber $firstName $surname: Doesn't exist, creating as second claim member of club $clubCode. Financial End Date = $financialEndDate<br />\n";

                // Create an address first
                $addressId = sw_createAddress($address1, $address2, $suburb, $state, $country, $postcode);

                // Create Member
                if ($gender == "Male") {
                    $gender = 1;
                } else {
                    $gender = 2;
                }

                // Fix date of birth
                $dobFormated = date('Y-m-d', strtotime($dob));

                $memberInsert = $GLOBALS ['db']->query("INSERT INTO member (number, surname, firstname, othernames, dob, gender, address) VALUES ('$msaNumber', '$surname', '$firstName', '$otherNames', '$dobFormated', '$gender', '$addressId');");
                db_checkerrors($memberInsert);

                $memberId = mysql_insert_id();

                // Changing to using full object oriented system
                $memberObj = new Member ();
                $memberObj->loadId($memberId);

                // Create Email address
                $emailInsert = $GLOBALS ['db']->query("INSERT INTO emails (email_type, address) VALUES ('1', '$email');");
                db_checkerrors($emailInsert);

                $emailId = mysql_insert_id();

                $emailMapInsert = $GLOBALS ['db']->query("INSERT INTO member_emails (member_id, email_id) VALUES ('$memberId', '$emailId');");
                db_checkerrors($emailMapInsert);

                // Create Emergency Contacts
                if (strpos($emergName, ' ')) {

                    list ($emergFirstName, $emergSurname) = explode(' ', $emergName);
                } else {

                    $emergFirstName = $emergName;
                    $emergSurname = '';
                }

                $phoneId = sw_addPhone($emergPhone, 7);

                $emergContactInsert = $GLOBALS ['db']->query("INSERT INTO member_emerg (member_id, surname, firstname) VALUES ('$memberId', '$emergSurname', '$emergFirstName');");
                db_checkerrors($emergContactInsert);
                $emergContactId = mysql_insert_id();

                $emergContactPhoneInsert = $GLOBALS ['db']->query("INSERT INTO member_emerg_phones (member_emerg_id, phone_id) VALUES ('$emergContactId', '$phoneId');");
                db_checkerrors($emergContactPhoneInsert);

                // Create Phone numbers
                if ($businessPhone != '') {

                    $businessPhoneId = sw_addPhone($businessPhone, 8);
                    $businessPhoneInsert = $GLOBALS ['db']->query("INSERT INTO member_phones (member_id, phone_id) VALUES ('$memberId', '$businessPhoneId');");
                    db_checkerrors($businessPhoneInsert);
                }

                if ($directPhone != '') {

                    $directPhoneId = sw_addPhone($directPhone, 7);
                    $directPhoneInsert = $GLOBALS ['db']->query("INSERT INTO member_phones (member_id, phone_id) VALUES ('$memberId', '$directPhoneId');");
                }

                if ($privatePhone != '') {

                    $privatePhoneId = sw_addPhone($privatePhone, 6);
                    $privatePhoneInsert = $GLOBALS ['db']->query("INSERT INTO member_phones (member_id, phone_id) VALUES ('$memberId', '$privatePhoneId');");
                    db_checkerrors($privatePhoneInsert);
                }

                if ($mobilePhone != '') {

                    $mobilePhoneId = sw_addPhone($mobilePhone, 2);
                    $mobilePhoneInsert = $GLOBALS ['db']->query("INSERT INTO member_phones (member_id, phone_id) VALUES ('$memberId', '$mobilePhoneId');");
                    db_checkerrors($mobilePhoneInsert);
                }

                if ($faxPhone != '') {

                    $faxPhoneId = sw_addPhone($faxPhone, 5);
                    $faxPhoneInsert = $GLOBALS ['db']->query("INSERT INTO member_phones (member_id, phone_id) VALUES ('$memberId', '$faxPhoneId');");
                    db_checkerrors($faxPhoneInsert);
                }

                // Membership details
                // Create Member object
                $memberObj = new Member ();
                $memberObj->loadId($memberId);

                if ($financialEndDate == "31 Dec 2013") {

                    if ($memberObj->applyMembership(8, $clubCode)) {

                        $statsNew2++;
                    }
                } elseif ($financialEndDate == "31 Dec 2014") {

                    if ($memberObj->applyMembership(13, $clubCode)) {

                        $uploadStatus = $uploadStatus . "Member $msaNumber $firstName $surname: Updating/adding second claim membership. Club = $clubCode. Financial End Date = $financialEndDate<br />\n";
                        $statsUpdated2++;
                    }
                } elseif ($financialEndDate == "31 Dec 2015") {

                    // echo "detected 2014hcc\n";

                    if ($memberObj->applyMembership(16, $clubCode)) {

                        $uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
                        $statsUpdated++;
                    } else {

                        // echo "Unable to apply membership";
                    }
                } elseif ($financialEndDate == "31 Dec 2016") {

                    if ($memberObj->applyMembership(17, $clubCode)) {

                        $uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
                        $statsUpdated++;
                    } else {

                        //
                    }
                } elseif ($financialEndDate == "31 Dec 2017") {

                    if ($memberObj->applyMembership(18, $clubCode)) {

                        $uploadStats = $uploadStatus . "Member $msaNumber $firstName $surname: Updated Financial End Date to $financialEndDate.<br />\n";
                        $statsUpdated++;
                    } else {

                        //
                    }
                }

                unset ($memberObj);
            }

            $arrMemberTrackSec ["$memberId"] = $clubId;
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
	$uploadStatus = $uploadStatus . "<tr>\n";
	$uploadStatus = $uploadStatus . "<td>\n";
	$uploadStatus = $uploadStatus . "New branch Second Claim Members:\n";
	$uploadStatus = $uploadStatus . "</td>\n";
	$uploadStatus = $uploadStatus . "<td>\n";
	$uploadStatus = $uploadStatus . "$statsNew2\n";
	$uploadStatus = $uploadStatus . "</td>\n";
	$uploadStatus = $uploadStatus . "</tr>\n";
	$uploadStatus = $uploadStatus . "<tr>\n";
	$uploadStatus = $uploadStatus . "<td>\n";
	$uploadStatus = $uploadStatus . "New Second Claim Members:\n";
	$uploadStatus = $uploadStatus . "</td>\n";
	$uploadStatus = $uploadStatus . "<td>\n";
	$uploadStatus = $uploadStatus . "$statsUpdated2\n";
	$uploadStatus = $uploadStatus . "</td>\n";
	$uploadStatus = $uploadStatus . "</tr>\n";
	$uploadStatus = $uploadStatus . "</p>\n";

	addlog("IMG Sync", "Parsed IMG Membership File", "Membership file contained $statsExisting existing members, $statsNew new members, $statsUpdated renewed members, $statsNew2 new branch second claims and $statsUpdated2 new second claims.");

	return $uploadStatus;

}
