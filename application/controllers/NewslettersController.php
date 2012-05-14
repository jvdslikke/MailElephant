<?php

class NewslettersController extends MailElephantWeb_Controller_Action_Abstract
{
	public function indexAction()
	{
		$newsletters = MailElephantModel_Newsletter::fetchAll($this->getStorageProvider());
		
		$this->view->newsletters = $newsletters;
	}
	
	public function addAction()
	{
		
	}
	
	public function addFileAction()
	{
		$form = new MailElephantWeb_Form_EmailUpload();
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
				
				$newsletter->save($this->getStorageProvider(), 
						$this->getInvokeArg('bootstrap')->getOption('datapath'));
				
				unlink($upload->getFileName('emailfile'));
				
				//TODO display ok message, redirect to index
			}
		}
	}
	
	public function addFromMailboxAction()
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		$this->view->mailboxes = $user->getMailboxes();
		
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
	
	public function addMailboxAction()
	{
		$form = new MailElephantWeb_Form_Mailbox();
		$this->view->form = $form;
		
		if($this->getRequest()->isPost())
		{
			$formData = $this->getRequest()->getPost();
			
			if($form->isValid($formData))
			{
				$mailbox = new MailElephantModel_Mailbox(
						$formData['mailbox'], 
						$formData['username'], 
						$formData['password']);
				
				$this->getLoggedInUser()->addMailbox($mailbox);
				$this->getLoggedInUser()->save($this->getStorageProvider());
				
				$this->_getRedirector()->gotoSimpleAndExit('add-from-mailbox');
			}
		}		
	}
	
	public function openMailboxAction()
	{
		$mailboxData = null;
		
		foreach($this->getLoggedInUser()->getMailboxes() as $searchMailbox)
		{
			if($searchMailbox->getMailbox() == urldecode($this->getRequest()->getParam('mailbox')))
			{
				$mailboxData = $searchMailbox;
			}
		}
		
		if($mailboxData === null)
		{
			$this->jsonError("mailbox not found");
		}
		
		/* @var $mailboxData MailElephantModel_Mailbox */
		$mailbox = new Common_Mailbox(
				$mailboxData->getMailbox(),
				$mailboxData->getUsername(),
				$mailboxData->getPassword());

		$jsonHeaders = array();
		
		$msgs = $mailbox->getNumMessages();
		foreach($mailbox->getHeaders($msgs-50, $msgs) as $header)
		{
			$jsonHeaders[] = array(
					'subject' => $header->getSubject(),
					'date' => $header->getDate()->format('c'));
		}
		
		$this->sendJSON($jsonHeaders);
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
				$this->getStorageProvider(),
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
		$this->disableView();
		
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