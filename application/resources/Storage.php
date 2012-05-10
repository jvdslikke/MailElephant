<?php

class Application_Resource_Storage extends Zend_Application_Resource_ResourceAbstract
{
	const DEFAULT_SERVER = "mongodb://localhost:27017";
	
	private $_provider;
    
    public function init()
    {
    	if($this->_provider == null)
    	{
	    	$this->_provider = Common_Storage_Provider_Factory::factor($this->getOptions());
    	}
    	
    	return $this->_provider;
    }	
}