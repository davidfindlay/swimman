<?php

// JSON Web Service
// Returns a list of members who match a search criteria

require_once("../includes/setup.php");

$criteria = $_GET['criteria'];

$searchResults = $GLOBALS['db']->getAll("SELECT CONCAT(a.firstname, ' ', a.surname) 
		FROM member as a, member_memberships as b, clubs as c;");