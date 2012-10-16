<?php 

class MailElephantWeb_Validate_MailboxNotAlreadyExists implements Zend_Validate_Interface
{
	private $user;
	private $origMailboxName;
	private $messages = array();
	
	public function __construct(MailElephantModel_User $user, $origMailboxName=null)
	{
		$this->user = $user;
		$this->origMailboxName = $origMailboxName;
	}
	
	public function setOrigMailboxName($value)
	{
		$this->origMailboxName = $value;
	}
	
	public function isValid($value)
	{		
		if($this->origMailboxName && $value == $this->origMailboxName)
		{
			return true;
		}
		
		if($this->user->getMailboxByName($value) !== null)
		{
			$this->messages[] = "Mailbox which such a name already exists";
			return false;
		}
		
		return true;
	}
	
	public function getMessages()
	{
		return $this->messages;
	}
}