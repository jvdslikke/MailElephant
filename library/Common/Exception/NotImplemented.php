<?php

class Common_Exception_NotImplemented extends Exception
{
	public function __construct($message)
	{
		parent::__construct($message, 6001);
	}
}