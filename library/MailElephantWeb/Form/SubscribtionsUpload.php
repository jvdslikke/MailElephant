<?php

class MailElephantWeb_Form_SubscribtionsUpload extends Zend_Form
{
	private $encodingElem;
	
	public function __construct($options = null)
	{
		parent::__construct($options);
		
		$this->setName("subscribtionsupload");
		$this->setAction("");
		$this->setAttrib('enctype', "multipart/form-data");
		
		$fileElem = new Zend_Form_Element_File('subscribtionsfile');
		$fileElem->setRequired(true);
		
		$this->encodingElem = new Zend_Form_Element_Select('encoding');
		$this->encodingElem->setMultiOptions(Common_File::getEncodings());
		$this->encodingElem->setValue(array_search(Common_File::ENCODING_PHP, Common_File::getEncodings()));
		$this->encodingElem->setLabel("File encoding");
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue("upload");
		
		$this->addElements(array($fileElem, $this->encodingElem, $submit));
	}
	
	public function getSelectedEncoding()
	{
		$encodings = Common_File::getEncodings();
		return $encodings[$this->encodingElem->getValue()];
	}
}