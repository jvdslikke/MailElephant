<?php

class MailElephantModel_User
{
	private $email;
	private $passwordHash;
	private $mailboxes = array();
	private $emailFromSettings;
	private $unsubscribeHtml;
	private $unsubscribeText;
	
	private static $cache = array();
	
	public function __construct($email, $passwordHash, array $mailboxes,
			MailElephantModel_MailSenderDetails $emailFromSettings,
			$unsubscribeHtml, $unsubscribeText)
	{
		$this->email = $email;
		$this->passwordHash = $passwordHash;
		
		$this->setMailboxes($mailboxes);
		
		$this->emailFromSettings = $emailFromSettings;
		
		$this->unsubscribeHtml = $unsubscribeHtml;
		$this->unsubscribeText = $unsubscribeText;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	public function getPasswordHash()
	{
		return $this->passwordHash;
	}
	
	public function getMailboxes()
	{
		return $this->mailboxes;
	}
	
	public function hasEmailFromSettings()
	{
		return $this->emailFromSettings !== null;
	}
	
	public function getEmailFromSettings()
	{
		return $this->emailFromSettings;
	}
	
	public function getMailboxByName($mailboxName)
	{
		$result = null;
		
		foreach($this->mailboxes as $mailbox)
		{
			if($mailbox->getName() == $mailboxName)
			{
				$result = $mailbox;
			}
		}
		
		return $result;
	}
	
	public function getUnsubscribeHtml()
	{
		return $this->unsubscribeHtml;
	}
	
	public function getUnsubscribeText()
	{
		return $this->unsubscribeText;
	}
	
	public function deleteMailbox($mailboxName)
	{
		$result = array();
		
		foreach($this->mailboxes as $mailbox)
		{
			if($mailbox->getName() != $mailboxName)
			{
				$result[] = $mailbox;
			}
		}
		
		$this->mailboxes = $result;
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
	
	public function setPasswordHash($passwordHash)
	{
		$this->passwordHash = $passwordHash;
	}
	
	public function setPasswordPlainText($plainTextPassword)
	{
		$this->passwordHash = Common_Bcrypt::hash($plainTextPassword);
	}
	
	public function setEmailFromSettings(MailElephantModel_MailSenderDetails $fromSettings)
	{
		$this->emailFromSettings = $fromSettings;
	}
	
	public function setUnsubscribeHtml($unsubscribeHtml)
	{
		$this->unsubscribeHtml = $unsubscribeHtml;
	}
	
	public function setUnsubscribeText($unsubscribeText)
	{
		$this->unsubscribeText = $unsubscribeText;
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
						$mailboxResult['name'],
						$mailboxResult['host'],
						$mailboxResult['port'],
						$mailboxResult['username'], 
						$mailboxResult['password'],
						$mailboxResult['useSsl'],
						$mailboxResult['novalidateCert']);
			}
			
			self::$cache[$email] = new self(
					$result['email'], 
					$result['passwordHash'], 
					$mailboxes,
					new MailElephantModel_MailSenderDetails(
							$result['emailFromAddress'],
							$result['emailFromName'],
							$result['emailFromReplyTo']),
					$result['unsubscribeHtml'],
					$result['unsubscribeText']);
		}
		
		return self::$cache[$email];
	}
	
	public function save(Common_Storage_Provider_Interface $db)
	{
		$mailboxes = array();
		foreach($this->mailboxes as $mailbox)
		{
			/* @var $mailbox MailElephantModel_Mailbox */
			$mailboxes[] = array(
					'name' => $mailbox->getName(),
					'host' => $mailbox->getHost(),
					'port' => $mailbox->getPort(),
					'username' => $mailbox->getUsername(),
					'password' => $mailbox->getPassword(),
					'useSsl' => $mailbox->getUseSsl(),
					'novalidateCert' => $mailbox->getNovalidateCert());
		}
		
		$db->upsert('users', 
				array('email' => $this->email), 
				array('passwordHash' => $this->passwordHash,
						'mailboxes' => $mailboxes,
						'emailFromAddress' => $this->emailFromSettings->getAddress(),
						'emailFromName' => $this->emailFromSettings->getName(),
						'emailFromReplyTo' => $this->emailFromSettings->getReplyTo(),
						'unsubscribeHtml' => $this->unsubscribeHtml,
						'unsubscribeText' => $this->unsubscribeText));
	}
}