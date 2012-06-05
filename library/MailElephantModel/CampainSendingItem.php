<?php

class MailElephantModel_CampainSendingItem
{
	const SENDINGSTATUS_QUEUED = 1;

	private $recipientEmail;
	private $recipientName;
	private $sendingStatus;
	
	public function __construct($recipientEmail, $recipientName, $sendingStatus)
	{
		$this->recipientEmail = $recipientEmail;
		$this->recipientName = $recipientName;
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
	
	public function getSendingStatus()
	{
		return $this->sendingStatus;
	}
}