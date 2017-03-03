<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 28/2/17
 * Time: 9:19 PM
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/setup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Member.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEvent.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/vendor/setasign/fpdf/fpdf.php');

$A6 = array(148, 105);

$pdf = new FPDF('P', 'mm', $A6);

$pdf->SetTopMargin(0);
$pdf->SetLeftMargin(0);
$pdf->SetRightMargin(0);
$pdf->SetAutoPageBreak(false);

// get list of athletes

$athList = $GLOBALS['db']->getAll("SELECT * FROM eprogram_athletes WHERE meet_id = 112;");
db_checkerrors($athList);

foreach ($athList as $a) {

    $athNo = $a[2];
    $memberId = $a[1];
    $teamNo = $a[3];

    list($clubCode, $clubName) = $GLOBALS['db']->getRow("SELECT code, clubname FROM clubs 
        WHERE id = (SELECT club_id FROM eprogram_teams WHERE meet_id = 112 AND team_no = ?);",
        array($teamNo));

    $clubName = preg_replace('/\bClub\b/', '', $clubName);
    $clubName = preg_replace('/\bSwimming\b/', '', $clubName);
    $clubName = preg_replace('/\bInc\b/', '', $clubName);
    $clubName = preg_replace('/\bMasters\b/', '', $clubName);
    $clubName = preg_replace('/\bMasters\b/', '', $clubName);
    $clubName = preg_replace('/\bSwimmers\b/', '', $clubName);
	$clubName = preg_replace('/[.]/', '', $clubName);
    $clubName = preg_replace('!\s+!', ' ', $clubName);
    $clubName = trim($clubName);

    $clubCode = trim($clubCode);

    $member = new Member();
    $member->loadId($memberId);

    $pdf->AddPage();

    $midX = $A6[1] / 2;

    if ($memberId == "") {

        $memberName = titleCase($a[4] . ' ' . $a[5]);
        $memberNumer = $a[7];

    } else {

	    $memberName = titleCase( $member->getFullname() );
	    $memberNumber = $member->getMSANumber();

    }
    $memberClub = "$clubName ($clubCode)";


// Logo
    $pdf->Image($_SERVER['DOCUMENT_ROOT'] . '/images/NatChamps2017.jpg', getCentreX(80), 6, 80);

// Meet Name
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetY(35);
    $pdf->Cell(0, 40, "MSA National Championships 2017", 0, 2, 'C');

// Name
    $pdf->SetFont('Arial', 'B', 27);
    $pdf->SetY(50);
    $pdf->Cell(0, 40, $memberName, 0, 2, 'C');

// MSA Number
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetY(60);
    $pdf->Cell(0, 40, $memberNumber, 0, 2, 'C');

// Club
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetY(70);
    $pdf->Cell(0, 40, $memberClub, 0, 2, 'C');

// Events
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetY(110);

    // Get the events
    $events = $GLOBALS['db']->getAll("SELECT * FROM eprogram_entry, eprogram_events 
                WHERE eprogram_entry.meet_id = 112
                AND eprogram_events.meet_id = 112
                AND eprogram_entry.event_ptr = eprogram_events.event_ptr
                AND ath_no = ? 
                ORDER BY eprogram_events.event_id",
        array($athNo));
    db_checkerrors($events);

    $eventCounter = 0;
    $eventsPerCol = 6;

    foreach ($events as $e) {

        $eventPtr = $e[1];

        if ($eventCounter == 6) {

            $pdf->SetY(110);

        }

        if ($eventCounter >= 6) {


            $pdf->SetX($midX);

        }

        $heatNum = $e[3];
        $laneNum = $e[4];
        $eventId = $e[11];

        $meetEvent = new MeetEvent();
        $meetEvent->load($eventId);
        $eventDist = $meetEvent->getDistanceMetres();
        $prog = $meetEvent->getProgNumber();
        $disc = $meetEvent->getDiscipline();

        switch ($disc) {
            case 1:
                $discText = "Free";
                break;
            case 2:
                $discText = "Breast";
                break;
            case 3:
                $discText = "Fly";
                break;
            case 4:
                $discText = "Back";
                break;
            case 5:
                $discText = "IM";
                break;
        }

        $pdf->MultiCell($midX, 2.5, "#" . $prog . " " . $eventDist . "m " . "$discText H$heatNum L$laneNum", 0, 'C');
        $pdf->Ln();

//        if ($eventCounter == 6) {
//
//            $pdf->SetY(110);
//            $pdf->SetX($midX);
//
//        }

        $eventCounter++;

    }

}

$pdf->Output();

function getCentreX($width) {

    $left = ((105 / 2) - ($width / 2));
    return $left;

}