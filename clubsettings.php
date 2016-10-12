<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
checkLogin();

// Check for submit
if (isset($_POST['settingSubmit'])) {
	
	$clubId = mysql_real_escape_string($_POST['clubId']);
	$rowExists = $GLOBALS['db']->getRow("SELECT * FROM clubs_captains WHERE club_id = '$clubId' LIMIT 1;");
	
	$daysPrior = mysql_real_escape_string($_POST['clubdeadline']);
	$address = mysql_real_escape_string($_POST['entryemail']);
	$emailId = sw_addEmail($address, 10);
	
	if (isset($rowExists)) {
				
		// Update existing settings row
		$update = $GLOBALS['db']->query("UPDATE clubs_captains SET email_id = '$emailId', daysprior = '$daysPrior' WHERE club_id = '$clubId';");
		db_checkerrors($update);
		
	} else {
		
		// No existing settings
		$insert = $GLOBALS['db']->query("INSERT INTO clubs_captains (club_id, email_id, daysprior) VALUES ('$clubId', '$emailId', '$daysPrior');");
		db_checkerrors($insert);		
	}
	
	// Check if a payment types row already exists
	$rowExists2 = $GLOBALS['db']->getRow("SELECT * FROM club_payment_types WHERE club_id = '$clubId';");
	db_checkerrors($rowExists2);

	if (isset($_POST['paymentbank'])) {
			
		$paymentBank = 1;
			
	} else {
			
		$paymentBank = 0;
			
	}
	
	if (isset($_POST['paymentcash'])) {
	
		$paymentCash = 1;
	
	} else {
	
		$paymentCash = 0;
	
	}
	
	if (isset($rowExists2)) {
		
		$update = $GLOBALS['db']->query("UPDATE club_payment_types SET cash = '$paymentCash',
				banktransfer = '$paymentBank' WHERE club_id = '$clubId';");
		db_checkerrors($update);
		
	} else {
		
		$insert = $GLOBALS['db']->query("INSERT INTO club_payment_types (club_id, cash, banktransfer)
				VALUES ('$clubId', '$paymentCash', '$paymentBank');");
		db_checkerrors($insert);
		
	}
	
	echo "<strong><i>Settings updated.</i></strong>\n";
	
}

// Load preset values
$clubId = mysql_real_escape_string($_GET['club']);

if (isset($clubId)) {
	
	$presets = $GLOBALS['db']->getRow("SELECT * FROM clubs_captains WHERE club_id = '$clubId' LIMIT 1;");
	db_checkerrors($presets);
	
	$psClubDeadline = $presets[4];
	$psClubEntryEmailId = $presets[3];
	$psClubEntryEmail = $GLOBALS['db']->getOne("SELECT address FROM emails WHERE id = '$psClubEntryEmailId';");
	db_checkerrors($psClubEntryEmail);
	
	// Payment methods
	$paymentData = $GLOBALS['db']->getRow("SELECT * FROM club_payment_types WHERE club_id = '$clubId';");
	db_checkerrors($paymentData);
	
	if ($paymentData[1] == 1) {
		
		$psPaymentCash = " checked=\"checked\"";
		
	} else {
		
		$psPaymentCash = "";
		
	}
	
	if ($paymentData[2] == 1) {
		
		$psPaymentBank = " checked=\"checked\"";
		
	} else {
		
		$psPaymentBank = "";
		
	}
	
	
}

htmlHeaders("Club Settings - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Club Settings</h1>\n";

echo "<p>\n";
echo "<a href=\"clubs.php\">Back to Club List</a>\n";
echo "</p>\n";

echo "<form method=\"post\">\n";

echo "<p>\n";
echo "<label>Club Entry Deadline: </label>\n";
echo "<input type=\"text\" name=\"clubdeadline\" size=\"4\" value=\"$psClubDeadline\" /><br />\n";
echo "<label>Club Captain Email: </label>\n";
echo "<input type=\"text\" name=\"entryemail\" size=\"40\" value=\"$psClubEntryEmail\" />\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Accepted Payment Methods: </label><br />\n";
echo "<input type=\"checkbox\" name=\"paymentcash\" id=\"cash\" value=\"cash\" $psPaymentCash/>\n";

echo "<label for=\"cash\">Cash</label><br />\n";
echo "<input type=\"checkbox\" name=\"paymentbank\" id=\"banktransfer\" value=\"banktransfer\" $psPaymentBank/>\n";
echo "<label for=\"banktransfer\">Bank Transfer</label><br />\n";
echo "</p>\n";

echo "<p>\n";
echo "<input type=\"hidden\" name=\"clubId\" value=\"$clubId\" />\n";
echo "<input type=\"submit\" name=\"settingSubmit\" value=\"Submit\" />\n";
echo "</p>\n";

echo "</form>\n";

echo "</div>\n";   // Main div

htmlFooters();

?>