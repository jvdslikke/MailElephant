<?php

class MailElephantModel_User
{
	private $email;
	private $passwordHash;
	private $mailboxes = array();
	
	private static $cache = array();
	
	public function __construct($email, $passwordHash, array $mailboxes = array())
	{
		$this->email = $email;
		$this->passwordHash = $passwordHash;
		
		$this->setMailboxes($mailboxes);
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
	
	public function getMailboxes()
	{
		return $this->mailboxes;
	}
	
	public function setMailboxes(array $mailboxes)
	{
		$this->mailboxes = array();
		
		foreach($mailboxes as $mailbox)
		{
			$this->addMailbox($mailbox);
		}
	}
	
	public function addMailbox(MailElephantModel_Mailbox $mailbox)
	{
		$this->mailboxes[] = $mailbox;
	}
	
	
	public static function fetchOneByEmail(Common_Storage_Provider_Interface $db, $email)
	{
		if(!isset(self::$cache[$email]))
		{
			$result = $db->fetchOneBy('users', array('email'=>$email));
			
			if($result === null)
			{
				return null;
			}
			
			$mailboxes = array();
			foreach($result['mailboxes'] as $mailboxResult)
			{
				$mailboxes[] = new MailElephantModel_Mailbox(
						$mailboxResult['mailbox'], 
						$mailboxResult['username'], 
						$mailboxResult['password']);
			}
			
			self::$cache[$email] = new self($result['email'], $result['passwordHash'], $mailboxes);
		}
		
		return self::$cache[$email];
	}
	
	public function save(Common_Storage_Provider_Interface $db)
	{
		$mailboxes = array();
		foreach($this->mailboxes as $mailbox)
		{
			$mailboxes[] = array(
					'mailbox' => $mailbox->getMailbox(),
					'username' => $mailbox->getUsername(),
					'password' => $mailbox->getPassword());
		}
		
		$db->upsert('users', 
				array('email' => $this->email), 
				array('passwordHash' => $this->passwordHash,
						'mailboxes' => $mailboxes));
	}
}