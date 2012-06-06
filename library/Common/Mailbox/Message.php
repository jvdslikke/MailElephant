<?php

class Common_Mailbox_Message extends Common_Mailbox_Message_Header
{
	private $plainTextBody;
	private $htmlBody;
	private $attachments = array();
	
	public function __construct($msgno, $subject, DateTime $date,
			$plainTextBody, $htmlBody, array $attachments)
	{
		parent::__construct($msgno, $subject, $date);
		
		$this->plainTextBody = $plainTextBody;
		$this->htmlBody = $htmlBody;
		
		$this->setAttachments($attachments);
	}
	
	public function getPlainTextBody()
	{
		return $this->plainTextBody;
	}
	
	public function getHtmlBody()
	{
		return $this->htmlBody;
	}
	
	public function getAttachments()
	{
		return $this->attachments;
	}
	
	public function setAttachments(array $attachments)
	{
		$this->attachments = array();
		
		foreach($attachments as $attachment)
		{
			$this->addAttachment($attachment);
		}
	}
	
	public function addAttachment(Common_Mailbox_Message_Attachment $attachment)
	{
		$this->attachments[] = $attachment;
	}
}