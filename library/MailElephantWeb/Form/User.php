<?php 

class MailElephantWeb_Form_User extends Zend_Form
{
	private $passwordElem;
	private $passwordRepeatElem;
	private $emailFromAddressElem;
	private $emailFromNameElem;
	private $emailReplyToElem;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->setName('user');
		$this->setMethod('post');
		
		$this->passwordElem = new Zend_Form_Element_Password('password');
		$this->passwordElem->setLabel("Password");
		
		$this->passwordRepeatElem = new Zend_Form_Element_Password('password-repeat');
		$this->passwordRepeatElem->setLabel("Repeat password");
		$this->passwordRepeatElem->addValidator(new Zend_Validate_Identical('password'));

		$this->emailFromAddressElem = new Zend_Form_Element_Text('email-from-address');
		$this->emailFromAddressElem->setLabel("Email from email address");
		$this->emailFromAddressElem->setRequired(true);
		
		$this->emailFromNameElem = new Zend_Form_Element_Text('email-from-name');
		$this->emailFromNameElem->setLabel("Email from name");
		
		$this->emailReplyToElem = new Zend_Form_Element_Text('email-reply-to');
		$this->emailReplyToElem->setLabel("Email reply to");
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue("save");
		
		$this->addElements(array(
				$this->passwordElem,
				$this->passwordRepeatElem,
				$this->emailFromAddressElem,
				$this->emailFromNameElem,
				$this->emailReplyToElem,
				$submit));
	}
	
	public function setUser(MailElephantModel_User $user)
	{
		if($user->hasEmailFromSettings())
		{
			$this->emailFromAddressElem->setValue($user->getEmailFromSettings()->getAddress());
			$this->emailFromNameElem->setValue($user->getEmailFromSettings()->getName());
			$this->emailReplyToElem->setValue($user->getEmailFromSettings()->getReplyTo());
		}
	}
	
	public function newPasswordProvided()
	{
		return strlen($this->passwordElem->getValue()) > 0 
				&& strlen($this->passwordRepeatElem->getValue()) > 0;
	}
	
	public function getNewPassword()
	{
		return $this->passwordElem->getValue();
	}
	
	public function getMailSenderDetails()
	{
		$result = new MailElephantModel_MailSenderDetails($this->emailFromAddressElem->getValue());
		
		if($this->emailFromNameElem->getValue())
		{
			$result->setName($this->emailFromNameElem->getValue());
		}
		
		if($this->emailReplyToElem->getValue())
		{
			$result->setReplyTo($this->emailReplyToElem->getValue());
		}
		
		return $result;
	}
}