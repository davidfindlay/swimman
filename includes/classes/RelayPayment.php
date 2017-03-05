<?php

/**
 * Created by PhpStorm.
 * User: david
 * Date: 3/3/17
 * Time: 7:16 PM
 */
class RelayPayment {

	private $id;
	private $club_id;
	private $qty;
	private $amount;
	private $event_id;
	private $datetime;
	private $method;

	public function store() {



	}

	/**
	 * @return mixed
	 */
	public function getId() {

		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId( $id ) {

		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getClubId() {

		return $this->club_id;
	}

	/**
	 * @param mixed $club_id
	 */
	public function setClubId( $club_id ) {

		$this->club_id = $club_id;
	}

	/**
	 * @return mixed
	 */
	public function getQty() {

		return $this->qty;
	}

	/**
	 * @param mixed $qty
	 */
	public function setQty( $qty ) {

		$this->qty = $qty;
	}

	/**
	 * @return mixed
	 */
	public function getAmount() {

		return $this->amount;
	}

	/**
	 * @param mixed $amount
	 */
	public function setAmount( $amount ) {

		$this->amount = $amount;
	}

	/**
	 * @return mixed
	 */
	public function getEventId() {

		return $this->event_id;
	}

	/**
	 * @param mixed $event_id
	 */
	public function setEventId( $event_id ) {

		$this->event_id = $event_id;
	}

	/**
	 * @return mixed
	 */
	public function getDatetime() {

		return $this->datetime;
	}

	/**
	 * @param mixed $datetime
	 */
	public function setDatetime( $datetime ) {

		$this->datetime = $datetime;
	}

	/**
	 * @return mixed
	 */
	public function getMethod() {

		return $this->method;
	}

	/**
	 * @param mixed $method
	 */
	public function setMethod( $method ) {

		$this->method = $method;
	}



}