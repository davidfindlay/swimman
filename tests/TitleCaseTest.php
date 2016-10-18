<?php

require_once("../includes/setup.php");

/**
 * Created by PhpStorm.
 * User: david
 * Date: 17/10/2016
 * Time: 8:08 AM
 */
class TitleCaseTest extends PHPUnit_Framework_TestCase
{

    public function test_particle_de() {

        $test1 = "de meer";
        $result1 = titleCase($test1);
        $this->assertEquals("de Meer", $result1);

    }

}
