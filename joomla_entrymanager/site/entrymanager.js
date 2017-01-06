function toggleFormVis(eId) {

	var entd = document.getElementById("enter_" + eId); // Check if entered checkbox ticked
	var ntChk = document.getElementById("nt_" + eId);	// Check if no time checkbox ticked
	
	if (entd.checked == true) {
	
		if (ntChk.checked == false) {
			
			document.getElementById("st_" + eId).style.visibility = "visible";
			document.getElementById("info_" + eId).style.visibility = "visible";
		
		}
		
		document.getElementById("nt_" + eId).style.visibility = "visible";	
		document.getElementById("ntl_" + eId).style.visibility = "visible";
	
	} else {
	
		document.getElementById("st_" + eId).style.visibility = "hidden";
		document.getElementById("nt_" + eId).style.visibility = "hidden";
		document.getElementById("ntl_" + eId).style.visibility = "hidden";
		document.getElementById("info_" + eId).style.visibility = "hidden";
	
	}

}

function noTime(eId) {
	
	var ntChk = document.getElementById("nt_" + eId);

	if (ntChk.checked == true) {
	
		document.getElementById("st_" + eId).style.visibility = "hidden";
		document.getElementById("info_" + eId).style.visibility = "hidden";
	
	} else {
	
		document.getElementById("st_" + eId).style.visibility = "visible";
		document.getElementById("info_" + eId).style.visibility = "visible";
		document.getElementById("st_" + eId).value = "";
		
	}

}

function rewriteTime(timeString) {

	var seconds;

	// Handle where user has separated with .'s instead of :
    var dotstring = timeString.split('.');
    var dotstringSections = dotstring.length;

    // Check if there is more than one .
    if (dotstringSections > 2) {

        var newTimeString = "";

        for (var i = 0; i < dotstringSections; i++) {

            newTimeString += dotstring[i];


            // Replace all but last instance of '.' with ':'
            if (i <= (dotstringSections - 3)) {

                newTimeString += ':';

            } else if (i <= (dotstringSections - 2)) {

                newTimeString += '.';

            }

        }

        //console.log("newTimeString = " + newTimeString);

        timeString = newTimeString;

    }

    // Is there a colon in the time?
	if (timeString.search(':') != -1) {
	
		var timeArray = timeString.split(':');

		// Check how many colons are in the time
        if (timeArray.length == 2) {

            // Time in minutes and seconds
            seconds = (parseFloat(timeArray[0]) * 60) + parseFloat(timeArray[1]);

        } else if (timeArray.length == 3) {

            // Time in hours, minutes and seconds
            seconds = (parseFloat(timeArray[0]) * 60 * 60) +
                (parseFloat(timeArray[1]) * 60) + parseFloat(timeArray[2]);

        }
	
	} else {
		
		// Handle times entered sequentially e.g. 132 for 1:32.00
		seconds = parseFloat(timeString);
		
		if (seconds > 99) {
			
			var strLength = timeString.length;
			
			// Handle 3200 as 32.00
			if (strLength <= 4) {
				
				seconds = parseFloat(timeString.substring(0, strLength - 2) + "." + timeString.substring(strLength - 2));
				
			}
			
			// Handle 13200 as 1:32.00
			if (strLength <= 6 && strLength > 4) {
				
				seconds = (parseInt(timeString.substring(0, strLength - 4)) * 60) +
						parseFloat(timeString.substring(strLength - 4, strLength - 2) + "." +
						timeString.substring(strLength - 2));
				
			}
			
			// Handle 1023200 as 1:02:32.00
			if (strLength <= 8 && strLength > 6) {
				
				seconds = (parseInt(timeString.substring(0, strLength - 6)) * 60 * 60) +
						(parseInt(timeString.substring(strLength - 6, strLength - 4)) * 60) +
						parseFloat(timeString.substring(strLength - 4, strLength - 2) + "." +
						timeString.substring(strLength - 2));
				
			}
			
		} else {
		
			
			
		}
		
	
	}
	
	var timeMin = Math.floor(seconds / 60);
    var timeHours = 0;

    if (timeMin > 60) {

        timeHours = Math.floor(timeMin / 60);
        timeMin = timeMin - (timeHours * 60);


    }

	var timeSecs = seconds % 60;
	timeSecs = timeSecs.toFixed(2);
	var secString = timeSecs.toString();
	
	if (secString.length == 4) {
	
		secString = "0" + secString;
	
	}

	if (timeHours > 0) {

	    var minString = timeMin.toString();

	    if (minString.length == 1) {

	        minString = "0" + minString;

        }

	    var nTimeString = timeHours.toString() + ":" + minString + ":" + secString;

    } else {

        var nTimeString = timeMin.toString() + ":" + secString;

    }
	
	if (nTimeString == "NaN:NaN") {
		nTimeString = "0:00.00";
	}
	
	return nTimeString;
	
}

function fixSeedTimes(eId) {

	var timeString = document.getElementById('st_' + eId).value;
	
	var fixedString = rewriteTime(timeString);
	
	if (fixedString == "0:00.00") {
		
		// If the correct time is 0 set the field empty
		document.getElementById('st_' + eId).value = "";
		
	} else {
	
		// Write the time to the field
		document.getElementById('st_' + eId).value = fixedString;
		
	}

}

function displayEntryList(eId) {
	
	var entryDivId = "eventList_" + eId;

	if (document.getElementById(entryDivId).style.visibility == 'collapse') {
		
		document.getElementById(entryDivId).style.visibility = 'visible';
		document.getElementById(entryDivId).style.display = '';
		
	} else {
	
		document.getElementById(entryDivId).style.visibility = 'collapse';
		document.getElementById(entryDivId).style.display = 'none';
		
	}
	
}
