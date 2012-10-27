<?php

class MailElephantModel_Subscribtion
{
	private $email;
	private $name;
	private $added;
	
	public function __construct($email, $name = null, DateTime $added = null)
	{
		$this->email = $email;
		$this->name = $name;
		$this->added = $added;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	/**
	 * @return DateTime
	 */
	public function getAdded()
	{
		return $this->added;
	}
	
	public function setName($name)
	{
		$this->name = $name;
	}
}