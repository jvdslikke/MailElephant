<?php

class Common_Mailbox
{
	private $imapResource;
	private $path;
	
	private static $attachmentPartTypes = array(
			3 => "application",
			5 => "image"
	);
	
	private static $attachmentPartSubTypes = array(
			"jpeg",
			"pdf",
			"png"
	);
	
	private static $attachmentEncodings = array(
			3 => "base64"
	);
	
	public function __construct($path, $username='', $password='')
	{
		if(!function_exists('imap_open'))
		{
			throw new Exception("imap extension not installed");
		}
		
		$this->path = $path;
		$this->imapResource = imap_open($path, $username, $password, OP_READONLY);
		
		if(($error = imap_last_error()) !== false)
		{			
			throw new Exception("opening the mailbox failed: ".$error);
		}
	}
	
	public function getMailbox()
	{
		return $this->path;
	}
	
	public function getNumMessages()
	{
		$num = imap_num_msg($this->imapResource);
		
		if(($err = imap_last_error()) !== false)
		{
			throw new Exception("retrieving message count failed: ".$err);
		}
		
		return $num;
	}
	
	public function getHeaders($from, $to)
	{
		$imapHeaders = imap_fetch_overview($this->imapResource, "$from:$to");
		
		$headers = array();
		foreach($imapHeaders as $imapHeader)
		{			
			$headers[] = $this->createMessageHeaderFromImapHeader($imapHeader);
		}
		
		return array_reverse($headers);
	}
	
	private function createMessageHeaderFromImapHeader($imapHeader)
	{		
		$subject = null;
		if(!empty($imapHeader->subject))
		{
			$subject = trim($this->decodeImapHeader($imapHeader->subject), '"');
		}
		
		$date = null;
		if(isset($imapHeader->date))
		{
			$date = new DateTime($imapHeader->date);
		}
		
		// get name and email
		$fromEmail = null;
		$fromName = null;
		
		$from = null;
		if(!empty($imapHeader->from))
		{
			$from = $this->decodeImapHeader($imapHeader->from);
		}
		
		if($from !== null)
		{			
			if(preg_match('/^(?P<name>.+?) <(?P<email>.+)>$/', $from, $fromMatches))
			{
				$fromEmail = $fromMatches['email'];
				$fromName = trim($fromMatches['name'], '"');
			}
			else
			{
				$fromEmail = $from;
			}
		}
		
		return new Common_Mailbox_Message_Header(
				$imapHeader->msgno, 
				$subject,
				$date,
				$fromEmail,
				$fromName);
	}
	
	private function decodeImapHeader($imapHeader)
	{
		$result = null;
		
		$decodedSubject = imap_mime_header_decode($imapHeader);
		foreach($decodedSubject as $decodedSubjectPart)
		{
			if(!empty($decodedSubjectPart))
			{
				$result .= $decodedSubjectPart->text;
			}
		}

		return $result;
	}
	
	/**
	 * @return Common_Mailbox_Message Null if message not found
	 */
	public function getMessage($index)
	{
		$imapHeaders = imap_fetch_overview($this->imapResource, $index);
		if(empty($imapHeaders))
		{
			return null;
		}
		
		$header = $this->createMessageHeaderFromImapHeader($imapHeaders[0]);
		
		$msgStructure = imap_fetchstructure($this->imapResource, $index);
		
		$plainTextBody = $this->fetchPlainTextBody($index, $msgStructure, array());
		$htmlBody = $this->fetchHtmlBody($index, $msgStructure, array());
		
		$attachments = $this->fetchAttachments($index, $msgStructure, array());
		
		$newsletter = new Common_Mailbox_Message(
				$header->getMsgNo(), 
				$header->getSubject(), 
				$header->getDate(), 
				$plainTextBody, 
				$htmlBody,
				$attachments,
				$header->getFromName(),
				$header->getFromEmail());
				
		return $newsletter;
	}
	
	private function fetchPlainTextBody($msgIndex, $structure, array $path)
	{
		if($structure->type === 0
				&& $structure->ifsubtype
				&& $structure->subtype == "PLAIN")
		{			
			$body = imap_fetchbody($this->imapResource, $msgIndex, implode('.', $path));
			
			$body = $this->decodeBodyPart($structure->encoding, $body);
			
			if($structure->ifparameters)
			{
				foreach($structure->parameters as $parameter)
				{
					if($parameter->attribute == "charset")
					{
						$body = $this->charsetDecodeBodyPart($parameter->value, $body);
					}
				}
			}
			
			return $body;
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
			if(empty($path))
			{
				$path[] = '1';
			}
			
			$body = imap_fetchbody($this->imapResource, $msgIndex, implode('.', $path));
			
			$body = $this->decodeBodyPart($structure->encoding, $body);
			
			if($structure->ifparameters)
			{
				foreach($structure->parameters as $parameter)
				{
					if($parameter->attribute == "charset")
					{
						$body = $this->charsetDecodeBodyPart($parameter->value, $body);
					}
				}
			}
			
			return $body;
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
	
	private function decodeBodyPart($encodingTypeFlag, $body)
	{
		switch($encodingTypeFlag)
		{
			case 0:
			case 1:
				return $body;
			
			case 4:
				return imap_qprint($body);
			
			default:
				throw new Exception("encoding type ".$encodingTypeFlag." not supported");
		}
	}
	
	/**
	 * @return utf-8 encoded string
	 */
	private function charsetDecodeBodyPart($charset, $body)
	{
		$charset = strtoupper($charset);
		if($charset == "UTF-8")
		{
			return $body;
		}
		else
		{
			$body = iconv($charset, "UTF-8", $body);
			
			if($body === false)
			{
				throw new Exception("converting from charset ".$charset." failed");
			}
			
			return $body;
		}
	}
	
	private function fetchAttachments($msgIndex, $structure, array $path)
	{
		$attachments = array();
		
		// create attachment
		if(isset(self::$attachmentPartTypes[$structure->type])
				&& $structure->ifparameters
				&& $structure->ifsubtype
				&& in_array(strtolower($structure->subtype), self::$attachmentPartSubTypes))
		{			
			$cid = null;
			if($structure->ifid)
			{
				$cid = trim($structure->id, "<>");				
			}
			
			$name = null;
			foreach($structure->parameters as $parameter)
			{
				if($parameter->attribute == "NAME" || $parameter->attribute == "name")
				{
					$name = $parameter->value;
				}
			}
			
			if(empty($name) && strpos($cid, '@') !== false)
			{
				$cidVars = explode('@', $cid);
				
				$name = $cidVars[0];
			}
			
			if(!empty($name))
			{
				$mimeType = self::$attachmentPartTypes[$structure->type].'/'.strtolower($structure->subtype);
				
				$attachment = new Common_Mailbox_Message_Attachment($mimeType, $name, $cid, null);
				
				$body = imap_fetchbody($this->imapResource, $msgIndex, implode('.', $path));
				if(self::$attachmentEncodings[$structure->encoding] == "base64")
				{
					$attachment->setData(base64_decode($body));
				}
				else
				{
					throw new Common_Exception_NotImplemented("attachment encoding not implemented");
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