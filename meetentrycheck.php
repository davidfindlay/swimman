<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");
require_once("includes/classes/MeetEntry.php");
require_once("includes/classes/MeetEntryEvent.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Club.php");
checkLogin();

$meetId = 0;

if (isset($_GET['meet'])) {

    $meetId = $_GET['meet'];

}

htmlHeaders("Swimming Management System - Enter a Meet");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Meet Entry Integrity Check</h1>\n";

if ($meetId != 0) {

    echo "<h3>$meetName</h3>\n";

} else {

    echo "<p>No meet selected!</p>\n";

}

?>

<table width="100%">
  <thead>
  <tr>
      <th>Check</th>
      <th>Entry ID</th>
      <th>Entrant</th>
      <th>Messages</th>
  </tr>
  </thead>
    <tbody>

    <?php

// Get full list of entries
$entries = $GLOBALS['db']->getAll("SELECT id FROM meet_entries WHERE meet_id = ? ORDER BY id ASC", array($meetId));
db_checkerrors($entries);

foreach ($entries as $entry) {

    // Clear per row stuff
    $check = true;
    $messages = array();

    $entryId = $entry[0];
    $meetEntry = new MeetEntry();
    $meetEntry->loadId($entryId);

    $memberId = $meetEntry->getMemberId();
    $member = new Member();
    $member->loadId($memberId);
    $entrant = $member->getFullname();

    // Check cost
    $currentCost = $meetEntry->getCost();
    $correctCost = $meetEntry->calcCost();

    if ($currentCost != $correctCost) {

        $check = false;
        $messages[] = "Stored cost: \$" . number_format($currentCost, 2) .", Correct cost: \$" .
            number_format($currentCost, 2);

    }


    // Render
    echo "<tr>\n";

    echo "<td>\n";

    if ($check) {

        echo "<span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\">&nbsp;Pass</span>\n";

    } else {

        echo "<span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\">&nbsp;Fail</span>\n";

    }

    echo "</td>\n";

    echo "<td>\n";
    echo "<a href=\"meetentry.php?id=$entryId\">$entryId</a>\n";
    echo "</td>\n";

    echo "<td>\n";
    echo $entrant;
    echo "</td>\n";

    echo "<td>\n";
    foreach ($messages as $message) {
        echo "$message<br />\n";
    }
    echo "</td>\n";

    echo "</tr>\n";

}

?>

    </tbody>
</table>
