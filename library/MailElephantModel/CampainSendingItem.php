<?php

class MailElephantModel_CampainSendingItem
{
	const SENDINGSTATUS_QUEUED = 1;
	const SENDINGSTATUS_SENT = 2;

	private $recipientEmail;
	private $recipientName;
	private $sendingStatus;
	
	public function __construct($recipientEmail, $recipientName, $sendingStatus=null)
	{
		$this->recipientEmail = $recipientEmail;
		$this->recipientName = $recipientName;
		
		if($sendingStatus === null)
		{
			$sendingStatus = self::SENDINGSTATUS_QUEUED;
		}
		$this->sendingStatus = $sendingStatus;
	}
	
	public function getRecipientEmail()
	{
		return $this->recipientEmail;
	}
	
	public function getRecipientName()
	{
		return $this->recipientName;
	}
	
	public function isSent()
	{
		return $this->sendingStatus == self::SENDINGSTATUS_SENT;
	}
	
	public function getSendingStatus()
	{
		return $this->sendingStatus;
	}
}