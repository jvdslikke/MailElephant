<?php

define('APPLICATION_PATH', dirname(__FILE__));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
		realpath(APPLICATION_PATH . '/../library'),
		get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance();
$config = new Zend_Config_Ini(APPLICATION_PATH . "/../config.ini", 'production'); //TODO development modus
$autoloader->registerNamespace($config->autoloadernamespaces->toArray());