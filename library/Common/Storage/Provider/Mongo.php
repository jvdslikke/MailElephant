<?php

class Common_Storage_Provider_Mongo implements Common_Storage_Provider_Interface
{
	const DEFAULT_SERVER = "mongodb://localhost:27017";
	
	private $_mongo;
	private $_db;
	
	public function __construct(array $options = array())
	{
		// check options
		if(!isset($options['database']))
		{
			throw new InvalidArgumentException("option 'database' not provided");
		}
		
		$server = self::DEFAULT_SERVER;
		if(isset($options['server']))
		{
			$server = $options['server'];
		}
		
		$this->_mongo = new Mongo($server);
		
		$this->_db = $this->_mongo->{$options['database']};
	}
	
	public function insert($scheme, $data, $identifyingDataSpecifiers = null)
	{		
		$this->_db->{$scheme}->insert($data,
				array('safe'=>true));
		
		if(!empty($identifyingDataSpecifiers))
		{
			if(is_string($identifyingDataSpecifiers)) 
			{
				return $data[$identifyingDataSpecifiers]->{'$id'};
			}
			elseif(is_array($identifyingDataSpecifiers))
			{
				$identifyingData = array();
				foreach($identifyingDataSpecifiers as $identifyingDataSpecifier)
				{
					$identifyingData[$identifyingDataSpecifier] = $data[$identifyingDataSpecifier]->{'$id'};
				}
				return $identifyingData;
			}	
			else
			{
				throw new InvalidArgumentException("invalid identifying data argument type");
			}	
		}
	}
	
	public function upsert($scheme, $identifyingData, $data)
	{		
		$this->_db->{$scheme}->update(
				$identifyingData,
				array('$set' => $data),
				array('upsert'=>true, 'multiple'=>true, 'safe'=>true));
	}
	
	public function fetchOneBy($scheme, $identifyingData)
	{
		return $this->_db->{$scheme}->findOne($identifyingData);
	}
	
	public function fetchMoreBy($scheme, $data)
	{
		return iterator_to_array($this->_db->{$scheme}->find($data));		
	}
	
	public function fetchAll($scheme)
	{
		return iterator_to_array($this->_db->{$scheme}->find());
	}
	
	public function exists($scheme, $identifyingData)
	{
		return $this->fetchOneBy($scheme, $identifyingData) !== null;
	}
	
	public function createDateTimeFromInternalDateValue($dateValue)
	{
		return DateTime::createFromFormat('U', $dateValue->sec);
	}
	

	public function createInternalDateValueFromDateTime(DateTime $dateTime)
	{
		return new MongoDate($dateTime->getTimestamp());
	}
}