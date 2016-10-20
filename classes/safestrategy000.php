<?php
/**
 * check website safe strategy
 */
class safeStrategy
{
	private $safeInfo = array();

	/**
	 * constructor
	 */
	public function __construct()
	{
	}

	/**
	 * start check website safe options and return a array
	 * @return array
	 */
	public function check()
	{
		return $this->safeInfo;
	}

	/**
	 * check authorize info
	 */
}