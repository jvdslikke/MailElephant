<?php

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__)));

// Define application environment
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
		realpath(APPLICATION_PATH . '/../library'),
		get_include_path(),
)));

//config
require_once 'Zend/Config/Ini.php';
require_once 'MailElephantCommon/Config.php';
$config = new MailElephantCommon_Config(APPLICATION_PATH . "/../config.ini", APPLICATION_ENV);

// autoload
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace($config->autoloadernamespaces->toArray());

// swift
require_once 'Swift/swift_required.php';

// time
date_default_timezone_set($config->timezone);

// connect to storage
$storage = Common_Storage_Provider_Factory::factor($config->resources->storage->toArray());

if(APPLICATION_ENV == "development")
{
	ini_set("soap.wsdl_cache_enabled", "0");
}

require 'SoapFunctionality.php';

if(isset($_GET['wsdl']))
{
	require 'WSDLDocument.php';
	
	// put wsdl
    header('Content-Type: text/xml');
	$wsdl = new WSDLDocument('SoapFunctionality');
	echo $wsdl->saveXML();
}
else
{
	$wsdlUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?wsdl";
	$soapServer = new SoapServer($wsdlUrl);
	$soapServer->setClass('SoapFunctionality', $storage);
	
	// HTTP Basic authentication
	if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']))
	{
    	$soapServer->fault("Client", "No authentication info provided");
	}
	else
	{
		$user = MailElephantModel_User::fetchOneByEmail($storage, $_SERVER['PHP_AUTH_USER']);
		if(!$user || !Common_Bcrypt::check($_SERVER['PHP_AUTH_PW'], $user->getPasswordHash()))
		{
			$soapServer->fault("Client", "Authentication failed");
		}
	}
	
	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		try
		{
			$soapServer->handle();
		}
		catch(Common_Exception_BadRequest $e)
		{
			$soapServer->fault("Client", $e->getMessage());
		}
		catch(Exception $e)
		{
			$soapServer->fault("Server", $e->getMessage());
		}
	}
	else
	{
		echo "This is a SOAP web service";
	}
}