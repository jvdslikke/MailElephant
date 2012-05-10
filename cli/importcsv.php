<?php 

include 'cli.inc.php';

$storage = Newsletter_Storage_Provider_Factory::factor($config->resources->storage->toArray());

if(!isset($argv[1]))
{
	throw new Exception("no file given");
}

$csvFile = new Common_CsvFileIterator($argv[1]);

$updatedAmount = 0;
$newAmount = 0;

$group = new Newsletter_Model_SubscribtionsGroup($csvFile->getFilename());

foreach($csvFile as $line=>$values)
{
	$email = $values["EmailAddress"];
	$name = null;
	if(!empty($values["Name"]))
	{
		$name = $values["Name"];
	}
	
	if(Newsletter_Model_Subscribtion::exists($storage, $email))
	{
		$updatedAmount += 1;
	}
	else
	{
		$newAmount += 1;
	}
	
	$subscribtion = new Newsletter_Model_Subscribtion($email, $name, $group);
	$subscribtion->save($storage);
}

echo "added ".$newAmount." new subscribtions, updated ".$updatedAmount."\n";

exit(0);
