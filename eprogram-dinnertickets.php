<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 28/2/17
 * Time: 9:19 PM
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/setup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Member.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Club.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEvent.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEntry.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/vendor/setasign/fpdf/fpdf.php');

$card = array(90, 55);

$pdf = new FPDF('P', 'mm', $card);

$pdf->SetTopMargin(0);
$pdf->SetLeftMargin(0);
$pdf->SetRightMargin(0);
$pdf->SetAutoPageBreak(false);

// get list of meals
$meals = $GLOBALS['db']->getAll("SELECT * FROM meet_entries WHERE meet_id = 112
						AND meals > 0;");
db_checkerrors($meals);

foreach ($meals as $m) {

    $memberId = $m[2];
    $clubId = $m[8];
    $numMeals = $m[4];

    $member = new Member();
    $member->loadId($memberId);
    $memberName = $member->getFullname();
    $club = new Club();
    $club->load($clubId);
    $clubName = $club->getName();

	$clubName = preg_replace('/\bClub\b/', '', $clubName);
	$clubName = preg_replace('/\bSwimming\b/', '', $clubName);
	$clubName = preg_replace('/\bInc\b/', '', $clubName);
	$clubName = preg_replace('/\bMasters\b/', '', $clubName);
	$clubName = preg_replace('/\bSwimmers\b/', '', $clubName);
	$clubName = preg_replace('/\bAUSSI\b/', '', $clubName);
	$clubName = preg_replace('/[.]/', '', $clubName);
	$clubName = preg_replace('!\s+!', ' ', $clubName);
	$clubName = trim($clubName);

    for ($i = 0; $i < $numMeals; $i++) {

	    $pdf->AddPage();

	    $midX = $card[1] / 2;

	    $memberClub = "$clubName";

		// Logo
	    $pdf->Image( $_SERVER['DOCUMENT_ROOT'] . '/images/NatChamps2017.jpg', getCentreX( 50 ), 3, 50 );

		// Meet Name
	    $pdf->SetFont( 'Arial', 'B', 14 );
	    $pdf->SetY( 15 );
	    $pdf->Cell( 0, 40, "Presentation Dinner", 0, 2, 'C' );

		// Name
	    $pdf->SetFont( 'Arial', 'B', 14 );
	    $pdf->SetY( 35 );
	    $pdf->Cell( 0, 40, $memberName, 0, 2, 'C' );

		// Club
	    $pdf->SetFont( 'Arial', 'B', 10 );
	    $pdf->SetY( 60 );
	    $pdf->Cell( 0, 40, $memberClub, 0, 2, 'C' );

    }

}

$pdf->Output();

function getCentreX($width) {

    $left = ((55 / 2) - ($width / 2));
    return $left;

}