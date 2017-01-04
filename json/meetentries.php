<?php

// JSON Web Service
// Returns a list of Meets available for viewing via eProgram

require_once("../includes/setup.php");

checkLogin();

// Set up the Associative Array fetch mode
$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);

$meetId = intval($_GET['meet']);

$relayTeams = array();

// Get list of available entrants
// Get list of relay entries for this meet
if (isset($meetId)) {

    $sql = "SELECT
    meet_entries.id,
    member.firstname,
    member.surname,
    member.number,
    age_groups.groupname,
    clubs.code,
    clubs.clubname,
    meet_entries.meals,
    meet_entries.massages,
    meet_entries.programs,
    CONCAT('$', FORMAT(meet_entries.cost, 2)) as cost,
    CONCAT('$', FORMAT((SELECT sum(amount) FROM meet_entry_payments WHERE entry_id = meet_entries.id), 2)) AS paid,
    meet_entries.cancelled,
    (SELECT 
            meet_entry_status_codes.label
        FROM 
            meet_entry_statuses 
            , meet_entry_status_codes  
        WHERE 
            meet_entry_statuses.code = meet_entry_status_codes.id
            AND meet_entry_statuses.entry_id = meet_entries.id
        ORDER BY meet_entry_statuses.id DESC
        LIMIT 1
    ) AS status,
    COUNT(DISTINCT meet_events_entries.id) AS entries
FROM 
    meet_entries                    
    , meet_events_entries           
    , member                        
    , clubs
    , age_groups                         
WHERE 
    meet_entries.meet_id = ?
    AND meet_entries.id = meet_events_entries.meet_entry_id
    AND meet_entries.member_id = member.id
    AND meet_entries.club_id = clubs.id
    AND YEAR(CURDATE()) - YEAR(member.dob) >= age_groups.min AND YEAR(CURDATE()) - YEAR(dob) <= age_groups.max
    AND member.gender = age_groups.gender
    AND age_groups.swimmers = 1
GROUP BY meet_entries.id
; ";

    $meetEntries = $GLOBALS['db']->getAll($sql, array($meetId));
    db_checkerrors($meetEntries);

}

// Send JSON Response
header('Content-type: application/json');
$callback = filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_SPECIAL_CHARS);

//$entries = array('entries' => $meetEntries);
$entries = $meetEntries;

if (isset($_GET['callback'])) {

    echo $callback . '(' . json_encode($entries) . ');';

} else {

    echo json_encode($entries);

}

?>
