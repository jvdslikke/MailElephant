<?php

class MailElephantWeb_Form_EmailUpload extends Zend_Form
{
	public function __construct($options = null)
	{
		parent::__construct($options);
		
		$this->setName("emailupload");
		$this->setAction("");
		$this->setAttrib('enctype', "multipart/form-data");
		
		$fileElem = new Zend_Form_Element_File('emailfile');
		$fileElem->setRequired(true);
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue("upload");
		
		$this->addElements(array($fileElem, $submit));
	}
}