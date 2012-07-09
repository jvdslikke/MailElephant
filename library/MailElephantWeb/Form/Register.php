<?php

class MailElephantWeb_Form_Register extends Zend_Form
{
	public function __construct($options = null)
	{
		parent::__construct($options);
		
		$this->setName('register');
		$this->setMethod('post');
		
		$emailElem = new Zend_Form_Element_Text('email');
		$emailElem->setLabel('Emailaddress');
		$emailElem->setRequired(true);
		
		$passwordElem = new Zend_Form_Element_Password('password');
		$passwordElem->setLabel('Password');
		$passwordElem->setRequired(true);
		
		$submitElem = new Zend_Form_Element_Submit('Submit');
		
		$this->addElements(array($emailElem, $passwordElem, $submitElem));
	}
}