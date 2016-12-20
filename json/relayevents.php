<?php

// JSON Web Service
// Returns a list of Meets available for viewing via eProgram

require_once("../includes/setup.php");
require_once("../includes/classes/Meet.php");

checkLogin();

// Set up the Associative Array fetch mode
$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);

if (isset($_GET['meetId'])) {

    $meetId = $_GET['meetId'];

}

// Get list of available events
$events = $GLOBALS['db']->getAll("SELECT a.id, a.prognumber, a.progsuffix, a.legs, c.distance, 
              CASE d.gender WHEN 1 THEN 'Men\'s' WHEN 2 THEN 'Women\'s' WHEN 3 THEN 'Mixed' END as gender, 
              b.discipline
              FROM meet_events as a, event_disciplines as b, event_distances as c, event_types as d
              WHERE meet_id = ? AND legs > 1 
              AND c.id = a.distance AND b.id = a.discipline AND d.id = a.type ORDER BY prognumber ASC;",
    array($meetId));
db_checkerrors($events);

// Send JSON Response
header('Content-type: application/json');
$callback = filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_SPECIAL_CHARS);

if (isset($_GET['callback'])) {

    echo $callback . '(' . json_encode($events) . ');';

} else {

    echo json_encode($events);

}

?>
