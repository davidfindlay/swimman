<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");
checkLogin();

if (isset($_POST['submitUpdate'])) {
	
	$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet ORDER BY startdate;");
	db_checkerrors($meetList);
	
	foreach ($meetList as $m) {
		
		$mId = $m[0];
		$mPublish = $m[11];
		
		// If it's listed as published but not shown in list
		if (in_array($mId, $_POST['publish']) && ($mPublish != 1)) {
			
			$meetToPub = new Meet();
			$meetToPub->loadMeet($mId);
			$meetToPub->publish();
			
		}
		
		// If it is published but unlessed in list
		if (!in_array($mId, $_POST['publish']) && ($mPublish == 1)) {
			
			$meetToUnpub = new Meet();
			$meetToUnpub->loadMeet($mId);
			$meetToUnpub->unpublish();
			
		}
		
	}
	
}

if (isset($_GET['deleteMeet'])) {
	
	$dMeet = mysql_real_escape_string($_GET['deleteMeet']);
	
	$meetToDel = new Meet();
	$meetToDel->loadMeet($dMeet);
	//$meetToDel->delete();	
	
}


addlog("Access", "Accessed meets.php");

//echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"DTD/xhtml1-frameset.dtd\">\n";
//echo "<html>\n";
//echo "<head>\n";
	
//echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style/screen.css\">\n";
echo "<script type=\"text/javascript\" src=\"meets.js\"></script>\n";
	
//echo "<title>Meet List</title>\n";
	
//echo "</head>\n";
	
//echo "<body>\n";

htmlHeaders("Meets");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Meet List</h1>\n";

// Filter and search
// echo "<h2>Filter</h2>\n";

echo "<div id=\"filterandsearch\">\n";
echo "<form method=\"post\">\n";

echo "<fieldset>\n";
echo "<legend>Meet Details</legend>\n";
echo "<label ford=\"filter\">Filter: </label>\n";
echo "<select name=\"filter\">\n";
echo "<option value=\"current\">Current</option>\n";
echo "<option value=\"all\">All</option>\n";
echo "<option value=\"past\">Past</option>\n";
echo "<option value=\"future\">Future</option>\n";
echo "</select>\n";

echo "</fieldset>\n";

echo "<input type=\"submit\" name=\"filterSubmit\" value=\"Filter\" />\n";
echo "</form>\n";
echo "</div>\n";

echo "<div id=\"meetlist\">\n";

echo "<form method=\"post\">\n";

// Get list of meets in criteria

if (isset($_POST['filterSubmit'])) {
	
	if ($_POST['filter'] == "all") {
		
		$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet ORDER BY startdate;");
		
	}
	
	if ($_POST['filter'] == "future") {
		
		$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet WHERE startdate > NOW() ORDER BY startdate;");
		
	}
	
	if ($_POST['filter'] == "past") {
	
		$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet WHERE startdate < NOW() ORDER BY startdate DESC;");
	
	}
	
	if ($_POST['filter'] == "current") {
	
		$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet WHERE startdate < DATE_ADD(NOW(), INTERVAL 3 MONTH) AND startdate > DATE_SUB(NOW(), INTERVAL 1 MONTH) ORDER BY startdate;");
	
	}
	
} else {

	$meetList = $GLOBALS['db']->getAll("SELECT * FROM meet WHERE startdate < DATE_ADD(NOW(), INTERVAL 3 MONTH) AND startdate > DATE_SUB(NOW(), INTERVAL 1 MONTH) ORDER BY startdate;");
	
}	

db_checkerrors($meetList);

// echo "<h2>Meet List</h2>\n";
echo "<p>\n";
echo "<div align=\"right\">\n";
echo "<a href=\"meetbuilder.php\">Add a Meet<img src=\"images/add.png\" alt=\"Add a Meet\" /></a>\n";
echo "</div>\n";
echo "<table width=\"100%\">\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>Date</th>\n";
echo "<th>Meet</th>\n";
echo "<th>Admins</th>\n";
echo "<th>Entries</th>\n";
echo "<th>Publish</th>\n";
echo "<th></th>\n";
echo "</tr>\n";
echo "</thead>\n";

foreach ($meetList as $m) {
	
	$meetId = $m[0];
	
	$meetDate = $m[2];
	$meetEnd = $m[3];
	$meetDeadline = $m[4];
	$meetName = $m[1];
	$meetLocation = $m[10];
	$meetNumEvents = $GLOBALS['db']->getOne("SELECT count(*) FROM meet_events WHERE meet_id = '$meetId';");
	db_checkerrors($meetNumEvents);
	$meetPublished = $m[11];
	
	echo "<tr class=\"list\">\n";
	
	// Meet Start Date
	echo "<td class=\"short\">\n";
	echo "<strong>\n";
	echo date('j M y', strtotime($meetDate));
	
	if (($meetEnd != $meetDate) && ($meetEnd != "0000-00-00")) {
		
		echo " to <br />";
		echo date('j M y', strtotime($meetEnd));
		
	}
	
	echo "</strong>\n";
	echo "<br />\n";
	echo "<span class=\"smalltext\">\n";
	echo date('l', strtotime($meetDate));
	
	if (($meetEnd != $meetDate) && ($meetEnd != "0000-00-00")) {
	
		echo " to ";
		echo date('l', strtotime($meetEnd));
	
	}
	echo "</span>\n";

	echo "</td>\n";
	
	// Meet name
	echo "<td valign=\"top\">\n";
	echo "<a href=\"#\" onclick=\"displayDetails($meetId)\">\n";
	echo $meetName;
	echo "</a>\n";
	echo "<br />\n";
	echo "<span class=\"smalltext\">\n";
	echo $meetLocation;
	echo "</span>\n";
	echo "</td>\n";
	
	// Number of events
	echo "<td class=\"short\">\n";
	
	// get number of people who have access to the meet
	$meetAccessNum = $GLOBALS['db']->getOne("SELECT count(*) FROM meet_access WHERE meet_id = '$meetId';");
	db_checkerrors($meetAccessNum);
	
	if ($meetAccessNum > 0) {

		echo $meetAccessNum;
		
	}
	
	echo "</td>\n";
	
	// Entry numbers
	$numEntries = $GLOBALS['db']->getOne("SELECT count(*) FROM meet_entries WHERE meet_id = '$meetId'
			 AND cancelled = 0;");
	db_checkerrors($numEntries);
	
	echo "<td class=\"short\">\n";
	echo "<a href=\"meetentries.php?meet=$meetId\">\n";
	echo $numEntries;	
	echo "</a>\n";
	echo "</td>\n";
	
	// Published to users?
	echo "<td class=\"short\">\n";
	echo "<input type=\"checkbox\" name=\"publish[]\" value=\"$meetId\" \n";
		
	if ($meetPublished == 1) {
		
		echo " checked=\"checked\"";
		
	}
	
	echo " />\n";
	
	echo "</td>\n";
	
	echo "<td class=\"short\" width=\"10%\">\n";
	
	echo "<a href=\"meetbuilder.php?meetId=$meetId\" class=\"tooltip\" data-tip=\"Edit this meet\"><img src=\"images/edit.png\" alt=\"Edit\" /></a>";
	
	// If eProgram exists show link
	$eProgramExists = $GLOBALS['db']->getRow("SELECT * FROM meet_programs WHERE meet_id = '$meetId';");
	db_checkerrors($eProgramExists);
	
	if (isset($eProgramExists)) {
		
		echo "<a href=\"eprogram.php?id=$meetId\" class=\"tooltip\" data-tip=\"View the eProgram for this meet\"><img src=\"images/eprogram.png\" alt=\"eProgram\" /></a>";
		
	}
	
	// If no events yet, display TM Import link
	if ($meetNumEvents == 0) {
		
		echo "<a href=\"importmeet.php?meet=$meetId\" class=\"tooltip\" data-tip=\"Import TM Event File\"><img src=\"images/import.png\" alt=\"Import TM Events\" /></a>";
		
	}
	
	// Meet Access
	echo "<a href=\"meetaccess.php?id=$meetId\" class=\"tooltip\" data-tip=\"Meet Access\">";
	echo "<img src=\"images/admin.png\" alt=\"Meet Access\" />";
	echo "</a>\n";
	
	echo "<a href=\"meetfiles.php?id=$meetId\" class=\"tooltip\" data-tip=\"Meet Files\">\n";
	echo "<img src=\"images/folder.png\" alt=\"Meet Files\" />";
	echo "</a>\n";
	
	echo "<a href=\"meets.php?deleteMeet=$meetId\" class=\"tooltip\" data-tip=\"Delete this meet\"><img src=\"images/delete.png\" alt=\"Delete\" /></a>";
	
	echo "</td>\n";	
	
	echo "</tr>\n";
	
	// Hidden rows
	echo "<tr class=\"meetInfo\" id=\"mEventList_$meetId\" style=\"visibility: collapse; display: none;\">\n";

	// if event is single day, don't show date column info
	// if multiday, show day x and day name
	echo "<td>\n";
	// TODO: multiple session days
	echo "</td>\n";
	
	echo "<td>\n";
	$eventList = $GLOBALS['db']->getAll("SELECT id FROM meet_events WHERE meet_id = '$meetId';");
	db_checkerrors($eventList);
	
	// Show deadline
	echo "<div class=\"smalltext\">\n";
	echo "<strong>Entry Deadline: </strong>\n";
	echo date('l jS \of F, Y', strtotime($meetDeadline));
	echo "</div>\n";
	
	echo "<table border=\"0\">\n";
	
	echo "<tr>\n";
	echo "<td>\n";
	
	$eventCounter = 0;
	$ePerCol = round($meetNumEvents / 3);
	
	foreach ($eventList as $e) {
	
		$eId = $e[0];
		$eObj = new MeetEvent;
		$eObj->load($eId);
		
		echo "<span class=\"smalltext\">\n";
		echo $eObj->getProgNumber();
		echo " - </span>\n";
		// echo "</td>\n";
		// echo "<td>\n";		
		echo "<span class=\"smalltext\">\n";
		echo $eObj->getShortDetails() . "<br />\n";
		echo "</span>\n";
		
		$eventCounter++;
		
		if (($eventCounter == $ePerCol) || ($eventCounter == ($ePerCol * 2))) {
			
			echo "</td><td>\n";
			
		} 
		
	}
	echo "</td>\n";
	echo "</tr>\n";
		
	echo "</table>\n";
	
	echo "</td>\n";
	
	echo "</tr>\n";
	
}

echo "</table>\n";
echo "</p>\n";
echo "<input type=\"submit\" name=\"submitUpdate\" value=\"Update\" />\n";

echo "</form>\n";

echo "</div>\n";

echo "</div>\n";  // Main Div

htmlFooters();

?>

