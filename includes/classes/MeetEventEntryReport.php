<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/classes/Report.php');

/**
 * Created by PhpStorm.
 * User: david
 * Date: 18/02/17
 * Time: 2:36 PM
 */
class MeetEventEntryReport extends Report {

    protected $name = "Meet Event Entry Report";

    public function get() {

        // Set up the Associative Array fetch mode
        $GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);

        $sql = "SELECT meet_entries.id as entry_id,
member.firstname,
member.surname,
member.number,
clubs.code,
clubs.clubname,
CONCAT(meet_events.prognumber, meet_events.progsuffix) as event,
event_distances.distance,
event_disciplines.discipline,
meet_events_entries.seedtime,
event_distances.metres,
FROM meet_entries, meet_events_entries, meet_events, member, clubs, event_disciplines, event_distances
WHERE meet_entries.meet_id = ?
AND meet_entries.id = meet_events_entries.meet_entry_id
AND meet_events_entries.event_id = meet_events.id
AND meet_entries.member_id = member.id
AND meet_events.discipline = event_disciplines.id
AND meet_events.distance = event_distances.id
AND meet_events.legs = 1
AND meet_entries.club_id = clubs.id
ORDER BY entry_id ASC, meet_events.prognumber ASC, meet_events.progsuffix ASC";

        $data = $GLOBALS['db']->getAll($sql, array(112));
        db_checkerrors($data);

        // Set up the Associative Array fetch mode
        $GLOBALS['db']->setFetchMode(DB_FETCHMODE_ORDERED);

        $outputData = array();

        // Step through the data and check the times
        foreach ($data as $d) {

            $metres = $d['metres'];
            $seedTime = $d['seedtime'];
            $d['formatedtime'] = sw_formatSecs($seedTime);
            $warning = "";

            if ($seedTime / $metres > 3.125) {

                // If time is faster than 8 seconds per 25m
                $warning = "Too fast";

            } elseif ($seedTime / $metres < 0.3125) {

                // If time is slower than 80 seconds per 25m
                $warning = "Too slow";

            }

            $d['warning'] = $warning;

        }

        return $outputData;

    }

}