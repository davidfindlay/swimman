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
$clubs = $GLOBALS['db']->getAll("SELECT * FROM clubs WHERE id IN
                          (SELECT club_id FROM meet_entries WHERE meet_id = ? AND
                          club_id IN (SELECT club_id FROM meet_entries GROUP BY club_id HAVING count(*) > 3))
  ORDER BY clubname ASC;",
    array($meetId));
db_checkerrors($clubs);

// Send JSON Response
header('Content-type: application/json');
$callback = filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_SPECIAL_CHARS);

if (isset($_GET['callback'])) {

    echo $callback . '(' . json_encode($clubs) . ');';

} else {

    echo json_encode($clubs);

}

?>
