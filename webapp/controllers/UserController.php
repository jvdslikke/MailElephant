<?php 

class UserController extends MailElephantWeb_Controller_Action_Abstract
{
	public function editAction()
	{
		$form = new MailElephantWeb_Form_User();
		
		if($this->getRequest()->isPost())
		{
			$postData = $this->getRequest()->getPost();
			
			if($form->isValid($postData))
			{
				if($form->newPasswordProvided())
				{
					$this->getLoggedInUser()->setPasswordPlainText($form->getNewPassword());					
				}
				
				$this->getLoggedInUser()->setEmailFromSettings($form->getMailSenderDetails());
				
				$this->getLoggedInUser()->save($this->getStorageProvider());
				
				$this->addFlashMessage("Data saved!");
				
				$this->_getRedirector()->gotoSimpleAndExit('edit');
			}
		}
		else
		{
			$form->setUser($this->getLoggedInUser());
		}
		
		$this->view->form = $form;
	} 
}