<?php

class MailElephantModel_Newsletter
{
	private $id;
	private $subject;
	private $created;
	private $plainTextBody;
	private $htmlBody;
	private $attachments;
	private $user;
	
	public function __construct($id, $subject, DateTime $created, 
			$plainTextBody, $htmlBody, array $attachments = array(),
			MailElephantModel_User $user = null)
	{
		$this->id = $id;
		$this->subject = $subject;
		$this->created = $created;
		$this->plainTextBody = $plainTextBody;
		$this->htmlBody = $htmlBody;
		
		$this->setAttachments($attachments);
		
		$this->user = $user;
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
	
	public function setUser(MailElephantModel_User $user)
	{
		$this->user = $user;
	}
	
	public static function fetchMoreByUser(Common_Storage_Provider_Interface $storage, 
			MailElephantModel_User $user)
	{
		$newsletters = array();
		
		foreach($storage->fetchMoreBy('newsletters', array('user'=>$user->getEmail())) as $data)
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
	
	public static function createNewsletterFromData(Common_Storage_Provider_Interface $storage, $data)
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
		
		$user = MailElephantModel_User::fetchOneByEmail($storage, $data['user']);
		
		return new self(
				$data['_id'],
				$data['subject'],
				$storage->createDateTimeFromInternalDateValue($data['created']),
				$data['plainTextBody'], 
				$data['htmlBody'],
				$attachments,
				$user);
	}
	
	public function save(Common_Storage_Provider_Interface $storage, $dataPath)
	{		
		$data = array('subject' => $this->subject,
				'created' => $storage->createInternalDateValueFromDateTime($this->created),
				'plainTextBody' => $this->plainTextBody,
				'htmlBody' => $this->htmlBody,
				'user' => $this->user->getEmail());
		
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
		
		$storage->update('newsletters', 
				array('_id'=>$this->id), 
				array('attachments'=>$attachments));
	}
	
	public function delete(Common_Storage_Provider_Interface $storage, $dataPath)
	{
		if($this->getNumAttachments() > 0)
		{
			$attachmentsDir = $dataPath."/attachments/".$this->id;
			
			foreach($this->attachments as $attachment)
			{
				$path = $attachmentsDir."/".$attachment->getName();
				if(!@unlink($path))
				{
					throw new Exception("failed to delete attachment ".$attachment->getName());
				}
			}
			
			if(!@rmdir($attachmentsDir))
			{
				throw new Exception("failed to delete attachments directory");
			}
		}
		
		$storage->delete('newsletters', array('_id'=>$this->id));
	}
}