<?php

class Common_Storage_Provider_Mongo implements Common_Storage_Provider_Interface
{
	const DEFAULT_SERVER = "mongodb://localhost:27017";
	
	private $_mongo;
	private $_db;
	
	public function __construct(array $options = array())
	{
		if(!class_exists('Mongo'))
		{
			throw new Exception("Mongo driver not installed");
		}
		
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
		$data = $this->handleData($data);
		
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
		$data = $this->handleData($data);
		
		$result = $this->_db->{$scheme}->update(
				$this->handleIdentifyingData($identifyingData),
				array('$set' => $data),
				array('upsert'=>true, 'multiple'=>true, 'safe'=>true));
		
		return $result['n'];
	}
	
	public function update($scheme, $identifyingData, $data)
	{		
		$data = $this->handleData($data);
		
		$result = $this->_db->{$scheme}->update(
				$this->handleIdentifyingData($identifyingData),
				array('$set'=>$data),
				array('multiple'=>true, 'safe'=>true));
		
		return $result['n'];
	}
	
	private function handleIdentifyingData($identifyingData)
	{
		if(isset($identifyingData['_id']))
		{
			$identifyingData['_id'] = new MongoId($identifyingData['_id']);
		}
		
		return $identifyingData;
	}
	
	private function handleData($data)
	{
		$result = array();
		
		foreach($data as $var=>$value)
		{
			if(is_array($value))
			{
				$value = $this->handleData($value);
			}
			elseif($value instanceof DateTime)
			{	
				$secs = $value->format('U');
				
				// add timezone to date
				if($value->getOffset())
				{
					$secs += $value->getOffset();
				}
				
				$value = new MongoDate($secs);
			}
			
			$result[$var] = $value;
		}
		
		return $result;
	}
	
	public function fetchOneBy($scheme, $identifyingData)
	{
		$identifyingData = $this->handleIdentifyingData($identifyingData);
		return $this->createArrayFromResultDoc(
				$this->_db->{$scheme}->findOne($identifyingData));
	}
	
	public function fetchMoreBy($scheme, $data)
	{
		$data = $this->handleIdentifyingData($data);
		return $this->createArrayFromResultCursor($this->_db->{$scheme}->find($data));		
	}
	
	public function fetchAll($scheme)
	{
		return $this->createArrayFromResultCursor($this->_db->{$scheme}->find());
	}
	
	private function createArrayFromResultCursor(MongoCursor $cursor)
	{
		$result = array();
		
		foreach($cursor as $doc)
		{
			$result[] = $this->createArrayFromResultDoc($doc);
		}
		
		return $result;
	}
	
	private function createArrayFromResultDoc($doc)
	{
		$result = array();
		
		foreach($doc as $var=>$value)
		{
			if(is_array($value))
			{
				$value = $this->createArrayFromResultDoc($value);
			}
			elseif($value instanceof MongoId)
			{
				$value = $value->{'$id'};
			}
			elseif($value instanceof MongoDate)
			{
				$value = DateTime::createFromFormat('U', $value->sec);
			}
			
			$result[$var] = $value;
		}
		
		return $result;
	}
	
	public function exists($scheme, $identifyingData)
	{
		$identifyingData = $this->handleIdentifyingData($identifyingData);
		return $this->fetchOneBy($scheme, $identifyingData) !== null;
	}
	
	public function delete($scheme, $identifyingData)
	{
		$identifyingData = $this->handleIdentifyingData($identifyingData);
		
		$result = $this->_db->{$scheme}->remove($identifyingData,
				array('safe'=>true));
		
		return $result['n'];
	}
}