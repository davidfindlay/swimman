<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Meet.php");

checkLogin();

if (isset($_GET['id'])) {

	$meetId = $_GET['id'];
	
	// Get meet details
	$meetDetails = new Meet();
	$meetDetails->loadMeet($meetId);
	
	$meetName = $meetDetails->getName();
	
}

if (isset($_POST['clubselect'])) {
	
	$clubId = intval($_POST['clubselect']);
	
}

if (isset($_POST['submitAdd'])) {
	
	$memberId = intval($_POST['memberaccess']);
	
	$insert = $GLOBALS['db']->query("INSERT INTO meet_access (meet_id, member_id) 
			VALUES ('$meetId', '$memberId');");
	db_checkerrors($insert);
	
	addlog("Meet Access", "Granted access to meet $meetId to member $memberId");
	
	// Add joomla role
	$jCheck = $GLOBALS['db']->getRow("SELECT * FROM member_msqsite WHERE member_id = '$memberId';");
	db_checkerrors($jCheck);
	
	if (isset($jCheck)) {
	
		$juserId = $jCheck[1];
	
		// Add to MSQ Members Group
		$msqMemGroup = 9;
		$msqClubGroup = 19;
		$msqMeetGroup = 20;
	
		// Connect to joomla database
		$jdbuser = $GLOBALS['jdbuser'];
		$jdbpass = $GLOBALS['jdbpass'];
		$jdbhost = $GLOBALS['jdbhost'];
		$jdbport = $GLOBALS['jdbport'];
		$jdbname = $GLOBALS['jdbname'];
		$dsn = "mysql://$jdbuser:$jdbpass@$jdbhost:$jdbport/$jdbname";
		$jdb =& DB::connect($dsn);
		db_checkerrors($jdb);
	
		// Set correct timezone for all operations
		$result = $jdb->query("SET time_zone = '+10:00';");
		db_checkerrors($result);
	
		$checkIfLink = $jdb->getRow("SELECT * FROM j_user_usergroup_map WHERE user_id = '$juserId'
				AND group_id = '$msqMeetGroup';");
		db_checkerrors($checkIfLink);
	
		if (count($checkIfLink) == 0) {
	
			$insert = $jdb->query("INSERT INTO j_user_usergroup_map (user_id, group_id) VALUES ('$juserId',
					'$msqMeetGroup');");
			db_checkerrors($insert);
	
			addlog("Joomla", "Member $memberId added to Joomla Meet Recorders group");
	
		}
	
	}
	
}

if (isset($_POST['submitRemove'])) {
	
	if (isset($_POST['remove'])) {
		
		foreach ($_POST['remove'] as $r) {
			
			$removeId = intval($r);
			
			$remove = $GLOBALS['db']->query("DELETE FROM meet_access WHERE meet_id = '$meetId' 
					AND id = '$removeId';");
			db_checkerrors($remove);
			
			addlog("Meet Access", "Revoked access to meet $meetId to member $removeId");
			
			// Remove joomla role
			$jCheck = $GLOBALS['db']->getRow("SELECT * FROM member_msqsite WHERE member_id = '$removeId';");
			db_checkerrors($jCheck);
				
			if (isset($jCheck)) {
					
				$juserId = $jCheck[1];
					
				// Add to MSQ Members Group
				$msqMemGroup = 9;
				$msqClubGroup = 19;
				$msqMeetGroup = 20;
					
				// Connect to joomla database
				$jdbuser = $GLOBALS['jdbuser'];
				$jdbpass = $GLOBALS['jdbpass'];
				$jdbhost = $GLOBALS['jdbhost'];
				$jdbport = $GLOBALS['jdbport'];
				$jdbname = $GLOBALS['jdbname'];
				$dsn = "mysql://$jdbuser:$jdbpass@$jdbhost:$jdbport/$jdbname";
				$jdb =& DB::connect($dsn);
				db_checkerrors($jdb);
					
				// Set correct timezone for all operations
				$result = $jdb->query("SET time_zone = '+10:00';");
				db_checkerrors($result);
					
				$checkIfLink = $jdb->getRow("SELECT * FROM j_user_usergroup_map WHERE user_id = '$juserId'
						AND group_id = '$msqClubGroup';");
				db_checkerrors($checkIfLink);
					
				if (isset($checkIfLink)) {
						
					$delete = $jdb->query("DELETE FROM j_user_usergroup_map WHERE
							user_id = '$juserId' AND group_id = '$msqMeetGroup';");
							db_checkerrors($delete);
								
							addlog("Joomla", "Member $removeId removed from Joomla Meet Recorders group");
								
				} else {
					
				addlog("Joomla", "Member $delMemId not removed from Joomla Meet Recorders group as was
				not found!");
					
				}
					
				//$jdb->disconnect();
			
			}
				
			
		}
		
	}
	
}

addlog("Access", "Accessed meetaccess.php");

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"DTD/xhtml1-frameset.dtd\">\n";
echo "<html>\n";
echo "<head>\n";

echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style/screen.css\">\n";
echo "<script type=\"text/javascript\" src=\"meets.js\"></script>\n";

echo "<title>Meet Access</title>\n";

echo "</head>\n";

echo "<body>\n";

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Meet Access</h1>\n";

echo "<a href=\"meets.php\" target=\"main\">Back to Meet List</a>\n";

echo "<h2>Access List</h2>\n";

echo "<form method=\"post\" action=\"meetaccess.php?id=$meetId\">\n";

echo "<table class=\"list\">\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>Member Name</th>\n";
echo "<th>Remove</th>\n";
echo "</tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

// Get list of members already with access to this meet
$accessList = $GLOBALS['db']->getAll("SELECT * FROM meet_access WHERE meet_id = '$meetId';");
db_checkerrors($accessList);

foreach ($accessList as $a) {
	
	$aId = $a[0];
	$memberId = $a[2];
	
	echo "<tr class=\"list\">\n";
	echo "<td>\n";
	
	$memberDetails = new Member();
	$memberDetails->loadId($memberId);
	echo $memberDetails->getFullname();
	
	echo "</td>\n";
	echo "<td>\n";
	
	echo "<input type=\"checkbox\" name=\"remove[]\" value=\"$aId\" />\n";
	
	echo "</td>\n";
	echo "</tr>\n";
	
}


echo "</tbody>\n";
echo "</table>\n";

echo "<p>\n";
echo "<input type=\"submit\" name=\"submitRemove\" value=\"Update Access\" />\n";
echo "</p>\n";

echo "</form>\n";

echo "<h2>Grant Access to Other Members</h2>\n";

echo "<form method=\"post\" action=\"meetaccess.php?id=$meetId\">\n";

echo "<p>\n";
echo "<label>Meet Name: </label>\n"; 
echo "$meetName<br />\n";

echo "<label>Club Name: </label>\n";

$clubList = $GLOBALS['db']->getAll("SELECT * FROM clubs ORDER BY code;");
db_checkerrors($clubList);

echo "<select name=\"clubselect\">\n";

if (!isset($clubId)) {
	
	$clubId = $clubList[0][0];
	
}

foreach ($clubList as $c) {
	
	$cId = intval($c[0]);
	$cName = $c[2];
	
	echo "<option value=\"$cId\"";
	
	if (isset($clubId)) {
	
		if ($clubId == $cId) {
			
			echo " selected=\"selected\"";
			
		}
		
	}
	
	echo ">$cName</option>\n";
	
}

echo "</select>\n";
echo "<input type=\"submit\" name=\"submitGetList\" value=\"Get Member List\" />\n";
echo "<br />\n";

echo "<label>Grant Access to: </label>\n";

$memberList = $GLOBALS['db']->getAll("SELECT member.id, member.firstname, member.surname
		FROM member, member_memberships WHERE member.id = member_memberships.member_id
		AND member_memberships.club_id = '$clubId';");
db_checkerrors($memberList);

echo "<select name=\"memberaccess\">\n";

echo "<option value=\"none\"></option>\n";

foreach ($memberList as $m) {

	$mId = $m[0];
	$mName = $m[1] . ' ' . $m[2];

	echo "<option value=\"$mId\">$mName</option>\n";

}

echo "</select><br />\n";

echo "</p>\n";

echo "<p>\n";
echo "<input type=\"submit\" name=\"submitAdd\" value=\"Grant Access\" />\n";
echo "</p>\n";

echo "</form>\n";

echo "</div>\n";  // Main Div

htmlFooters();

?>