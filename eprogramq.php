<?php
require_once("includes/setup.php");
require_once("includes/classes/Club.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");
require_once("includes/classes/MeetEntry.php");
require_once("includes/classes/MeetEntryEvent.php");
require_once("includes/classes/MeetSelector.php");
require_once("includes/classes/MeetProgramQ.php");
//checkLogin();

if (isset($_GET['id'])) {
	
	$meetId = mysql_real_escape_string($_GET['id']);
	
}

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"DTD/xhtml1-frameset.dtd\">\n";
echo "<html>\n";
echo "<head>\n";
	
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style/screen.css\">\n";
echo "<script type=\"text/javascript\" src=\"eprogram.js\"></script>\n";
	
echo "<title>eProgram</title>\n";
	
echo "</head>\n";
	
echo "<body>\n";

echo "<div id=\"main\">\n";

echo "<h1>eProgram</h1>\n";

if (!isset($meetId)) {

	addlog("Access", "Accessed eprogramq.php no meet selected.");
	
	echo "<form method=\"get\">\n";
	echo "<label>Choose a Meet: </label>\n";
	
	$meetSel = new MeetSelector;
	$meetSel->setName("id");
	$meetSel->output();
	
	echo "<input type=\"submit\" name=\"loadMeet\" value=\"Load\" />\n";
	
	echo "<br />\n";
	
	echo "</form>\n";
	
	echo "</div>\n";  // Main Div
	
	htmlFooters();

	exit();
}

addlog("Access", "Accessed eprogramq.php meet $meetId");

$meetDetails = new Meet();
$meetDetails->LoadMeet($meetId);
$meetName = $meetDetails->getName();
$meetStartDate = new DateTime($meetDetails->getStartDate());
$meetStartDateText = $meetStartDate->format('l jS \of F, Y');
$meetEndDate = $meetDetails->getEndDate();

$eProgram = new MeetProgram();
$eProgram->setMeet($meetId);
$eProgram->setUrl("eprogramq.php");

echo "<h2>QLD State Championships 2014</h2>\n";
echo "<h3>$meetStartDateText</h3>\n";

echo "<p><strong>Note:</strong> The QLD State Championships is being held concurrently with ";
echo "the National Championships. This eProgram view shows only QLD entries and results. For ";
echo "National results select the National Championships 2014 option from the left hand menu.</p>\n";

if ($eProgram->exists()) {
	
	$eProgram->load();
	$filename = $eProgram->getFilename();
	list($version, $updated) = $eProgram->getUpdated();
	
	echo "<p>\n";
	echo "<a href=\"eprogramq.php?id=$meetId&byheat=1\">List by Heat</a>";
	echo " | <a href=\"eprogramq.php?id=$meetId&byage=1\">List by Age Group</a>";
	echo " | <a href=\"eprogramq.php?id=$meetId&teamscores=1\">Team Scores</a>\n"; 
	echo " | <a href=\"eprogramq.php?id=$meetId&individualProg=1\">Individual Program</a>\n";
	echo " | <a href=\"eprogramq.php?id=$meetId&teamProg=1\">Team Program</a>\n";
	echo "</p>\n";
	
	
	if (isset($_GET['byheat'])) {
	
		$eProgram->outputProgram('byheat');
		
	} elseif (isset($_GET['byage'])) {
		
		$eProgram->outputProgram('byage');
		
	} elseif (isset($_GET['teamscores'])) {
		
		$eProgram->outputTeamScores();
		
	} elseif (isset($_GET['individualProg'])) {
		
		$ath_no = 0;
		
		if (isset($_GET['member'])) {
		
			$progMember = $_GET['member'];
			
		}
		
		if (isset($_GET['ath_no'])) {
			
			$ath_no = $_GET['ath_no'];
			$progMember = 0;
			
		}
		
		// Get swimmer selection drop down box
		echo "<form method=\"get\" action=\"eprogramq.php?id=$meetId&individualProg=1\">\n";
		echo "<p>\n";
		
		echo "<input type=\"hidden\" name=\"id\" value=\"$meetId\" />\n";
		echo "<input type=\"hidden\" name=\"individualProg\" value=\"1\" />\n";
		
		echo "<label for=\"memSelector\">Select a Member:</label>\n";
		$eProgram->outputMemberSelector("member", "memberSelector");
		echo " <input type=\"submit\" name=\"selectSubmit\" value=\"Show Individual Program\" />\n";
		echo "</p>\n";
		
		echo "</form>\n";

		if (!isset($progMember) && $ath_no = 0) {
		
			$progMember = 0;

			echo "<p>To view your individual eProgram, select your name from the drop down box above.</p>\n";
		
		}
		
		$eProgram->outputIndividualProgram($progMember, $ath_no);
	
		
	} elseif (isset($_GET['teamProg'])) {
		
		// Get swimmer selection drop down box
		echo "<form method=\"get\" action=\"eprogramq.php?id=$meetId&teamProg=1\">\n";
		echo "<p>\n";
		
		echo "<input type=\"hidden\" name=\"id\" value=\"$meetId\" />\n";
		echo "<input type=\"hidden\" name=\"teamProg\" value=\"1\" />\n";
		
		echo "<label for=\"memSelector\">Select a Team:</label>\n";
		$eProgram->outputTeamSelector("team", "teamSelector");
		echo " <input type=\"submit\" name=\"selectSubmit\" value=\"Show Team Program\" />\n";
		echo "</p>\n";
		
		echo "</form>\n";
		
		if (isset($_GET['team'])) {
		
			$progTeam = $_GET['team'];
			
		}
		
		if (!isset($progTeam)) {
		
			$progTeam = 0;
		
			echo "<p>To view your team eProgram, select your team from the drop down box above.</p>\n";
		
		}
		
		$eProgram->outputTeamProgram($progTeam);
		
	} else {
		
		$eProgram->outputProgram('byheat');
		
	}
	
	echo "<p>\n";
	echo "The results listed in eProgram are unofficial results and do not necessarily match the \n";
	echo "official results that will appear in the Results Portal. If you have any questions about \n";
	echo "your results, please check the official posting at the pool or speak to the Meet Director. \n";
	echo "</p>\n";

} else {
	
	echo "<p>No eProgram is available for this meet yet. Please check again later.</p>\n";
	
}	

echo "</div>\n";  // Main Div



htmlFooters();

?>
