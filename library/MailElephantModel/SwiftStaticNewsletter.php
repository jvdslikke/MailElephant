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
	
	public function setRecipient($emailAddress, $name)
	{
		$this->swiftMessage->setTo($emailAddress, $name);
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
		
		//TODO attachments
		
		return new self($swiftMessage);
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
}