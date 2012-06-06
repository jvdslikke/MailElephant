<?php

class Common_Mailbox_Message_Header
{
	private $msgno;
	private $subject;
	private $date;
	
	public function __construct($msgno, $subject, DateTime $date)
	{
		$this->msgno = $msgno;
		$this->subject = $subject;
		$this->date = $date;
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
	
	public function getDate()
	{
		return $this->date;
	}
}