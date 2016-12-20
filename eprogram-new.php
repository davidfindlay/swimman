<?php 

// Find out what the latest meet with a program is
// Database include
require_once ("DB.php");
require_once ("/hsphere/local/home/davsoft/forum.mastersswimmingqld.org.au/swimman/config.php");
global $db;

$dbuser = $GLOBALS['dbuser'];
$dbpass = $GLOBALS['dbpass'];
$dbhost = $GLOBALS['dbhost'];
$dbport = $GLOBALS['dbport'];
$dbname = $GLOBALS['dbname'];
	
$dsn = "mysql://$dbuser:$dbpass@$dbhost:$dbport/$dbname";

$GLOBALS['db'] =& DB::connect($dsn);
	
// Set correct timezone for all operations
$result = $GLOBALS['db']->query("SET time_zone = '+10:00';");

$latestMeet = $GLOBALS['db']->getOne("SELECT meet_id FROM meet_programs ORDER BY meet_id DESC LIMIT 1");

?>

<html>
<head>
<meta charset="UTF-8" />
<meta name='viewport' content='width=device-width, user-scalable=no,
    initial-scale=1, maximum-scale=1, minimum-scale=1'/>
<link rel="stylesheet" href="/swimman/style/eprogram-screen.css" type="text/css" media="screen" />
<link rel="stylesheet" href="/swimman/style/eprogram-mobile.css" type="text/css" media="only screen and (max-device-width:480px)" />
<link rel="stylesheet" href="/swimman/style/jquery.mobile-1.4.5.min.css" type="text/css" />
<script type="text/javascript" src="/swimman/includes/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="/swimman/includes/jquery.mobile-1.4.5.min.js"></script>
<script type="text/javascript">

    $body = $("body");
    $(document).on({
        ajaxStart: function() { $body.addClass("loading");    },
        ajaxStop: function() { $body.removeClass("loading"); }
    });

	// Get ordinal number function by Chris West 
	// http://cwestblog.com/2012/09/28/javascript-number-getordinalfor/
	(function(o) {
	  Number.getOrdinalFor = function(intNum, includeNumber) {
	    return (includeNumber ? intNum : "")
	      + (o[((intNum = Math.abs(intNum % 100)) - 20) % 10] || o[intNum] || "th");
	  };
	})([,"st","nd","rd"]);

	function refreshEvents() {

		// Clear options
		$('#event > option').remove();

		if ($("#meet").val() == null) {

			var meetId = <?php echo $latestMeet; ?>;
			
		} else {

			var meetId = $("#meet").val();
			
		}
		
		// Load Event List
		var requestData = {
			'action' : "geteventlist",
			'meet'   : meetId
		};
    	
		$.ajax({
			type	: 'GET',
			url		: 'xmltest.php',
			data	: requestData,
			datatype: 'xml',
			encode	: true
		})
		
		.done (function(data) {

			// Render data
			$(data).find('event').each(function(){

				var id = $(this).attr("id");
				var eventProg = $(this).find('number').text();
				var eventLegs = $(this).find('legs').text();
				var eventDist = $(this).find('metres').text();
				var eventDisc = $(this).find('disciplineabrev').text();

				if (eventLegs > 1) {

					var eventDetails = eventLegs + "x" + eventDist + "m " + eventDisc;
					
				} else {

					var eventDetails = eventDist + "m " + eventDisc;
					
				}
				
				$("#event").append("<option value='" + eventProg + "'>Event " + eventProg + 
						" - " + eventDetails + "</option>");
				$("#event").selectmenu('refresh');
				
			});	

		});

		refreshHeats();
		
	}

	function refreshHeats() {

		// Clear options
		$('#heat > option').remove();

		if ($("#meet").val() == null) {

			var meetId = <?php echo $latestMeet; ?>;
			
		} else {

			var meetId = $("#meet").val();
			
		}

		if ($("#event").val() == null) {

			var event = 1;
			
		} else {

			var event = $("#event").val();
			
		}
		
		// Load Event List
		var requestData = {
			'action' : "getheatlist",
			'meet'   : meetId,
			'event'  : event
		};
    	
		$.ajax({
			type	: 'GET',
			url		: 'xmltest.php',
			data	: requestData,
			datatype: 'xml',
			encode	: true
		})
		
		.done (function(data) {

			// Render data
			$(data).find('heat').each(function(){

				var id = $(this).attr('number');
				var heatNum = id;
				
				$("#heat").append("<option value='" + id + "'>Heat " + heatNum + "</option>");
				$("#heat").selectmenu('refresh');
				
			});	

		});

		$('#heat option:first').attr("selected", "selected");
		refreshAgeGroups();
		
	}

	function refreshAgeGroups() {

		// Clear options
		$('#agegroup > option').remove();

		if ($("#meet").val() == null) {

			var meetId = <?php echo $latestMeet; ?>;
			
		} else {

			var meetId = $("#meet").val();
			
		}

		if ($("#event").val() == null) {

			var event = 1;
			
		} else {

			var event = $("#event").val();
			
		}
		
		// Load Event List
		var requestData = {
			'action' : "getagegrouplist",
			'meet'   : meetId,
			'event'  : event
		};
    	
		$.ajax({
			type	: 'GET',
			url		: 'xmltest.php',
			data	: requestData,
			datatype: 'xml',
			encode	: true
		})
		
		.done (function(data) {

			// Render data
			$(data).find('agegroup').each(function(){

				var id = $(this).find("id").text();
				var ageGroup = $(this).find("name").text();
				
				$("#agegroup").append("<option value='" + id + "'>" + ageGroup + "</option>");
				$("#agegroup").selectmenu('refresh');
				
			});	

		});

		$('#agegroup option:first').attr("selected", "selected");
		//showHeat();
		
	}

	function showHeat(heatNum) {

		// Clear tables
		$('#heatDetails > tbody > tr').remove();

		if ($("#meet").val() == null) {

			var meetId = <?php echo $latestMeet; ?>;
			
		} else {

			var meetId = $("#meet").val();
			
		}

		if ($("#event").val() == null) {

			var event = 1;
			
		} else {

			var event = $("#event").val();
			
		}
		
		if (heatNum != null) {

			var heat = heatNum;
			
		} else {

			var heat = $("#heat ").val();
			
		}
		
		var formData = {
			'action' : "getheat",
			'meet' : meetId,
			'event' : event,
			'heat' : heat
		};

		// Make request
		$.ajax({
			type	: 'GET',
			url		: 'xmltest.php',
			data	: formData,
			datatype: 'xml',
			encode	: true
		})
		
		.done (function(data) {

			// Render data
			var heatNum = $(data).find('heat').attr('number');
			var prevHeat = parseInt(heatNum) - 1;
			var nextHeat = parseInt(heatNum) + 1;

			if (prevHeat <= 0) {
				prevHeat = 1;
			}

			var heatLabel = "<a href=\"#\" onclick=\"selectDisplay()\">" 
				+ "<img src=\"/swimman/images/left.png\" height=\"20\" width=\"20\" id=\"leftarrow\" /></a>"
				+ " Heat " + heatNum
				+ "<a href=\"#\" onclick=\"showHeat(" + prevHeat + ")\">"
				+ "<img src=\"/swimman/images/up.png\" height=\"20\" width=\"20\" />"
				+ "</a> <a href=\"#\" onclick=\"showHeat(" + nextHeat + ")\">"
				+ "<img src=\"/swimman/images/down.png\" height=\"20\" width=\"20\" /></a>";
			
			$('#heatLabel').html(heatLabel);
			
			$(data).find('entry').each(function(){

				var lane = $(this).find('lane').text();
				var swimmer = $(this).find('fullname').text();
				var agegroup = $(this).find('agegroup').text();
				var age = $(this).find('age').text();
                var seedtime = $(this).find('seedtime').text();
				var finaltime = $(this).find('finaltime').text();
				var clubname = $(this).find('clubname').text();
				var heatplace = Number.getOrdinalFor($(this).find('heatplace').text(), true);
				var ageplace = Number.getOrdinalFor($(this).find('ageplace').text(), true);
				var points = $(this).find('points').text();

				if (finaltime != "") {

					$('<tr></tr>').html(
						'<td class=\"resultRow\"><div class=\"lblLane\">Lane</div><div class=\"numLane\">'+lane+
						'</div></td><td class=\"resultRow\"><div class=\"swimmerName\">'
						+swimmer+'</div><div class=\"ageGroup\">'
						+agegroup+'</div><div class=\"swimmerAge\">('
						+age+')</div><div class=\"clubName\">'
						+clubname+'</div></td><td class=\"resultRow\"><div class=\"finalTime\">'
						+finaltime+'</div></td>')
						.appendTo("#heatDetails");

					$('<tr></tr>').html(
							'<td class=\"placeCell\" colspan=\"3\">' +
                                '<strong>Seed Time: </strong>' + seedtime +
							'<strong>&nbsp;&nbsp;Heat: </strong>'
							+heatplace+'<strong>&nbsp;&nbsp;Age: </strong>'
							+ageplace+'<strong>&nbsp;&nbsp;Points: </strong>'
							+points+
							'</td>')
							.appendTo("#heatDetails");

					

				} else {

					$('<tr></tr>').html(
							'<td class=\"resultRow\"><div class=\"lblLane\">Lane</div><div class=\"numLane\">'+lane+
							'</div></td><td class=\"resultRow\"><div class=\"swimmerName\">'
							+swimmer+'</div><div class=\"ageGroup\">'
							+agegroup+'</div><div class=\"swimmerAge\">('
							+age+')</div><div class=\"clubName\">'
							+clubname+'</div></td><td class=\"resultRow\">'
                            +'</td><td class=\"resultRow\"> </td>')
							.appendTo("#heatDetails");

                    $('<tr></tr>').html(
                        '<td class=\"placeCell\" colspan=\"3\">' +
                        '<strong>Seed Time: </strong>' + seedtime +
                        '</td>')
                        .appendTo("#heatDetails");
					
				}
				
			});	

		});

		// Scroll to right
		
		var windowWidth = $(window).width();
		
		if (windowWidth < 700) {

			$('#resultDisplay').show();
			$('#resultSelector').hide();

		}
		
	}

	function showAgeGroup() {

		// Clear tables
		$('#heatDetails > tbody > tr').remove();

		if ($("#meet").val() == null) {

			var meetId = <?php echo $latestMeet; ?>;
			
		} else {

			var meetId = $("#meet").val();
			
		}

		if ($("#event").val() == null) {

			var event = 1;
			
		} else {

			var event = $("#event").val();
			
		}
		
		var agegroup = $("#agegroup ").val();
		
		var formData = {
			'action' : "getagegroup",
			'meet' : meetId,
			'event' : event,
			'agegroup' : agegroup
		};

		// Make request
		$.ajax({
			type	: 'GET',
			url		: 'xmltest.php',
			data	: formData,
			datatype: 'xml',
			encode	: true
		})
		
		.done (function(data) {

			// Render data
			var groupName = $(data).find('agegroup').attr('name');

			var heatLabel = "<a href=\"#\" onclick=\"selectDisplay()\">"
				+ "<img src=\"/swimman/images/left.png\" height=\"20\" width=\"20\"  id=\"leftarrow\" /> "
				+ "</a>" 
				+ groupName;
			
			$('#heatLabel').html(heatLabel);
			
			$(data).find('entry').each(function(){

				var lane = $(this).find('lane').text();
				var heatnumber = $(this).find('heat').text();
				var swimmer = $(this).find('fullname').text();
				var agegroup = $(this).find('agegroup').text();
				var age = $(this).find('age').text();
                var seedtime = $(this).find('seedtime').text();
				var finaltime = $(this).find('finaltime').text();
				var clubname = $(this).find('clubname').text();
				var heatplace = Number.getOrdinalFor($(this).find('heatplace').text(), true);
				var ageplace = Number.getOrdinalFor($(this).find('ageplace').text(), true);
				var points = $(this).find('points').text();

				if (finaltime != "") {

					$('<tr></tr>').html(
						'<td class=\"resultRow\"><div class=\"lblLane\">Place</div><div class=\"numLane\">'+ageplace+
						'</div></td><td class=\"resultRow\"><div class=\"swimmerName\">'
						+swimmer+'</div><div class=\"ageGroup\">'
						+agegroup+'</div><div class=\"swimmerAge\">('
						+age+')</div><div class=\"clubName\">'
						+clubname+'</div></td><td class=\"resultRow\"><div class=\"finalTime\">'
						+finaltime+'</div></td>')
						.appendTo("#heatDetails");

					$('<tr></tr>').html(
							'<td class=\"placeCell\" colspan=\"3\">' +
                                '<strong>Seed Time: </strong>' + seedtime +
							'<strong>&nbsp;&nbsp;Heat Number: </strong>'
							+heatnumber+'<strong>&nbsp;&nbsp;Heat Place: </strong>'
							+heatplace+'<strong>&nbsp;&nbsp;Points: </strong>'
							+points+
							'</td>')
							.appendTo("#heatDetails");

					

				} else {

					$('<tr></tr>').html(
							'<td class=\"resultRow\"> </td><td class=\"resultRow\"><div class=\"swimmerName\">'
							+swimmer+'</div><div class=\"ageGroup\">'
							+agegroup+'</div><div class=\"swimmerAge\">('
							+age+')</div><div class=\"clubName\">'
							+clubname+'</div></td><td class=\"resultRow\">'
							+'</td><td class=\"resultRow\"> </td>')
							.appendTo("#heatDetails");

                    $('<tr></tr>').html(
                        '<td class=\"placeCell\" colspan=\"3\">' +
                        '<strong>Seed Time: </strong>' + seedtime +
                        '</td>')
                        .appendTo("#heatDetails");
					
				}
				
			});	

		});

		// Scroll to right
		
		var windowWidth = $(window).width();
		
		if (windowWidth < 700) {

			$('#resultDisplay').show();
			$('#resultSelector').hide();

		}
		
	}

	function selectDisplay() {

		var windowWidth = $(window).width();
		
		if (windowWidth < 700) {
			
			$('#resultDisplay').hide();
			$('#resultSelector').show();

		}
		
	}
	
    $(document).ready(function() {

    	// Load Meet List
		var requestData = {
			'action' : "getmeetlist"
		};
    	
		$.ajax({
			type	: 'GET',
			url		: 'xmltest.php',
			data	: requestData,
			datatype: 'xml',
			encode	: true
		})
		
		.done (function(data) {
			
			// Render data
			$(data).find('meet').each(function(){

				var id = $(this).attr("id");
				var meetName = $(this).find('meetname').text();

				$("#meet").append("<option value=\"" + id+ "\">" + meetName + "</option>");
				$("#meet").selectmenu("refresh");
				
			});	

		});

		// Select the first option

		
		refreshEvents();
                
    });
 
</script>
<style>



</style>
<title>eProgram Mobile</title>
</head>
<body>
 
<div id="appContainer">
<div id="resultSelector">
<h1>MSQ eProgram Mobile</h1>
<form method="post">
<h2>Meet Name:</h2>
<select id="meet" onchange="refreshEvents()">

</select>

<h2>Event:</h2>
<select id="event" onchange="refreshHeats()">

</select>


<h2>Choose a Heat:</h2>

<select id="heat" onchange="showHeat()">
<option></option>
</select>
<a href="#" onclick="showHeat()" data-role="button" data-icon="arrow-r" data-iconpos="right">
Go
</a>


<h2>or Age Group:</h2>

<select id="agegroup" onchange="showAgeGroup()">
<option></option>
</select>
<a href="#" onclick="showAgeGroup()" data-role="button" data-icon="arrow-r" data-iconpos="right">
Go
</a>


</form>

</div>

<div id="resultDisplay">
<div class="backToResultSelector">
</div>
<div id="heatLabel">
</div>


<table id="heatDetails" class="dataTable">

</table>

</div>

</div>
</body>
</html>