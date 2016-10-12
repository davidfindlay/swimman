<?php

class entryError {
	
	private $error;
	private $rule_id;
	private $entrant;  	// Entrant name as shown in file
	private $event_id;
	private $details; 	// For extra details
	
	public function __construct($e, $m = '', $v = '', $r = '', $d = '') {
		
		$this->error = mysql_real_escape_string($e);
		
		if ($r != '')
			$this->rule_id = mysql_real_escape_string($r);
		
		if ($m != '')
			$this->entrant = mysql_real_escape_string($m);
				
		if ($v != '')
			$this->event_id = mysql_real_escape_string($v);
		
		if ($d != '')
			$this->details = $d;
		
	}
	
	public function getTitle() {
		
		$errorTitle = "";
		$memberName = $this->entrant;
		
		switch ($this->error) {
			
			case "guestornot":
				$errorTitle = "Member $memberName not found in the IMG Membership Database!";
				break;
			
			case "first_name":
				$errorTitle = "Unable to match $memberName to the IMG Membership Database!";
				break;
				
			case "toomany":
				$errorTitle = "Member $memberName has too many Events!";
				break;
				
			case "rules":
				$errorTitle = "Member $memberName's entry does not comply with the following rules:!";
				break;
				
			case "entrycourse":
				$errorTitle = "Member $memberName's entry times have an Incorrect Course";
				break;

			case "lsc_qlq":
				$errorTitle = "File contains QLQ LSC Code!";
				break;

			case "lsc_bs":
				$errorTitle = "File contains BS LSC Code!";
				break;

			case "no_dob":
				$errorTitle = "Member $memberName does not have Date of Birth listed!";
				break;

			case "dob":
				$errorTitle = "Unable to match $memberName's date of birth in the IMG Membership Registration database!";
				break;

			case "msa_number":
				$errorTitle = "Member $memberName has an MSA Registration Number in their entry!";
				break;
				
			case "unfinancial":
				$errorTitle = "Member $memberName is not financial";
				break;
				
				
		}
		
		
		
		return $errorTitle;
		
	}
	
	public function getDesc() {
		
		$errorDesc = "";
		$memberName = $this->entrant;
		$errorDetails = $this->details;
		
		switch ($this->error) {
			
			case "guestornot":
				
				$errorDesc = $errorDesc . "<p>";
				$errorDesc = $errorDesc . "<strong><i>Why is this?</i></strong><br>\n";
				$errorDesc = $errorDesc . "Possible reasons may be: \n";
				$errorDesc = $errorDesc . "</p>\n";
				$errorDesc = $errorDesc . "<ul>\n";
				$errorDesc = $errorDesc . "<li>The first and last names in your Team Manager file do not match the member's name on the IMG Console</li>\n";
				$errorDesc = $errorDesc . "<li>The member has not yet joined or renewed on the Member Portal</li>\n";
				$errorDesc = $errorDesc . "<li>The member has not yet been activated by the Club Recorder</li>\n";
				$errorDesc = $errorDesc . "<li>If this is a second claim member or transfering member, this may not yet have been completed on the IMG Console</li>\n";
				$errorDesc = $errorDesc . "<li>This is a guest member</li>\n";
				$errorDesc = $errorDesc . "</ul>\n";
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "If this is a Guest Member they must be registered by the Guest Registration option on the left.\n";
				$errorDesc = $errorDesc . "</p>\n";
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "More information on how to check details, activate and transfer members in the IMG Console is ";
				$errorDesc = $errorDesc . "available in the <a href=\"http://assets.imgstg.com/assets/console/document/documents/Registrar%20Help-book%20-%202012.pdf\">Registrar's Help Book</a>.\n";
				$errorDesc = $errorDesc . "</p>\n";
				
				break;
				
			case "first_name":
				
				$errorDesc = $errorDesc . "<p>";
				$errorDesc = $errorDesc . "<strong><i>Why is this?</i></strong><br>\n";
				$errorDesc = $errorDesc . "Possible reasons may be: <br>\n";
				$errorDesc = $errorDesc . "</p>\n";
				$errorDesc = $errorDesc . "<ul>\n";
				$errorDesc = $errorDesc . "<li>A shortened or preferred first name has been recorded in either the Members Portal or Team Manager.</li>\n";
				$errorDesc = $errorDesc . "<li>The first name recorded in the Members Portal or Team Manager is incorrect or spelt wrong.</li>\n";
				$errorDesc = $errorDesc . "</ul>\n";
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "Please check the first name recorded on the IMG Console and Team Manager and correct these to match.\n";
				$errorDesc = $errorDesc . "</p>\n";
				
				break;
				
			case "toomany":
				
				$meetMax = $this->details[0];
				$numEnts = $this->details[1];
				
				$errorDesc = $errorDesc . "<p>";
				$errorDesc = $errorDesc . "This event allows a maximum of $meetMax individual events per competitor, but ";
				$errorDesc = $errorDesc . "$memberName has $numEnts. Please amend this entry.\n";
				$errorDesc = $errorDesc . "</p>\n";
				
				break;
				
			case "rules":
			
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "The entry for $memberName does not comply with \n";
				$errorDesc = $errorDesc . "the following rules: \n";
				$errorDesc = $errorDesc . "</p>\n";
				
				$errorDesc = $errorDesc . "<ul>\n";
				
				foreach ($errorDetails as $d) {
					
					echo "<li>$d</li>\n";
					
				}
				
				$errorDesc = $errorDesc . "</ul>\n";

				break;
				
			case "entrycourse":
			
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "The entry for $memberName has the incorrect course type listed. Check the time shown \n";
				$errorDesc = $errorDesc . "for the entry in Team Manager. It should be S for Short Course meets and L for long \n";
				$errorDesc = $errorDesc . "course meets.\n";
				$errorDesc = $errorDesc . "</p>\n";
				
				break;
				
			case "lsc_qlq":
			
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "<strong><i>What is this?</i></strong><br />\n";
				$errorDesc = $errorDesc . "The QLQ LSC code is caused by an incorrect setting in Team Manager and causes ";
				$errorDesc = $errorDesc . "incorrect club codes to be shown in meet results. There are two places in Team Manager this should be removed. Please see this link on how to ";
				$errorDesc = $errorDesc . "fix the problem: <a target=\"new\" href=\"https://assets.imgstg.com/assets/console/document/documents/Help-Recorder-Remove-Incorrect-ClubCode2012.pdf\">";
				$errorDesc = $errorDesc . "Remove Incorrect Club Code</a>.\n";
				$errorDesc = $errorDesc . "</p>\n";
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "Please fix this problem and resubmit the file.";
				$errorDesc = $errorDesc . "</p>\n";

				break;
				
			case "lsc_bs":
					
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "<strong><i>What is this?</i></strong><br />\n";
				$errorDesc = $errorDesc . "The Brisbane Swimming LSC code is caused by an incorrect setting in Team Manager and causes ";
				$errorDesc = $errorDesc . "incorrect club codes to be shown in meet results. There are two places in Team Manager this should be removed. Please see this link on how to ";
				$errorDesc = $errorDesc . "fix the problem: <a target=\"new\" href=\"https://assets.imgstg.com/assets/console/document/documents/Help-Recorder-Remove-Incorrect-ClubCode2012.pdf\">";
				$errorDesc = $errorDesc . "Remove Incorrect Club Code</a>.\n";
				$errorDesc = $errorDesc . "</p>\n";
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "Please fix this problem and resubmit the file.";
				$errorDesc = $errorDesc . "</p>\n";
			
				break;
			
			case "no_dob":
						
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "This member's entry does not list their date of birth. Please add the date of birth for \n";
				$errorDesc = $errorDesc . "this entrant in the Team Manager athlete entry for them. If this is not correctly listed \n";
				$errorDesc = $errorDesc . "it will be require manual follow up by the meet organisers to correctly match up this member.\n";				
				$errorDesc = $errorDesc . "</p>\n";
				
				break;
				
			case "dob":

				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "The date of birth listed for this entrant is incorrectly listed either in the IMG Member's Portal or your Team Manager entry file. \n";
				$errorDesc = $errorDesc . "Please check the date of birth recorded on the IMG Console and Team Manager and correct these to match.\n";
				$errorDesc = $errorDesc . "</p>\n";
				
				break;
				
			case "msa_number":
				
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "This member's entry shows their Masters Swimming Australia registration number. This causes \n";
				$errorDesc = $errorDesc . "issues for meet organisers checking a member's membership status and also can cause problems \n";
				$errorDesc = $errorDesc . "when the results are uploaded to the Results Portal. For instructions \n";
				$errorDesc = $errorDesc . "on how to fix this see <a href=\"https://assets.imgstg.com/assets/console/document/documents/Removing%20IDs%20from%20the%20Team%20Manager%20File.pdf\">Removing IDs from the Team Manager file</a>.\n";
				$errorDesc = $errorDesc . "</p>\n";
				$errorDesc = $errorDesc . "<p>Please remove this number and resubmit the file.\n";
				$errorDesc = $errorDesc . "</p>\n";
				
				break;
				
				
			case "unfinancial":
						
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "This member's registration has been checked against the IMG Membership Registration ";
				$errorDesc = $errorDesc . "Database and it has been indicated that the member is not currently a financial ";
				$errorDesc = $errorDesc . "member this club.";
				$errorDesc = $errorDesc . "</p>\n";
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "Please advise the member to renew their membership as soon as possible. ";
				$errorDesc = $errorDesc . "To be eligible to compete in this meet they need to be a financial member by the ";
				$errorDesc = $errorDesc . "closing date for entries.\n";
				$errorDesc = $errorDesc . "</p>\n";
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "If you believe this message to be in error, please check the IMG Console for your club. ";
				$errorDesc = $errorDesc . "If this member has recently transferred or joined from another club, please check transfer ";
				$errorDesc = $errorDesc . "has been done or second claim membership has been registered. ";
				$errorDesc = $errorDesc . "</p>\n";
				$errorDesc = $errorDesc . "<p>\n";
				$errorDesc = $errorDesc . "More information on how to check details, activate and transfer members in the IMG Console is ";
				$errorDesc = $errorDesc . "available in the <a href=\"http://assets.imgstg.com/assets/console/document/documents/Registrar%20Help-book%20-%202012.pdf\">Registrar's Help Book</a>.\n";
				$errorDesc = $errorDesc . "</p>\n";
				
				break;
				
		}
		
		return $errorDesc;
		
	}
	
	public function getEntrantName() {
		
		return $this->entrant;
		
	}
	
}

?>