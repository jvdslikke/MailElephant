<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initAuth()
	{
		Zend_Controller_Front::getInstance()->registerPlugin(
				new MailElephantWeb_Controller_Plugin_Acl());
	}
	
	protected function _initTimezone()
	{
		if(!date_default_timezone_set($this->getOption('timezone')))
		{
			throw new Exception("setting timezone failed, check config");
		}
	}
	
	protected function _initSwift()
	{
		require("Swift/swift_required.php");
	}
}

