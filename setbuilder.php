<?php
require_once("includes/setup.php");
require_once("includes/classes/TrainingSet.php");
require_once("includes/classes/TrainingSetItem.php");
checkLogin();

if (isset($_POST['setCreate'])) {

	$author = 0;
	$title = $_POST['setTitle'];
	$desc = $_POST['setDesc'];
	$type = $_POST['setType'];

	$nSet = new TrainingSet();
	$nSet->setTitle($title);
	$nSet->setDesc($desc);
	$nSet->setType($type);

	if (isset($_POST['setPublish'])) {
		
		$nSet->publish();
		
	}
	
	$nSet->store();
	
	$setCreate = 1;
	
}

htmlHeaders("Swimming Management System - Import IMG Members");

sidebarMenu();

if (isset($_GET['id'])) {

	$setId = mysql_real_escape_string($_GET['id']);
	
	$psSet = new TrainingSet();
	$psSet->load($setId);

}

// Load data

$psSetTitle = $psSet->getTitle();
$psSetDesc = $psSet->getDescription();;
$psSetDist = 0;
$psModifier = '';
$psTimeMin = '';
$psTimeSec = '';

echo "<div id=\"main\">\n";

echo "<h1>Set Builder</h1>\n";


echo "<form method=\"post\">\n";

echo "<fieldset>\n";
echo "<p>\n";
echo "<label for=\"setTitle\">Title: </label>\n";
echo "<input type=\"text\" name=\"setTitle\" id=\"setTitle\" value=\"$psSetTitle\" /><br />\n";

echo "<label for=\"setDesc\">Description: </label>\n";
echo "<textarea rows=\"4\" cols=\"80\" name=\"setDesc\" id=\"setDesc\">\n";

echo "</textarea><br />\n";

echo "<label>Type: </label>\n";

$setTypes = $GLOBALS['db']->getAll("SELECT * FROM training_set_types;");
db_checkerrors($setTypes);

echo "<select name=\"setType\">\n";

foreach ($setTypes as $s) {

	$typeId = $s[0];
	$typeName = $s[1];
	echo "<option value=\"$typeId\">$typeName</option>\n";

}

echo "</select><br />\n";

echo "<label>Publish: </label>\n";
echo "<input type=\"checkbox\" name=\"setPublish\" id=\"setPublish\" />\n";
echo "</p>\n";

echo "<p>\n";
echo "<input type=\"submit\" name=\"setCreate\" value=\"Create Set\" />\n";
echo "</p>\n";

echo "</fieldset>\n";

if (isset($variant)) {

	echo "<fieldset>\n";
	
	echo "<h3>Variant 1</h3>\n";
	echo "<label>Total Distance:</label> ";
	echo "$psSetDist<br />\n";
	
	echo "</fieldset>\n";

}

if (isset($setCreate)) {

	echo "<fieldset>\n";
	
	echo "<p>\n";
	
	echo "<table width=\"100%\" border=\"0\" class=\"list\">\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th>\n";
	echo "Order\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Repeats\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Distance\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Discipline\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Modifier\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "Time\n";
	echo "</th>\n";
	echo "<th>\n";
	echo "</th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";
	echo "<tr id=\"setBuilderNew\">\n";
	echo "<td>\n";
	
	echo "</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" size=\"2\" name=\"newRepeats\" id=\"newRepeats\" />\n";
	echo "</td>\n";
	echo "<td>\n";
	echo "<input type=\"text\" size=\"3\" name=\"newDist\" id=\"newDist\" />m\n";
	echo "</td>\n";
	echo "<td>\n";
	
	// List training disciplines
	$trainDiscGroups = $GLOBALS['db']->getAll("SELECT * FROM training_disciplines_groups;");
	db_checkerrors($trainDiscGroups);
	
	echo "<select name=\"newDisc\" id=\"newDisc\">\n";
	
	foreach ($trainDiscGroups as $g) {
	
		$discGroupId = $g[0];
		$discGroupName = $g[1];
		
		echo "<optgroup label=\"$discGroupName\">\n";
	
		// Get items in group
		$trainDiscItems = $GLOBALS['db']->getAll("SELECT * FROM training_disciplines WHERE id IN (SELECT disc_id FROM training_disciplines_groups_links WHERE group_id = '$discGroupId');");
		db_checkerrors($trainDiscItems);
		
		foreach ($trainDiscItems as $i) {
		
			$discId = $i[0];
			$discName = $i[1];
			
			echo "<option value=\"$discId\">$discName</option>\n";
		
		}
		
		echo "</optgroup>\n";
	
	}
	
	echo "</select>\n";
	
	
	echo "</td>\n";
	echo "<td>\n";
	
	echo "<input type=\"text\" name=\"newModifier\" id=\"newModifier\" value=\"$psModifier\" />\n";
	
	echo "</td>\n";
	echo "<td>\n";
	
	echo "<input type=\"text\" size=\"2\" name=\"newTimeMin\" id=\"newTimeMin\" value=\"$psTimeMin\" />\n";
	echo ":\n";
	echo "<input type=\"text\" size=\"2\" name=\"newTimeSec\" id=\"newTimeSec\" value=\"$psTimeSec\" />\n";
	
	echo "<select name=\"newTimeType\" id=\"newTimeType\">\n";
	
	echo "<option value=\"notime\"></option>\n";
	echo "<option value=\"int\">Interval</option>\n";
	echo "<option value=\"rest\">Rest</option>\n";
	
	echo "</select>\n";
	
	echo "</td>\n";
	echo "<td>\n";
	echo "<a href=\"#\"><img src=\"images/ok.png\" alt=\"Save\" /></a>\n";
	echo "</td>\n";
	echo "<tr>\n";
	echo "</tbody>\n";
	echo "</table>\n";
	
	echo "</p>\n";
	
	
	echo "</fieldset>\n";

}
echo "</form>\n";

echo "</div>\n"; // main div

htmlFooters();

?>