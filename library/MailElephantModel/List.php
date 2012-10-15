<?php

class MailElephantModel_List
{
	private $id;
	private $title;
	private $user;
	private $subscribtions = array();
	
	public function __construct($id, $title, MailElephantModel_User $user, 
			array $subscribtions = array())
	{
		$this->id = $id;
		$this->title = $title;
		$this->user = $user;
		
		$this->setSubscribtions($subscribtions);
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setSubscribtions(array $subscribtions)
	{
		$this->subscribtions = array();
		foreach($subscribtions as $subscribtion)
		{
			$this->addSubscribtion($subscribtion);
		}
	}
	
	public function addSubscribtion(MailElephantModel_Subscribtion $subscribtion)
	{
		if($this->hasSubscribtion($subscribtion->getEmail()))
		{
			throw new Exception("A subscribtion with that emailaddress already exists");
		}
		
		$this->subscribtions[] = $subscribtion;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function getNumSubscribtions()
	{
		return count($this->subscribtions);
	}
	
	public function getSubscribtions()
	{
		return $this->subscribtions;
	}
	
	public function hasSubscribtion($email)
	{
		return $this->getSubscribtion($email) !== null;
	}
	
	public function getSubscribtion($email)
	{
		foreach($this->subscribtions as $subscribtion)
		{
			if($subscribtion->getEmail() == $email)
			{
				return $subscribtion;
			}
		}
		
		return null;		
	}
	
	public static function fetchOneById(Common_Storage_Provider_Interface $storage, $id)
	{
		$data = $storage->fetchOneBy('lists', array('_id'=>$id));
		
		if($data == null)
		{
			return null;
		}
		
		$user = MailElephantModel_User::fetchOneByEmail($storage, $data['user']);
		
		$subscribtions = array();
		foreach($data['subscribtions'] as $subscribtionData)
		{
			$subscribtions[] = new MailElephantModel_Subscribtion(
					$subscribtionData['email'],
					$subscribtionData['name']);
		}
		
		return new self($data['_id'], $data['title'], $user, $subscribtions);
	}
	
	public static function fetchMoreByUser(Common_Storage_Provider_Interface $storage, 
			MailElephantModel_User $user)
	{
		$result = array();
		
		foreach($storage->fetchMoreBy('lists', array('user'=>$user->getEmail())) as $resultDoc)
		{
			$list = new self($resultDoc['_id'], $resultDoc['title'], $user);
			
			foreach($resultDoc['subscribtions'] as $subscribtion)
			{
				$list->addSubscribtion(new MailElephantModel_Subscribtion(
						$subscribtion['email'], 
						$subscribtion['name']));
			}
			
			$result[] = $list;
		}
		
		return $result;
	}
	
	public function save(Common_Storage_Provider_Interface $storage)
	{
		$data = array(
				'title' => $this->title,
				'user' => $this->user->getEmail(),
				'subscribtions' => array());
		
		foreach($this->subscribtions as $subscribtion)
		{
			$data['subscribtions'][] = array(
					'email' => $subscribtion->getEmail(),
					'name' => $subscribtion->getName());
		}
		
		if($this->id === null)
		{
			$this->id = $storage->insert('lists', $data, '_id');
		}
		else 
		{
			$storage->update('lists', array('_id'=>$this->id), $data);
		}
	}
}