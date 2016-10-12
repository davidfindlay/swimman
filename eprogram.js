function displayDetails(eventId) {
	
	var eventDivId = "event_" + eventId;

	if (document.getElementById(eventDivId).style.visibility == 'collapse') {
		
		document.getElementById(eventDivId).style.visibility = 'visible';
		document.getElementById(eventDivId).style.display = '';
		
	} else {
	
		document.getElementById(eventDivId).style.visibility = 'collapse';
		document.getElementById(eventDivId).style.display = 'none';
		
	}
	
}

function displayAge(ageId) {
	
	var ageDivId = "agegroup_" + eventId;

	if (document.getElementById(ageDivId).style.visibility == 'collapse') {
		
		document.getElementById(ageDivId).style.visibility = 'visible';
		document.getElementById(ageDivId).style.display = '';
		
	} else {
	
		document.getElementById(ageDivId).style.visibility = 'collapse';
		document.getElementById(ageDivId).style.display = 'none';
		
	}
	
}