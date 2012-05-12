<?php

class MailElephantModel_Newsletter
{
	private $id;
	private $subject;
	private $created;
	private $plainTextBody;
	private $htmlBody;
	private $attachments;
	private $headers;
	
	public function __construct($id, $subject, DateTime $created, 
			$plainTextBody, $htmlBody, array $attachments = array())
	{
		$this->id = $id;
		$this->subject = $subject;
		$this->created = $created;
		$this->plainTextBody = $plainTextBody;
		$this->htmlBody = $htmlBody;
		
		$this->setAttachments($attachments);
		
		$this->headers = array();
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getSubject()
	{
		return $this->subject;
	}
	
	/**
	 * @return DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}
	
	public function hasPlainTextBody()
	{
		return !empty($this->plainTextBody);
	}
	
	public function getPlainTextBody()
	{
		return $this->plainTextBody;
	}
	
	public function hasHtmlBody()
	{
		return !empty($this->htmlBody);
	}
	
	public function getHtmlBody($convertAttachmentPathsToWebView = false)
	{
		$htmlBody = $this->htmlBody;
		
		if($convertAttachmentPathsToWebView)
		{
			//TODO more dynamic url (maybe using the url view helper?)
			$replacement = "'src=\"/newsletters/download-attachment/newsletterid/"
					.urlencode($this->id)
					."/attachmentcid/'.urlencode(\"$1\").'\"'";
			$htmlBody = preg_replace('/src="cid:(.*?)"/e', $replacement, $htmlBody);
		}
		
		return $htmlBody;
	}
	
	public function getAttachments()
	{
		return $this->attachments;
	}
	
	public function getNumAttachments()
	{
		return count($this->attachments);
	}
	
	public function setAttachments(array $attachments)
	{
		$this->attachments = array();
		
		foreach($attachments as $attachment)
		{
			$this->addAttachment($attachment);
		}
	}
	
	public function addAttachment(MailElephantModel_NewsletterAttachment $attachment)
	{
		$this->attachments[] = $attachment;
	}
	
	public function getAttachmentByCid($attachmentCid)
	{
		foreach($this->attachments as $attachment)
		{
			/* @var $attachment MailElephantModel_NewsletterAttachment */
			
			if($attachment->getCid() == $attachmentCid)
			{
				return $attachment;
			}
		}
		
		return null;
	}
	
	public static function fetchAll(Common_Storage_Provider_Interface $storage)
	{
		$newsletters = array();
		
		foreach($storage->fetchAll('newsletters') as $data)
		{
			$newsletters[] = self::createNewsletterFromData($storage, $data);
		}
		
		return $newsletters;
	}
	
	public static function fetchOneById(Common_Storage_Provider_Interface $storage, $id)
	{
		$result = $storage->fetchOneBy('newsletters', array('_id'=>$id));
		
		if($result === null)
		{
			return null;
		}
		
		return self::createNewsletterFromData($storage, $result);
	}
	
	private static function createNewsletterFromData(Common_Storage_Provider_Interface $storage, $data)
	{
		$attachments = array();
		
		foreach($data['attachments'] as $attachmentData)
		{
			$attachments[] = new MailElephantModel_NewsletterAttachment(
					$attachmentData['mimeType'], 
					$attachmentData['name'],
					$attachmentData['cid'],
					$attachmentData['path']);
		}
		
		return new self(
				$data['_id'],
				$data['subject'],
				$storage->createDateTimeFromInternalDateValue($data['created']),
				$data['plainTextBody'], 
				$data['htmlBody'],
				$attachments);
	}
	
	public function save(Common_Storage_Provider_Interface $storage, $dataPath)
	{		
		$data = array('subject' => $this->subject,
				'created' => $storage->createInternalDateValueFromDateTime($this->created),
				'plainTextBody' => $this->plainTextBody,
				'htmlBody' => $this->htmlBody,
				'headers' => $this->headers);
		
		if(empty($this->id))
		{
			$this->id = $storage->insert('newsletters', $data, '_id');
		}
		else
		{
			$storage->update('newsletters', array('_id'=>$this->id), $data);
		}
		
		$attachments = array();
		foreach($this->getAttachments() as $attachment)
		{
			/* @var $attachment Newsletter_Model_NewsletterAttachment */
			if(!$attachment->hasPath())
			{
				$attachment->setPath($dataPath."/attachments/".$this->id."/".$attachment->getName());
			}
			
			$attachment->save();
			
			$attachments[] = array(
					'mimeType' => $attachment->getMimeType(),
					'name' => $attachment->getName(),
					'cid' => $attachment->getCid(),
					'path' => $attachment->getPath());
		}
		
		var_dump($storage->update('newsletters', 
				array('_id'=>$this->id), 
				array('attachments'=>$attachments)));
	}
}