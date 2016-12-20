/**
 * Created by david on 2/11/16.
 */

function getRelayEvents(meetId) {

    console.log('getRelayEvents');

    if (meetId != undefined) {

        console.log('Requesting relay events for meetId=' + meetId);

        var url = "/swimman/json/relayevents.php?meetId=" + meetId;
        $.getJSON(url, {
            format: "json"
        })
            .done(function (data) {

                $('select[name=eventId]')
                    .find('option')
                    .remove();

                $.each(data, function (key, value) {

                    // Populate the events drop down
                    var eventId = value.id;
                    var eventDesc = "#" + value.prognumber + value.progsuffix;
                    eventDesc += " " + value.legs + "x" + value.distance;
                    eventDesc += " " + value.gender + " " + value.discipline;

                    $('select[name=eventId]')
                        .append('<option value="' + eventId + '">' + eventDesc + '</option>');

                })

                // Make default first event selection
                $('select[name=eventId]').val($('select[name=eventId] option:first').val());
                eventId = $('select[name=eventId] option:selected').val();

                console.log("test" + eventId);

                setEventGender();

                getAvailableSwimmers(meetId, eventId, eventGender, clubId);
                getTeams(meetId, eventId);

            });

        var url2 = "/swimman/json/relayclubs.php?meetId=" + meetId;

        $.getJSON(url2, {
            format: "json"
        })
            .done(function (data) {

                $('select[name=newTeamClub]')
                    .find('option')
                    .remove();

                $('select[name=newTeamClub]')
                    .append('<option value=""></option>');

                $.each(data, function (key, value) {

                    // Populate the events drop down
                    var clubId = value.id;
                    var clubName = value.clubname;
                    var clubCode = value.code;

                    $('select[name=newTeamClub]')
                        .append('<option value="' + clubId + '">' + clubName + ' (' + clubCode + ')</option>');

                })
            });

    }

}

function getAvailableSwimmers(meetId, eventId, eventGender, clubId) {

    console.log('getAvailableSwimmers');

    if (meetId != undefined && eventId != undefined) {

        console.log('Requesting available ' + eventGender + ' swimmers for meetId=' + meetId + ' eventId=' + eventId);

        var url = "/swimman/json/entrants.php?meetId=" + meetId + "&eventId=" + eventId;
        $.getJSON(url, {
            format: "json"
        })
            .done(function (data) {

                $('#newTeamSwimmer1')
                    .find('option')
                    .remove()
                    .end()
                    .append('<option value=""></option>');

                $('#newTeamSwimmer2')
                    .find('option')
                    .remove()
                    .end()
                    .append('<option value=""></option>');

                $('#newTeamSwimmer3')
                    .find('option')
                    .remove()
                    .end()
                    .append('<option value=""></option>');

                $('#newTeamSwimmer4')
                    .find('option')
                    .remove()
                    .end()
                    .append('<option value=""></option>');

                $.each(data, function (key, value) {

                    var swimmerId = value.id;
                    var swimmerAge = value.gender + value.age;

                    var swimmerName = value.firstname + " " + value.surname + "(" + swimmerAge + ")";

                    if ((eventGender == "X") || (eventGender == value.gender)) {

                       // if (clubId == "" || clubId = value.clubcode)

                        $('#newTeamSwimmer1')
                            .append('<option value="' + swimmerId + '">' + swimmerName + '</option>');

                        $('#newTeamSwimmer2')
                            .append('<option value="' + swimmerId + '">' + swimmerName + '</option>');

                        $('#newTeamSwimmer3')
                            .append('<option value="' + swimmerId + '">' + swimmerName + '</option>');

                        $('#newTeamSwimmer4')
                            .append('<option value="' + swimmerId + '">' + swimmerName + '</option>');

                    }

                })

            });

    }

}

function getTeams(meetId, eventId) {

    console.log('getTeams for meetId=' + meetId + ' eventId=' + eventId);

    if (meetId != undefined && eventId != undefined) {

        console.log('Requesting teams for meetId=' + meetId + ' eventId=' + eventId);

        var url = "/swimman/json/relayteams.php?meetId=" + meetId + "&eventId=" + eventId;

        $('#data').DataTable( {
            "ajax": url,
            "sAjaxDataProp": "",
            "columns": [
                { "defaultContent": "Delete Edit" },
                { "data": function (json) {
                    var clubInfo;
                    clubInfo = "<abbr title=\"" + json.clubname + "\">" + json.code + "</abbr>";
                    return clubInfo;
                } },
                { "data": 'groupname' },
                { "data": 'letter' },
                { "data": 'teamname' },
                { "data": 'swimmer1name' },
                { "data": 'swimmer2name' },
                { "data": 'swimmer3name' },
                { "data": 'swimmer4name' }
            ]
        } );

    }


}

function createTeam() {

    console.log("Create team for meetId=" + meetId + " eventId=" + eventId);

    console.log($("#newRelayForm").serialize());

    $.post("/swimman/json/createrelay.php", $("#newRelayForm").serialize())
        .done(function (data) {
            getTeams(meetId, eventId);

            $("#newTeamClub").val("");
            $("#newTeamName").val("");
            $("#newTeamSwimmer1").val("");
            $("#newTeamSwimmer2").val("");
            $("#newTeamSwimmer3").val("");
            $("#newTeamSwimmer4").val("");

        });

}

function setEventGender() {

    var eventName = $('select[name=eventId] option:selected').text();

    console.log("eventId changed to " + eventId + " " + eventName);

    if (eventName.includes("Women")) {
        eventGender = "F";
        console.log("Womens event selected");
    } else if (eventName.includes("Men")) {
        eventGender = "M";
        console.log("Mens event selected");
    } else {
        eventGender = "X";
        console.log("Mixed event selected");
    }

}

function deleteTeam(teamId) {

    var retVal = confirm("Are you sure you want to delete this relay team?");

    if (retVal == true) {

        console.log("Delete requested for teamId=" + teamId);

        $.post("/swimman/json/deleterelay.php", {teamId: teamId})
            .done(function (data) {
                getTeams(meetId, eventId);
                getAvailableSwimmers(meetId, eventId, eventGender, clubId);
            });
    }

}

function downloadEntries(meetId, eventId) {

    console.log("Download request for meetid=" + meetId + " eventId=" + eventId);

    window.location.href = "gettmentries.php?meet=" + meetId + "&eventId=" + eventId;

}