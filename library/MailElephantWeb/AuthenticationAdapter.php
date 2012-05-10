<?php

class MailElephantWeb_AuthenticationAdapter implements Zend_Auth_Adapter_Interface
{
	private $_storage;
	private $_username;
	private $_password;
	
	public function __construct(Common_Storage_Provider_Interface $storage, 
			$username, $password)
	{
		$this->_storage = $storage;
		$this->_username = $username;
		$this->_password = $password;
	}
	
	public function authenticate()
	{
		$identity = $this->_storage->findOneBy('users', array('_id'=>$this->_username));
		if(!$identity)
		{
			return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, null);
		}
		
		if(!Common_Bcrypt::check($this->_password, $identity->getPasswordHash()))
		{
			return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $identity);
		}
		
		return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity);
	}
}