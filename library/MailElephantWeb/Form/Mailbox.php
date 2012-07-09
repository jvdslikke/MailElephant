<?php

class MailElephantWeb_Form_Mailbox extends Zend_Form
{
	private $mailboxElem;
	private $usernameElem;
	private $passwordElem;
	
	public function __construct($options = null)
	{
		parent::__construct($options);

		$this->setName('mailbox');
		$this->setMethod('post');
		
		$this->mailboxElem = new Zend_Form_Element_Text('mailbox');
		$this->mailboxElem->setLabel("mailbox");
		$this->mailboxElem->setRequired(true);
		
		$this->usernameElem = new Zend_Form_Element_Text('username');
		$this->usernameElem->setLabel("username");
		
		$this->passwordElem = new Zend_Form_Element_Password('password');
		$this->passwordElem->renderPassword = true;
		$this->passwordElem->setLabel("password");
		
		$submitElem = new Zend_Form_Element_Submit('submit');
		$submitElem->setValue("proceed");
		
		$this->addElements(array(
				$this->mailboxElem, 
				$this->usernameElem, 
				$this->passwordElem, 
				$submitElem));
	}
	
	public function getMailboxInput()
	{
		return $this->mailboxElem;
	}
	
	public function getUsernameInput()
	{
		return $this->usernameElem;
	}
	
	public function getPasswordInput()
	{
		return $this->passwordElem;
	}
	
	public function setMailbox(MailElephantModel_Mailbox $mailbox)
	{
		$this->mailboxElem->setValue($mailbox->getMailbox());
		$this->usernameElem->setValue($mailbox->getUsername());
		$this->passwordElem->setValue($mailbox->getPassword());
	}
}