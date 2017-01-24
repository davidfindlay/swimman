<?php
/**
 * @version		$Id: default.php 15 2009-11-02 18:37:15Z chdemko $
 * @package		Joomla16.Tutorials
 * @subpackage	Components
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @author		Christophe Demko
 * @link		http://joomlacode.org/gf/project/entrymanager_1_6/
 * @license		License GNU General Public License version 2 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/setup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Club.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Meet.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Member.php');


// Get Joomla User ID
$curJUser = JFactory::getUser();
$curUserId = $curJUser->id;
$curUsername = $curJUser->username;

// Look up Swimman DB to see if this user is linked to a member
$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite WHERE joomla_uid = '$curUserId';");
db_checkerrors($memberId);

$member = new Member;
$member->loadId($memberId);
$memberFullname = $member->getFullname();
$memberClubs = $member->getClubIds();

echo "<h1>My MSX Results</h1>\n";

echo "<style type=\"text/css\">\n";
echo "label {\n";
echo "	font-weight: bold;\n";
echo "	width: 12em;\n";
echo "	float: left;\n";
echo "}\n\n";
echo "th {\n";
echo "  padding-left: 5px;\n";
echo "  padding-right: 5px;\n";
echo "}\n";
echo "td {\n";
echo "  padding-left: 5px;\n";
echo "  padding-right: 5px;\n";
echo "}\n";
echo "</style>\n";

echo "<p>\n";
echo "This page shows your MSX program results for 2016. Any award you have one is displayed \n";
echo "below. The qualifying swims are also shown. \n";
echo "</p>\n";

echo "<p>\n";
echo "<strong>2016 MSX Results are currently at draft stage only.</strong> ";
echo "If you believe some qualifying swims are missing, please contact the <a href=\"mailto:recorder@mastersswimmingqld.org.au\">State Recorder</a>. \n";
echo "</p>\n";

echo "<h2>2016 MSX Program</h2>\n";

$perf_prog_id = 4;

$award = $GLOBALS['db']->getOne("SELECT l.levelname FROM performance_programs_awards as a, 
			performance_programs_levels as l
			WHERE a.member_id = ?
			AND a.level = l.id
			AND a.perf_prog_id = ?;", array($memberId, $perf_prog_id));
db_checkerrors($award);

echo "<p>\n";
echo "<label>My MSX Award Level: </label>\n";

if (isset($award)) {
	
	echo "$award\n";
	
} else {
	
	echo "Sorry, you did not achieve an MSX award this year.";
	
}

echo "</p>\n";

echo "<h3>My Qualifying Swims:</h3>\n";

echo "<p>Here are listed any of your swims that met an MSX time standard. To acheive a ";
echo "particular award level, you needed to acheive three qualifying swims at that level.</p>\n";

$levels = $GLOBALS['db']->getAll("SELECT * FROM performance_programs_levels 
				WHERE perf_prog_id = ?", array($perf_prog_id));
db_checkerrors($levels);

foreach ($levels as $l) {

	$qualSwims = $GLOBALS['db']->getAll("SELECT r.meet_name, r.perf_prog_event, r.time,
			r.perf_prog_std
			FROM performance_programs_results as r
			WHERE r.perf_prog_id = ? AND r.member_id = ?
			AND r.level = ?;", array($perf_prog_id, $memberId, $l[0]));
	db_checkerrors($qualSwims);
	
	$levelName = $l[2];
	echo "<p>\n";
	echo "<h4>$levelName</h4>\n";
	
	if (count($qualSwims) > 0) {
		
		echo "<table border=\"1\" width=\"100%\">\n";
		echo "<thead>\n";
		echo "<tr>\n";
		echo "<td><strong>Meet</strong></td>\n";
		echo "<td><strong>Event</strong></td>\n";
		echo "<td><strong>My Time</strong></td>\n";
		echo "<td><strong>Required Standard</strong></td>\n";
		echo "</tr>\n";
		echo "</thead>\n";
		
		echo "<tbody>\n";
		
		foreach ($qualSwims as $q) {
			
			$qMeet = $q[0];
			$qEventId = $q[1];
			$qTime = $q[2];
			$qEventStd = $q[3];
			
			$eventName = $GLOBALS['db']->getOne("SELECT CONCAT(d.distance, ' ', e.discipline) 
					FROM event_disciplines as e, event_distances as d, 
					performance_programs_events as a
					WHERE a.id = ?
					AND a.discipline = e.id
					AND a.distance = d.id;", array($qEventId));
			db_checkerrors($eventName);
			
			$eventStandard = $GLOBALS['db']->getOne("SELECT hightime 
					FROM performance_programs_stds
					WHERE id = ?", array($qEventStd));
			db_checkerrors($eventStandard);
			
			echo "<tr>\n";
			
			echo "<td>$qMeet</td>\n";
			echo "<td>$eventName</td>\n";
			echo "<td>" . sw_formatSecs($qTime) . "</td>\n";
			echo "<td>" . sw_formatSecs($eventStandard) . "</td>\n";
			
			echo "</tr>\n";
			
		}
		
		echo "</tbody>\n";
		
		echo "</table>\n";
		
	} else {
		
		echo "<p>You did not acheive any qualifying swims at this level.</p>\n";
		
	}
	
	echo "</p>\n";
	
}

?>