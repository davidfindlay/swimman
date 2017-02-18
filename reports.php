<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 18/02/17
 * Time: 3:38 PM
 */

require_once("includes/setup.php");
require_once("includes/classes/MeetEventEntryReport.php");

checkLogin();

htmlHeaders("Swimming Management System - Meet Entry List");

//sidebarMenu();

?>

<div id="main">

    <h1>Reports</h1>

    <table id="report" width="100%">
        <thead>
        <tr>
            <th>
                Member
            </th>
            <th>
                Club
            </th>
            <th>
                Event
            </th>
            <th>
                Seed Time
            </th>
            <th>
                Check Status
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th>
                Member
            </th>
            <th>
                Club
            </th>
            <th>
                Event
            </th>
            <th>
                Seed Time
            </th>
            <th>
                Check Status
            </th>
        </tr>
        </tfoot>

    </table>

    <script>

        $('#report').DataTable(
            {
                "ajax" : "/swimman/json/report.php",
                "sAjaxDataProp": "",
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                dom: 'Bfrtip',
                "columns" : [
                    { "data" : function (json) {
                        var fullname;
                        fullname = json.firstname + " " + json.surname + " (" + json.number + ")";
                        return fullname;
                    }},
                    { "data" : function (json) {
                        var clubDetails;
                        clubDetails = "<abbr title=\"" + json.clubname + "\">" + json.code + "</abbr>\n";
                        return clubDetails;
                    }},
                    { "data" : function (json) {
                        var eventDetails;
                        eventDetails = "#" + json.event + " " + json.distance + " " + json.discipline;
                        return eventDetails;
                    }},
                    { "data" : "formatedtime"},
                    { "data" : "warning"}
                ]
            }
        );

    </script>

</div>
</body>
</html>