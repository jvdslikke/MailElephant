<?php

abstract class Common_Exception_Abstract extends Exception
{
	/**
	 * @param unknown_type $message
	 * @param int $code Use a code higher than 5000 to recognize custom exceptions
	 */
	public function __construct($message, $code = 5000)
	{
		parent::__construct($message);
	}
	
	public abstract function getHttpStatusCode();
}