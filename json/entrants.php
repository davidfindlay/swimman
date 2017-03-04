<?php

// JSON Web Service
// Returns a list of Meets available for viewing via eProgram

require_once("../includes/setup.php");
require_once("../includes/classes/Meet.php");

checkLogin();

// Set up the Associative Array fetch mode
$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);

$meetId = intval($_GET['meetId']);
$eventId = intval($_GET['eventId']);
$clubId = intval($_GET['clubId']);

// if clubId is set
if ($clubId != 0) {

    $clubClause = " AND meet_entries.club_id = $clubId ";

} else {

    $clubClause = "";

}

// Get list of available entrants
$entrants = $GLOBALS['db']->getAll("SELECT member.id, member.firstname, member.surname, IF(member.gender = 1, 'M', 'F') as gender, 
                member.dob, TIMESTAMPDIFF(YEAR,member.dob,DATE(CONCAT(YEAR(CURRENT_DATE()), \"-12-31\"))) as age, clubs.id as clubId, clubs.code, clubs.clubname,
                CASE WHEN (
                  SELECT id
                  FROM meet_events_entries
                  WHERE meet_id = ?
                  AND member_id = member.id
                  AND event_id = ?
                ) IS NOT NULL THEN true
                              ELSE false
                  END AS nominated
                FROM member, clubs, meet_entries 
                WHERE meet_entries.meet_id = ?
                AND meet_entries.club_id = clubs.id
                AND meet_entries.member_id = member.id
                AND member.id NOT IN (SELECT member_id FROM meet_entries_relays, meet_entries_relays_members 
                    WHERE meet_id = ? AND meetevent_id = ?
                    AND meet_entries_relays_members.relay_team = meet_entries_relays.id
                )
                $clubClause
                ORDER BY nominated DESC, member.surname, member.firstname;",
    array($meetId, $eventId, $meetId, $meetId, $eventId));
db_checkerrors($entrants);

// Send JSON Response
header('Content-type: application/json');
$callback = filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_SPECIAL_CHARS);

if (isset($_GET['callback'])) {

    echo $callback . '(' . json_encode($entrants) . ');';

} else {

    echo json_encode($entrants);

}

?>
