<?php

class MailElephantModel_User
{
	// ensure index
	private $email;
	private $passwordHash;
	
	public function __construct($email, $passwordHash)
	{
		$this->email = $email;
		$this->passwordHash = $passwordHash;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	public function getPasswordHash()
	{
		return $this->passwordHash;
	}
	
	public function setPasswordHash($passwordHash)
	{
		$this->passwordHash = $passwordHash;
	}
	
	
	public static function fetchOneByEmail(Common_Storage_Provider_Interface $db, $email)
	{
		$result = $db->fetchOneBy('users', array('email'=>$email));
		
		if($result === null)
		{
			return null;
		}
		
		return new self($result['email'], $result['passwordHash']);
	}
	
	public function save(Common_Storage_Provider_Interface $db)
	{
		$db->upsert('users', 
				array('email' => $this->email), 
				array('passwordHash' => $this->passwordHash));
	}
}