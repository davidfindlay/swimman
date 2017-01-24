<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Club.php");
checkLogin();

// Connect to joomla database
$dsn = "mysql://$jdbuser:$jdbpass@$jdbhost:$jdbport/$jdbname";
$jdb =& DB::connect($dsn);
db_checkerrors($jdb);
	
// Set correct timezone for all operations
$result = $jdb->query("SET time_zone = '+10:00';");
db_checkerrors($result);

if (isset($_POST['linksubmit'])) {

	$jId = mysql_real_escape_string($_POST['linkJid']);
	$jUser = mysql_real_escape_string($_POST['linkJUser']);
	$sId = mysql_real_escape_string($_POST['linkMid']);

	// Create user link
	$linkUser = new Member();
	$linkUser->loadId($sId);
	$linkUser->linkJUser($jId, $jUser);
	
	// Update Joomla to the correct details
	$mNum = $GLOBALS['db']->getOne("SELECT number FROM member WHERE id = ?", array($sId));
	db_checkerrors($mNum);
	
	$mDob = $GLOBALS['db']->getOne("SELECT dob FROM member WHERE id = ?", array($sId));
	db_checkerrors($mDob);
	
	$jdb->query("UPDATE j_user_profiles SET profile_value = ? WHERE user_id = ? 
			AND profile_key = 'profile.msanumber';", array($mNum, $jId));
	db_checkerrors($jdb);
	
	$jdb->query("UPDATE j_user_profiles SET profile_value = ? WHERE user_id = ?
			AND profile_key = 'profile.dob';", array($mDob, $jId));
	db_checkerrors($jdb);
	
}

if (isset($_POST['unlinksubmit'])) {

	$jId = mysql_real_escape_string($_POST['linkJid']);
	$sId = mysql_real_escape_string($_POST['linkMid']);

	// Create user link
	$linkUser = new Member();
	$linkUser->loadId($sId);
	$linkUser->unlinkJUser($jId);

}

htmlHeaders("Joomla User Links - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

if (isset($_GET['linkjuid'])) {

	$jId = mysql_real_escape_string($_GET['linkjuid']);

	// Get Joomla user details
	$jUser = $jdb->getRow("SELECT * FROM j_users WHERE id = '$jId';");
	db_checkerrors($jUser);
	
	$jName = $jUser[1];
	$jUsername = $jUser[2];
	
	$jDob = $jdb->getOne("SELECT profile_value FROM j_user_profiles WHERE user_id = '$jId' AND profile_key = 'profile.dob';");
	db_checkerrors($jDob);
	$jMSA = $jdb->getOne("SELECT profile_value FROM j_user_profiles WHERE user_id = '$jId' AND profile_key = 'profile.msanumber';");
	db_checkerrors($jMSA);
	$jClub = $jdb->getOne("SELECT profile_value FROM j_user_profiles WHERE user_id = '$jId' AND profile_key = 'profile.club';");
	db_checkerrors($jClub);
		
	$jDob = trim($jDob, '"');
	$jMSA = trim($jMSA, '"');
	$jClub = trim($jClub, '"');

	$sId = '';
	$sName = '';
	$sMSA = '';
	$sDob = '';
	
	// Search for matching Member Details
	if ($jMSA != '') {
	
		// If MSA number has been provided search based on this only
		$memberDetails = new Member();
		$memberDetails->loadNumber($jMSA);
		
		if ($memberDetails->getId() == 0) {
			
			// If no match on MSA number, fall back to name search
			list($tFirst, $tLast) = explode(' ', $jName);
			$jClubId = $GLOBALS['db']->getOne("SELECT id FROM clubs WHERE code = '$jClub' OR
					MATCH (clubname) AGAINST ('$jClub') LIMIT 1;");
			db_checkerrors($jClubId);
			$memberDetails->find($tFirst, $tLast, $jDob, $jClubId);
			
		}
			
	} else {
		
		// If no match on MSA number, fall back to name search
		$memberDetails = new Member();
		list($tFirst, $tLast) = explode(' ', $jName);
		$jClubId = $GLOBALS['db']->getOne("SELECT id FROM clubs WHERE code = '$jClub' OR 
				MATCH (clubname) AGAINST ('$jClub') LIMIT 1;");
		db_checkerrors($jClubId);
		$memberDetails->find($tFirst, $tLast, $jDob, $jClubId);
		
	}
	
	if (isset($memberDetails)) {
	
		$sId = $memberDetails->getId();
		$sName = $memberDetails->getFullname();
		$sMSA = $memberDetails->getMSANumber();
		$sDob = $memberDetails->getDob();
		
		// Search for club memberships involving this member
		$sClubsIds = $memberDetails->getClubIds();
	
	}
	
	$sClubs = "";
	
	if (isset($sClubsIds)) {
	
		foreach ($sClubsIds as $c) {
		
			$curClub = new Club();
			$curClub->load($c);
			
			$sClubs = $sClubs . $curClub->getName() . "<br />\n";
		
		}
	
	}
	
	echo "<h1>Create Joomla User Link</h1>\n";
	
	echo "<form method=\"post\" action=\"juserlink.php\">\n";
	
	echo "<table border=\"1\">\n";
	echo "<tr>\n";
	echo "<th>\n";
	echo "\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Joomla User\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Member Details\n";
	echo "</th>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<th>\n";
	echo "Name:\n";
	echo "</th>\n";
	echo "<td>\n";
	echo "$jName\n";
	echo "</td>\n";
	echo "<td>\n";
	echo "$sName\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<th>\n";
	echo "MSA Number:\n";
	echo "</th>\n";
	echo "<td>\n";
	echo "$jMSA\n";
	echo "</td>\n";
	echo "<td>\n";
	echo "$sMSA\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<th>\n";
	echo "Date of Birth:\n";
	echo "</th>\n";
	echo "<td>\n";
	echo "$jDob\n";
	echo "</td>\n";
	echo "<td>\n";
	echo "$sDob\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<th>\n";
	echo "Club:\n";
	echo "</th>\n";
	echo "<td>\n";
	echo "$jClub\n";
	echo "</td>\n";
	echo "<td>\n";
	echo "$sClubs\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	
	echo "<p>\n";
	
	echo "<input type=\"hidden\" name=\"linkJid\" value=\"$jId\">\n";
	echo "<input type=\"hidden\" name=\"linkJUser\" value=\"$jUsername\">\n";
	echo "<input type=\"hidden\" name=\"linkMid\" value=\"$sId\">\n";
	
	echo "<input type=\"checkbox\" name=\"link\" /><label for=\"link\">Confirm matching member/user information</label><br />\n";
	echo "<input type=\"submit\" name=\"linksubmit\" value=\"Submit\" />\n";
	echo "<input type=\"submit\" name=\"linkcancel\" value=\"Cancel\" />\n";
	echo "</p>\n";
	
	echo "</form>\n";

} elseif (isset($_GET['unlinkjuid'])) {

	$jId = mysql_real_escape_string($_GET['unlinkjuid']);

	// Get Joomla user details
	$jUser = $jdb->getRow("SELECT * FROM j_users WHERE id = '$jId';");
	db_checkerrors($jUser);

	$jName = $jUser[1];
	$jUsername = $jUser[2];

	// Get current link details
	$sId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite WHERE joomla_uid = '$jId';");
	db_checkerrors($sId);
	
	$memDetails = new Member();
	$memDetails->loadId($sId);
	$memName = $memDetails->getFullname();
	$memNum = $memDetails->getMSANumber();
	
	echo "<h1>Unlink Joomla User</h1>\n";
	
	echo "<form method=\"post\">\n";
	
	echo "<p>";
	echo "<label>Member ID:</label>\n";
	echo "$sId";
	echo "</p>";
	echo "<p>";
	echo "<label>Member Name:</label>\n";
	echo $memName;
	echo "</p>";
	echo "<p>\n";
	echo "<label>Member Registration Number:</label>\n";
	echo $memNum;
	echo "</p>\n";
	echo "<p>\n";
	echo "<label>Joomla User ID:</label>\n";
	echo $jId;
	echo "</p>";
	echo "<p>\n";
	echo "<label>Joomla Username:</label>\n";
	echo $jUsername;
	echo "</p>\n";
	
	echo "<input type=\"hidden\" name=\"linkJid\" value=\"$jId\">\n";
	echo "<input type=\"hidden\" name=\"linkJUser\" value=\"$jUsername\">\n";
	echo "<input type=\"hidden\" name=\"linkMid\" value=\"$sId\">\n";
	
	echo "<input type=\"checkbox\" name=\"unlink\" /><label for=\"link\">Confirm unlinking</label><br />\n";
	echo "<input type=\"submit\" name=\"unlinksubmit\" value=\"Submit\" />\n";
	echo "<input type=\"submit\" name=\"unlinkcancel\" value=\"Cancel\" />\n";
	echo "</p>\n";
	
	echo "</form>\n";
	
	
} else {

echo "<h1>Joomla User Links</h1>\n";


$jUsers = $jdb->getAll("SELECT * FROM j_users;");
db_checkerrors($jUsers);

echo "<table width=\"100%\" id=\"jusertable\" class='display'>\n";
echo "<thead>\n";
echo "<tr>\n";
echo "<th>\n";
echo "Username\n";
echo "</th>\n";
echo "<th>\n";
echo "Name\n";
echo "</th>\n";
echo "<th>\n";
echo "Date of Birth\n";
echo "</th>\n";
echo "<th>\n";
echo "MSA Number\n";
echo "</th>\n";
echo "<th>\n";
echo "Club\n";
echo "</th>\n";
    echo "<th>\n";
    echo "Registered Date:\n";
    echo "</th>\n";
echo "<th>\n";
echo "Link Status\n";
echo "</th>\n";
echo "<th>\n";
echo "Linked Member\n";
echo "</th>\n";
echo "</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";

if (isset($jUsers)) {

	foreach ($jUsers as $j) {
	
		$jId = $j[0];
		$jName = $j[1];
		$jUsername = $j[2];
        $registeredDate = $j[8];
		
		// Get this user's profile items
		$jDob = $jdb->getOne("SELECT profile_value FROM j_user_profiles WHERE user_id = '$jId' AND profile_key = 'profile.dob';");
		db_checkerrors($jDob);
		$jMSA = $jdb->getOne("SELECT profile_value FROM j_user_profiles WHERE user_id = '$jId' AND profile_key = 'profile.msanumber';");
		db_checkerrors($jMSA);
		$jClub = $jdb->getOne("SELECT profile_value FROM j_user_profiles WHERE user_id = '$jId' AND profile_key = 'profile.club';");
		db_checkerrors($jClub);
		
		$jDob = trim($jDob, '"');

		// Process DOB
        $jDob = date('d/m/Y', strtotime($jDob));

		$jMSA = trim($jMSA, '"');
		$jClub = trim($jClub, '"');
		
		// Get this user's link status
		$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite WHERE joomla_uid = '$jId';");
		db_checkerrors($memberId);
	
		echo "<tr>\n";
		echo "<td>\n";
		echo $jUsername;
		echo "</td>\n";
		echo "<td>\n";
		echo $jName;
		echo "</td>\n";
		echo "<td>\n";
		echo $jDob;
		echo "</td>\n";
		echo "<td>\n";
		echo $jMSA;
		echo "</td>\n";
		echo "<td>\n";
		echo $jClub;
		echo "</td>\n";
        echo "<td>\n";
        echo $registeredDate;
        echo "</td>\n";
		echo "<td>\n";
		
		if (isset($memberId)) {
		
			echo "<a href=\"juserlink.php?unlinkjuid=$jId\">Linked</a>\n";
		
		} else {
				
			echo "<a href=\"juserlink.php?linkjuid=$jId\">Unlinked</a>\n";
			
		}
		echo "</td>\n";
		echo "<td>\n";
		if (isset($memberId)) {
		
			$memberDetails = new Member();
			$memberDetails->loadId($memberId);
			
			echo "<div align=\"center\">\n";
			
			echo $memberDetails->getFullname();
			
			echo "(";
			echo $memberId;
			echo ")</div>\n";
			
		} else {
		
			//echo "Create Link</a>\n";
		
		}
				
		echo "</td>\n";
		echo "</tr>\n";
	
	}
	
	echo "</tbody>\n";
	echo "</table>\n";

} else {

	echo "</tbody>\n";
	echo "</table>\n";
	echo "<strong>Error no users found!</strong>\n";

}

echo "<p>\n";

if ($offset != 0) {

	$prev = $offset - $interval;
	
	if ($prev < 0) {
		
		$prev = 0;
		
	}
	
	echo "<a href=\"juserlink.php?start=0\">First</a> \n";
	echo "<a href=\"juserlink.php?start=$prev\">Previous</a> \n";
			
}

$next = $offset + $interval;
$last = $jUsersCount - $interval;
if ($next < $jUsersCount) {
	
	echo "<a href=\"juserlink.php?start=$next\">Next</a> \n";
	echo "<a href=\"juserlink.php?start=$last\">Last</a>\n";
	
}

echo "</p>\n";

}

echo "</div>\n";   // Main div

?>

<script>

$(document).ready(function() {

    $('#jusertable').DataTable( {
        "order": [[5, "desc"]]
    });

});

</script>

<?php

htmlFooters();

?>