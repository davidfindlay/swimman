<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Member.php");
require_once("includes/classes/Club.php");
require_once("includes/classes/Meet.php");
require_once("includes/classes/MeetEvent.php");
require_once("includes/classes/MeetEntry.php");
require_once("includes/classes/MeetEntryEvent.php");
checkLogin();

htmlHeaders("Swimming Management System - Meet Entry List");

sidebarMenu();

?>

<div id="main">

    <h1>Meet Entries</h1>

    <!--<form method="get" action="meetentries.php">
        <p>
            <label>Year: </label>
            <select name="meetyear">

            </select>

        </p>

        <p>
            <label>Meet: </label>
            <select name="meet">
                <option value="none"></option>


            </select></p>

        <input type="submit" name="refreshSubmit" value="Refresh"/>

    </form>
-->

    <form method="post" action="meetentries.php?meet=$meetId&filter=$timeFrame">


        <table width="100%" id="meetEntries" class="display">

            <thead>

            <tr>
                <th>
                    Club:
                </th>
                <th>
                    Member:
                </th>
                <th>
                    Age Group:
                </th>
                <th>
                    Status:
                </th>
                <th>
                    Events:
                </th>
                <th>
                    Meals:
                </th>
                <th>
                    Massages:
                </th>
                <th>
                    Programmes:
                </th>
                <th>
                    Cost:
                </th>
                <th>
                    Paid:
                </th>
                <th>

                </th>
            </tr>

            </thead>

        </table>

        <input type="submit" name="entrySelectSubmit" value="Submit"/>


    </form>

</div>

<script>

    <?php
    if (isset($_GET['meet'])) {

        echo "var meetId = " . intval($_GET['meet']) . ";\n";

    }

    ?>

$(document).ready(function() {


    $('#meetEntries').DataTable(
        {
            "ajax" : "/swimman/json/meetentries.php?meet=" + meetId,
            "sAjaxDataProp": "",
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            dom: 'Bfrtip',
            "columns" : [
                { "data" : function (json) {
                    var clubDetails;
                    clubDetails = "<abbr title=\"" + json.clubname + "\">" + json.code + "</abbr>\n";
                    return clubDetails;
                }},
                { "data" : function (json) {
                    var fullname;
                    fullname = json.firstname + " " + json.surname + " (" + json.number + ")";
                    return fullname;
                }},
                { "data" : "groupname"},
                { "data" : "status"},
                { "data" : "entries", className : "dt-center"},
                { "data" : "meals", className : "dt-center"},
                { "data" : "massages", className : "dt-center"},
                { "data" : "programs", className : "dt-center"},
                { "data" : "cost", className : "dt-right"},
                { "data" : "paid", className : "dt-right"},
                { "data" : function (json) {
                    var entryId = json.id;
                    var editLink = "meetentry.php?entry=" + entryId;
                    return "<a href=\"" + editLink + "\"><img src=\"/swimman/images/edit.png\" alt=\"Edit\" /></a>\n";
                }, className : "dt-right"
                }
            ]
        }
    );
} );

</script>

<?php

htmlFooters();

?>
