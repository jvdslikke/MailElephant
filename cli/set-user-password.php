<?php

include 'cli.inc.php';

if (count($argv) < 3)
{
	echo "usage: set-user-password <email> <password>\n";
	exit;
}

if(strlen($argv[2]) < 1)
{
	echo "empty password provided\n";
	exit;
}

$user = MailElephantModel_User::fetchOneByEmail($storage, $argv[1]);

if ($user == null)
{
	echo "no user found with that email\n";
	exit;
}

$user->setPasswordPlainText($argv[2]);
$user->save($storage);

echo "password succesfully set\n";