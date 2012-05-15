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
	
	/**
	 * Return the configured data path without trailing slashes
	 */
	public function getDataPath()
	{
		return rtrim($this->getInvokeArg('bootstrap')->getOption('datapath'), '\\/');
	}
	
	public function disableLayout()
	{
		$this->_helper->layout()->disableLayout();		
	}
	
	public function disableView()
	{
		$this->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);		
	}
	
	/**
	 * @return Zend_Controller_Action_Helper_Redirector
	 */
	protected function _getRedirector()
	{
		return $this->_helper->getHelper('Redirector');
	}
	
	public function refresh()
	{
		$this->_getRedirector()->gotoRouteAndExit();
	}
	
	public function sendJSON($jsonData)
	{
		$this->_helper->json($jsonData);
	}
	
	public function jsonError($message, $code = 500)
	{
		$this->getResponse()->setHttpResponseCode($code);
		
		$jsonError = array('isError'=>true, 'errorMessage'=>$message);
		$this->sendJSON(json_encode($jsonError));
	}
	
	/**
	 * @return MailElephantModel_User
	 */
	public function getLoggedInUser()
	{
		if(!Zend_Auth::getInstance()->hasIdentity())
		{
			return null;
		}
		
		return Zend_Auth::getInstance()->getIdentity();
	}
}