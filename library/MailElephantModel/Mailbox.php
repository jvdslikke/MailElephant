<?php

class MailElephantModel_Mailbox
{
	private $mailbox;
	private $username;
	private $password;
	
	public function __construct($mailbox, $username, $password)
	{
		$this->mailbox = $mailbox;
		$this->username = $username;
		$this->password = $password;
	}
	
	public function getMailbox()
	{
		return $this->mailbox;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	public function getPassword()
	{
		return $this->password;
	}
}