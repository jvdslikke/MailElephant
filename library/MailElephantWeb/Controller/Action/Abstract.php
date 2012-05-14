<?php

abstract class MailElephantWeb_Controller_Action_Abstract extends Zend_Controller_Action
{
	/**
	 * @return Common_Storage_Provider_Interface
	 */
	public function getStorageProvider()
	{
		return $this->getInvokeArg('bootstrap')->getResource('storage');
	}
	
	public function disableView()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);		
	}
	
	/**
	 * @return Zend_Controller_Action_Helper_Redirector
	 */
	private function getRedirector()
	{
		return $this->_helper->getHelper('Redirector');
	}
	
	public function refresh()
	{
		$this->getRedirector()->gotoRouteAndExit();
	}
}