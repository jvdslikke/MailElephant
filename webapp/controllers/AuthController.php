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
			
			$result = Zend_Auth::getInstance()->authenticate($authAdapter);
			
			if(!$result->isValid())
			{
				$this->addFlashMessage("Authentication failed");
			}
			else
			{
				$this->addFlashMessage("Succesfully logged in");
			}
			
			$this->refresh();
		}
	}
	
	public function logoutAction()
	{
		Zend_Auth::getInstance()->clearIdentity();
		
		$this->view->succes = true;
	}
	
}