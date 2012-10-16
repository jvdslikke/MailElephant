<?php 

class MailElephantModel_SwiftStaticNewsletter implements MailElephantModel_IStaticNewsletter
{
	private $swiftMessage;
	
	public function __construct(Swift_Message $message)
	{
		$this->swiftMessage = $message;
	}
	
	public function getSubject()
	{
		return $this->swiftMessage->getSubject();
	}

	public static function createFromNewsletter(MailElephantModel_Newsletter $newsletter)
	{
		$swiftMessage = Swift_Message::newInstance();
		$swiftMessage->setSubject($newsletter->getSubject());
		
		// body
		if($newsletter->hasPlainTextBody()
				&& $newsletter->hasHtmlBody())
		{
			$swiftMessage->setBody($newsletter->getHtmlBody(), 'text/html');
			$swiftMessage->addPart($newsletter->getPlainTextBody(), 'text/plain');
		}
		elseif(!$newsletter->hasHtmlBody())
		{
			$swiftMessage->setBody($newsletter->getPlainTextBody(), 'text/plain');
		}
		elseif(!$newsletter->hasPlainTextBody())
		{
			$swiftMessage->setBody($newsletter->getHtmlBody(), 'text/html');
		}
		else
		{
			throw new Exception("no body in newsletter");
		}
		
		// attachments
		foreach($newsletter->getAttachments() as $attachment)
		{
			/* @var $attachment MailElephantModel_NewsletterAttachment */
			
			$swiftAttachment = Swift_Attachment::newInstance(
					$attachment->getFileContents(),
					null,
					$attachment->getMimeType());
			
			$swiftAttachment->setId($attachment->getCid());
			
			if($attachment->isEmbedded())
			{
				$swiftAttachment->setDisposition('inline');
			}
			
			$swiftAttachment->setFilename($attachment->getBasename());				
			
			$swiftMessage->attach($swiftAttachment);
		}
		
		
		return new self($swiftMessage);
	}
	
	public function setRecipient($email, $name=null)
	{
		$this->swiftMessage->setTo($email, $name);
	}
	
	public function setFrom(MailElephantModel_MailSenderDetails $senderSettings)
	{
		$this->swiftMessage->setFrom($senderSettings->getAddress(), $senderSettings->getName());
		
		if($senderSettings->getReplyTo())
		{
			$this->swiftMessage->setReplyTo($senderSettings->getReplyTo());
		}
	}
	
	public function serialize() 
	{
		return serialize($this->swiftMessage);
	}

	public function unserialize($serialized) 
	{	
		$swiftMessage = unserialize($serialized);
		
		$this->__construct($swiftMessage);
	}
	
	public function getSwiftMessage()
	{
		return $this->swiftMessage;
	}
}