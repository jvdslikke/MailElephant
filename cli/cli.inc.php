<?php

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../webapp'));

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
date_default_timezone_set(date_default_timezone_get());

// connect to storage
$storage = Common_Storage_Provider_Factory::factor($config->resources->storage->toArray());