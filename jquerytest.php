<html>
<head>
<meta charset="UTF-8" />
<meta name='viewport' content='width=device-width, user-scalable=no,
    initial-scale=1, maximum-scale=1, minimum-scale=1'/>
<link rel="stylesheet" href="/swimman/style/eprogram-screen.css" type="text/css" media="screen" />
<link rel="stylesheet" href="/swimman/style/eprogram-mobile.css" type="text/css" media="only screen and (max-device-width:480px)" />
<script type="text/javascript" src="/swimman/includes/jquery-1.11.1.min.js"></script>
<script type="text/javascript">

	// Get ordinal number function by Chris West 
	// http://cwestblog.com/2012/09/28/javascript-number-getordinalfor/
	(function(o) {
	  Number.getOrdinalFor = function(intNum, includeNumber) {
	    return (includeNumber ? intNum : "")
	      + (o[((intNum = Math.abs(intNum % 100)) - 20) % 10] || o[intNum] || "th");
	  };
	})([,"st","nd","rd"]);

// 	function refreshEvents() {

// 		// Clear options
// 		$('#event > option').remove();

// 		if ($("#meet").val() == null) {

// 			var meetId = 37;
			
// 		} else {

// 			var meetId = $("#meet").val();
			
// 		}
		
// 		// Load Event List
// 		var requestData = {
// 			'action' : "geteventlist",
// 			'meet'   : meetId
// 		};
    	
// 		$.ajax({
// 			type	: 'GET',
// 			url		: 'xmltest.php',
// 			data	: requestData,
// 			datatype: 'xml',
// 			encode	: true
// 		})
		
// 		.done (function(data) {

// 			// Render data
// 			$(data).find('event').each(function(){

// 				var id = $(this).attr("id");
// 				var eventProg = $(this).find('number').text();
// 				var eventLegs = $(this).find('legs').text();
// 				var eventDist = $(this).find('metres').text();
// 				var eventDisc = $(this).find('disciplineabrev').text();

// 				if (eventLegs > 1) {

// 					var eventDetails = eventLegs + "x" + eventDist + "m " + eventDisc;
					
// 				} else {

// 					var eventDetails = eventDist + "m " + eventDisc;
					
// 				}
				
// 				$("#event").append("<option value='" + eventProg + "'>Event " + eventProg + 
// 						" - " + eventDetails + "</option>");
				
// 			});	

// 		});

// 		refreshHeats();
		
// 	}

// 	function refreshHeats() {

// 		// Clear options
// 		$('#heat > option').remove();

// 		if ($("#meet").val() == null) {

// 			var meetId = 37;
			
// 		} else {

// 			var meetId = $("#meet").val();
			
// 		}

// 		if ($("#event").val() == null) {

// 			var event = 1;
			
// 		} else {

// 			var event = $("#event").val();
			
// 		}
		
// 		// Load Event List
// 		var requestData = {
// 			'action' : "getheatlist",
// 			'meet'   : meetId,
// 			'event'  : event
// 		};
    	
// 		$.ajax({
// 			type	: 'GET',
// 			url		: 'xmltest.php',
// 			data	: requestData,
// 			datatype: 'xml',
// 			encode	: true
// 		})
		
// 		.done (function(data) {

// 			// Render data
// 			$(data).find('heat').each(function(){

// 				var id = $(this).attr('number');
// 				var heatNum = id;
				
// 				$("#heat").append("<option value='" + id + "'>Heat " + heatNum + "</option>");
				
// 			});	

// 		});

// 		$('#heat option:first').attr("selected", "selected");
// 		showHeat();
		
// 	}

// 	function showHeat() {

// 		// Clear tables
// 		$('#heatDetails > tbody > tr').remove();

// 		// Clear options
// 		// $('#heat > option').remove();

// 		if ($("#meet").val() == null) {

// 			var meetId = 37;
			
// 		} else {

// 			var meetId = $("#meet").val();
			
// 		}

// 		if ($("#event").val() == null) {

// 			var event = 1;
			
// 		} else {

// 			var event = $("#event").val();
			
// 		}

// 		//alert($("#heat ").val());
		
// 		if ($("#heat ").val() == null) {

// 			var heat = 1;
			
// 		} else {

// 			var heat = $("#heat ").val();
			
// 		}

// 		var formData = {
// 			'action' : "getheat",
// 			'meet' : meetId,
// 			'event' : event,
// 			'heat' : heat
// 		};

// 		// Make request
// 		$.ajax({
// 			type	: 'GET',
// 			url		: 'xmltest.php',
// 			data	: formData,
// 			datatype: 'xml',
// 			encode	: true
// 		})
		
// 		.done (function(data) {

// 			// Render data
// 			var heatNum = $(data).find('heat').attr('number');

// 			$('#heatLabel').html("Heat " + heatNum);
			
// 			$(data).find('entry').each(function(){

// 				var lane = $(this).find('lane').text();
// 				var swimmer = $(this).find('fullname').text();
// 				var agegroup = $(this).find('agegroup').text();
// 				var age = $(this).find('age').text();
// 				var finaltime = $(this).find('finaltime').text();
// 				var clubname = $(this).find('clubname').text();
// 				var heatplace = Number.getOrdinalFor($(this).find('heatplace').text(), true);
// 				var ageplace = Number.getOrdinalFor($(this).find('ageplace').text(), true);
// 				var points = $(this).find('points').text();

// 				if (finaltime != "") {

// 					$('<tr></tr>').html(
// 						'<td><div class=\"lblLane\">Lane</div><div class=\"numLane\">'+lane+
// 						'</div></td><td><div class=\"swimmerName\">'
// 						+swimmer+'</div><div class=\"ageGroup\">'
// 						+agegroup+'</div><div class=\"swimmerAge\">('
// 						+age+')</div><div class=\"clubName\">'
// 						+clubname+'</div></td><td class=\"placeCell\">' +
// 						'<div class="lblHeatPlace">Heat:</div><div class=\"heatPlace\">'
// 						+heatplace+'</div><div class="lblAgePlace">Age:</div><div class=\"agePlace">'
// 						+ageplace+'</div><div class="lblPoints">Points:</div><div class=\"points\">'
// 						+points+
// 						'</div></td><td><div class=\"finalTime\">'
// 						+finaltime+'</div></td>')
// 						.appendTo("#heatDetails");

// 				} else {

// 					$('<tr></tr>').html(
// 							'<td><div class=\"lblLane\">Lane</div><div class=\"numLane\">'+lane+
// 							'</div></td><td><div class=\"swimmerName\">'
// 							+swimmer+'</div><div class=\"ageGroup\">'
// 							+agegroup+'</div><div class=\"swimmerAge\">('
// 							+age+')</div><div class=\"clubName\">'
// 							+clubname+'</div></td><td>' +
// 							'</td><td> </td>')
// 							.appendTo("#heatDetails");
					
// 				}
				
// 			});	

// 		});
	

// 	}

    $(document).ready(function() {

    	// Load Meet List
		var requestData = {
			'action' : "getmeetlist"
		};

		var firstMeetName = "Select a Meet";
		$("#ddMeetControlTop").append(firstMeetName);
    	
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

				$("#ddMeetControlMenu").append("<li><a href=\"#\">" + meetName + "</a></li>");
				
			});	

		});

		// Select the first option

		
		// refreshEvents();
                
    });


		function DropDown(el) {
				this.ddMeetControl = el;

				this.mcPlaceholder = this.ddMeetControl.children('span');
				this.mcOpts = this.ddMeetControl.find('ul.dropdown > li');
				this.mvVal = '';
				this.mcIndex = -1;

				this.ddEventControl = el;
				this.ddHeatControl = el;
				this.initEvents();
				
			}
			DropDown.prototype = {
				initEvents : function() {
					var obj = this;

					obj.ddMeetControl.on('click', function(event){
						$(this).toggleClass('active');
						event.stopPropagation();
					});

					obj.mcOpts.on('click',function(){
						var opt = $(this);
						obj.mcVal = opt.text();
						obj.mcIndex = opt.index();
						// obj.mcPlaceholder.text(obj.val);
						alert("Yuck yuck yuck!");
						
						obj.mcPlaceholder.text("blah blah blah");
					});

					obj.ddEventControl.on('click', function(event){
						$(this).toggleClass('active');
						event.stopPropagation();
					});

					obj.ddHeatControl.on('click', function(event){
						$(this).toggleClass('active');
						event.stopPropagation();
					});
						
				},

				getValue : function() {
					return this.val;
				},
				getIndex : function() {
					return this.index;
				}
				
			}

			$(function() {

				var ddMeetControl = new DropDown( $('#ddMeetControl') );
				var ddEventControl = new DropDown( $('#ddEventControl') );
				var ddHeatControl = new DropDown( $('#ddHeatControl') );
				
				$(document).click(function() {
					// all dropdowns
					$('.wrapper-dropdown').removeClass('active');
				});

			});

 
</script>
<style>



</style>
<title>eProgram Mobile</title>
</head>
<body>
 
<div id="appContainer">
<div id="resultSelector">
<form method="post">
<h2>Meet Name:</h2>
<div id="ddMeetControl" class="wrapper-dropdown" tabindex="1">
<span id="ddMeetControlTop"></span>
<ul id="ddMeetControlMenu" class="dropdown">
</ul>
</div>

<h2>Event:</h2>
<div id="ddEventControl" class="wrapper-dropdown" tabindex="1">
<span id="ddEventControlTop">John Doe</span>
<ul id="ddEventControlMenu" class="dropdown">
<li><a href="#">Profile</a></li>
<li><a href="#">Settings</a></li>
<li><a href="#">Log out</a></li>
</ul>
</div>

<h2>Heat:</h2>
<div id="ddHeatControl" class="wrapper-dropdown" tabindex="1">
<span id="ddHeatControlTop">John Doe</span>
<ul id="ddHeatControlMenu" class="dropdown">
<li><a href="#">Profile</a></li>
<li><a href="#">Settings</a></li>
<li><a href="#">Log out</a></li>
</ul>
</div>

<!-- <div id="eventControl" class="controlSelect"> -->
<!-- <span id="eventControlTitle">Event 1</span> -->
<!-- <ul class="dropdown" id="eventControlDropDown"> -->
<!-- <li>Event 1</li> -->
<!-- <li>Event 2</li> -->
<!-- <li>Event 3</li> -->
<!-- </ul> -->
<!-- </div> -->

<!-- <div id="heatControl" class="controlSelect"> -->
<!-- <span id="heatControlTitle">Heat 1</span> -->
<!-- <ul class="dropdown" id="heatControlDropDown"> -->
<!-- <li>Heat 1</li> -->
<!-- <li>Heat 2</li> -->
<!-- <li>Heat 3</li> -->
<!-- </ul> -->
<!-- </div> -->

</form>

</div>

<div id="resultDisplay">

<div id="heatLabel">

</div>

<table id="heatDetails" class="dataTable">

</table>

</div>

</div>
</body>
</html>