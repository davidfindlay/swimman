<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
checkLogin();

// Check for adding of a new level
if (isset($_POST['submitAdd'])) {

	if ($_POST['submitAdd'] == "Add Level") {
		
		if ($_POST['copyLevels'] == "0") {

			$perfProgId = mysql_real_escape_string($_POST['perfProgId']);
			$levelName = mysql_real_escape_string($_POST['levelName']);
			$description = mysql_real_escape_string($_POST['description']);
			$sortOrder = mysql_real_escape_string($_POST['sortOrder']);
			
			$insert = $GLOBALS['db']->query("INSERT INTO performance_programs_levels (perf_prog_id, 
					levelname, description, sort) VALUES ('$perfProgId', '$levelName', '$description', 
					'$sortOrder');");
			db_checkerrors($insert);
			
		} else {
			
			$copyLevels = $_POST['copyLevels'];
			$progId = $_POST['perfProgId'];
			
			$copy = $GLOBALS['db']->query("INSERT INTO performance_programs_levels (perf_prog_id,
					levelname, description, sort)
					SELECT ?, levelname, description, sort
					FROM performance_programs_levels
					WHERE perf_prog_id = ?;", array($progId, $copyLevels));
			db_checkerrors($copy);
			
		}
		
	}
	
}

// Check for adding of a new level
if (isset($_POST['submitAddEvent'])) {

	if ($_POST['submitAddEvent'] == "Add Event") {
		
		if ($_POST['copyEvents'] == "0") {

			$distId = mysql_real_escape_string($_POST['distance']);
			$discId = mysql_real_escape_string($_POST['discipline']);
			$progId = mysql_real_escape_string($_POST['perfProgId']);
			$sort = mysql_real_escape_string($_POST['sort']);
	
			$insert = $GLOBALS['db']->query("INSERT INTO performance_programs_events (perf_prog_id, 
					discipline, distance, sort) VALUES (?, ?, ?, ?);",
					array($progId, $discId, $distId, $sort));
			db_checkerrors($insert);
			
			echo "Add Event!\n";
			
		} else {
			
			$copyEvents = $_POST['copyEvents'];
			$progId = $_POST['perfProgId'];
			
			$copy = $GLOBALS['db']->query("INSERT INTO performance_programs_events (perf_prog_id, 
					discipline, distance, sort) 
					SELECT ?, discipline, distance, sort 
					FROM performance_programs_events
					WHERE perf_prog_id = ?;", array($progId, $copyEvents));
			db_checkerrors($copy);
			
			echo "Copy Events!\n";
			
		}

	}

}

htmlHeaders("Performance Program Setup - Swimming Management System");

sidebarMenu();

$progId = mysql_real_escape_string($_GET['id']);
$progDetails = $GLOBALS['db']->getRow("SELECT * FROM performance_programs WHERE id = '$progId';");
db_checkerrors($progDetails);
$ageSet = $progDetails[3];

echo "<div id=\"main\">\n";

echo "<h1>Performance Program Setup</h1>\n";

echo "<h2>Program Details - Men</h2>\n";

// Get list of Age Groups
$ageGroups = $GLOBALS['db']->getAll("SELECT * FROM age_groups WHERE age_groups.set = '$ageSet' 
		AND gender = '1' AND swimmers = 1 ORDER BY min ASC, gender ASC;");
db_checkerrors($ageGroups);

// Get list of levels
$levels = $GLOBALS['db']->getAll("SELECT * FROM performance_programs_levels WHERE perf_prog_id = '$progId'
		ORDER BY sort ASC;");
db_checkerrors($levels);

echo "<form method=\"post\" action=\"perfprogedit.php?id=$progId\">\n";

echo "<table class=\"list\">\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<td style=\"width: 40em;\">\n";

echo "</td>\n";

foreach ($ageGroups as $a) {
	
	$ageGroupName = $a[5];

	echo "<th colspan=\"4\">\n";
	echo $ageGroupName;
	echo "</th>\n";
	
}

echo "</tr>\n";
echo "<tr>\n";
echo "<th>\n";
echo "</th>\n";

foreach ($ageGroups as $a) {
	
	foreach ($levels as $l) {

		$levelName = $l[2];
	
		echo "<th>\n";
		echo $levelName;
		echo "</th>\n";
	
	}

}

echo "</tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

$progEvents = $GLOBALS['db']->getAll("SELECT performance_programs_events.id, event_disciplines.id, event_distances.id, 
		event_disciplines.discipline, event_distances.distance  
		FROM event_disciplines, event_distances, performance_programs_events
		WHERE event_disciplines.id = performance_programs_events.discipline 
		AND event_distances.id = performance_programs_events.distance
		AND performance_programs_events.perf_prog_id = ?
		ORDER BY performance_programs_events.sort, event_disciplines.discipline, event_distances.metres", 
		array($progId));
db_checkerrors($progEvents);

foreach ($progEvents as $p) {
	
	$progEventId = $p[0];
	$eventDisc = $p[3];
	$eventDist = $p[4];
	
	echo "<tr class=\"list\">\n";
	
	echo "<th>\n";
	echo "$eventDisc $eventDist\n";
	echo "</th>\n";
	
	foreach ($ageGroups as $a) {
	
		$ageGroupId = $a[0];
		
		foreach ($levels as $l) {
		
			$levelId = $l[0];
			$textBoxName = $eventDisc . "_" . $eventDist . '_' . $ageGroupId . '_' . $levelId;
			
			$timeSec = $GLOBALS['db']->getOne("SELECT hightime FROM performance_programs_stds WHERE
					perf_prog_id = '$progId' AND age_group = '$ageGroupId' AND level = '$levelId' AND
					perf_prog_event = '$progEventId';");
			db_checkerrors($timeSec);
			
			$timeStd = sw_formatSecs($timeSec);
			
			echo "<td>\n";
			//echo "<input type=\"text\" name=\"$textBoxName\" size=\"10\" value=\"$timeStd\" />\n";
			echo $timeStd;
			echo "</td>\n";
		
		}
	
	}
	
	echo "</tr>\n";
	
}

echo "</tbody>\n";
echo "</table>\n";

echo "<input type=\"submit\" name=\"submitChanges\" value=\"Submit Changes\" />\n";
echo "<input type=\"reset\" name=\"resetChanges\" value=\"Reset\" />\n";

// Women
echo "<h2>Program Details - Women</h2>\n";

// Get list of Age Groups
$ageGroups = $GLOBALS['db']->getAll("SELECT * FROM age_groups WHERE age_groups.set = '$ageSet'
		AND gender = '2' AND swimmers = 1 ORDER BY min ASC, gender ASC;");
db_checkerrors($ageGroups);

// Get list of levels
$levels = $GLOBALS['db']->getAll("SELECT * FROM performance_programs_levels WHERE perf_prog_id = '$progId'
		ORDER BY sort ASC;");
db_checkerrors($levels);

echo "<form method=\"post\" action=\"perfprogedit.php?id=$progId\">\n";

echo "<table class=\"list\">\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<td style=\"width: 40em;\">\n";

echo "</td>\n";

foreach ($ageGroups as $a) {

	$ageGroupName = $a[5];

	echo "<th colspan=\"4\">\n";
	echo $ageGroupName;
	echo "</th>\n";

}

echo "</tr>\n";
echo "<tr>\n";
echo "<th>\n";
echo "</th>\n";

foreach ($ageGroups as $a) {

	foreach ($levels as $l) {

			$levelName = $l[2];

			echo "<th>\n";
			echo $levelName;
			echo "</th>\n";

	}

}

echo "</tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

$progEvents = $GLOBALS['db']->getAll("SELECT performance_programs_events.id, event_disciplines.id, event_distances.id,
		event_disciplines.discipline, event_distances.distance
		FROM event_disciplines, event_distances, performance_programs_events
		WHERE event_disciplines.id = performance_programs_events.discipline
		AND event_distances.id = performance_programs_events.distance
		AND performance_programs_events.perf_prog_id = ?
		ORDER BY performance_programs_events.sort, event_disciplines.discipline, event_distances.metres",
		array($progId));
db_checkerrors($progEvents);

foreach ($progEvents as $p) {

	$progEventId = $p[0];
	$eventDisc = $p[3];
	$eventDist = $p[4];

	echo "<tr class=\"list\">\n";

	echo "<th>\n";
	echo "$eventDisc $eventDist\n";
	echo "</th>\n";

	foreach ($ageGroups as $a) {
		
		$ageGroupId = $a[0];

		foreach ($levels as $l) {

			$levelId = $l[0];
			$textBoxName = $eventDisc . "_" . $eventDist . '_' . $ageGroupId . '_' . $levelId;
			//echo "$progId - $ageGroupId - $levelId - $progEventId <br />\n";
				
			$timeSec = $GLOBALS['db']->getOne("SELECT hightime FROM performance_programs_stds WHERE
					perf_prog_id = '$progId' AND age_group = '$ageGroupId' AND level = '$levelId' AND
					perf_prog_event = '$progEventId';");
			db_checkerrors($timeSec);
				
			$timeStd = sw_formatSecs($timeSec);
			
			echo "<td>\n";
			//echo "<input type=\"text\" name=\"$textBoxName\" size=\"10\" value=\"$timeStd\" />\n";
			echo $timeStd;
			echo "</td>\n";

		}

	}

	echo "</tr>\n";

}

echo "</tbody>\n";
echo "</table>\n";

echo "<input type=\"submit\" name=\"submitChanges\" value=\"Submit Changes\" />\n";
echo "<input type=\"reset\" name=\"resetChanges\" value=\"Reset\" />\n";

echo "</form>\n";

echo "<p>\n";
echo "<a href=\"perfprogimport.php?id=$progId\">Import from CSV</a>\n";
echo "</p>\n";

echo "<p>\n";
echo "<a href=\"perfprogtimestd.php?id=$progId\">Calculate Time Standards</a>\n";
echo "</p>\n";

echo "<h2>Events</h2>\n";

echo "<table class=\"list\">\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>\n";
echo "Distance";
echo "</th>\n";
echo "<th>\n";
echo "Discipline";
echo "</th>\n";
echo "</tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

echo "</tbody>\n";
echo "</table>\n";

echo "<h2>Add Event</h2>\n";

echo "<form method=\"post\" action=\"perfprogedit.php?id=$progId\">\n";
echo "<p>\n";
echo "<label for=\"distance\">Distance:</label>\n";

$distances = $GLOBALS['db']->getAll("SELECT * FROM event_distances ORDER BY metres;");
db_checkerrors($distances);

echo "<select name=\"distance\">\n";

foreach ($distances as $d) {
	
	$dId = $d[0];
	$dName = $d[1];
	
	echo "<option value=\"$dId\">$dName</option>\n";
	
}

echo "</select><br />\n";

$disciplines = $GLOBALS['db']->getAll("SELECT * FROM event_disciplines ORDER BY id;");
db_checkerrors($disciplines);

echo "<label for=\"discipline\">Discipline:</label>\n";
echo "<select name=\"discipline\">\n";

echo "<option value=\"0\"></option>\n";

foreach ($disciplines as $d) {

	$dId = $d[0];
	$dName = $d[1];

	echo "<option value=\"$dId\">$dName</option>\n";

}

echo "</select>\n";
echo "</p>\n";

echo "<p>\n";
echo "<label for=\"sort\">Order:</label>\n";
echo "<input type=\"text\" name=\"sort\" id=\"sort\" />\n";
echo "</p>\n";

echo "<p>\n";
echo "<label for=\"copyEvents\">Copy Events from Another Program:</label>\n";

echo "<select name=\"copyEvents\" id=\"copyEvents\">\n";
echo "<option value=\"0\"></option>\n";

// Get Programs
$programs = $GLOBALS['db']->getAll("SELECT id, longname FROM performance_programs;");
db_checkerrors($programs);

foreach ($programs as $p) {

	echo "<option value=\"" . $p[0] . "\">" . $p[1] . "</option>\n";
	
}
	
echo "</select>\n";
echo "</p>\n";

echo "<p>\n";
echo "<input type=\"hidden\" name=\"perfProgId\" value=\"$progId\" />\n";
echo "<input type=\"submit\" name=\"submitAddEvent\" value=\"Add Event\" />\n";
echo "</p>\n";

echo "</form>\n";

echo "<h2>Levels</h2>\n";

echo "<table class=\"list\">\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>\n";
echo "Level Name";
echo "</th>\n";
echo "<th>\n";
echo "Description";
echo "</th>\n";
echo "<th>\n";
echo "Sort Order";
echo "</th>\n";
echo "</tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

foreach ($levels as $l) {
	
	echo "<tr class=\"list\">\n";
	echo "<td>\n";
	echo $l[2];
	echo "</td>\n";
	echo "<td>\n";
	echo $l[3];
	echo "</td>\n";
	echo "<td>\n";
	echo $l[4];
	echo "</td>\n";
	echo "</tr>\n";
	
}

echo "</tbody>\n";
echo "</table>\n";

echo "<h2>Add Level</h2>\n";

echo "<form method=\"post\" action=\"perfprogedit.php?id=$progId\">\n";
echo "<p>\n";
echo "<label for=\"levelName\">Level Name:</label>\n";
echo "<input type=\"text\" name=\"levelName\" id=\"levelName\" />\n";
echo "</p>\n";
echo "<p>\n";
echo "<label for=\"description\">Description:</label>\n";
echo "<input type=\"text\" name=\"description\" id=\"description\" />\n";
echo "</p>\n";
echo "<p>\n";
echo "<label for=\"sortOrder\">Sort Order:</label>\n";
echo "<input type=\"text\" name=\"sortOrder\" id=\"sortOrder\" />\n";
echo "</p>\n";
echo "<p>\n";
echo "<label for=\"copyLevels\">Copy Levels from Another Program:</label>\n";

echo "<select name=\"copyLevels\" id=\"copyLevels\">\n";
echo "<option value=\"0\"></option>\n";

// Get Programs
$programs = $GLOBALS['db']->getAll("SELECT id, longname FROM performance_programs;");
db_checkerrors($programs);

foreach ($programs as $p) {

	echo "<option value=\"" . $p[0] . "\">" . $p[1] . "</option>\n";

}

echo "</select>\n";
echo "</p>\n";


echo "<p>\n";
echo "<input type=\"hidden\" name=\"perfProgId\" value=\"$progId\" />\n";
echo "<input type=\"submit\" name=\"submitAdd\" value=\"Add Level\" />\n";
echo "</p>\n";

echo "</form>\n";

echo "</div>\n";

htmlFooters();

?>