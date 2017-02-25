<?php

/**
 * @version		$Id: controller.php 15 2009-11-02 18:37:15Z chdemko $
 * @package		Joomla16.Tutorials
 * @subpackage	Components
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @author		Christophe Demko
 * @link		http://joomlacode.org/gf/project/entrymanager_1_6/
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/setup.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Meet.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEntry.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEntryEvent.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEvent.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetProgram.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Club.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Member.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/EntryChecker.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/RelayEntry.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/RelayEntryMember.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/PayPalEntryPayment.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/PayPal-PHP-SDK/autoload.php');

//require_once("libphp-phpmailer/class.phpmailer.php");

//require_once( JPATH_COMPONENT.DS.'header.php' );
//require_once(JPATH_COMPONENT.DS.'PhpSocket.php');

/**
 * Hello World Component Controller
 */
class EntryManagerController extends JController {
	
	public $phpSocket;
	
	function __construct() {
		
		$sess = JFactory::getSession();
		
		$jinput = JFactory::getApplication()->input;
		
		// Find which view has been selected
		$viewName = JRequest::getVar('view');
		
		$curJUser = JFactory::getUser();
		$curUserId = $curJUser->id;
		$curUsername = $curJUser->username;

		if ($viewName == 'step1' && $jinput->get('emSubmit1c') != "Next") {
			
			// Get Joomla User ID
			$curJUser = JFactory::getUser();
			$curUserId = $curJUser->id;
			$curUsername = $curJUser->username;
			
			// Look up Swimman DB to see if this user is linked to a member
			$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite WHERE joomla_uid = '$curUserId';");
			db_checkerrors($memberId);
			
			// Check if member has access to other members
			$clubsAccess = $GLOBALS['db']->getAll("SELECT DISTINCT(club_id) FROM club_roles
					WHERE member_id = '$memberId';");
			db_checkerrors($clubsAccess);
			
			if (count($clubsAccess) >= 1) {
				
				// Member is a club captain so direct to view step1c
				JRequest::setVar('view', 'step1c', 'method', true);
				
			}
			
			$memberDets = new Member();
			$memberDets->loadId($memberId);
			$membersNominee = $memberDets->isNominee();
			
			if (count($membersNominee) >= 1) {
				
				// Member is a nominee
				JRequest::setVar('view', 'step1c', 'method', true);
				
			}
			
		}
		
		// Step 1c Submit
		if ($jinput->get('emSubmit1c') == "Next") {
			
			$sess->clear('emEntryEdit');
			
			$emYourself = $jinput->get('emYourself');
			$emSomeoneElse = $jinput->get('emSomeoneElse');
			
			if ($emSomeoneElse != "") {
				
				// Set entrant variable
				$sess->set('emEntrant', $emSomeoneElse);
				
			}
			
			if ($emYourself == '' && $emSomeoneElse == '') {

				$sess->set('emErrorStep1c', 'You must select an option!');
				JRequest::setVar('view', 'step1c', 'method', true);
				
			} else {
			
				JRequest::setVar('view', 'step1', 'method', true);
				
			}
			
		}
		
		// Step 1 Submit
		if ($jinput->get('emSubmit1') == "Next") {
			
			// Retrieve data from page
			$sess->clear('emEntryEdit');
			
			// Get Joomla User ID
			$curJUser = JFactory::getUser();
			$curUserId = $curJUser->id;
			$curUsername = $curJUser->username;
			
			// Look up Swimman DB to see if this user is linked to a member
			$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite 
					WHERE joomla_uid = '$curUserId';");
			db_checkerrors($memberId);
			
			$emEntrant = $sess->get('emEntrant');
			$emMeetId = $jinput->get('emMeetId');
			
			if ($emEntrant == '') {
			
				$sess->set('emMemberId', $memberId);
				$chkEntrant = $memberId;
				
			} else {
				
				$chkEntrant = $emEntrant;
				
			}
			
			$sess->set('emMeetId', $emMeetId);

			// Check if a club ID has been defined
			$chosenClub = $jinput->get('emClubId');
			
			if ($chosenClub != "") {
				
				$sess->set('emClubId', $chosenClub);
				$sess->clear('emClubError');

                JRequest::setVar('view', 'step2', 'method', true);
				
			} else {

			    $sess->set('emClubError', 'You must choose a club for this entry!');

                // No event entries
                JRequest::setVar('view', 'step1', 'method', true);

            }
			
			
			// Check if member has already entered this meet
			$otherEntries = $GLOBALS['db']->getAll("SELECT * FROM meet_entries WHERE member_id = '$chkEntrant'
					AND meet_id = '$emMeetId';");
			db_checkerrors($otherEntries);
			
			if (count($otherEntries) > 0) {
					
				$entryId = $otherEntries[0][0];
				$entryData = new MeetEntry();
				$entryData->loadId($entryId);

//                if ($sess->get('emClubId') != $entryData->getClubId()) {
//
//                    $entryData->setClubId($sess->get('emClubId'));
//
//                }
				
				$sess->set('emEntryData', serialize($entryData));
                $sess->set('emEntryId', $entryId);
				$sess->set('emEntryEdit', 'true');

                // Set the club Id to the one from the existing entry
                // TODO: make this better - currently you can't change clubs
                $sess->set('emClubId', $entryData->getClubId());
					
			} else {
				
				$sess->set('emEntryEdit', 'false');
				
			}
			
			// JRequest::setVar('view', 'step2', 'method', true);
			
			
			
		}
		
		if ($jinput->get('emSubmit1') == "Back") {
			
			// Get Joomla User ID
			$curJUser = JFactory::getUser();
			$curUserId = $curJUser->id;
			$curUsername = $curJUser->username;
				
			// Look up Swimman DB to see if this user is linked to a member
			$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite WHERE joomla_uid = '$curUserId';");
			db_checkerrors($memberId);
				
			// Check if member has access to other members
			$clubsAccess = $GLOBALS['db']->getAll("SELECT DISTINCT(club_id) FROM club_roles
					WHERE member_id = '$memberId';");
			db_checkerrors($clubsAccess);
			
			$sess->clear('emEntryData');
			$sess->clear('emMemberId');
			$sess->clear('emMeetId');
			$sess->clear('emClubId');
			$sess->clear('emEntrant');
			$sess->clear('emErrorStep1c');
			$sess->clear('emEntryEdit');
			
			if (count($clubsAccess) >= 1) {
					
				// Member is a club captain so direct to view step1c
				JRequest::setVar('view', 'step1c', 'method', true);
					
			} else {

				header("Location: entry-manager-new/");
				
			}
			
			
		}
			
		// Step 2 Submit
		if ($jinput->get('emSubmit2') == "Next") {
			
			// Retrieve data from page
			// Search through events list to find any entries
			
			$entrant = $sess->get('emEntrant');
			
			if ($entrant != '') {

				// TODO: Confirm that current member can access entrant
				$entryData = new MeetEntry($sess->get('emEntrant'), $sess->get('emClubId'), $sess->get('emMeetId'));
				
			} else {

				$entryData = new MeetEntry($sess->get('emMemberId'), $sess->get('emClubId'), $sess->get('emMeetId'));
				
			}
			
			$numMeals = 0;
            $massages = 0;
			$entryErrors = "";
			$numMeals = $jinput->get('numMeals');
            $massages = $jinput->get('numMassages');
            $programs = $jinput->get('numPrograms');
			$medical = $jinput->get('medical');
			$comments = $jinput->get('comments', null, "STRING");
			
			// If there are meals requested add them
			$entryData->setNumMeals($numMeals);

            // If there are massages requested add them
            $entryData->setMassages($massages);

            // If there are programs requested add them
            $entryData->setPrograms($programs);
			
			if ($medical == "on")
				$entryData->setMedical(TRUE);
			else
				$entryData->setMedical(FALSE);
			
			$entryData->setNotes($comments);
			
			if (isset($_POST['enterEvent'])) {
			
				foreach ($_POST['enterEvent'] as $e) {
					
					// Get seedtime entered
					if ($jinput->get("nt_$e", 'off', 'string') == "on") {
						
						$st = "0";
						
					} else {

						$st = $jinput->get("seedtime_$e", '', 'string');
						
					}
						
					// Reprocess to standard format - seed time goes into system as seconds
					if (strpos($st, ':') !== FALSE) {
							
						$stArray = explode(':', $st);
				 		$seconds = (floatval($stArray[0]) * 60) + floatval($stArray[1]);
					 	
					} else {
							
						$seconds = floatval($st);
							
					}
						
					$entryData->addEvent("$e", "$seconds");
						
					
				}
				
			}
			
			
			$sEntryData = serialize($entryData);
			
			$sess->set('emEntryData', $sEntryData);
			
			// Validate entries
			
			$meetDetails = new Meet();
			$meetDetails->loadMeet($sess->get('emMeetId'));
			
			// First check for too many events
			if (($entryData->getNumEntries() > $meetDetails->getMax()) && ($meetDetails->getMax() != 0)) {
				
				JRequest::setVar('view', 'step2', 'method', true);
				$sess->clear('emEntryErrorGroups');
				//echo "What? Too Many Entries!\n";
				
			} elseif (count($_POST['enterEvent']) < 1) {
				
				//echo "What? No entries!\n";
				
				$sess->clear('emEntryErrorGroups');
				
				// No event entries
				JRequest::setVar('view', 'step2', 'method', true);
				
			} else {
			
				// Second check for meet group violations
				$entryErrors = $entryData->checkMeetGroups();
				
				if ($entryErrors != 1) {
				
					// Errors exist - return to Step 2 to display errors
					$sess->set('emEntryErrorGroups', $entryErrors);
					
					//echo "What? Entry errors!\n";
					
					JRequest::setVar('view', 'step2', 'method', true);
				
				} else {
	
					// Check all entry times for times that are too fast
					$entryEvents = $entryData->getEvents();
					
					$tcFail = false;
					
					foreach ($entryEvents as $d) {
						
						$tcEventId = $d->getEventId();
						$tcSeedTime = $d->getSeedTime();
						
						$eventDetails = new MeetEvent();
						$eventDetails->load($tcEventId);
						$tcDistance = $eventDetails->getDistanceMetres();

						if ((($tcDistance * 0.28) > $tcSeedTime) && ($tcSeedTime != 0)) {
							
							$tcFail = true;
							
						}
						
					}
					
					$sess->clear('emEntryErrorGroups');
					
					// If time check has failed, don't allow progression
					if ($tcFail == true) {
						
						JRequest::setVar('view', 'step2', 'method', true);
						
					} else {
						
						JRequest::setVar('view', 'step3', 'method', true);
						
					}
					
				}
			
			}
			
		}

		// Step 2 Back
		if ($jinput->get('emSubmit2') == "Back") {
			
			JRequest::setVar('view', 'step1', 'method', true);
			
		}

		// Step 3 Submit
		if ($jinput->get('emSubmit3') == "Submit") {
			
			// Load entry information
			$entryD = $sess->get('emEntryData');
			$entryDetails = unserialize($entryD);
			
			// Update entry or create entry
			if ($entryDetails->checkConflicting() == true) {
				
				// Update entry
				//$entryDetails->update(5, 11);

                $oldEntry = new MeetEntry($entryDetails->getMemberId(), $entryDetails->getClubId(), $entryDetails->getMeetId());
                $oldEntry->load();
                $oldEntry->calcCost();
                $oldCost = $oldEntry->getCost();

                $oldEntryId = $oldEntry->getId();
                $sess->set("emEntryId", $oldEntryId);

				$entryDetails->updateExisting();

                // Check if payment needs to be made
                $alreadyPaid = $oldEntry->getPaid();

                $entryDetails->updateCost();
                $newCost = $entryDetails->getCost();
                $differentCost = $newCost - $alreadyPaid;

                // Update the entry data
                $sEntryData = serialize($entryDetails);
                $sess->set('emEntryData', $sEntryData);

                //addlog("test", "newCost = $newCost - alreadyPaid = $alreadyPaid - differentCost = $differentCost");

                $meetId = $entryDetails->getMeetId();
                $meetDetails = new Meet();
                $meetDetails->loadMeet($meetId);
                $meetName = $meetDetails->getName();

                if ($differentCost == 0) {

                    // User has already paid
                    JRequest::setVar('view', 'step4', 'method', true);

                } else if ($differentCost > 0) {

                    // Get payment choice
                    $paymentType = $jinput->get('paymentType');

                    // Confirm payment choice is available
                    if (!in_array($meetDetails->getPaymentTypes())) {

                        // TODO Return error
                        JRequest::setVar('view', 'step3', 'method', true);

                    }

                    // PayPal payments are type 1
                    if ($paymentType == 1) {

                        // Send to paypal
                        $pp = new PayPalEntryPayment();
                        $pp->setMeetName($meetName);
                        $pp->setEntryId($oldEntryId);
                        $pp->addItem("Meet Entry Amendment", 1, $differentCost);

                        $approvalUrl = $pp->processPayment();

                        $app = JFactory::getApplication();
                        $app->redirect($approvalUrl, "Redirecting to PayPal", $msgType = 'message');

                    }

                } elseif ($differentCost < 0) {

                    // Raise refund
                    $sess->set("emRefundAmount", $differentCost);

                    JRequest::setVar('view', 'step4', 'method', true);

                }

				
			} else {
			
				$entryDetails->setStatus(5);  // Awaiting Payment
				$entryDetails->setEventStatuses(5); 	// Pending
			

				$entryCreated = $entryDetails->create();

                if ($entryCreated) {

                    $entryDetails->calcCost();

                    $entryId = $entryDetails->getId();
//                    $entryMember = $entryDetails->getMemberId();
                    $sess->set('emEntryId', $entryId);


                    $meetDetails = new Meet();
                    $meetId = $sess->get('emMeetId');
                    $meetDetails->loadMeet($meetId);
                    $meetName = $meetDetails->getName();
//                    $clubDetails = new Club();
//                    $clubId = $sess->get('emClubId');
//                    $clubDetails->load($clubId);

//                    $submitterId = $entryDetails->getMemberId();
//                    $subDetails = new Member();
//                    $subDetails->loadId($submitterId);

                    // Check payment method selected
                    // Get payment choice
                    $paymentType = $jinput->get('paymentType');

                    // Confirm payment choice is available
                    if (!in_array($meetDetails->getPaymentTypes())) {

                        // TODO Return error
                        JRequest::setVar('view', 'step3', 'method', true);

                    }

                    // PayPal payments are type 1
                    if ($paymentType == 1) {

                        $pp = new PayPalEntryPayment();
                        $pp->setMeetName($meetName);
                        $pp->setEntryId($entryId);
                        $pp->addItem("Meet Entry", 1, $meetDetails->getMeetFee());

                        if ($entryDetails->getNumEntries() > 0) {
                            $pp->addItem("Individual Entries", $entryDetails->getNumEntries(), $entryDetails->calcEventFee());
                        }

                        if ($entryDetails->getNumMeals() > 0) {
                            $pp->addItem($meetDetails->getMealName(), $entryDetails->getNumMeals(), $entryDetails->getMealFee());
                        }

                        if ($entryDetails->getMassages() > 0) {
                            $pp->addItem("Massages", $entryDetails->getMassages(), $entryDetails->getMassageFee());
                        }

                        if ($entryDetails->getPrograms() > 0) {
                            $pp->addItem("Programmes", $entryDetails->getPrograms(), $entryDetails->getProgramFee());
                        }

                        $approvalUrl = $pp->processPayment();

                        $app = JFactory::getApplication();
                        $app->redirect($approvalUrl, "Processing PayPal Payment", $msgType = 'message');

                    } else {

                        // Unset session
//                        unset($entryDetails);
//                        $sess->clear('emEntryData');
//                        $sess->clear('emMemberId');
//                        $sess->clear('emEntrant');
//                        $sess->clear('emMeetId');
//                        $sess->clear('emClubId');
//                        $sess->clear('emEntryEdit');
//                        $sess->clear('emEntryId');

                        // Return to Entry List
                        JRequest::setVar('view', 'step4', 'method', true);


                    }


                    // Update the entry data
                    $sEntryData = serialize($entryData);
                    $sess->set('emEntryData', $sEntryData);


                } else {

                    addlog("Entry Manager", "Unable to create entry", $entryD);

                    // Return to Entry List
                    JRequest::setVar('view', 'step3', 'method', true);

                }
				
			}
			
		}
		
		// Step 3 Back
		if ($jinput->get('emSubmit3') == "Back") {
			
			JRequest::setVar('view', 'step2', 'method', true);
			
		}

		// Process Club Recorder updates
		if ($jinput->get('emClubUpdate') == "Update") {
			
			// Check if Club Recorder has cancelled entry
			
			if (isset($_POST['cancelEntry'])) {
			
				foreach ($_POST['cancelEntry'] as $c) {
					
					// Cancel the entry this relates to
					$cEntry = new MeetEntry();
					
					$cEntry->loadId($c);
					$cEntry->cancel();
					
					addlog("Enter Manager", "Club recorder cancelled entry $c", "", $curUserId);
					
				}
				
			}
			
		}
		
		// Invoke Entry Tool
		// TODO: change to use the proper system
		if (isset($_POST['emSendEntries'])) {
		if ($_POST['emSendEntries'] == "Check Entries") {
			
			// Clear data initially
			$sess->clear('ecEntries');
			$sess->clear('ecRelays');
			$sess->clear('ecErrors');
			$sess->clear('ecMemberErrors');
			$sess->clear('ecEventErrors');
			$sess->clear('ecEnteringClub');
			
			$uploaddir = $GLOBALS['home_dir'] . '/masters-data/entries/';
			
			// Check that this member has the appropriate roles
			//if (isset($_POST['tmClubId'])) {
			
		//		$cusClubId = $_POST['tmClubId'];
				
			//}
			
			//$cusMeetId = $_POST['tmMeetId'];
			
			$uploadfile = $uploaddir . $_FILES['emUserfile']['name'];
			$sess->set('ecEntryFile', $_FILES['emUserfile']['name']);
						
			// Get Joomla User ID
			$curJUser = JFactory::getUser();
			$curUserId = $curJUser->id;
			$curUsername = $curJUser->username;
			
			// Look up Swimman DB to see if this user is linked to a member
			$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite WHERE joomla_uid = '$curUserId';");
			db_checkerrors($memberId);
			
			$member = new Member;
			$member->loadId($memberId);
			$memberFullname = $member->getFullname();
			$memberClubs = $member->getClubIds();
			
			if (isset($_POST['tmClubId'])) {
				
				$enteringClub = $_POST['tmClubId'];
				
			} else {
				
				$enteringClub = $memberClubs[0];
				
			}
			
			$sess->set('ecEnteringClub', $enteringClub);
			
			// Check member is allowed to submit for this club
			// if ($member->checkRole($enteringClub, 1) || $member->checkRole($enteringClub, 2)) {
				
				if (move_uploaded_file($_FILES['emUserfile']['tmp_name'], $uploadfile)) {
				
					$curJUser = JFactory::getUser();
					$curUserId = $curJUser->id;
					$curUsername = $curJUser->username;
						
					// Look up Swimman DB to see if this user is linked to a member
					$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite
							WHERE joomla_uid = '$curUserId';");
					db_checkerrors($memberId);
				
					$entryChecker = new EntryChecker();
					
					if ($entryChecker->loadFile($uploadfile)) {

						$entryChecker->processFile($enteringClub);
						
						$errorCount = count($entryChecker->getErrors());
						$memErrorCount = count($entryChecker->getMemberErrors());
						$eventErrorCount = count($entryChecker->getEventErrors());
						
						$sess->set('ecEntries', serialize($entryChecker->getEntries()));
						$sess->set('ecRelays', serialize($entryChecker->getRelays()));
						$sess->set('ecErrors', $entryChecker->getErrors());
						$sess->set('ecMemberErrors', $entryChecker->getMemberErrors());
						$sess->set('ecEventErrors', $entryChecker->getEventErrors());
						
						// Check which meet ID this this for
						$cusMeetId = $entryChecker->getMeetId();
		
						addlog("Enter Manager", "Entry checked for $cusMeetId by club $enteringClub", "<p>Errors: $errorCount<br />Member Errors: $memErrorCount<br />Event Errors: $eventErrorCount</p>", $curUserId);
						
						JRequest::setVar('view', 'entrycheck', 'method', true);
						
					}
				
				} else {
				
					echo "Unable to move file.\n";
				
				}
				
			// }
			
			// Don't move on unless file has been checked
			
			//JRequest::setVar('view', 'entrycheck', 'method', true);
			
		}
		}
		
		if (isset($_POST['ecSubmitEntry'])) {
		if ($_POST['ecSubmitEntry'] == "Submit Entries") {
			
			// Get Joomla User ID
			$curJUser = JFactory::getUser();
			$curUserId = $curJUser->id;
			$curUsername = $curJUser->username;
				
			// Look up Swimman DB to see if this user is linked to a member
			$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite WHERE joomla_uid = '$curUserId';");
			db_checkerrors($memberId);
				
			$member = new Member;
			$member->loadId($memberId);
			$memberFullname = $member->getFullname();
			$memberClubs = $member->getClubIds();
			
			$entryList = unserialize($sess->get('ecEntries'));
			$relayList = unserialize($sess->get('ecRelays'));
			$errors = $sess->get('ecErrors');
			$memberErrors = $sess->get('ecMemberErrors');
			$eventErrors = $sess->get('ecEventErrors');
			$enteringClub = $sess->get('ecEnteringClub');
			
			// TODO: add validation allowing meet organisers to import entries
			
			if ($member->checkRole($enteringClub, 1) || $member->checkRole($enteringClub, 2)) {
				
				// If no errors, submit entries
				if (count($errors) == 0 && count($memberErrors) == 0 && count($eventErrors) == 0) {
					
					if (count($entryList) > 0) {
						
						foreach ($entryList as $entry) {
							
							// Check if entry already exists for this member
							$mId = $entry->getMemberId();
							$cId = $entry->getClubId();
							$eId = $entry->getMeetId();
							
							$existEntry = new MeetEntry($mId, $cId, $eId);
							
							if ($existEntry->load()) {
							
								// Update entry
								$existEntry->setStatus(5);
								$existEntry->updateStatus();
								$existEntry->updateEvents($entry->getEvents(), 5, 11);

							} else {
								
								// No Existing entry
								$entry->setStatus(5);
								$entry->setEventStatuses(5);
								$entry->create();
								
							}
							
						}
						
					}
					
					// Handle relays
					if (count($entryList) > 0) {
						
						foreach ($relayList as $r) {
							
							$rmeetId = $r->getMeet();
							$rEventId = $r->getMeetEvent();
							$rclubId = $r->getClub();
							$rageGroup = $r->getAgeGroup();
							$rrelayLetter = $r->getLetter();
							
							// Check if existing relay exists
							$relayExists = $GLOBALS['db']->getAll("SELECT id FROM meet_entries_relays
									WHERE meet_id = ? AND club_id = ? AND meetevent_id = ? 
									AND letter = ? and agegroup = ?;",
									array($rmeetId, $rEventId, $rclubId, $rrelayLetter, $rageGroup));
							db_checkerrors($relayExists);
							
							if (count($relayExists) > 0) {
								
								$existingRelayId = $relayExists[0][0];
								$existingRelay = new RelayEntry();
								$existingRelay->load($existingRelayId);
								$existingRelay->delete();
								
							}
							
							// Create the relay
							$r->create();
							
						}
						
					}
					
					// Update file upload status
					$tmFilename = $sess->get('ecEntryFile');
					$uploaddir = $GLOBALS['home_dir'] . '/masters-data/entries/';
					$uploadtmp = $uploaddir . $tmFilename;
					
					if (!file_exists($uploaddir . $eId)) {
						mkdir($uploaddir . $eId, 0777, true);
					}
					
					if (!file_exists($uploaddir . $eId . '/' . $cId)) {
						mkdir($uploaddir . $eId . '/' . $cId, 0777, true);
					}
					
					rename($uploaddir . $tmFilename, $uploaddir . $eId . '/' . $cId . '/' . $tmFilename);
					
					$insert = $GLOBALS['db']->query("INSERT INTO meet_entry_files (filename, uploaded,
							juser, member, clubid, meetid) VALUES ('$tmFilename', now(), '$curUserId', 
							'$memberId', '$enteringClub', '$eId');");
					db_checkerrors($insert);
					
					// Log entry submission
					$clubDet = new Club();
					$clubDet->load($enteringClub);
					$enteringClubCode = $clubDet->getCode();
					
					$meetDet = new Meet();
					$meetDet->loadMeet($eId);
					$eMeet = $meetDet->getName();
					
					addlog("Enter Manager", "TM Entry submitted for $eMeet by club $enteringClubCode", "", $curUserId);
					
				}
				
			}
			
			$sess->clear('ecEntries');
			$sess->clear('ecRelays');
			$sess->clear('ecErrors');
			$sess->clear('ecMemberErrors');
			$sess->clear('ecEventErrors');
			$sess->clear('ecEnteringClub');
			
			JRequest::setVar('view', 'clubentries', 'method', true);
			
			
		}
		}
		
		if (isset($_POST['importbackupfile'])) {
			
			$meetId = mysql_real_escape_string($_POST['meetId']);
			
			$uploaddir = $GLOBALS['home_dir'] . '/masters-eprogram';
			$uploadfile = $uploaddir . '/' . basename($_FILES['userfile']['name']);
			$uploadname = $_FILES['userfile']['name'];
			
			if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
			
				$eProgram = new MeetProgram();
				$eProgram->setMeet($meetId);
				$eProgram->import($uploadname);
				
				echo "<strong>Meet program imported, Entry Manager updated.</strong><br />\n";
				echo " <br />You can upload again using the form below<br />\n";
			
			}
			
		}
		
		// Handle registration of a guest member
		if ($jinput->get('emGuestReg') == "Submit") {
			
			// Get Joomla User ID
			$curJUser = JFactory::getUser();
			$curUserId = $curJUser->id;
			$curUsername = $curJUser->username;
			
			// Look up Swimman DB to see if this user is linked to a member
			$memberId = $GLOBALS['db']->getOne("SELECT member_id FROM member_msqsite WHERE joomla_uid = '$curUserId';");
			db_checkerrors($memberId);
			
			$member = new Member;
			$member->loadId($memberId);
			$memberFullname = $member->getFullname();
			$memberClubs = $member->getClubIds();

			
			$newFirstName = trim($jinput->get('emGuestFirstName'));
			$newSurname = trim($jinput->get('emGuestSurname'));
			$newOtherNames = trim($jinput->get('emGuestOtherNames'));
			$newDobDay = trim($jinput->get('emGuestDobDay'));
			$newDobMon = trim($jinput->get('emGuestDobMon'));
			$newDobYear = trim($jinput->get('emGuestDobYear'));
			$newGuestGender = $jinput->get('emGuestGender');
			
			$newGuestClub = $jinput->get('emGuestClubId');
			
			if ($newGuestClub == '') {
				
				$newGuestClub = $memberClubs[0];
				
			}
			
			$newGuestErrors = array();
			
			if ($newFirstName == "") {
				
				$newGuestErrors[] = "You must provide the first name of the Guest Swimmer!";
				
			}
			
			if ($newSurname == "") {
			
				$newGuestErrors[] = "You must provide the surname of the Guest Swimmer!";
			
			}
			
			if ($newDobDay == "" || $newDobMon == "" || $newDobYear == "") {
				
				$newGuestErrors[] = "You must provide the date of birth of the Guest Swimmer!";
				
			}
			
			if (strlen($newDobYear) > 4) {
				
				$newGuestErrors[] = "The year of date of birth must be provided as 4 digits!";
				
			}
			
			$newDob = substr($newDobYear, 0, 4) . '-' . substr($newDobMon, 0, 2) . '-' . substr($newDobDay, 0, 2);
			
			if (count($newGuestErrors) == 0) {
				
				// Create a guest number
				$highestGuest = $GLOBALS['db']->getOne("SELECT number FROM member WHERE number LIKE 'GUEST%' 
						ORDER BY number DESC LIMIT 1;");
				db_checkerrors($highestGuest);
				
				if (!isset($highestGuest)) {
					
					$guestNum = "GUEST00001";
					
				} else {
					
					$guestNumPart = intval(substr($highestGuest, 5, 5));
					$guestNumPart++;
					
					$guestNum = "GUEST" . str_pad($guestNumPart, 5, '0', STR_PAD_LEFT);
					
				}
				
				
				// Create user
				$newGuest = new Member();
				$newGuest->create($guestNum, $newSurname, $newFirstName, $newOtherNames, $newDob, $newGuestGender);
				
				// Apply a guest membership to the user
				$newGuestStartDate = date('Y-m-d');

				$newGuest->applyMembership(3, $newGuestClub, $newGuestStartDate);
				
				echo "Guest membership created, membership expires 4 weeks from today.<br />\n";
				
			} else {
				
				if (count($newGuestErrors) > 0) {
					
					foreach ($newGuestErrors as $e) {
						
						echo "$e<br />\n";
						
					}
					
				}
				
			}
			
		}
		
		if ($jinput->get('emNomSubmit') == "Submit") {
			
			$nomMemId = $jinput->get('nomMember');
			$nomNomId = $jinput->get('nomNominee');
			
			// Load and validate end date
			$nomEndDay = $jinput->get('emNomEndDay');
			$nomEndMon = $jinput->get('emNomEndMon');
			$nomEndYear = $jinput->get('emNomEndYear');
			$nomEndDate = "0000-00-00";
			$invalidDate = 0;
			
			if ($nomEndYear != "") {
				
				if (preg_match('/[0-9]{4}/', $nomEndYear) === 1 && preg_match('/[0-9]{1,2}/', $nomEndMon) === 1 && preg_match('/[0-9]{1,2}/', $nomEndDay) === 1) {
					
					$nomEndDate = $nomEndYear . '-' . $nomEndMon . '-' . $nomEndDay;
					
					// Check date does not equal today or earlier
					$nomEndDt = strtotime($nomEndDate . " 23:59:59");
					
					if ($nomEndDt < time()) {
						
						$invalidDate = 1;
						
					}
					
				} else {
					
					$invalidDate = 1;
					
				}
				
			}
			
			// New nominee arrangment
			if ($nomMemId != "" && $nomNomId != "") {
				
				$nomMemDet = new Member();
				$nomMemDet->loadid($nomMemId);
				
				if ($invalidDate == 0) {
				
					if ($nomMemDet->addNominee($nomNomId, $nomEndDate)) {
						
						echo "Nominee successfully added<br />\n";
						
					} else {
						
						echo "Unable to add nominee!<br />\n";
						
					}
					
				} else {
					
					echo "Invalid nominee end date provided! Please use DD/MM/YYYY format and the date provided must be after today.";
				}
				
			}
			
			// Check for removes
			foreach ($_POST as $key => $value) {
				
				if ($key == "nomDel") {
					
					$removeNomId = $value;
					
					$nomDet = $GLOBALS['db']->getRow("SELECT * FROM member_access WHERE id = '$removeNomId';");
					db_checkerrors($nomDet);
					$nomMemId = $nomDet[1];
					
					$nomMemDet = new Member();
					$nomMemDet->loadid($nomMemId);
					
					if ($nomMemDet->hasNominee($nomDet[2])) {
					
						if ($nomMemDet->delNominee($nomDet[2])) {
						
							echo "Nominee arrangement ended!<br />\n";
						
						} else {
						
							echo "Unable to remove nominee!<br />\n";
						
						}
						
					} else {
						
						echo "Nominee arrangement has already been ended!<br />\n";
						
					}
					
				}
				
			}
			
			
			
		}
		
		// Handle meet filter on club entries and my entries page
		if (isset($_POST['emMeetFilterSubmit'])) {
			
			$sess->set('emMeetFilter', $_POST['emMeetFilter']);
						
		}
		
		if (isset($_POST['emMeetViewSubmit'])) {
			
			$sess->set('emMeetView', $_POST['meetSelect']);
				
		}
		
		// New meet filter
		if (isset($_POST['emMeetFilter'])) {
				
			$sess->set('emMeetFilter', $_POST['emMeetFilter']);
			$sess->set('emMeetView', $_POST['meetSelect']);
		
		}
		
		// Handle creating a relay entry
		if (isset($_POST['createRelaySubmit'])) {
			
			// Get data
			$eventId = mysql_real_escape_string($_POST['event']);
			$letter = mysql_real_escape_string($_POST['letter']);
			$meetId = mysql_real_escape_string($_POST['meet']);
			$clubId = mysql_real_escape_string($_POST['club']);
			$suppliedAge = mysql_real_escape_string($_POST['agegroup']);
			$swimmer1 = mysql_real_escape_string($_POST['swimmer1']);
			$swimmer2 = mysql_real_escape_string($_POST['swimmer2']);
			$swimmer3 = mysql_real_escape_string($_POST['swimmer3']);
			$swimmer4 = mysql_real_escape_string($_POST['swimmer4']);
			$seedtime = mysql_real_escape_string($_POST['seedTime']);
			
			// Basic errors checking
			$error = "";
			if ($swimmer1 == "" || $swimmer2 == "" || $swimmer3 == "" || $swimmer4 == "") {
				
				if ($suppliedAge == "") {
				
					$error = "Please select an age group or 4 swimmers!";
				
				}
				
			}
			
			if ($error == "") {
			
				$rEntry = new RelayEntry();
				$rEntry->setMeet($meetId);
				$rEntry->setClub($clubId);
				$rEntry->setEvent($eventId);
				$rEntry->setLetter($letter);

				// Check that each swimmer has an individual entry
				if ($swimmer1 != "") {

					$mEntry1 = new MeetEntry($swimmer1, $clubId, $meetId);
					$mEntry2 = new MeetEntry($swimmer2, $clubId, $meetId);
					$mEntry3 = new MeetEntry($swimmer3, $clubId, $meetId);
					$mEntry4 = new MeetEntry($swimmer4, $clubId, $meetId);
				
					if (!$mEntry1->load()) {
						
						$mEntry1->create();
						
					}
					
					if (!$mEntry2->load()) {
							
						$mEntry2->create();
							
					}
					
					if (!$mEntry3->load()) {
							
						$mEntry3->create();
							
					}
					
					if (!$mEntry4->load()) {
							
						$mEntry4->create();
							
					}
					
					// Add the members
					$rEntry->addMember(1, $swimmer1);
					$rEntry->addMember(2, $swimmer2);
					$rEntry->addMember(3, $swimmer3);
					$rEntry->addMember(4, $swimmer4);
					
					// Calculate Age Group
					$rEntry->calcAgeGroup();
					
				} elseif ($suppliedAge != "") {
					
					$rEntry->setAgeGroup($suppliedAge);
					
				}
				
				// Check for existing relay with same letter
				if ($rEntry->checkLetter() == false) {
				
					$error = "This letter has already been used for this age group!";
					
				} else {
					
					$rEntry->create();
					
					JRequest::setVar('view', 'clubentries', 'method', true);
					
				}
				
			}
			
			$sess->set('emRelayErrors', $error);
			
		}
		
		if (isset($_POST['createRelayCancel'])) {

			JRequest::setVar('view', 'clubentries', 'method', true);
			
		}
		
		if (isset($_GET['deleteRelay'])) {
			
			$relayId = mysql_real_escape_string($_GET['deleteRelay']);
			
			$relay = new RelayEntry();
			$relay->load($relayId);
			$relay->delete();
			
		}

		// Handle return from Paypal
        if ($jinput->get('paymentId') && $jinput->get('PayerID')) {

            $pp = new PayPalEntryPayment();
            $paymentId = $jinput->get('paymentId');
            $payerID = $jinput->get('PayerID');

            if ($jinput->get('success') == 'true') {

                $amountPaid = $pp->finalisePayment($paymentId, $payerID);

                addlog("Entry Manager", "PayPal Payment", "User paid $amountPaid for $meetId");

            } elseif($jinput->get('success') == 'false') {

                // payment not made
                addlog("Entry Manager", "PayPal Payment Failed", "Entrant did not pay for $meetId");

            } else {

                // User loaded step 4 without coming from Paypal
                addlog("Entry Manager", "Step 4 without entry", "User landed on Step 4 without return from Paypal.");

                JRequest::setVar('view', 'entrymanager', 'method', true);

            }

            // Rebuild session
            $entryId = $pp->getEntryId();
            $sess->set("emEntryId", $entryId);

            $entry = new MeetEntry();
            $entry->loadId($entryId);
            $sess->set('emMeetId', $entry->getMeetId());
            $sess->set('emClubId', $entry->getClubId());
            $sess->set('emEntrant', $entry->getMemberId());
            $sess->set('emEntryData', serialize($entry));

            JRequest::setVar('view', 'step4', 'method', true);

        }
		
		parent::__construct();
		
	}
	
	
}
