<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 18/02/17
 * Time: 3:48 PM
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/setup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/classes/MeetEventEntryReport.php');

checkLogin();

$report = new MeetEventEntryReport();
$json = $report->get();

echo $json;