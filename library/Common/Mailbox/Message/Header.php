<?php

class Common_Mailbox_Message_Header
{
	private $msgno;
	private $subject;
	private $date;
	private $fromEmail;
	private $fromName;
	
	public function __construct($msgno, $subject, DateTime $date=null, $fromEmail=null, $fromName=null)
	{
		$this->msgno = $msgno;
		$this->subject = $subject;
		$this->date = $date;
		$this->fromEmail = $fromEmail;
		$this->fromName = $fromName;
	}
	
	public function getMsgNo()
	{
		return $this->msgno;
	}
	
	public function getSubject()
	{
		return $this->subject;
	}
	
	public function setDate(DateTime $date)
	{
		$this->date = $date;
	}
	
	public function hasDate()
	{
		return $this->date != null;
	}
	
	public function getDate()
	{
		return $this->date;
	}
	
	public function getFromEmail()
	{
		return $this->fromEmail;
	}
	
	public function getFromName()
	{
		return $this->fromName;
	}
}