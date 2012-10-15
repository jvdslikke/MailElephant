<?php 

class MailElephantWeb_Form_Subscribtion extends Zend_Form
{
	private $nameInput;
	private $emailInput;
	
	private $list;
	
	public function __construct($options = null, MailElephantModel_List $list)
	{
		parent::__construct($options);
		
		$this->list = $list;
		
		$this->setName('subscribtion');
		$this->setMethod('post');
		
		$this->nameInput = new Zend_Form_Element_Text('name');
		$this->nameInput->setLabel("Name");
		
		$this->emailInput = new Zend_Form_Element_Text('email');
		$this->emailInput->setRequired(true);
		$this->emailInput->addValidator(new Zend_Validate_EmailAddress());
		$this->emailInput->addValidator(new MailElephantWeb_Validate_SubscribtionEmailNotAlreadyExists($list));
		$this->emailInput->setLabel("Emailaddress");
		
		$submitElem = new Zend_Form_Element_Submit('submit');
		$submitElem->setValue("save");
		
		$this->addElements(array($this->nameInput, $this->emailInput, $submitElem));
	}
	
	public function saveSubscribtion(Common_Storage_Provider_Interface $storage)
	{
		$this->list->addSubscribtion(new MailElephantModel_Subscribtion(
				$this->emailInput->getValue(),
				$this->nameInput->getValue()));
		
		$this->list->save($storage);
	}
}