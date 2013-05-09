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
	 * @return DateTimeZone The current timezone
	 */
	public function getCurrentTimezone()
	{
		return new DateTimeZone(date_default_timezone_get());
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
	
	/**
	 * @return Zend_Controller_Action_Helper_ViewRenderer
	 */
	private function getViewRenderer()
	{
		return $this->_helper->viewRenderer;
	}
	
	public function disableView()
	{
		$this->disableLayout();
		$this->getViewRenderer()->setNoRender(true);		
	}
	
	public function isViewDisabled()
	{
		return $this->getViewRenderer()->getNoRender();
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
	
	/**
	 * @return Zend_Controller_Action_Helper_FlashMessenger
	 */
	private function _getFlashMessenger()
	{
		return $this->_helper->getHelper('FlashMessenger');
	}
	
	public function addFlashMessage($message)
	{
		$this->_getFlashMessenger()->addMessage("<p>$message</p>");
	}
	
	public function addSuccesMessage($message)
	{
		$this->_addFlashMessageWithClass($message, "succes");
	}
	
	public function addErrorMessage($message)
	{
		$this->_addFlashMessageWithClass($message, "error");
	}
	
	protected function _addFlashMessageWithClass($message, $class)
	{
		$this->_getFlashMessenger()->addMessage("<p class=\"$class\">$message</p>");
	}
	
	public function postDispatch()
	{
		if(!$this->getResponse()->isRedirect() && !$this->isViewDisabled());
		{
			$messages = array();
			
			$messages = array_merge($messages, $this->_getFlashMessenger()->getCurrentMessages());
			$this->_getFlashMessenger()->clearCurrentMessages();
			
			$messages = array_merge($messages, $this->_getFlashMessenger()->getMessages());
			
			$messages = array_unique($messages);			
			$this->view->flashMessages = $messages;
		}
	}
}