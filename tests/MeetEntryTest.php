<?php

require_once("../includes/setup.php");
require_once("../includes/meet.php");
require_once("../includes/classes/MeetEntry.php");
require_once("../includes/classes/MeetEntryEvents.php");

/**
 * Created by PhpStorm.
 * User: david
 * Date: 16/12/16
 * Time: 8:55 PM
 */
class MeetEntryTest extends PHPUnit_Framework_TestCase
{

    protected static $meet;

    public static function setUpBeforeClass() {

        self::$meet = new Meet();
        self::$meet->setName("Test Meet");


    }

    public function testEventUpdates() {

        // Create an initial entry
        $entry1 = new MeetEntry();
        $entry1->addEvent(1, 10.25, 1);
        $entry1->addEvent(2, 32.11, 1);
        $entry1->addEvent(3, 145.99, 1);
        $entry1->addEvent(4, 54.33, 1);
        $entry1->addEvent(5, 23.00, 1);

        $entry2 = new MeetEntry();
        $entry2->addEvent(1, 20.25, 1);
        $entry2->addEvent(2, 42.11, 1);
        $entry2->addEvent(6, 100, 1);
        $entry2->addEvent(4, 54.33, 1);
        $entry2->addEvent(9, 25.00, 1);

        $entry1->updateEvents($entry2->getEvents(), 2, 3);

        $entryEvents = $entry1->getEvents();


    }

}
