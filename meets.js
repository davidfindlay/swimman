function displayDetails(meetId) {
	
	var meetDivId = "mEventList_" + meetId;

	if (document.getElementById(meetDivId).style.visibility == 'collapse') {
		
		document.getElementById(meetDivId).style.visibility = 'visible';
		document.getElementById(meetDivId).style.display = '';
		
	} else {
	
		document.getElementById(meetDivId).style.visibility = 'collapse';
		document.getElementById(meetDivId).style.display = 'none';
		
	}
	
	
	
}