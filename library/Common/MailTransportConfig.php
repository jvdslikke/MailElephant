<?php 

class Common_MailTransportConfig
{
	private $host;
	private $port;
	private $username;
	private $password;
	private $returnPathDomain;
	
	public function __construct($host, $port, $username, $password, $returnPathDomain)
	{
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->returnPathDomain = $returnPathDomain;
	}
	
	public static function createFromOptions($options)
	{
		// required options
		if(empty($options['host']))
		{
			throw new Exception("no host specified");
		}
		$host = $options['host'];
		
		if(empty($options['returnPathDomain']))
		{
			throw new Exception("no return path domain specified");
		}
		$returnPathDomain = $options['returnPathDomain'];
		
		// other options
		$port = null;
		$username = null;
		$password = null;
		
		if(!empty($options['port']))
		{
			$port = $options['port'];
		}
		if(!empty($options['username']))
		{
			$username = $options['username'];
		}
		if(!empty($options['password']))
		{
			$password = $options['password'];
		}
		
		return new self($host, $port, $username, $password, $returnPathDomain);
	}
	
	public function getHost()
	{
		return $this->host;
	}
	
	public function hasPort()
	{
		return $this->port !== null;
	}
	
	public function getPort()
	{
		return $this->port;
	}
	
	public function hasCredentials()
	{
		return $this->username !== null && $this->password !== null;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	public function getPassword()
	{
		return $this->password;
	}
	
	public function getReturnPathDomain()
	{
		return $this->returnPathDomain;
	}
}