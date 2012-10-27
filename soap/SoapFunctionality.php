<?php 

class SoapFunctionality
{
	private $storage;
	
	public function __construct(Common_Storage_Provider_Interface $storage)
	{
		$this->storage = $storage;
	}
	
	/**
	 * Returns if an emailaddress is subscribed to the given list
	 * 
	 * @param string $listId
	 * @param string $email
	 * @return boolean
	 */
	public function isSubscribed($listId, $email)
	{
		$list = MailElephantModel_List::fetchOneById($this->storage, $listId);
		
		if(!$list)
		{
			throw new Common_Exception_BadRequest("list with id ".$listId." not found");
		}
		
		return $list->hasSubscribtion($email);
	}
	
	/**
	 * Adds a subscribtion to the given list
	 * 
	 * @param string $listId
	 * @param string $email
	 * @param string $name (optional) The name of the subscriber
	 * 
	 * @return boolean True if added, false if already existed
	 */
	public function addSubscribtion($listId, $email, $name=null)
	{
		$list = MailElephantModel_List::fetchOneById($this->storage, $listId);
		
		if(!$list)
		{
			throw new Common_Exception_BadRequest("list with id ".$listId." not found");
		}
		
		if($list->hasSubscribtion($email))
		{
			return false;
		}
		
		$list->addSubscribtion(new MailElephantModel_Subscribtion($email, $name, new DateTime()));
		$list->save($this->storage);
		
		return true;
	}
}