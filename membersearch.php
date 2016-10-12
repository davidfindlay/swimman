<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Member.php");
checkLogin();

// Check for search criteria
if (isset($_POST['memberSearchSubmit'])) {
	
	$searchTerms = $_POST['memberSearchCriteria'];
	
	$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);
	
	$results = $GLOBALS['db']->getAll("SELECT CONCAT(a.firstname, ' ', a.othernames, ' ', a.surname) as membername,
			c.code as clubcode, c.clubname as clubname, a.number as msanumber, d.groupname as agegroup, 
			DATE_FORMAT(CURRENT_DATE(), '%Y') - DATE_FORMAT(a.dob, '%Y') - (DATE_FORMAT(CURRENT_DATE(), '00-%m-%d') < DATE_FORMAT(a.dob, '00-%m-%d')) AS age
			FROM member as a, member_memberships as b, clubs as c, age_groups as d
			WHERE a.id = b.member_id
			AND b.club_id = c.id
			AND d.set = 1
			AND d.swimmers = 1
			AND d.gender = a.gender
			AND (DATE_FORMAT(CURRENT_DATE(), '%Y') - DATE_FORMAT(a.dob, '%Y') - (DATE_FORMAT(CURRENT_DATE(), '00-%m-%d') < DATE_FORMAT(a.dob, '00-%m-%d'))) >= d.min
			AND (DATE_FORMAT(CURRENT_DATE(), '%Y') - DATE_FORMAT(a.dob, '%Y') - (DATE_FORMAT(CURRENT_DATE(), '00-%m-%d') < DATE_FORMAT(a.dob, '00-%m-%d'))) <= d.max
			AND a.id IN (
			SELECT id FROM member 
			WHERE MATCH (firstname, othernames, surname) 
			AGAINST ( ? IN BOOLEAN MODE) ORDER BY surname, firstname)
			GROUP BY c.code;", array($searchTerms));
	db_checkerrors($results);
	
	$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ORDERED);
	
	addlog("Member Search", "Searched for $searchTerms");

}

addlog("Access", "Accessed membersearch.php");

htmlHeaders("Member Search");

sidebarMenu();

?>

<div id="main">

<h1>Member Search</h1>

<form method="post">
<p>
<label>Search Criteria: </label>
<input type="text" name="memberSearchCriteria" id="memberSearchCriteria" size="60" <?php echo "value=\"$searchTerms\"" ?> />
<input type="submit" name="memberSearchSubmit" id="memberSearchSubmit" value="Search" />
</p>

</form>

<?php

if (isset($results)) {

	echo "<table width=\"100%\">\n";
	echo "<thead class=\"list\">\n";
	echo "<tr>\n";
	echo "<th>Name</th>\n";
	echo "<th>Club(s)</th>\n";
	echo "<th>MSA Number</th>\n";
	echo "<th>Age Group</th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	
	echo "<tbody>\n";
	
	foreach ($results as $r) {

		$fullname = $r['membername'];
		$clubcode = $r['clubcode'];
		$clubname = $r['clubname'];
		$msa = $r['msanumber'];
		$agegroup = $r['agegroup'];
		$age = $r['age'];
		
		echo "<tr class=\"list\">\n";
		echo "<td>$fullname</td>\n";
		echo "<td>$clubname($clubcode)</td>\n";
		echo "<td>$msa</td>\n";
		echo "<td>$agegroup($age)</td>\n";
		echo "</tr>\n";

	}
	
	echo "</tbody>\n";
	
	echo "</table>\n";

}

?>

</div>

<?php

htmlFooters();

?>
