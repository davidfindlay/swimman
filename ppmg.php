<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");

require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");
require_once("includes/classes/PPMGMeet.php");
require_once("includes/classes/PPMGMeetEvent.php");

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
$uploadStatus = "";
if (isset($_POST['ppmgupload'])) {

    $uploaddir = $GLOBALS['home_dir'] . '/masters-data/';
    $filename = basename($_FILES["ppmgfile"]["name"]);

    addlog("PPMG Module", "Upload PPMG Data", "PPMG Database has been uploaded.");

    if (move_uploaded_file($_FILES['ppmgfile']['tmp_name'], $uploaddir. basename($_FILES["ppmgfile"]["name"]))) {

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

// Handle Match Events
if (isset($_POST['matchevents'])) {

    $year = intval($_POST['year']);
    $ppmgMeet = new PPMGMeet();
    $ppmgMeet->load($year);
    $ppmgMeet->matchEvents();

}

// Handle Match Members
if (isset($_POST['matchmembers'])) {

    $year = intval($_POST['year']);
    $ppmgMeet = new PPMGMeet();
    $ppmgMeet->load($year);
    $ppmgMeet->matchMembers();

}

// Handle Create Entries
if (isset($_POST['createentries'])) {

    $year = intval($_POST['year']);
    $ppmgMeet = new PPMGMeet();
    $ppmgMeet->load($year);
    $ppmgMeet->createEntries();

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
echo "<br />\n";
echo $uploadStatus;
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

echo "<table width='100%'>\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>Entry Manager Event</th>\n";
echo "<th>PPMG Event</th>\n";
echo "<tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

$listEvents = $GLOBALS['db']->getAll("SELECT * FROM PPMG_meetevent WHERE meet_year = ? 
  ORDER BY meet_event_id;", array($year));
db_checkerrors($listEvents);

foreach ($listEvents as $e) {
    $eventId = $e[3];
    $ppmgEvent = $e[4];
    $ppmgColumn = $e[5];
    $event = new MeetEvent();
    $event->load($eventId);

    echo "<tr class=\"list\">\n";
    echo "<td>\n";
    echo $event->getProgNumber() . " - " . $event->getShortDetails();
    echo "</td>\n";
    echo "<td>\n";
    echo $ppmgEvent .  "(" . $ppmgColumn . ")\n";
    echo "</td>\n";
    echo "</tr>\n";
}

echo "</tbody>\n";
echo "</table>\n";

echo "<h2>Entrants</h2>\n";

echo "<p>\n";
echo "<label>Match Members:</label>\n";
echo "<input type=\"submit\" name=\"matchmembers\" value=\"Match Members\" />\n";
echo "</p>\n";

echo "<p>\n";
echo "<label>Create Entries:</label>\n";
echo "<input type=\"submit\" name=\"createentries\" value=\"Create Entries\" />\n";
echo "</p>\n";

echo "<table width='100%'>\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>Entry Manager Member</th>\n";
echo "<th>PPMG Entrant</th>\n";
echo "<th>Status</th>\n";
echo "<th>Controls</th>\n";
echo "<tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

$matchedMembers = 0;
$totalmembers = 0;

$entrantList = $GLOBALS['db']->getAll("SELECT PPMG_entry.first_name, PPMG_entry.last_name, PPMG_entry.gender, 
    PPMG_entry.dob, PPMG_entry.msa_id, PPMG_entry.member_id, member.firstname, member.surname, member.gender,
    member.dob, member.number, PPMG_entry.status, PPMG_entry.account_number, PPMG_entry.entry_id, member.id
    FROM member RIGHT JOIN PPMG_entry ON member.id = PPMG_entry.member_id ORDER BY PPMG_entry.date_registered;");
db_checkerrors($entrantList);

foreach ($entrantList as $e) {

    echo "<tr class=\"list\">\n";

    echo "<td>\n";

    $totalmembers++;

    // Display entry manager details if a member is matched
    if ($e[6] != "") {

        echo $e[6] . " " . $e[7] . " - " . $e[9] . " - ";
        if ($e[8] == 1) {
            echo "M";
        } else {
            echo "F";
        }
        echo " (<a href=\"memberdetails.php?member=$e[14]\">" . $e[10] . "</a>)";

        $matchedMembers++;

    }
    echo "</td>\n";

    echo "<td>\n";
    echo $e[0] . " " . $e[1] . " - " . $e[3] . " - " . $e[2] . " (" . $e[4] . ")";
    echo "</td>\n";

    echo "<td>\n";

    echo $e[11];

    echo "</td>\n";
    echo "<td>\n";

    echo "<a href=\"ppmgedit.php?account=$e[12]\">Edit</a>\n";
    echo " | ";

    if ($e[13] != "") {
        echo "<a href=\"meetentry.php?entry=$e[13]\">Meet Entry</a>\n";
    }

    echo "</td>\n";
    echo "</tr>\n";

}

echo "</tbody>\n";
echo "</table>\n";

echo "</form>\n";

echo "<p>\n";
echo "<label>Matched Members:</label>\n";
echo $matchedMembers;
echo "</p>\n";

echo "<p>\n";
echo "<label>Total Members:</label>\n";
echo $totalmembers;
echo "</p>\n";


echo "</div>\n";

htmlFooters();

?>