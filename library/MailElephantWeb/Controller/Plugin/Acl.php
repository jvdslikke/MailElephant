<?php 

class MailElephantWeb_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
	const GUEST_ROLE_ID = 'guest';
	const AUTHENTICATED_ROLE_ID = 'authenticated';
	
	private static $acl;
	
	private static function createAcl()
	{
		$acl = new Zend_Acl();
		
		// resources
		$acl->addResource('/newsletters');
		$acl->addResource('/auth');
		$acl->addResource('/error');
		$acl->addResource('/index');
		$acl->addResource('/lists');
		$acl->addResource('/campains');
		$acl->addResource('/user');
		
		// roles
		$acl->addRole(self::GUEST_ROLE_ID);
		$acl->addRole(self::AUTHENTICATED_ROLE_ID);
		
		// rules
		$acl->allow(null, '/error');
		$acl->allow(null, '/auth');
		$acl->allow(self::AUTHENTICATED_ROLE_ID, '/newsletters');
		$acl->allow(self::AUTHENTICATED_ROLE_ID, '/lists');
		$acl->allow(null, '/index');
		$acl->allow(self::AUTHENTICATED_ROLE_ID, '/campains');
		$acl->allow(self::AUTHENTICATED_ROLE_ID, '/user');
		
		return $acl;
	}
	
	public function __construct()
	{
		if(self::$acl === null)
		{
			self::$acl = self::createAcl();
		}
	}
	
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		// get role
		$role = self::GUEST_ROLE_ID;
		if(Zend_Auth::getInstance()->hasIdentity())
		{
			$role = self::AUTHENTICATED_ROLE_ID;
		}
		
		// get most specific resource
		$currentResource = "";
		foreach(self::$acl->getResources() as $resource)
		{
			if(strncmp($resource, $request->getPathInfo(), strlen($resource)) === 0
					&& strlen($resource) > strlen($currentResource))
			{
				$currentResource = $resource;
			}
		}
		
		$authorized = false;
		
		if($currentResource)
		{
			if(!self::$acl->isAllowed($role, $currentResource))
			{
				if($role == self::GUEST_ROLE_ID
						&& self::$acl->isAllowed(self::AUTHENTICATED_ROLE_ID, $currentResource))
				{
					$this->getResponse()->setHttpResponseCode(401);
					$request->setControllerName('auth');
					$request->setActionName('login');
					
					/* @var $flashMessenger Zend_Controller_Action_Helper_FlashMessenger */
					$flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
					$flashMessenger->addMessage("Please login to access this page");
					
					
					$authorized = true;
				}
			}
			else
			{
				$authorized = true;
			}		
		}
		
		if(!$authorized)
		{
			$request->setControllerName('error');
			$request->setActionName('forbidden');
		}
	}
}