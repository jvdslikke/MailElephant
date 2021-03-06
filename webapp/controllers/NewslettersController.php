<?php

class NewslettersController extends MailElephantWeb_Controller_Action_Abstract
{
	public function indexAction()
	{
		if($this->getRequest()->isPost() 
				&& ($deleteNewsletterId = $this->getRequest()->getPost('delete-newsletterid', false)) !== false)
		{			
			$newsletter = MailElephantModel_Newsletter::fetchOneById(
					$this->getStorageProvider(), $deleteNewsletterId);
			
			//TODO check if logged in user is the owner
			
			if($newsletter)
			{
				$newsletter->delete($this->getStorageProvider(), $this->getDataPath());
				
				$this->addFlashMessage("Message deleted");
			}
		}
		
		$newsletters = MailElephantModel_Newsletter::fetchMoreByUser(
				$this->getStorageProvider(), 
				Zend_Auth::getInstance()->getIdentity());
		
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
						$this->getDataPath().DIRECTORY_SEPARATOR."tmp",
						'emailfile');
				$upload->receive('emailfile');
				
				$mailbox = new Common_Mailbox($upload->getFileName('emailfile'));
				
				$newsletter = $mailbox->getMessage(1);
				
				unset($mailbox);
				
				$newsletter->save($this->getStorageProvider(), $this->getDataPath());
				
				unlink($upload->getFileName('emailfile'));
				
				//TODO display ok message, redirect to index
			}
		}
	}
	
	public function addFromMailboxAction()
	{
		$deleteMailbox = $this->getRequest()->getParam("delete-mailbox");
		if($deleteMailbox)
		{
			$this->getLoggedInUser()->deleteMailbox($deleteMailbox);
			$this->getLoggedInUser()->save($this->getStorageProvider());
			$this->addFlashMessage("Mailbox deleted");
			$this->_getRedirector()->gotoSimpleAndExit("add-from-mailbox");
		}

		$this->view->mailboxes = $this->getLoggedInUser()->getMailboxes();
	}
	
	public function mailboxAction()
	{		
		$editMailbox = null;
		if($this->getRequest()->getParam('mailbox') != null)
		{
			$editMailbox = $this->getMailboxFromRequest();
		}
		
		$editMailboxName = null;
		if($editMailbox)
		{
			$editMailboxName = $editMailbox->getName();
		}
		$form = new MailElephantWeb_Form_Mailbox($this->getLoggedInUser(), $editMailboxName);
		$this->view->form = $form;
		
		if($this->getRequest()->isPost())
		{
			$formData = $this->getRequest()->getPost();
			
			if($form->isValid($formData))
			{
				// remove edited mailbox
				$this->getLoggedInUser()->deleteMailbox($editMailboxName);
				
				$mailbox = $form->getMailboxData();
				
				$this->getLoggedInUser()->addMailbox($mailbox);
				$this->getLoggedInUser()->save($this->getStorageProvider());
				
				$this->_getRedirector()->gotoSimpleAndExit('add-from-mailbox');
			}
		}
		elseif($editMailbox !== null)
		{
			$form->setMailbox($editMailbox);
		}	
	}
	
	public function openMailboxAction()
	{
		$mailboxData = $this->getMailboxFromRequest();
		$mailbox = $this->openMailbox($mailboxData);

		$jsonHeaders = array('mailbox'=>$mailboxData->getName(), 'headers'=>array());
		
		$msgs = $mailbox->getNumMessages();
		foreach($mailbox->getHeaders($msgs-30, $msgs) as $header)
		{
			$jsonHeader = array(
				'msgno' => $header->getMsgNo(),
				'subject' => utf8_encode($header->getSubject()),
				'fromEmail' => $header->getFromEmail(),
				'fromName' => $header->getFromName());
			
			if($header->hasDate())
			{
				$jsonHeader['date'] = $header->getDate()->format('c');
			}
			
			$jsonHeaders['headers'][] = $jsonHeader;
		}
		
		$this->sendJSON($jsonHeaders);
	}
	
	/**
	 * @return Common_Mailbox
	 */
	private function openMailbox(MailElephantModel_Mailbox $mailboxData)
	{
		return new Common_Mailbox(
				$mailboxData->getMailboxConnectionString(),
				$mailboxData->getUsername(),
				$mailboxData->getPassword());		
	}
	
	/**
	 * @return MailElephantModel_Mailbox
	 */
	private function getMailboxFromRequest()
	{
		$mailboxId = $this->getRequest()->getParam("mailbox", null);
		
		if(!$mailboxId)
		{
			throw new Common_Exception_BadRequest("no mailbox given");
		}
		
		$mailboxData = null;
		foreach($this->getLoggedInUser()->getMailboxes() as $searchMailbox)
		{
			if($searchMailbox->getName() == $mailboxId)
			{
				$mailboxData = $searchMailbox;
				break;
			}
		}
		
		if($mailboxData === null)
		{
			throw new Common_Exception_NotFound("mailbox not found");
		}
		
		return $mailboxData;
	}
	
	public function addMessageFromMailboxAction()
	{
		$this->disableView();
		
		$mailbox = $this->openMailbox($this->getMailboxFromRequest());
		
		$messageNo = $this->getRequest()->getParam('message', null);
		if($messageNo === null)
		{
			throw new Common_Exception_BadRequest("no message no given");
		}
		
		$imapMessage = $mailbox->getMessage($messageNo);
		
		if(!$imapMessage)
		{
			throw new Common_Exception_NotFound("message not found");
		}
		
		// create newselephant message from imap message
		$attachments = array();
		foreach($imapMessage->getAttachments() as $imapAttachment)
		{
			/* @var $imapAttachment Common_Mailbox_Message_Attachment */
			
			$attachment = new MailElephantModel_NewsletterAttachment(
					$imapAttachment->getMimeType(), 
					$imapAttachment->getName(),
					$imapAttachment->getCid(),
					null);
			
			$attachment->setData($imapAttachment->getData());
			
			$attachments[] = $attachment;
		}
		
		$message = new MailElephantModel_Newsletter(null, 
				$imapMessage->getSubject(), 
				$imapMessage->getDate(), 
				$imapMessage->getPlainTextBody(), 
				$imapMessage->getHtmlBody(),
				$attachments,
				Zend_Auth::getInstance()->getIdentity());		
		
		$message->save($this->getStorageProvider(), $this->getDataPath());
		
		$this->addFlashMessage("Message added");
		
		$this->_getRedirector()->gotoSimpleAndExit('index');
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
	
	public function getNewsletterHtmlAction()
	{
		$this->view->newsletter = $this->getNewsletterByRequest();
		
		$this->disableLayout();
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
		//TODO lock while it's being sent
		
		$newsletter = $this->getNewsletterByRequest();
		
		$this->view->newsletter = $newsletter;
	}
}