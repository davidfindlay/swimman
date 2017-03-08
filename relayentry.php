<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/MeetSelector.php");
require_once("includes/classes/MeetEvent.php");
checkLogin();

addlog("Access", "Accessed relayentry.php");

if (isset($_POST['meetId'])) {

    $meetId = intval($_POST['meetId']);

}

if (isset($_POST['eventId'])) {

    $eventId = intval($_POST['eventId']);

}

htmlHeaders("Relay Entries");

echo "<script type=\"text/javascript\" src=\"relayentry.js\"></script>\n";

sidebarMenu();

echo "<div id=\"main\">\n";

echo "<h1>Relay Entries</h1>\n";

echo "<form method=\"post\" id=\"newRelayForm\">\n";

echo "<p>\n";
echo "<label>Meet: </label>\n";

$meetSelector = new MeetSelector();
$meetSelector->setName("meetId");

if (isset($meetId)) {
    $meetSelector->selected($meetId);
}

$meetSelector->publishedOnly();
$meetSelector->output();

//echo "<input type=\"submit\" name=\"meetload\" value=\"Select Meet\" />\n";

echo "<br />\n";

echo "<label>Event: </label>\n";

echo "<select name=\"eventId\">\n";

if (isset($meetId)) {

    // Get a list of relay events for this meet
    $events = $GLOBALS['db']->getAll("SELECT a.id, a.prognumber, a.progsuffix, a.legs, c.distance, 
              IF(d.gender = 1, 'Men\'s', 'Women\'s'), b.discipline
              FROM meet_events as a, event_disciplines as b, event_distances as c, event_types as d
              WHERE meet_id = ? AND legs > 1 
              AND c.id = a.distance AND b.id = a.discipline AND d.id = a.type ORDER BY prognumber ASC;",
        array($meetId));
    db_checkerrors($events);

    foreach ($events as $e) {

        $eId = $e[0];
        $eventDetails = "#" . $e[1] . $e[2] . " " . $e[3] . "x" . $e[4] . " " . $e[5] . " " . $e[6];

        echo "<option value=\"$eId\"";

        if (isset($eventId) && ($eventId == $eId)) {

            echo " selected=\"selected\"";

        }

        echo ">$eventDetails</option>\n";

    }



}

echo "</select>\n";

//echo "<input type=\"submit\" name=\"eventload\" value=\"Select Event\" />\n";

echo "</p>\n";

echo "<table class=\"list\" id=\"data\" width=\"100%\">\n";
echo "<thead class=\"list\">\n";
echo "<tr>\n";
echo "<th>\n";
echo "</th>\n";
echo "<th>\n";
echo "Club\n";
echo "</th>\n";
echo "<th>\n";
echo "Age\n";
echo "</th>\n";
echo "<th>\n";
echo "Team\n";
echo "</th>\n";
echo "<th>\n";
echo "1st Swimmer\n";
echo "</th>\n";
echo "<th>\n";
echo "2nd Swimmer\n";
echo "</th>\n";
echo "<th>\n";
echo "3rd Swimmer\n";
echo "</th>\n";
echo "<th>\n";
echo "4th Swimmer\n";
echo "</th>\n";
echo "</tr>\n";
echo "</thead>\n";
echo "<tbody class=\"list\" id=\"relayTeams\">\n";

echo "</tbody>\n";
echo "</table>\n";

echo "<p>\n";
echo "<input type=\"button\" id=\"downloadRelays\" value=\"Download Entries\" />\n";
echo "</p>\n";

echo "<h2 id='createTeamHeader'>Create a Team</h2>\n";

echo "<p>\n";
echo "For club relay teams, select the correct club from the Club Team drop down list. Only club members ";
echo "will be shown. For mixed club relay teams, leave Club Team blank. ";
echo "</p>\n";

echo "<p>\n";
echo "<label>Club Team:</label>\n";

echo "<input type=\"hidden\" name=\"teamId\" id=\"teamId\" value=\"\" />\n";

echo "<select name=\"newTeamClub\" id=\"newTeamClub\">\n";

echo "</select>\n";

echo "</p>\n";

echo "<p>\n";
echo "<label>Team Name: </label>\n";
echo "<input type=\"text\" name=\"newTeamName\" id=\"newTeamName\" size=\"40\" /> optional";
echo "</p>\n";

echo "<p>\n";
echo "<label for=\"newTeamSwimmer1\" id=\"newTeamSwimmerLbl1\">Swimmer 1:</label>\n";

echo "<select name=\"newTeamSwimmer1\" id=\"newTeamSwimmer1\" class=\"newTeamSwimmers\">\n";
echo "<option value=\"\"></option>\n";
echo "</select>\n";
echo "</p>\n";

echo "<p>\n";
echo "<label for=\"newTeamSwimmer2\" id=\"newTeamSwimmerLbl2\">Swimmer 2:</label>\n";

echo "<select name=\"newTeamSwimmer2\" id=\"newTeamSwimmer2\" class=\"newTeamSwimmers\">\n";
echo "<option value=\"\"></option>\n";
echo "</select>\n";
echo "</p>\n";

echo "<p>\n";
echo "<label for=\"newTeamSwimmer3\" id=\"newTeamSwimmerLbl3\">Swimmer 3:</label>\n";

echo "<select name=\"newTeamSwimmer3\" id=\"newTeamSwimmer3\" class=\"newTeamSwimmers\">\n";
echo "<option value=\"\"></option>\n";
echo "</select>\n";
echo "</p>\n";

echo "<p>\n";
echo "<label for=\"newTeamSwimmer4\" id=\"newTeamSwimmerLbl4\">Swimmer 4:</label>\n";

echo "<select name=\"newTeamSwimmer4\" id=\"newTeamSwimmer4\" class=\"newTeamSwimmers\">\n";
echo "<option value=\"\"></option>\n";
echo "</select>\n";
echo "</p>\n";

echo "<p>\n";
echo "<input type=\"button\" id=\"newTeamCreate\" value=\"Create Team\" />\n";
echo "</p>\n";

echo "</form>\n";

echo "</div>\n";  // Main Div

?>

<div id="deleteModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Confirmation</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this relay team?</p>
                <p class="text-warning"><small>This can not be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                <button type="button" class="btn btn-primary">Yes, delete the team</button>
            </div>
        </div>
    </div>
</div>

<div class="modal"></div>

<?php

htmlFooters();

?>

<script>

    var meetId = "";
    var eventId = "";
    var eventGender = "";
    var clubId = "";

    var rowSelected = false;

    $body = $("body");
//    $(document).on({
//        ajaxStart: function() { $body.addClass("loading");    },
//        ajaxStop: function() { $body.removeClass("loading"); }
//    });

    $("tbody.list").on("click", "tr", function() {

        var highlighted = $(this).hasClass("highlight");
        rowSelected = !rowSelected;

        if (!highlighted) {
            $(".list tr").removeClass("highlight");
        }

    });

    $('tbody.list').on("mouseenter", "tr", function() {

        if (rowSelected == false) {
            $(this).addClass('highlight');
        }

    });

    $('tbody.list').on("mouseleave", "tr", function() {

        if (rowSelected == false) {
            $(this).removeClass('highlight');
        }
    });

    $( document ).ready(function() {

        $('select[name=meetId]').change(function() {
            meetId = $(this).val();
            console.log("meetId changed to " + meetId);

            getRelayEvents(meetId);

        });

        $('select[name=eventId]').change(function() {
            eventId = $(this).val();

            setEventGender();

            getAvailableSwimmers(meetId, eventId, eventGender);
            getTeams(meetId, eventId);

            var teamClubComboBox = $("#newTeamClub").data("custom-combobox");

        });

        $('#newTeamClub').combobox();
        $('#newTeamSwimmer1').combobox();
        $('#newTeamSwimmer2').combobox();
        $('#newTeamSwimmer3').combobox();
        $('#newTeamSwimmer4').combobox();

        $('#newTeamClub').change(function() {
            clubId = $('#newTeamClub').val();
            getAvailableSwimmers(meetId, eventId, eventGender);

            console.log ("Team Club change");
        });

//        $('.newTeamSwimmers').change(function() {
//            var dropDownId = $(this).attr('id');
//            var selected = $(this).val();
//
//            console.log("dropDownId=" + dropDownId + " selected=" + selected);
//
//            if (dropDownId != "newTeamSwimmer1") {
//                console.log("not newTeamSwimmer1");
//                if ($('#newTeamSwimmer1').val() == selected) {
//                    console.log("clear newTeamSwimmer1");
//                    $('#newTeamSwimmer1').val("");
//                }
//            }
//
//            if (dropDownId != "newTeamSwimmer2") {
//                if ($('#newTeamSwimmer2').val() == selected) {
//                    $('#newTeamSwimmer2').val("");
//                }
//            }
//
//            if (dropDownId != "newTeamSwimmer3") {
//                if ($('#newTeamSwimmer3').val() == selected) {
//                    $('#newTeamSwimmer3').val("");
//                }
//            }
//
//            if (dropDownId != "newTeamSwimmer4") {
//                if ($('#newTeamSwimmer4').val() == selected) {
//                    $('#newTeamSwimmer4').val("");
//                }
//            }
//
//        });

        $('#newTeamCreate').click(function() {
            createTeam();
        });

        $('#downloadRelays').click(function() {
            downloadEntries(meetId, eventId);
        });

        $('select[name=meetId]').val($('select[name=meetId] option:first').val());

        meetId = $('select[name=meetId] option:selected').val();
        eventId = $('select[name=eventId] option:selected').val();

        console.log("meetId initially set to " + meetId);
        console.log("eventId initially set to " + eventId);

        getRelayEvents(meetId);

        console.log( "ready!" );

    });


    $.ajaxSetup({
        beforeSend:function(){
            $('body').addClass('loading');
        },
        complete:function(){
            $('body').removeClass('loading');
        }
    });

</script>