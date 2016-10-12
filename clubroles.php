<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
checkLogin();

// Handle adding role to a member
if (isset($_POST['addrole'])) {
	
	$clubToAddRole = mysql_real_escape_string($_POST['clubChoice']);
	$memberId = mysql_real_escape_string($_POST['memberroleadd']);
	$roletype = mysql_real_escape_string($_POST['roletype']);
	
	$insert = $GLOBALS['db']->query("INSERT INTO club_roles (member_id, club_id, role_id) 
			VALUES ('$memberId', '$clubToAddRole', '$roletype');");
	db_checkerrors($insert);	
	
	addlog("Club Roles", "Granted club roles to member $memberId");
	
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
				AND group_id = '$msqClubGroup';");
		db_checkerrors($checkIfLink);
		
		if (count($checkIfLink) == 0) {
				
			$insert = $jdb->query("INSERT INTO j_user_usergroup_map (user_id, group_id) VALUES ('$juserId',
					'$msqClubGroup');");
			db_checkerrors($insert);
				
			addlog("Joomla", "Member $memberId added to Joomla Club Recorders group");
				
		}
		
		//$jdb->disconnect();
		
		
	}
	
}

// Remove role
if (isset($_POST['removerole'])) {
	
	$clubToAddRole = mysql_real_escape_string($_POST['clubChoice']);
	$removeArray = $_POST['remove'];

	// Remove Joomla Role
	foreach($removeArray as $rId) {
	
		$delMemDet = $GLOBALS['db']->getRow("SELECT * FROM club_roles WHERE id = '$rId';");
		db_checkerrors($delMemDet);
		
		$delMemId = $delMemDet[1];
		$delRoleId = $delMemDet[3];
		
		// Check that member does not have same role with other clubs
		$otherClubRoles = $GLOBALS['db']->getAll("SELECT * FROM club_roles WHERE member_id = '$delMemId' 
				AND role_id = '$delRoleId';");
		db_checkerrors($otherClubRoles);
		
		if (count($otherClubRoles) <= 1) {
		
			// Remove joomla role
			$jCheck = $GLOBALS['db']->getRow("SELECT * FROM member_msqsite WHERE member_id = '$delMemId';");
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
							user_id = '$juserId' AND group_id = '$msqClubGroup';");
					db_checkerrors($delete);
			
					addlog("Joomla", "Member $delMemId removed from Joomla Club Recorders group");
			
				} else {
					
					addlog("Joomla", "Member $delMemId not removed from Joomla Club Recorders group as was 
						not found!");
					
				}
			
				//$jdb->disconnect();
				
			}
			
		} else {
			
			addlog("Joomla", "Member $delMemId not removed from Joomla Club Recorders group as the member
			is also Club Recorder for other clubs!");
			
		}
		
		$del = $GLOBALS['db']->query("DELETE FROM club_roles WHERE id = '$rId';");
		db_checkerrors($del);
		
		addlog("Club Roles", "Removed role from member $delMemId");
		
	}
	
}

htmlHeaders("Club Roles - Swimming Management System");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Club Roles</h1>\n";

echo "<p>\n";
echo "<a href=\"clubs.php\">Back to Club List</a>\n";
echo "</p>\n";

$clubId = intval($_GET['club']);
echo "<form method=\"post\" action=\"clubroles.php?club=$clubId\">\n";

echo "<p>\n";
echo "<input type=\"hidden\" name=\"clubChoice\" value=\"$clubId\" />\n";

echo "<label>Member: </label>\n";
$memberList = $GLOBALS['db']->getAll("SELECT member.id, member.firstname, member.surname 
		FROM member, member_memberships WHERE member.id = member_memberships.member_id 
		AND member_memberships.club_id = '$clubId';");
db_checkerrors($memberList);

echo "<select name=\"memberroleadd\">\n";

foreach ($memberList as $m) {
	
	$mId = $m[0];
	$mName = $m[1] . ' ' . $m[2];
	
	echo "<option value=\"$mId\">$mName</option>\n";
	
}

echo "</select><br />\n";

echo "<label>Role: </label>\n";
echo "<select name=\"roletype\">\n";
$roleList = $GLOBALS['db']->getAll("SELECT * FROM club_role_types;");
db_checkerrors($roleList);

foreach ($roleList as $r) {
	
	$rId = $r[0];
	$rName = $r[1];
	
	echo "<option value=\"$rId\">$rName</option>\n";
	
}

echo "</select><br />\n";

echo "<input type=\"submit\" name=\"addrole\" value=\"Add Member Role\" />\n";
echo "</p>\n";

echo "<label>Club Roles: </label><br />\n";

echo "<table width=\"100%\">\n";

echo "<tr>\n";
echo "<th>\n";
echo "Role:\n";
echo "</th>\n";
echo "<th>\n";
echo "Member Name:\n";
echo "</th>\n";
echo "<th>\n";
echo "\n";
echo "</th>\n";
echo "</tr>\n";

$clubRolesList = $GLOBALS['db']->getAll("SELECT club_roles.*, club_role_types.* 
		FROM club_roles, club_role_types WHERE club_roles.role_id = club_role_types.id AND 
		club_roles.club_id = '$clubId';");
db_checkerrors($clubRolesList);

foreach ($clubRolesList as $c) {
	
	$rId = $c[0];
	$mId = $c[1];
	$mName = $GLOBALS['db']->getRow("SELECT firstname, surname FROM member WHERE id = '$mId';");
	db_checkerrors($mName);
	$mRole = $c[5];
	
	echo "<tr>\n";
	echo "<td>\n";
	echo $mRole;
	echo "</td>\n";
	echo "<td>\n";
	echo $mName[0] . ' ' . $mName[1];
	echo "</td>\n";
	echo "<td>\n";
	echo "<input type=\"checkbox\" name=\"remove[]\" value=\"$rId\"><label>Remove</label>\n";
	echo "</td>\n";
	echo "</tr>\n";
	
}

echo "</table>\n";

echo "<input type=\"submit\" name=\"removerole\" value=\"Remove Roll\" />\n";

echo "</form>\n";

echo "</div>\n";   // Main div

htmlFooters();

?>