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

                setEventGender();

                getAvailableSwimmers(meetId, eventId, eventGender, clubId);
                getTeams(meetId, eventId);

            });

        var url2 = "/swimman/json/relayclubs.php?meetId=" + meetId;

        // // Load magic select
        // $('#newTeamClubMS').magicSuggest({
        //     data: url2,
        //     valueField: 'clubname',
        //     placeholder: 'Select a club',
        //     renderer: function(data){
        //         return data.clubname + ' (' + data.code + ')';
        //     },
        //     resultAsString: true
        // });


        $.getJSON(url2, {
            format: "json"
        })
            .done(function (data) {

                $('select[name=newTeamClub]')
                    .find('option')
                    .remove();

                $('select[name=newTeamClub]')
                    .append('<option value="0">Unattached</option>');

                $.each(data, function (key, value) {

                    // Populate the events drop down
                    var clubId = value.id;
                    var clubName = value.clubname;
                    var clubCode = value.code;

                    $('select[name=newTeamClub]')
                        .append('<option value="' + clubId + '">' + clubName + ' (' + clubCode + ')</option>');

                });

            });

    }

}

function getAvailableSwimmers(meetId, eventId, eventGender) {

    console.log('getAvailableSwimmers');

    if (meetId != undefined && eventId != undefined) {

        console.log('Requesting available ' + eventGender + ' swimmers for meetId=' + meetId + ' eventId=' + eventId);

        var url = "/swimman/json/entrants.php?meetId=" + meetId + "&eventId=" + eventId;
        $.getJSON(url, {
            format: "json"
        })
            .done(function (data) {

                console.log("clubId = " + clubId);

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

                        // If a clubId is set, then only show those members from selected club
                        if ((clubId == '0') || (value.clubId == clubId)) {

                            $('#newTeamSwimmer1')
                                .append('<option value="' + swimmerId + '">' + swimmerName + '</option>');

                            $('#newTeamSwimmer2')
                                .append('<option value="' + swimmerId + '">' + swimmerName + '</option>');

                            $('#newTeamSwimmer3')
                                .append('<option value="' + swimmerId + '">' + swimmerName + '</option>');

                            $('#newTeamSwimmer4')
                                .append('<option value="' + swimmerId + '">' + swimmerName + '</option>');

                        }

                    }

                });

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
            "destroy": "true",
            "columns": [
                { "data": function (json) {
                    var output = "<a href='#' onclick='deleteTeam(" + json.id + ")'>" +
                            "<img src=\"/swimman/images/delete.png\" alt='Delete Team' /></a>" +
                            "<a href='#' onclick='editTeam(" + json.id + ")'>" +
                            "<img src=\"/swimman/images/edit.png\" alt='Edit Team' /></a>";
                    return output;
                } },
                { "data": function (json) {
                    var clubInfo;
                    clubInfo = "<abbr title=\"" + json.clubname + "\">" + json.code + "</abbr>";
                    return clubInfo;
                }, className : "dt-center" },
                { "data": function (json) {
                    var groupName = json.groupname.split(' ');
                    return "<abbr title=\"" + json.groupname + "\">" + groupName[1] + "</abbr>";
                }, className : "dt-center" },
                { "data": function (json) {
                    var teamNameOut = json.letter;
                    if (json.teamname != null && json.teamname != '') {
                        teamNameOut += ' - ' + json.teamname;
                    }
                    return teamNameOut;
                }, className : "dt-center"  },
                { "data": 'swimmer1name',
                    "defaultContent": 'n/a' },
                { "data": 'swimmer2name',
                    "defaultContent": 'n/a'  },
                { "data": 'swimmer3name',
                    "defaultContent": 'n/a'  },
                { "data": 'swimmer4name',
                    "defaultContent": 'n/a'  }
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

/**
 * Loads a team for editing
 *
 * @param teamId to edit
 */
function editTeam(teamId) {

    // Change header
    $('#createTeamHeader').text("Edit a Team");

    // Set the existing team id
    $('#teamId').val(teamId);

    console.log("editTeam= " + teamId);

    // Get team details
    var team;
    $.ajax({
        type: 'GET',
        url: "/swimman/json/relayteams.php?meetId=" + meetId + "&eventId=" + eventId + "&teamId=" + teamId,
        dataType: 'json',
        success : function(data) {
            team = data;

            console.log("Loaded " + team[0].clubname + ' ' + team[0].letter);

            $('#newTeamClub').val(team[0].clubid);
            $('#newTeamClub').change();
            $('#newTeamClub')._source();
            $('#newTeamName').val(team[0].teamname);
            $('#newTeamName').change();
            $('#newTeamName')._source();
            $('#newTeamSwimmer1').val(team[0].swimmer1id);
            $('#newTeamSwimmer1').change();
            $('#newTeamSwimmer1')._source();
            $('#newTeamSwimmer2').val(team[0].swimmer2id);
            $('#newTeamSwimmer2').change();
            $('#newTeamSwimmer2')._source();
            $('#newTeamSwimmer3').val(team[0].swimmer3id);
            $('#newTeamSwimmer3').change();
            $('#newTeamSwimmer3')._source();
            $('#newTeamSwimmer4').val(team[0].swimmer4id);
            $('#newTeamSwimmer4').change();
            $('#newTeamSwimmer4')._source();

        }
    });



}

function downloadEntries(meetId, eventId) {

    console.log("Download request for meetid=" + meetId + " eventId=" + eventId);

    window.location.href = "gettmentries.php?meet=" + meetId + "&eventId=" + eventId;

}