<?php

class MailElephantModel_Mailbox
{	
	private $name;
	private $host;
	private $port;
	private $username;
	private $password;
	private $useSsl;
	private $novalidateCert;
	
	public function __construct($name, $host, $port=null, $username, $password, $useSsl=false, $novalidateCert=false)
	{
		$this->name = $name;
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->useSsl = $useSsl;
		$this->novalidateCert = $novalidateCert;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getHost()
	{
		return $this->host;
	}
	
	public function getPort()
	{
		return $this->port;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	public function getPassword()
	{
		return $this->password;
	}
	
	public function getUseSsl()
	{
		return $this->useSsl;
	}
	
	public function getNovalidateCert()
	{
		return $this->novalidateCert;
	}
	
	public function getMailboxConnectionString()
	{
		$result = $this->host;
		
		if($this->port)
		{
			$result .= ":".$this->port;
		}
		
		$flags = array("/readonly");
		if($this->useSsl)
		{
			$flags[] = "/ssl";
		}
		if($this->novalidateCert)
		{
			$flags[] = "/novalidate-cert";
		}
		$result .= implode("", $flags);
		
		return "{".$result."}";
	}
}