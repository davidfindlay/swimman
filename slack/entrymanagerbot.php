<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 19/12/16
 * Time: 4:51 PM
 */

require_once("../includes/setup.php");

// Check for correct token
$token = $GLOBALS['slackToken'];

if ($_POST['token'] != $token) {
    addlog("Entry Manager Bot", "Invalid Token");
    exit();
}

addlog("Entry Manager Bot", "Bot traffic", $_POST['text']);

$text = "";



if (preg_match('/count/', $_POST['text'])) {

    $sql = "SELECT count(*) FROM meet_entries WHERE meet_id = 112;";
    $count = $GLOBALS['db']->getOne($sql);
    db_checkerrors($count);

    $text .= "The MSA National Championships 2017 entry count is $count.\n";

}

if (preg_match('/meals/', $_POST['text'])) {

    $sql = "SELECT sum(meals) FROM meet_entries WHERE meet_id = 112;";
    $count = $GLOBALS['db']->getOne($sql);
    db_checkerrors($count);

    $text .= "The MSA National Championships 2017 presentation dinner ticket count is $count.\n";

}

if (preg_match('/massages/', $_POST['text'])) {

    $sql = "SELECT sum(massages) FROM meet_entries WHERE meet_id = 112;";
    $count = $GLOBALS['db']->getOne($sql);
    db_checkerrors($count);

    $text .= "The MSA National Championships 2017 massage ticket count is $count.\n";

}

echo json_encode(array('text' => $text));
