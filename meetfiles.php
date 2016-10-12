<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/Club.php");
require_once("includes/classes/EntryChecker.php");

checkLogin();

if (isset($_GET['id'])) {

	$meetId = $_GET['id'];
	
	// Get meet details
	$meetDetails = new Meet();
	$meetDetails->loadMeet($meetId);
	
	$meetName = $meetDetails->getName();
	
}

if (isset($_GET['reload'])) {
	
	$entryChecker = new EntryChecker();
		
	if ($entryChecker->loadFile($uploadfile)) {
	
		$entryChecker->processFile($enteringClub);
	
		$errorCount = count($entryChecker->getErrors());
		$memErrorCount = count($entryChecker->getMemberErrors());
		$eventErrorCount = count($entryChecker->getEventErrors());
		
		$_SESSION['ecEntries'] = serialize($entryChecker->getEntries());
		$_SESSION['ecErrors'] = $entryChecker->getErrors();
		$_SESSION['ecMemberErrors'] = $entryChecker->getMemberErrors();
		$_SESSION['ecEventErrors'] = $entryChecker->getEventErrors();

		header("Location: entryvalidator.php");
		
	}
	
}

if (isset($_POST['submitAdd'])) {
	
	$uploaddir = $GLOBALS['home_dir'] . '/masters-data/meets/';
	
	$filename = $_FILES['uploadfile']['name'];
	
	if (! is_dir($uploaddir . $meetId)) {
	
		mkdir($uploaddir . $meetId);

	}
	
	$uploadfile = $uploaddir . $meetId . '/' . $filename;
		
	if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $uploadfile)) {
	
		// Record details
		$fileType = $_POST['typeselect'];
		$shortTitle = $_POST['shortTitle'];
	
		$update = $GLOBALS['db']->query("INSERT INTO meet_files (type, meet_id, filename,
						shorttitle, uploadedby) VALUES (?, ?, ?, ?, 0);", array($fileType, $meetId, $filename,
				$shortTitle));
		db_checkerrors($update);
	
		addlog("Meet Files", "Uploaded new meet file $filename for meet $meetId.");
	
	}
	
	
}

if (isset($_POST['submitRemove'])) {
	
	if (isset($_POST['remove'])) {
		
		foreach ($_POST['remove'] as $r) {
			
			$remove = $GLOBALS['db']->query("DELETE FROM meet_files WHERE id = ?;",
					array($r));
			db_checkerrors($remove);
			
			addlog("Meet Files", "Removed meet file $r for meet $meetId.");
			
		}
		
	}
	
}

addlog("Access", "Accessed meetfiles.php");

htmlHeaders("Meet Files");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Meet Files</h1>\n";

echo "<a href=\"meets.php\">Back to Meet List</a>\n";

echo "<h2>Uploaded Files</h2>\n";

echo "<form method=\"post\" action=\"meetfiles.php?id=$meetId\">\n";

echo "<table class=\"list\">\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>File Type</th>\n";
echo "<th>Filename</th>\n";
echo "<th>Uploaded By</th>\n";
echo "<th>Date Uploaded</th>\n";
echo "<th>Remove</th>\n";
echo "</tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

// Get list of members already with access to this meet
$filesList = $GLOBALS['db']->getAll("SELECT * FROM meet_files, meet_file_types 
		WHERE meet_files.type = meet_file_types.id AND meet_id = '$meetId';");
db_checkerrors($filesList);

foreach ($filesList as $f) {
	
	$fId = $f[0];
	$typeName = $f[8];
	$filename = $f[3];
	$uploadedId = $f[5];
	$uploaderDetails = new Member();
	$uploaderDetails->loadId($uploadedId);
	$uploaderName = $uploaderDetails->getFullname();
	$updated = $f[6];
	
	echo "<tr class=\"list\">\n";
	echo "<td>\n";
	echo $typeName;
	echo "</td>\n";
	echo "<td>\n";
	echo "<a href=\"/meets/$meetId/$filename\" target=\"_blank\">\n";
	echo $filename;
	echo "</a>\n";
	echo "</td>\n";
	echo "<td>\n";
	echo $uploaderName;
	echo "</td>\n";
	echo "<td>\n";
	echo $updated;
	echo "</td>\n";
	echo "<td>\n";
	
	echo "<input type=\"checkbox\" name=\"remove[]\" value=\"$fId\" />\n";
	
	echo "</td>\n";
	echo "</tr>\n";
	
}


echo "</tbody>\n";
echo "</table>\n";

echo "<p>\n";
echo "<input type=\"submit\" name=\"submitRemove\" value=\"Update Files\" />\n";
echo "</p>\n";

echo "</form>\n";

echo "<h2>Add files</h2>\n";

echo "<form enctype=\"multipart/form-data\" method=\"post\" action=\"meetfiles.php?id=$meetId\">\n";

echo "<p>\n";
echo "<label>Meet Name: </label>\n"; 
echo "$meetName<br />\n";

echo "<label>File Type: </label>\n";

$typeList = $GLOBALS['db']->getAll("SELECT * FROM meet_file_types ORDER BY sort;");
db_checkerrors($typeList);

echo "<select name=\"typeselect\">\n";

foreach ($typeList as $t) {
	
	$tId = intval($t[0]);
	$tName = $t[1];
	
	echo "<option value=\"$tId\"";
	echo ">$tName</option>\n";
	
}

echo "</select>\n";
echo "<br />\n";

echo "<label>Short Title: </label>\n";
echo "<input type=\"text\" name=\"shortTitle\" /><br />\n";
echo "<label>Upload File: </label>\n";
echo "<input type=\"file\" name=\"uploadfile\" /><br />\n";
echo "</p>\n";

echo "<p>\n";
echo "<input type=\"submit\" name=\"submitAdd\" value=\"Add File\" />\n";
echo "</p>\n";

echo "</form>\n";

echo "<h2>Entry Manager Generated Files</h2>\n";

echo "<h2>Entry Files</h2>\n";

echo "<table width=\"100%\">\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>\n";
echo "Club\n";
echo "</th>\n";
echo "<th>\n";
echo "Entrants";
echo "</th>\n";
echo "<th>\n";
echo "Relays";
echo "</th>\n";
echo "<th>\n";
echo "TM File - Original Upload";
echo "</th>\n";
echo "<th>\n";
echo "TM File - Entry Manager";
echo "</th>\n";
echo "</tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

// Get list of all entries into this meet
$clubsEntering = $GLOBALS['db']->getAll("SELECT DISTINCT(club_id) FROM meet_entries
		WHERE meet_id = ?
		ORDER BY club_id;", array($meetId));
db_checkerrors($clubsEntering);

// Step through list
foreach ($clubsEntering as $c) {
		
	$clubId = $c[0];
	$clubDetails = new Club;
	$clubDetails->load($clubId);
	$clubName = $clubDetails->getName();

	$entryInfo = $GLOBALS['db']->getRow("SELECT count(*), sum(meals), sum(cost) FROM meet_entries
			WHERE meet_id = '$meetId' AND club_id = '$clubId' AND cancelled = 0;");
	
	$relayInfo = $GLOBALS['db']->getOne("SELECT count(*) FROM meet_entries_relays
			WHERE meet_id = '$meetId' AND club_id = '$clubId';");
				
	echo "<tr class=\"list\">\n";
		
	$meetEntries = $GLOBALS['db']->getAll("SELECT * FROM meet_entries WHERE meet_id = '$meetId'
			AND cancelled = 0 ORDER BY club_id;");
	db_checkerrors($meetEntries);
		
	echo "<td>\n";
	echo $clubName;
	echo "</td>\n";
		
	echo "<td class=\"cellCentre\">\n";
	echo $entryInfo[0];
	$totalEntrants = $totalEntrants + $entryInfo[0];
	echo "</td>\n";
	
	echo "<td class=\"cellCentre\">\n";
	echo $relayInfo;
	echo "</td>\n";

	echo "<td class=\"cellCentre\">\n";
	
	// Search for any uploaded files for this meet
	list($tmId, $tmUpload) = $GLOBALS['db']->getRow("SELECT id, filename FROM meet_entry_files 
			WHERE meetid = ? and clubid = ?
			ORDER BY uploaded DESC;",
			array($meetId, $clubId));
	db_checkerrors($tmId);
	
	if (isset($tmUpload)) {
		
		echo "<a href=\"/swimman/entries/$meetId/$clubId/$tmUpload\">Download</a>\n";
		echo " | ";
		echo "<a href=\"meetfiles.php?id=$meetId&reload=$tmId\">Reload</a>\n";
		
	}
	
	echo "</td>\n";
				
	echo "<td>\n";
	echo "<a href=\"/swimman/gettmentries.php?meet=$meetId&club=$clubId\">Download</a>\n";
	echo "</td>\n";
		
	echo "</tr>\n";
		
}

echo "<tr>\n";
echo "<th>Total</th>\n";
echo "<th class=\"cellCentre\">\n";
echo $totalEntrants;
echo "</th>\n";
echo "<th class=\"cellCentre\">\n";
echo "</th>\n";
echo "<td class=\"cellCentre\">\n";
echo "<a href=\"/swimman/gettmentries.php?meet=$meetId\">Download All</a>\n";
echo "</td>\n";
echo "</tr>\n";

echo "</tbody>\n";
echo "</table>\n";

echo "<h2>Registration and Records Files:</h2>";
echo "<p>\n";
echo "<label>Registration RE1 File:</label>\n";
echo "<a href=\"/swimman/re1/registrations.zip\">Download</a>\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Records File:</label>\n";
echo "<a href=\"/swimman/records/records-LCM.zip\">Long Course Records</a> - \n";
echo "<a href=\"/swimman/records/records-SCM.zip\">Short Course Records</a>\n";
echo "</p>\n";

echo "</div>\n";  // Main Div

htmlFooters();

?>