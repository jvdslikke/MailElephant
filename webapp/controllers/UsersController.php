<?php 

class UsersController extends MailElephantWeb_Controller_Action_Abstract
{
	public function indexAction()
	{
		
	}
	
	public function addAction()
	{
		$form = new MailElephantWeb_Form_UserAdmin();
		
		if($this->getRequest()->isPost())
		{
			$data = $this->getRequest()->getPost();
			
			if($form->isValid($data))
			{
				$user = $form->getNewUser();
				
				$user->save($this->getStorageProvider());
				
				$this->addFlashMessage("New user saved!");
				
				$this->_getRedirector()->gotoSimpleAndExit('index');
			}
		}
		
		$this->view->form = $form;
	}
}