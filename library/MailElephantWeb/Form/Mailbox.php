<?php

class MailElephantWeb_Form_Mailbox extends Zend_Form
{
	private $nameElem;
	private $nameValidator;
	private $hostElem;
	private $portElem;
	private $usernameElem;
	private $passwordElem;
	private $useSslElem;
	private $novalidateCertElem;
	
	public function __construct(MailElephantModel_User $user, $origMailboxName=null)
	{
		parent::__construct();

		$this->setName('mailbox');
		$this->setMethod('post');
		
		$this->nameElem = new Zend_Form_Element_Text('name');
		$this->nameElem->setLabel("Mailbox Name");
		$this->nameElem->setRequired(true);
		
		$this->nameValidator = new MailElephantWeb_Validate_MailboxNotAlreadyExists($user, $origMailboxName);
		$this->nameElem->addValidator($this->nameValidator);
		
		$this->hostElem = new Zend_Form_Element_Text('host');
		$this->hostElem->setLabel("Host");
		$this->hostElem->setRequired(true);
		
		$this->portElem = new Zend_Form_Element_Text('port');
		$this->portElem->setLabel("Port");
		$this->portElem->addValidator(new Zend_Validate_Digits());
		
		$this->usernameElem = new Zend_Form_Element_Text('username');
		$this->usernameElem->setLabel("Username");
		
		$this->passwordElem = new Zend_Form_Element_Password('password');
		$this->passwordElem->renderPassword = true;
		$this->passwordElem->setLabel("Password");
		
		$this->useSslElem = new Zend_Form_Element_Checkbox('use-ssl');
		$this->useSslElem->setLabel("Use SSL encrypted connection");
		
		$this->novalidateCertElem = new Zend_Form_Element_Checkbox('novalidate-cert');
		$this->novalidateCertElem->setLabel("Do not validate SSL certificate");
		
		$submitElem = new Zend_Form_Element_Submit("Save");
		
		$this->addElements(array(
				$this->nameElem,
				$this->hostElem,
				$this->portElem,
				$this->usernameElem,
				$this->passwordElem,
				$this->useSslElem,
				$this->novalidateCertElem,
				$submitElem));
	}
	
	public function getMailboxData()
	{
		return new MailElephantModel_Mailbox(
				$this->nameElem->getValue(), 
				$this->hostElem->getValue(),
				$this->portElem->getValue(),
				$this->usernameElem->getValue(), 
				$this->passwordElem->getValue(),
				$this->useSslElem->isChecked(),
				$this->novalidateCertElem->isChecked());
	}
	
	public function setMailbox(MailElephantModel_Mailbox $mailbox)
	{
		$this->nameElem->setValue($mailbox->getName());
		$this->hostElem->setValue($mailbox->getHost());
		$this->portElem->setValue($mailbox->getPort());
		$this->usernameElem->setValue($mailbox->getUsername());
		$this->passwordElem->setValue($mailbox->getPassword());
		
		if($mailbox->getUseSsl())
		{
			$this->useSslElem->setChecked(true);
		}
		
		if($mailbox->getNovalidateCert())
		{
			$this->novalidateCertElem->setChecked(true);
		}
	}
}