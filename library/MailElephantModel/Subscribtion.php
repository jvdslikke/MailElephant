<?php

class MailElephantModel_Subscribtion
{
	private $email;
	private $name;
	private $group;
	
	public function __construct($email, $name = null, MailElephantModel_SubscribtionsGroup $group)
	{
		$this->email = $email;
		$this->name = $name;
		$this->group = $group;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	public function getGroup()
	{
		return $this->group;
	}
	
	public static function exists(Common_Storage_Provider_Interface $storage, $email)
	{
		return $storage->exists('subscribtions', array('_id'=>$email));
	}
	
	public static function fetchAll(Common_Storage_Provider_Interface $storage)
	{
		$result = array();
		foreach($storage->fetchAll('subscribtions') as $row)
		{
			$group = new MailElephantModel_SubscribtionsGroup($row['groupTitle']);
			$result[] = new self($row['_id'], $row['name'], $group);
		}
		
		return $result;
	}
	
	public function save(Common_Storage_Provider_Interface $storage)
	{
		$storage->upsert('subscribtions', 
				array('_id'=>$this->email), 
				array('name'=>$this->name, 
						'groupTitle'=>$this->group->getTitle()));
	}
}