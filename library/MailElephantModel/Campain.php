<?php

class MailElephantModel_Campain
{
	private $id;
	private $user;
	private $newsletter;
	private $created;
	private $sendingItems = array();
	private $paused;
	private $listId;
	
	public function __construct($id,
			MailElephantModel_User $user,
			MailElephantModel_StaticNewsletterAbstract $newsletter,
			DateTime $created,
			array $sendingItems,
			$paused,
			$listId)
	{
		$this->id = $id;
		$this->user = $user;
		$this->newsletter = $newsletter;
		$this->created = $created;		
		$this->setSendingItems($sendingItems);
		$this->paused = $paused;
		$this->listId = $listId;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getUser()
	{
		return $this->user;
	}
	
	public function getCreationDate()
	{
		return $this->created;
	}
	
	public function getNewsletter()
	{
		return $this->newsletter;
	}
	
	public function getNewsletterSubject()
	{
		return $this->newsletter->getSubject();
	}
	
	public function getQueuedSendingItems()
	{
		$result = array();
		
		foreach($this->sendingItems as $sendingItem)
		{
			if($sendingItem->isQueued())
			{
				$result[] = $sendingItem;
			}
		}
		
		return $result;
	}
	
	public function getErrorSendingItems()
	{
		$result = array();
		
		foreach($this->sendingItems as $sendingItem)
		{
			if($sendingItem->isError())
			{
				$result[] = $sendingItem;
			}
		}
		
		return $result;
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
	
	public function getNumErrorSendingItems()
	{
		$result = 0;
		
		foreach($this->sendingItems as $sendingItem)
		{
			if($sendingItem->isError())
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
	
	public function isPaused()
	{
		return $this->paused;
	}
	
	public function isCompleted()
	{
		return ($this->getNumErrorSendingItems() + $this->getNumSentSendingItems())
			>= $this->getNumSendingItems();
	}
	
	public function isStarted()
	{
		return ($this->getNumErrorSendingItems() + $this->getNumSentSendingItems()) > 0;
	}
	
	public function getListId()
	{
		return $this->listId;
	}
	
	public function setPaused($paused)
	{
		$this->paused = $paused;
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
			$result[] = self::_createCampainFromStorageResultRow($storage, $doc);
		}
		
		return $result;
	}
	
	public static function fetchOneById(Common_Storage_Provider_Interface $storage, $id)
	{
		$row = $storage->fetchOneBy('campains', array('_id' => $id));
		
		if($row === null)
		{
			return null;
		}
		
		return self::_createCampainFromStorageResultRow($storage, $row);
	}
	
	public static function fetchOpenCampains(Common_Storage_Provider_Interface $storage)
	{
		$campains = array();
		foreach($storage->fetchMoreBy('campains', array('paused'=>false)) as $row)
		{
			$campain = self::_createCampainFromStorageResultRow($storage, $row);
			
			if(!$campain->isCompleted())
			{
				$campains[] = $campain;
			}
		}
		
		return $campains;
	}
	
	private static function _createCampainFromStorageResultRow(Common_Storage_Provider_Interface $storage, $doc)
	{
		$user = MailElephantModel_User::fetchOneByEmail($storage, $doc['user']);
		
		$newsletter = unserialize(utf8_decode($doc['newsletter']));
		
		$sendingItems = array();
		foreach($doc['sendingItems'] as $sendingItemDoc)
		{
			$sendingItems[] = new MailElephantModel_CampainSendingItem(
					$sendingItemDoc['recipientEmail'], 
					$sendingItemDoc['recipientName'], 
					$sendingItemDoc['sendingStatus'],
					$sendingItemDoc['sendingErrorMessage']);
		}
		
		return new self($doc['_id'], $user, $newsletter, 
				$doc['created'], $sendingItems,
				$doc['paused'], $doc['listId']);		
	}
	
	public function save(Common_Storage_Provider_Interface $storage)
	{
		$data = array(
				'user' => $this->user->getEmail(),
				'newsletter' => utf8_encode(serialize($this->newsletter)),
				'created' => $this->created,
				'sendingItems' => array(),
				'paused' => $this->paused,
				'listId' => $this->listId);
		
		foreach($this->sendingItems as $sendingItem)
		{
			/* @var $sendingItem MailElephantModel_CampainSendingItem */
			$data['sendingItems'][] = array(
					'recipientEmail' => $sendingItem->getRecipientEmail(),
					'recipientName' => $sendingItem->getRecipientName(),
					'sendingStatus' => $sendingItem->getSendingStatus(),
					'sendingErrorMessage' => $sendingItem->getSendingErrorMessage());
		}
		
		if($this->id === null)
		{
			$this->id = $storage->insert('campains', $data, '_id');
		}
		else
		{
			$storage->update('campains', array('_id'=>$this->id), $data);
		}
	}
}