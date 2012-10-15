<?php

class MailElephantModel_CampainSendingItem
{
	const SENDINGSTATUS_QUEUED = 1;
	const SENDINGSTATUS_SENT = 2;
	const SENDINGSTATUS_ERROR = 3;

	private $recipientEmail;
	private $recipientName;
	private $sendingStatus;
	private $sendingErrorMessage;
	
	public function __construct($recipientEmail, $recipientName, $sendingStatus=null, $sendingErrorMessage=null)
	{
		$this->recipientEmail = $recipientEmail;
		$this->recipientName = $recipientName;
		
		if($sendingStatus === null)
		{
			$sendingStatus = self::SENDINGSTATUS_QUEUED;
		}
		$this->sendingStatus = $sendingStatus;
		$this->sendingErrorMessage = $sendingErrorMessage;
	}
	
	public function getRecipientEmail()
	{
		return $this->recipientEmail;
	}
	
	public function getRecipientName()
	{
		return $this->recipientName;
	}
	
	public function isQueued()
	{
		return $this->sendingStatus == self::SENDINGSTATUS_QUEUED;
	}
	
	public function isSent()
	{
		return $this->sendingStatus == self::SENDINGSTATUS_SENT;
	}
	
	public function isError()
	{
		return $this->sendingStatus == self::SENDINGSTATUS_ERROR;
	}
	
	public function getSendingStatus()
	{
		return $this->sendingStatus;
	}
	
	public function getSendingErrorMessage()
	{
		return $this->sendingErrorMessage;
	}
	
	public function setError($errorMessage=null)
	{
		$this->sendingStatus = self::SENDINGSTATUS_ERROR;
		$this->sendingErrorMessage = $errorMessage;
	}
	
	public function setSent()
	{
		$this->sendingStatus = self::SENDINGSTATUS_SENT;
	}
}