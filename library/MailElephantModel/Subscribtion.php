<?php

class MailElephantModel_Subscribtion
{
	private $email;
	private $name;
	
	public function __construct($email, $name = null)
	{
		$this->email = $email;
		$this->name = $name;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	public function setName($name)
	{
		$this->name = $name;
	}
}