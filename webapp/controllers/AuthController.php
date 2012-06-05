<?php

class AuthController extends MailElephantWeb_Controller_Action_Abstract
{	
	public function loginAction()
	{		
		if($this->getRequest()->isPost())
		{
			$authAdapter = new MailElephantWeb_AuthenticationAdapter(
					$this->getStorageProvider(), 
					$this->getRequest()->getPost('email'), 
					$this->getRequest()->getPost('password'));
			
			Zend_Auth::getInstance()->authenticate($authAdapter);
			
			//TODO add message
			
			$this->refresh();
		}
	}
	
	public function logoutAction()
	{
		Zend_Auth::getInstance()->clearIdentity();
		
		$this->view->succes = true;
	}
	
}