<?php

class MailElephantWeb_Form_Mailbox extends Zend_Form
{
	public function __construct($options = null)
	{
		parent::__construct($options);

		$this->setName('mailbox');
		$this->setMethod('post');
		
		$mailboxElem = new Zend_Form_Element_Text('mailbox');
		$mailboxElem->setLabel("mailbox");
		$mailboxElem->setRequired(true);
		
		$usernameElem = new Zend_Form_Element_Text('username');
		$usernameElem->setLabel("username");
		
		$passwordElem = new Zend_Form_Element_Password('password');
		$passwordElem->setLabel("password");
		
		$submitElem = new Zend_Form_Element_Submit('submit');
		$submitElem->setValue("proceed");
		
		$this->addElements(array($mailboxElem, $usernameElem, $passwordElem, $submitElem));
	}
}