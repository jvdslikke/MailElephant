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
		
	}
	
	public function addFileAction()
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
	
	public function addFromMailboxAction()
	{
		$form = new MailElephantWeb_Form_Mailbox();
		$this->view->form = $form;
		
		if($this->getRequest()->isPost())
		{
			$formData = $this->getRequest()->getPost();
			
			if($form->isValid($formData))
			{
				$mailbox = new Common_Mailbox(
						$formData['mailbox'], $formData['username'], $formData['password']);
				
				
			}
		}
	}

	public function viewAction()
	{
		$newsletter = $this->getNewsletterByRequest();
		
		$mode = $this->getRequest()->getParam('mode', 'html');
		if(!$newsletter->hasHtmlBody())
		{
			$mode = 'plaintext';
		}
		
		$this->view->mode = $mode;
		
		$this->view->newsletter = $newsletter;
	}
	
	private function getNewsletterByRequest()
	{
		$newsletterId = $this->getRequest()->getParam('newsletterid', null);

		if($newsletterId === null)
		{
			throw new Common_Exception_BadRequest("No Newsletter Id provided");
		}
		
		$newsletter = MailElephantModel_Newsletter::fetchOneById(
				$this->getInvokeArg('bootstrap')->getResource('storage'),
				$newsletterId);
		
		if($newsletter === null)
		{
			throw new Common_Exception_NotFound("Newsletter Not Found");
		}
		
		return $newsletter;
	}
	
	public function downloadAttachmentAction()
	{
		$newsletter = $this->getNewsletterByRequest();
		
		$attachmentCid = $this->getRequest()->getParam('attachmentcid', null);
		
		if($attachmentCid === null)
		{
			throw new Common_Exception_BadRequest("No Attachment Cid provided");
		}
		
		$attachment = $newsletter->getAttachmentByCid($attachmentCid);
		
		if($attachment === null)
		{
			throw new Common_Exception_NotFound("Attachment Not Found");
		}
		
		// output attachment
		
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		$this->getResponse()->setHeader('Content-Type', $attachment->getMimeType());
		$this->getResponse()->sendHeaders();
		
		$attachment->output();
	}
	
	public function editAction()
	{
		$newsletter = $this->getNewsletterByRequest();
		
		$this->view->newsletter = $newsletter;
	}
}