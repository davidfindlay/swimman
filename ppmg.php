<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");

require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");

checkLogin();

addlog("Access", "Accessed ppmg.php");

// Handle PPMG Meet Creation
if (isset($_POST['create'])) {
    $ppmgMeetId = $_POST['meet'];

    $insert = $GLOBALS['db']->query("INSERT INTO PPMG_meets (meet_year, meet_id) 
                VALUES ((SELECT YEAR(startdate) FROM meet WHERE id = ?), ?);",
                array($ppmgMeetId, $ppmgMeetId));
    db_checkerrors($insert);
}

// Handle PPMG data file upload
if (isset($_POST['ppmgupload'])) {

    $uploaddir = $GLOBALS['home_dir'] . '/masters-data/';
    $filename = basename($_FILES["ppmgfile"]["name"]);

    addlog("PPMG Module", "Upload PPMG Data", "PPMG Database has been uploaded.");

    if (move_uploaded_file($_FILES['ppmgfile']['tmp_name'], basename($_FILES["ppmgfile"]["name"]))) {

        $uploadStatus = "Successfully uploaded.";

        // Get year
        $year = intval($_POST['year']);

        $addFile = $GLOBALS['db']->query("UPDATE PPMG_meets SET datafile = ? 
                  WHERE meet_year = ?;", array($filename, $year));
        db_checkerrors($addFile);

    } else {

        $uploadStatus = "Unable to read upload file!\n";

    }

}

htmlHeaders("Pan Pacific Masters Games");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Pan Pacific Masters Games</h1>\n";

$listPPMGs = $GLOBALS['db']->getAll("SELECT * FROM PPMG_meets ORDER BY meet_year DESC;");
db_checkerrors($listPPMGs);

// Get me year if set, or just default to latest year
if (isset($_GET['year'])) {
    $year = intval($_GET['year']);
} else {
    $year = substr($listPPMGs[0][0], 0, 4);
}

echo "<form enctype=\"multipart/form-data\" method=\"POST\">\n";

echo "<p>\n";
echo "<label for=\"yearselect\">PPMG Year:</label>";
echo "<select name=\"year\" id=\"yearselect\">";

foreach ($listPPMGs as $l) {
    echo "<option value=\"$l[0]\">$l[0]</option>\n";
}

echo "</select>\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Or create new PPMG Meet: </label>\n";

$listMeets = $GLOBALS['db']->getAll("SELECT * FROM meet 
  WHERE meetname LIKE 'Pan Pacific Masters Games%'
  AND startdate > CURDATE()
  AND YEAR(startdate) NOT IN (SELECT meet_year FROM PPMG_meets)
  ORDER BY startdate DESC;");
db_checkerrors($listMeets);

echo "<select name=\"meet\" id=\"meetselect\">\n";
echo "<option name=\"\"></option>\n";

foreach ($listMeets as $l) {
    echo "<option value=\"$l[0]\">$l[1] - $l[2]</option>\n";
}

echo "</select>\n";

echo "<input type=\"submit\" name=\"create\" value=\"Create\" />\n";

echo "</p>\n";

echo "<p>\n";
echo "<label>Upload PPMG Data File:</label>\n";
echo "<input type=\"file\" name=\"ppmgfile\" id=\"ppmgfile\" />";
echo "<input type=\"submit\" name=\"ppmgupload\" value=\"Upload File\" />";
echo "</p>\n";

echo "<p>\n";
echo "<label>Current PPMG Data File:</label>\n";

$ppmgMeet = $GLOBALS['db']->getRow("SELECT * FROM PPMG_meets WHERE meet_year = ?;",
    array($year));
db_checkerrors($ppmgMeet);

$meetId = $ppmgMeet[1];
$currentPPMGfile = $ppmgMeet[2];

echo $currentPPMGfile;
echo "</p>";

echo "<p>\n";
echo "<label>Meet: </label>\n";

$meet = new Meet();
$meet->loadMeet($meetId);

echo $meet->getName() . "(" . $meetId . ") " . $meet->getStartDate() . " - " . $meet->getEndDate();

echo "</p>\n";

echo "<h2>Events</h2>\n";

echo "<p>\n";
echo "<label>Match Events:</label>\n";
echo "<input type=\"submit\" name=\"matchevents\" value=\"Match Events\" />\n";
echo "</p>\n";

echo "<table>\n";
echo "<thead>\n";
echo "<tr>\n";
echo "<th>Entry Manager Event</th>\n";
echo "<th>PPMG Event</th>\n";
echo "<tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

$listEvents = $GLOBALS['db']->getAll("SELECT * FROM PPMG_meetevent WHERE meet_year = ?;", array($year));
db_checkerrors($listEvents);

foreach ($listEvents as $e) {
    $eventId = $e[2];
    $ppmgEvent = $e[3];
    $ppmgColumn = $e[4];
    $event = new MeetEvent();
    $event->load($eventId);

    echo "<tr>\n";
    echo "<td>\n";
    $event->getShortDetails();
    echo "</td>\n";
    echo "<td>\n";
    echo $ppmgEvent .  "(" . $ppmgColumn . ")\n";
    echo "</td>\n";
    echo "</tr>\n";
}

echo "</tr>\n";
echo "</tbody>\n";
echo "</table>\n";

echo "</form>\n";

echo "</div>\n";

htmlFooters();

?>