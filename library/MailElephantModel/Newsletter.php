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
	
	public function getAttachments()
	{
		return $this->attachments;
	}
	
	public static function fetchAll(Common_Storage_Provider_Interface $storage)
	{
		$newsletters = array();
		
		foreach($storage->fetchAll('newsletters') as $data)
		{
			$newsletters[] = new MailElephantModel_Newsletter(
					$data['_id'],
					$data['subject'],
					$storage->createDateTimeFromInternalDateValue($data['created']),
					$data['plainTextBody'], 
					$data['htmlBody']);
		}
		
		return $newsletters;
	}
	
	public function save(Common_Storage_Provider_Interface $storage, $dataPath)
	{
		$data = array('subject'=>$this->subject,
				'created'=>$storage->createInternalDateValueFromDateTime($this->created),
				'plainTextBody'=>$this->plainTextBody,
				'htmlBody'=>$this->htmlBody,
				'headers'=>$this->headers);
		
		if(empty($this->id))
		{
			$this->id = $storage->insert('newsletters', $data, '_id');
		}
		else
		{
			$storage->upsert('newsletters', array('_id'=>$this->id), $data);
		}
		
		foreach($this->getAttachments() as $attachment)
		{
			/* @var $attachment Newsletter_Model_NewsletterAttachment */
			if(!$attachment->hasPath())
			{
				$attachment->setPath($dataPath."/attachments/".$this->id."/".$attachment->getName());
			}
			
			$attachment->save();
		}
	}
}