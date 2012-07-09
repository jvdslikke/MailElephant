<?php

class IndexController extends MailElephantWeb_Controller_Action_Abstract
{

    public function indexAction()
    {
    }
    
    public function registerAction()
    {
    	$form = new MailElephantWeb_Form_Register();
    	$this->view->form = $form;
    	
    	if($this->getRequest()->isPost())
    	{
    		$formData = $this->getRequest()->getPost();
    		
    		if($form->isValid($formData))
    		{
    			$email = $form->getElement('email')->getValue();
    			$password = $form->getElement('password')->getValue();
    			$passwordHash = Common_Bcrypt::hash($password);
    			
    			$user = new MailElephantModel_User($email, $passwordHash);
    			$user->save($this->getStorageProvider());
    			
    			$this->addFlashMessage("New user created");
    		}
    	}
    }
}

