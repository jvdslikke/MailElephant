<?php 

class MailElephantWeb_User implements Zend_Acl_Role_Interface
{
	private $user;
	
	public function __construct(MailElephantModel_User $user)
	{
		$this->user = $user;
	}
	
	public function getRoleId()
	{
		if($this->user->hasRole('admin'))
		{
			return 'admin';
		}
		
		return 'user';
	}
}