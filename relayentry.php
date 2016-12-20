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

//echo "<script type=\"text/javascript\" src=\"includes/jquery-1.11.2.min.js\"></script>\n";
echo "<script type=\"text/javascript\" src=\"relayentry.js\"></script>\n";

htmlHeaders("Relay Entries");

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
echo "Age Group\n";
echo "</th>\n";
echo "<th>\n";
echo "Letter\n";
echo "</th>\n";
echo "<th>\n";
echo "Team Name\n";
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
//
//// Get list of relay entries for this meet
//if (isset($meetId) && isset($eventId)) {
//    $relayEntries = $GLOBALS['db']->getAll("SELECT a.id, b.meetname, c.code, d.prognumber,
//			d.progsuffix, e.groupname, a.letter, a.teamname
//			FROM meet_entries_relays AS a, meet AS b, clubs AS c, meet_events AS d, age_groups AS e
//			WHERE b.id = a.meet_id AND c.id = a.club_id AND a.meetevent_id = d.id
//			AND e.set = 1 AND a.agegroup = e.id
//			AND a.meet_id = ? AND a.meetevent_id = ?;", array($meetId, $eventId));
//    db_checkerrors($relayEntries);
//
//    foreach ($relayEntries as $r) {
//
//        $clubCode = $r[2];
//        $ageGroup = $r[5];
//        $letter = $r[6];
//        $teamname = $r[7];
//
//        // Get relay members
//        $relayMembers = $GLOBALS['db']->getAll("SELECT member.firstname, member.lastname, member.gender, member.dob
//                    FROM meet_entries_relays_members, member
//                    WHERE relay_team = ? AND meet_entries_relays_members.member_id = member.id
//                    ORDER BY leg ASC;");
//        db_checkerrors($relayMembers);
//
//        $arrMembers = array();
//
//        foreach ($relayMembers as $m) {
//
//            $member = new Member();
//            $member->setFirstname($m[0]);
//            $member->setLastname($m[1]);
//
//            if ($m2 == 1) {
//                $member->setGender("M");
//            } else {
//                $member->setGender("F");
//            }
//            $member->setDob($m[3]);
//
//            $arrMembers[] = $member;
//
//        }
//
//        echo "<tr class=\"list\">\n";
//
//        echo "<td>\n";
//        echo $clubCode;
//        echo "</td>\n";
//
//        echo "<td>\n";
//        echo $ageGroup;
//        echo "</td>\n";
//
//        echo "<td>\n";
//        echo $letter;
//        echo "</td>\n";
//
//        echo "<td>\n";
//        echo $teamname;
//        echo "</td>\n";
//
//        for ($x = 0; $x > 4; $x++) {
//
//            echo "<td>\n";
//            echo $arrMembers[x]->getFullname() . "(" . $arrMembers[x]->getGender() . $arrMembers[x]->getAge() . ")";
//            echo "</td>\n";
//
//        }
//
//
//        echo "</tr>\n";
//
//    }
//
//}

echo "</tbody>\n";
echo "</table>\n";

echo "<p>\n";
echo "<input type=\"button\" id=\"downloadRelays\" value=\"Download Entries\" />\n";
echo "</p>\n";

echo "<h2>Create a Team</h2>\n";

echo "<p>\n";
echo "For club relay teams, select the correct club from the Club Team drop down list. Only club members ";
echo "will be shown. For mixed club relay teams, leave Club Team blank. ";
echo "</p>\n";

echo "<p>\n";
echo "<label>Club Team:</label>\n";

echo "<select name=\"newTeamClub\" id=\"newTeamClub\">\n";

echo "<option value=\"\"></option>\n";

if (isset($meetId)) {

// Get list of clubs
    $clubs = $GLOBALS['db']->getAll("SELECT * FROM clubs WHERE id IN
                          (SELECT club_id FROM meet_entries WHERE meet_id = ? AND
                          club_id IN (SELECT club_id FROM meet_entries GROUP BY club_id HAVING count(*) > 3))
  ORDER BY clubname ASC;",
        array($meetId));
    db_checkerrors($clubs);

    foreach ($clubs as $c) {

        $cId = $c[0];
        $cCode = trim($c[1]);
        $cName = trim($c[2]);
        echo "<option value=\"$cId\">$cName($cCode)</option>\n";

    }

}

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

echo "<div class=\"modal\"><!-- Place at bottom of page --></div>\n";

htmlFooters();

?>

<script>

    var meetId = "";
    var eventId = "";
    var eventGender = "";
    var clubId = "";
    var clubCode = "";

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

            getAvailableSwimmers(meetId, eventId, eventGender, clubCode);
            getTeams(meetId, eventId);

            var teamClubComboBox = $("#newTeamClub").data("custom-  combobox");
            teamClubComboBox.resize();

        });

        $('.newTeamSwimmers').change(function() {
            var dropDownId = $(this).attr('id');
            var selected = $(this).val();

            console.log("dropDownId=" + dropDownId + " selected=" + selected);

            if (dropDownId != "newTeamSwimmer1") {
                console.log("not newTeamSwimmer1");
                if ($('#newTeamSwimmer1').val() == selected) {
                    console.log("clear newTeamSwimmer1");
                    $('#newTeamSwimmer1').val("");
                }
            }

            if (dropDownId != "newTeamSwimmer2") {
                if ($('#newTeamSwimmer2').val() == selected) {
                    $('#newTeamSwimmer2').val("");
                }
            }

            if (dropDownId != "newTeamSwimmer3") {
                if ($('#newTeamSwimmer3').val() == selected) {
                    $('#newTeamSwimmer3').val("");
                }
            }

            if (dropDownId != "newTeamSwimmer4") {
                if ($('#newTeamSwimmer4').val() == selected) {
                    $('#newTeamSwimmer4').val("");
                }
            }

        })

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

        $('#newTeamClub').combobox();
        $('#newTeamSwimmer1').combobox();
        $('#newTeamSwimmer2').combobox();
        $('#newTeamSwimmer3').combobox();
        $('#newTeamSwimmer4').combobox();

        console.log( "ready!" );

    });


</script>