<?php

class MailElephantModel_User
{
	// ensure index
	private $username;
	private $passwordHash;
	
	public function __construct($username, $passwordHash)
	{
		$this->username = $username;
		$this->passwordHash = $passwordHash;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	public function getPasswordHash()
	{
		return $this->passwordHash;
	}
	
	public function setPasswordHash($passwordHash)
	{
		$this->passwordHash = $passwordHash;
	}
	
	
	public static function fetchByUsername(Common_Storage_Provider_Interface $db, $username)
	{		
		$result = $db->fetchOneBy('users', array('_id'=>$username));
		
		if($result === null)
		{
			return null;
		}
		
		return new self($result['_id'], $result['passwordHash']);
	}
	
	public function save(Common_Storage_Provider_Interface $db)
	{
		$db->upsert('users', 
				array('_id' => $this->username), 
				array('passwordHash' => $this->passwordHash));
	}
}