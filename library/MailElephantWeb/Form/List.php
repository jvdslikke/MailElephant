<?php 

class MailElephantWeb_Form_List extends Zend_Form
{
	private $titleInput;
	
	public function __construct($options = null)
	{
		parent::__construct($options);
		
		$this->setName('list');
		$this->setMethod('post');
		
		$this->titleInput = new Zend_Form_Element_Text('title');
		$this->titleInput->setLabel("Title");
		$this->titleInput->setRequired(true);
		
		$submitElem = new Zend_Form_Element_Submit("Submit");
		
		$this->setElements(array(
				$this->titleInput,
				$submitElem));
	}
	
	public function getTitleInputValue()
	{
		return $this->titleInput->getValue();
	}
}