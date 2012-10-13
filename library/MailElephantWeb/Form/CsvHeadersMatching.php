<?php

class MailElephantWeb_Form_CsvHeadersMatching extends Zend_Form
{
	const NEEDED_FIELD_PREFIX = 'needed_field_';
	
	private $_csvHeaders;
	
	public function __construct($options = null, array $neededFields, array $csvHeaders)
	{
		parent::__construct($options);
		
		$this->_csvHeaders = $csvHeaders;
		
		$this->setName("csvheadersmatching");
		$this->setAction("");
		
		foreach($neededFields as $name => $title)
		{
			$elemName = self::NEEDED_FIELD_PREFIX.$name;
			$elem = new Zend_Form_Element_Select($elemName);
			
			$elem->addMultiOption("", "-- select a CSV field --");
			$elem->addMultiOptions($csvHeaders);
			
			$elem->setLabel($title);
			$elem->setRequired(true);
			
			$this->addElement($elem);
		}
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue("submit");
		
		$this->addElements(array($submit));
	}
	
	public function getCsvHeaderByNeededFieldName($neededFieldName)
	{
		$value = $this->getElement(self::NEEDED_FIELD_PREFIX.$neededFieldName)->getValue();
		
		return $this->_csvHeaders[$value];
	}
}