<?php 

class MailElephantModel_SwiftStaticNewsletter extends MailElephantModel_StaticNewsletterAbstract
{
	private $swiftMessage;
	private $bodyIsHtml;
	
	public function __construct(Swift_Message $message, $bodyIsHtml)
	{
		$this->swiftMessage = $message;
		$this->bodyIsHtml = $bodyIsHtml;
	}
	
	public function getSubject()
	{
		return $this->swiftMessage->getSubject();
	}
	
	public function setReturnPath($returnPath)
	{
		$this->swiftMessage->setReturnPath($returnPath);
	}
	
	//TODO seperate abstract protected methods for setHtmlBody, setPlainTextBody, setAttachments
	public static function createFromNewsletter(MailElephantModel_Newsletter $newsletter)
	{
		$bodyIsHtml = null;
		
		$swiftMessage = Swift_Message::newInstance();
		$swiftMessage->setSubject($newsletter->getSubject());
		
		// body
		if($newsletter->hasPlainTextBody()
				&& $newsletter->hasHtmlBody())
		{
			$swiftMessage->setBody($newsletter->getHtmlBody(), 'text/html');
			$swiftMessage->addPart($newsletter->getPlainTextBody(), 'text/plain');
			$bodyIsHtml = true;
		}
		elseif(!$newsletter->hasHtmlBody())
		{
			$swiftMessage->setBody($newsletter->getPlainTextBody(), 'text/plain');
			$bodyIsHtml = false;
		}
		elseif(!$newsletter->hasPlainTextBody())
		{
			$swiftMessage->setBody($newsletter->getHtmlBody(), 'text/html');
			$bodyIsHtml = true;
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
		
		
		return new self($swiftMessage, $bodyIsHtml);
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
		return serialize(array('bodyIsHtml'=>$this->bodyIsHtml,
				'swiftMessage'=>$this->swiftMessage));
	}

	public function unserialize($serialized) 
	{	
		$array = unserialize($serialized);
		
		$this->__construct($array['swiftMessage'], $array['bodyIsHtml']);
	}
	
	public function getSwiftMessage()
	{
		return $this->swiftMessage;
	}
	
	protected function addUnsubscribeHtml($unsubscribeHtml)
	{
		//TODO if the html is in a part?
		if($this->bodyIsHtml === true)
		{
			$doc = new DOMDocument();
			
			$oldErrorReporting = error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
			$doc->loadHTML($this->swiftMessage->getBody());
			error_reporting($oldErrorReporting);
			
			$bodies = $doc->getElementsByTagName('body');
			
			$unsubscribeFrag = $doc->createDocumentFragment();
			$unsubscribeFrag->appendXML($unsubscribeHtml);
			
			$unsubscribeP = $doc->createElement('p');
			$unsubscribeP->appendChild($unsubscribeFrag);
			
			foreach($bodies as $body)
			{
				$body->appendChild($unsubscribeP);
			}
				
			$this->swiftMessage->setBody($doc->saveHTML(), "text/html", "utf-8");
		}
	}

	protected function addUnsubscribeText($unsubscribeText)
	{
		foreach($this->swiftMessage->getChildren() as $child)
		{
			/* @var $child Swift_Mime_MimeEntity */
			if($child->getContentType() == "text/plain")
			{
				$body = $child->getBody();
				
				$body .= " ".$unsubscribeText;
				
				$child->setBody($body, $child->getContentType());
			}
		}
	}
	
	public function __clone()
	{
		$this->swiftMessage = clone $this->swiftMessage;
	}
}