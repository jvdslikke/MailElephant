<?php

abstract class Common_Exception_Http extends Exception
{
	/**
	 * @param unknown_type $message
	 * @param int $code Use a code between 5000 and 6000 to recognize as http exception
	 */
	public function __construct($message, $code)
	{
		if($code < 5000 || $code > 6000)
		{
			throw new InvalidArgumentException("exception code not between 5000 and 6000");
		}
		
		parent::__construct($message, $code);
	}
	
	public abstract function getHttpStatusCode();
}