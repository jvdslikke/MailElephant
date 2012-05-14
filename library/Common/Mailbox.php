<?php

class Common_Mailbox
{
	private $imapResource;
	
	private static $attachmentPartTypes = array(
			3 => "application",
			5 => "image"
	);
	
	private static $attachmentPartSubTypes = array(
			"jpeg",
			"pdf"
	);
	
	private static $attachmentEncodings = array(
			3 => "base64"
	);
	
	public function __construct($path, $username='', $password='')
	{		
		$this->imapResource = @imap_open($path, $username, $password, OP_READONLY);
		
		if(!$this->imapResource)
		{
			$error = imap_last_error();
			if(!$error)
			{
				$error = "unknown error";
			}			
			
			throw new Exception("opening the mailbox failed: ".$error);
		}
	}
	
	public function getNumMessages()
	{
		return imap_num_msg($this->imapResource);
	}
	
	public function getNewsletter($index)
	{
		$headerinfo = imap_headerinfo($this->imapResource, $index);
		$overview = imap_fetch_overview($this->imapResource, $index);
		$msgStructure = imap_fetchstructure($this->imapResource, $index);
		$date = new DateTime($overview[0]->date);
		$subject = $headerinfo->subject;
		
		$plainTextBody = $this->fetchPlainTextBody($index, $msgStructure, array());
		$htmlBody = $this->fetchHtmlBody($index, $msgStructure, array());
		
		$newsletter = new MailElephantModel_Newsletter(null, $subject, $date, $plainTextBody, $htmlBody);
		
		$newsletter->setAttachments($this->fetchAttachments($index, $msgStructure, array()));
		
		return $newsletter;
	}
	
	private function fetchPlainTextBody($msgIndex, $structure, array $path)
	{
		if($structure->type === 0
				&& $structure->ifsubtype
				&& $structure->subtype == "PLAIN")
		{			
			return imap_fetchbody($this->imapResource, $msgIndex, implode('.', $path));
		}
		
		if(isset($structure->parts) && count($structure->parts) > 0)
		{
			$index = 0;
			foreach($structure->parts as $structure)
			{
				$index += 1;
				$result = $this->fetchPlainTextBody($msgIndex, $structure, array_merge($path, array($index)));
				if($result !== null)
				{
					return $result;
				}
			}
		}
		
		return null;
	}
	
	private function fetchHtmlBody($msgIndex, $structure, array $path)
	{
		if($structure->type === 0
				&& $structure->ifsubtype
				&& $structure->subtype == "HTML")
		{			
			return imap_fetchbody($this->imapResource, $msgIndex, implode('.', $path));
		}
		
		if(isset($structure->parts) && count($structure->parts) > 0)
		{
			$index = 0;
			foreach($structure->parts as $structure)
			{
				$index += 1;
				$result = $this->fetchHtmlBody($msgIndex, $structure, array_merge($path, array($index)));
				if($result !== null)
				{
					return $result;
				}
			}
		}
		
		return null;
	}
	
	private function fetchAttachments($msgIndex, $structure, array $path)
	{
		$attachments = array();
		
		// create attachment
		if(isset(self::$attachmentPartTypes[$structure->type])
				&& $structure->ifparameters
				&& isset(self::$attachmentEncodings[$structure->encoding])
				&& $structure->ifsubtype
				&& in_array(strtolower($structure->subtype), self::$attachmentPartSubTypes))
		{
			$name = null;
			foreach($structure->parameters as $parameter)
			{
				if($parameter->attribute == "NAME")
				{
					$name = $parameter->value;
				}
			}
			
			$cid = null;
			if($structure->ifid)
			{
				$cid = trim($structure->id, "<>");				
			}
			
			if(!empty($name))
			{
				$mimeType = self::$attachmentPartTypes[$structure->type].'/'.strtolower($structure->subtype);

				$attachment = new MailElephantModel_NewsletterAttachment($mimeType, $name, $cid);
				
				$body = imap_fetchbody($this->imapResource, $msgIndex, implode('.', $path));
				if(self::$attachmentEncodings[$structure->encoding] == "base64")
				{
					$attachment->setData(base64_decode($body));
				}
				
				$attachments[] = $attachment;
			}
		}
		
		if(isset($structure->parts) && count($structure->parts) > 0)
		{
			$index = 0;
			foreach($structure->parts as $part)
			{
				$index += 1;
				$result = $this->fetchAttachments($msgIndex, $part, array_merge($path, array($index)));
				if(!empty($result))
				{
					$attachments = array_merge($attachments, $result);
				}
			}
		}
		
		return $attachments;
	}
	
	public function __destruct()
	{
		imap_close($this->imapResource);
	}
}