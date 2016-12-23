<?php

require 'PHPMailerAutoload.php';

require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Meet.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEntry.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEvent.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/MeetEntryEvent.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/swimman/includes/classes/Member.php');

/**
 * Creates and sends a confirmation email showing your entry to a meet
 *
 */
class ConfirmationEmail {

    /** @var int Member ID of recipient */
    private $member_id;

    /** @var int Entry ID of entry this email is about */
    private $entry_id;

    /** @var int Meet ID of the Meet this entry is for */
    private $meet_id;

    /** @var int Template ID used for email */
    private $template_id;


    /**
     * Sends message
     */
    public function send() {

        $mail = new PHPMailer();
        $member = new Member();
        $member->loadId($this->member_id);
        $memberEmail = $member->getEmail();

        $meet = new Meet();
        $meet->loadMeet($this->meet_id);

        $mail->isSMTP();

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'user@example.com';                 // SMTP username
        $mail->Password = 'secret';                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        $mail->setFrom('recorder@mastersswimmingqld.org.au', 'MSQ Entry Manager');
        $mail->addAddress($memberEmail);     // Add a recipient

        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = "Your " . $meet->getName() . " Entry";

        $mail->Body = createBody();

        if(!$mail->send()) {

            //TODO log it
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;

        } else {

            echo 'Message has been sent';

        }

    }

    /**
     * @return string returns the body of the entry confirmation email
     */
    public function createBody() {

        $body = "<p>Thank you for your entry.</p>\n";
        $body .= "<p>Your entry is as follows:</p>\n";

        $body .= "<h3>Individual Events</h3>\n";
        $body .= "<table border=\"1\">\n";
        $body .= "<thead>\n";
        $body .= "<tr>\n";
        $body .= "<th>No.</th>\n";
        $body .= "<th>Event:</th>\n";
        $body .= "<th>Type:</th>\n";
        $body .= "<th>Nominated Time:</th>\n";
        $body .= "<th>Status:</th>\n";
        $body .= "</tr>\n";
        $body .= "</thead>\n";
        $body .= "<tbody>\n";

        // Get the entry
        $curEntry = new MeetEntry();
        $curEntry->loadId($this->entry_id);

        $eventArray = array_reverse($curEntry->getEvents());
        foreach ($eventArray as $v) {

            $eventId = $v->getEventId();
            $eventDetails = new MeetEvent();
            $eventDetails->load($eventId);
            $eventName = $eventDetails->getName();
            $eventProg = $eventDetails->getProgNumber();
            $eventShort = $eventDetails->getShortDetails();
            $eventType = $eventDetails->getType();

            $seedTime = sw_formatSecs($v->getSeedTime());
            $vStatus = $v->getStatusText();

            $body .= "<tr>\n";
            $body .= "<td><div align=\"center\">$eventProg</div></td>\n";
            $body .= "<td>$eventShort";

            if ($eventName != '') {

                $body .= " - $eventName";

            }
            $body .= "\n";
            $body .= "</td>\n";
            $body .= "<td>$eventType</td>\n";
            $body .= "<td><div align=\"right\">$seedTime</div></td>\n";
            $body .= "<td>$vStatus</td>\n";

            $body .= "</tr>\n";

        }

        $body .= "</tbody>\n";
        $body .= "</table>\n";

        $body .= "<p>If you have made a payment online you will receive a separate ";
        $body .= "receipt email for this payment.</p>\n";

        $body .= "<p>If any details of this entry are incorrect and need amendment, please ";
        $body .= "log into the MSQ Members Community Entry Manager and go to the ";
        $body .= "<strong>Entry Manager</strong> menu link and click <strong>My Entries</strong>. ";
        $body .= "Select the meet at the top of the page and click <strong>View Entries</strong>. ";
        $body .= "You can then select the <strong>Edit</strong> link to edit your entry. </p>\n";

        $body .= "<p>Thank you for your entry. If you require any assistance feel free to reply ";
        $body .= "to this email or email <a href=mailto:\"recorder@mastersswimmingqld.org.au\">";
        $body .= "recorder@mastersswimmingqld.org.au</a>.</p>\n";

        return $body;

    }

    /**
     * @return int Member ID of the member this email is being sent to
     */
    public function getMemberId() {

        return $this->member_id;
    }

    /**
     * @param int $member_id Member ID of the member this email is being sent to
     */
    public function setMemberId($member_id) {

        $this->member_id = $member_id;
    }

    /**
     * @return int Entry ID of the entry this email is about
     */
    public function getEntryId() {

        return $this->entry_id;
    }

    /**
     * @param int $entry_id Entry ID of the entry this email is about
     */
    public function setEntryId($entry_id) {

        $this->entry_id = $entry_id;
    }

    /**
     * @return int Meet ID of the meet the entry in this email is about
     */
    public function getMeetId() {

        return $this->meet_id;
    }

    /**
     * @param int $meet_id Meet ID of the meet the entry in this email is about
     */
    public function setMeetId($meet_id) {

        $this->meet_id = $meet_id;
    }

    /**
     * @return int Template ID of the template being used for this email
     */
    public function getTemplateId() {

        return $this->template_id;
    }

    /**
     * @param int $template_id Template ID of the template being used for this email
     */
    public function setTemplateId($template_id) {

        $this->template_id = $template_id;
    }


}
