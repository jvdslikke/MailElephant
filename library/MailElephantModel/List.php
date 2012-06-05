<?php

class MailElephantModel_List
{
	private $id;
	private $name;
	private $title;
	private $subscribtions = array();
	
	public function __construct($id, $name, $title, array $subscribtions = array())
	{
		$this->id = $id;
		$this->name = $name;
		$this->title = $title;
		
		$this->setSubscribtions($subscribtions);
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
		$this->subscribtions[] = $subscribtion;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function getNumSubscribtions()
	{
		return count($this->subscribtions);
	}
	
	public static function fetchMoreByUser(Common_Storage_Provider_Interface $storage, $userEmail)
	{
		$result = array();
		
		foreach($storage->fetchMoreBy('lists', array('user'=>$userEmail)) as $resultDoc)
		{
			$list = new self($resultDoc['_id'], $resultDoc['name'], $resultDoc['title']);
			
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
		$data = array('name' => $this->name,
				'title' => $this->title,
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