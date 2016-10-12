<?php
// Gets latest QLD registrations from the IMG Console

require_once($_ENV['HOME'] . "/forum.mastersswimmingqld.org.au/swimman/includes/imgfunctions.php");

// Retrieve the IMG Data
getImg();

// Parse the IMG Data
parseImg();

?>