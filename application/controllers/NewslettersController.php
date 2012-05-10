<?php

class NewslettersController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$newsletters = MailElephantModel_Newsletter::fetchAll(
				$this->getInvokeArg('bootstrap')->getResource('storage'));
		
		$this->view->newsletters = $newsletters;
	}
	
	public function addAction()
	{
		$form = new MailElephantWeb_Form_EmailUploadForm();
		$this->view->form = $form;
		
		if($this->getRequest()->isPost())
		{
			$formData = $this->getRequest()->getPost();
			
			if($form->isValid($formData))
			{
				$upload = new Zend_File_Transfer_Adapter_Http();
				$upload->setDestination(
						$this->getInvokeArg('bootstrap')->getOption('datapath')."/tmp",
						'emailfile');
				$upload->receive('emailfile');
				
				$mailbox = new Common_Mailbox($upload->getFileName('emailfile'));
				
				$newsletter = $mailbox->getNewsletter(1);
				
				unset($mailbox);
				
				$newsletter->save($this->getInvokeArg('bootstrap')->getResource('storage'), 
						$this->getInvokeArg('bootstrap')->getOption('datapath'));
				
				unlink($upload->getFileName('emailfile'));
				
				//TODO display ok message, redirect to index
			}
		}
	}
}