<?php 

class MailElephantWeb_Validate_SubscribtionEmailNotAlreadyExists implements Zend_Validate_Interface
{
	private $list;
	private $messages = array();
	
	public function __construct(MailElephantModel_List $list)
	{
		$this->list = $list;
	}
	
	public function isValid($value)
	{
		if($this->list->hasSubscribtion($value))
		{
			$this->messages[] = "A subscribtion with this emailaddress already exists";
			return false;
		}
		
		return true;
	}
	
	public function getMessages()
	{
		return $this->messages;
	}
}