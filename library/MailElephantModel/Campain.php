<?php

class MailElephantModel_Campain
{
	private $id;
	private $user;
	private $newsletter;
	private $created;
	private $sendingItems = array();
	
	public function __construct($id,
			MailElephantModel_User $user,
			MailElephantModel_IStaticNewsletter $newsletter,
			DateTime $created,
			array $sendingItems)
	{
		$this->user = $user;
		$this->newsletter = $newsletter;
		$this->created = $created;
		
		$this->setSendingItems($sendingItems);
	}
	
	public function getCreationDate()
	{
		return $this->created;
	}
	
	public function getNewsletterSubject()
	{
		return $this->newsletter->getSubject();
	}
	
	public function getNumSentSendingItems()
	{
		$result = 0;
		
		foreach($this->sendingItems as $sendingItem)
		{
			if($sendingItem->isSent())
			{
				$result += 1;
			}
		}
		
		return $result;
	}
	
	public function getNumSendingItems()
	{
		return count($this->sendingItems);
	}
	
	public function setSendingItems(array $sendingItems)
	{
		$this->sendingItems = array();
		
		foreach($sendingItems as $sendingItem)
		{
			$this->addSendingItem($sendingItem);
		}
	}
	
	public function addSendingItem(MailElephantModel_CampainSendingItem $sendingItem)
	{
		$this->sendingItems[] = $sendingItem;
	}
	
	public static function fetchMoreByUser(Common_Storage_Provider_Interface $storage, 
			MailElephantModel_User $user)
	{
		$result = array();
		
		foreach($storage->fetchMoreBy('campains', array('user'=>$user->getEmail())) as $doc)
		{
			$user = MailElephantModel_User::fetchOneByEmail($storage, $doc['user']);
			
			$newsletter = unserialize($doc['newsletter']);
			
			$sendingItems = array();
			foreach($doc['sendingItems'] as $sendingItemDoc)
			{
				$sendingItems[] = new MailElephantModel_CampainSendingItem(
						$sendingItemDoc['recipientEmail'], 
						$sendingItemDoc['recipientName'], 
						$sendingItemDoc['sendingStatus']);
			}
			
			$result[] = new self($doc['_id'], $user, $newsletter, 
					$storage->createDateTimeFromInternalDateValue($doc['created']), $sendingItems);
		}
		
		return $result;
	}
	
	public function save(Common_Storage_Provider_Interface $storage)
	{
		$data = array(
				'user' => $this->user->getEmail(),
				'newsletter' => serialize($this->newsletter),
				'created' => $storage->createInternalDateValueFromDateTime($this->created),
				'sendingItems' => array());
		
		foreach($this->sendingItems as $sendingItem)
		{
			/* @var $sendingItem MailElephantModel_CampainSendingItem */
			$data['sendingItems'][] = array(
					'recipientEmail' => $sendingItem->getRecipientEmail(),
					'recipientName' => $sendingItem->getRecipientName(),
					'sendingStatus' => $sendingItem->getSendingStatus());
		}
		
		if($id === null)
		{
			$this->id = $storage->insert('campains', $data, '_id');
		}
		else
		{
			$storage->update('campains', array('_id'=>$this->id), $data);
		}
	}
}