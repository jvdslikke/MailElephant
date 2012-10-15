<?php 

class MailElephantModel_MailSenderDetails
{
	private $address;
	private $name;
	private $replyTo;
	
	public function __construct($address, $name=null, $replyTo=null)
	{
		$this->address = $address;
		$this->name = $name;
		$this->replyTo = $replyTo;
	}
	
	public function getAddress()
	{
		return $this->address;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getReplyTo()
	{
		return $this->replyTo;
	}
	
	public function setName($name)
	{
		$this->name = $name;
	}
	
	public function setReplyTo($replyTo)
	{
		$this->replyTo = $replyTo;
	}
}