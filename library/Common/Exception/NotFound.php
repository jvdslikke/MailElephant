<?php

class Common_Exception_NotFound extends Common_Exception_Http
{
	public function __construct($message = "Requested Resource Not Found")
	{
		parent::__construct($message, 5404);
	}
	
	public function getHttpStatusCode()
	{
		return 404;
	}
}