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
$teamId = intval($_GET['teamId']);

$relayTeams = array();

// Get list of available entrants
// Get list of relay entries for this meet
if (isset($meetId) && isset($eventId)) {

    if ($teamId != 0) {
        $teamClause = " AND a.id = $teamId ";
    }

    $relayEntries = $GLOBALS['db']->getAll("SELECT a.id, b.meetname, c.id as clubid, c.code, c.clubname, d.prognumber, 
			d.progsuffix, e.groupname, a.letter, a.teamname
			FROM meet_entries_relays AS a, meet AS b, clubs AS c, meet_events AS d, age_groups AS e
			WHERE b.id = a.meet_id AND c.id = a.club_id AND a.meetevent_id = d.id 
			AND e.set = 1 AND a.agegroup = e.id
			AND a.meet_id = ? AND a.meetevent_id = ? $teamClause;", array($meetId, $eventId));
    db_checkerrors($relayEntries);

    foreach ($relayEntries as $r) {

        // Get relay members
        $relayMembers = $GLOBALS['db']->getAll("SELECT member.id, member.firstname, member.surname,  IF(member.gender = 1, 'M', 'F') as gender, 
                    TIMESTAMPDIFF(YEAR,member.dob,DATE(CONCAT(YEAR(CURRENT_DATE()), \"-12-31\"))) as age, 
                    member.dob, meet_entries_relays_members.leg
                    FROM meet_entries_relays_members, member 
                    WHERE relay_team = ? AND meet_entries_relays_members.member_id = member.id 
                    ORDER BY leg ASC;",
            array($r['id']));
        db_checkerrors($relayMembers);

        $team = array();
        $team['id'] = $r['id'];
        $team['clubid'] = $r['clubid'];
        $team['code'] = $r['code'];
        $team['clubname'] = $r['clubname'];
        $team['groupname'] = stripslashes($r['groupname']);
        $team['letter'] = $r['letter'];
        $team['teamname'] = $r['teamname'];

        foreach ($relayMembers as $m) {

            $legNo = $m['leg'];

            $team['swimmer' . $legNo . 'id'] = $m['id'];
            $team['swimmer' . $legNo . 'name'] = stripslashes($m['firstname'] . ' ' . $m['surname']);
            $team['swimmer' . $legNo . 'gender'] = $m['gender'];
            $team['swimmer' . $legNo . 'age'] = $m['age'];

        }

        $relayTeams[] = $team;

    }

}

// Send JSON Response
header('Content-type: application/json');
$callback = filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_SPECIAL_CHARS);

if (isset($_GET['callback'])) {

    echo $callback . '(' . json_encode($relayTeams) . ');';

} else {

    echo json_encode($relayTeams);

}

?>
