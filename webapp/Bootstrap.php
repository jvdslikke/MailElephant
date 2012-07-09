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
		date_default_timezone_set('UTC');
	}
}

