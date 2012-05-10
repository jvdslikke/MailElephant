<?php

class Common_Storage_Provider_Factory	
{	
	public static function factor($options)
	{
		if(!isset($options['provider']))
		{
			throw new InvalidArgumentException("storage provider not specified");
		}
		
		if(!isset($options['options']))
		{
			$options['options'] = array();
		}
		
		$className = "Common_Storage_Provider_".ucfirst(strtolower($options['provider']));
		if(!class_exists($className))
		{
			throw new InvalidArgumentException("storage provider not found");
		}
		
		return new $className($options['options']);
	}
}