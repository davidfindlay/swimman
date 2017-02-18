<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 18/02/17
 * Time: 3:48 PM
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/setup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEventEntryReport.php');

checkLogin();

$report = new MeetEventEntryReport();
$json = $report->get();

// Send JSON Response
header('Content-type: application/json');
$callback = filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_SPECIAL_CHARS);

if (isset($_GET['callback'])) {

    echo $callback . '(' . json_encode($json) . ');';

} else {

    echo json_encode($json);

}