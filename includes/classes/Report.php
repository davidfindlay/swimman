<?php

/**
 * Created by PhpStorm.
 * User: david
 * Date: 18/02/17
 * Time: 2:06 PM
 */
abstract class Report {

    /**
     * @var $name Stores the name of the report
     */
    protected $name;

    protected $parameters;

    /**
     * Gets the report and returns the data as JSON
     *
     * @return string JSON data for report
     */
    abstract protected function get();

    public function getName() {

        return $this->name;

    }


}