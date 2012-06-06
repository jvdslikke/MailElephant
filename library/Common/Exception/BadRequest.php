<?php

class Common_Exception_BadRequest extends Common_Exception_Http
{
	public function __construct($message = "Bad Request")
	{
		parent::__construct($message, 5400);
	}
	
	public function getHttpStatusCode()
	{
		return 400;
	}
}