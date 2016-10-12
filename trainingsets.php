<?php
require_once("includes/setup.php");
require_once("includes/classes/TrainingSet.php");
require_once("includes/classes/TrainingSetItem.php");
checkLogin();

htmlHeaders("Swimming Management System - Import IMG Members");

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Training Sets</h1>\n";

// Get list of training sets
$setList = $GLOBALS['db']->getAll("SELECT * FROM training_sets;");
db_checkerrors($setList);

echo "<table width=\"100%\" class=\"list\">\n";
echo "<thead class=\"list\">\n";
echo "<tr class=\"list\">\n";
echo "<th>Published</th>\n";
echo "<th>Author</th>\n";
echo "<th>Type</th>\n";
echo "<th>Title</th>\n";
echo "<th>Date Created</th>\n";
echo "<th>Contols</th>\n";
echo "</tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

foreach ($setList as $s) {
	
	$setDetails = new TrainingSet();
	$setDetails->setRow($s);
	$sId = $setDetails->getId();
	$sPublished = $setDetails->getPublished();
	$sAuthor = $setDetails->getAuthor();
	$sTitle = $setDetails->getTitle();
	$sType = $setDetails->getTypeText();
	$sDateCreated = $setDetails->getCreated();
	
	
	echo "<tr class=\"list\">\n";
	echo "<td>\n";
	
	echo "</td>\n";
	echo "<td>\n";
	if (isset($sAuthor)) {
		
		echo $sAuthor;
		
	} else {

		echo "Admin\n";
		
	}
	
	echo "</td>\n";
	echo "<td>\n";
	echo $sType;
	echo "</td>\n";
	echo "<td>\n";
	echo $sTitle;
	echo "</td>\n";
	echo "<td>\n";
	echo $sDateCreated;
	echo "</td>\n";
	echo "<td>\n";
	echo "<a href=\"setbuilder.php?id=$sId\">Edit</a>\n";
	echo "</td>\n";
	echo "</tr>\n";
	
}

echo "</tbody>\n";
echo "</table>\n";

echo "<p>\n";
echo "<a href=\"setbuilder.php\">Add a New Set</a>\n";
echo "</p>";

echo "</div>\n"; // main div

htmlFooters();

?>