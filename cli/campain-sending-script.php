<?php 

include 'cli.inc.php';

/**
 * Exit status codes
 * 0 OK
 * 1 Error
 * 2 Script already running
 */

//
// SETUP
//

// shutdown function
function shutdown_function($storage)
{
	MailElephantModel_Status::setCampainSendingScriptRunning($storage, false);
}
register_shutdown_function('shutdown_function', $storage);

// set exception handler
function exception_handler($exception)
{
	echo "ERROR: ".$exception."\n";
	exit(1);
}
set_exception_handler('exception_handler');

// error handler
function error_handler($errno, $errstr, $errfile, $errline)
{
	throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("error_handler");

/**
 * zip several arrays into one
 */
function array_zip()
{
    $args = func_get_args();
    
    $result = array();
    
    $numArgs = count($args);
    $totalItems = 0;
    for($i=0; $i<$numArgs; $i++)
    {
        if(!is_array($args[$i]))
        {
        	throw new Exception("not an array");
        }
        
        $totalItems += count($args[$i]);
    }
    
    $srcIndexes = array_keys($args);
	for($i=0; $i<$totalItems; $i++)
	{
		$srcIndex = current($srcIndexes);
		if($srcIndex === false)
		{
			$srcIndex = reset($srcIndexes);
		}
		
		$result[] = array_shift($args[$srcIndex]);
		
		// look ahead
		if(reset($args[$srcIndex]) === false)
		{
			unset($srcIndexes[key($srcIndexes)]);
		}		
		
		next($srcIndexes);
	}
    
    return $result;
}


//
// SCRIPT STARTS HERE
//

// check if running
if(MailElephantModel_Status::isCampainSendingScriptRunning($storage))
{
	echo "script already running\n";
	exit(2);
}

// set running
MailElephantModel_Status::setCampainSendingScriptRunning($storage, true);

// get open campains
$campains = MailElephantModel_Campain::fetchOpenCampains($storage);

// create a flat list of items with their campains
$sendingItemsPerCampain = array();
foreach($campains as $campain)
{
	$sendingItemsPerCampain[$campain->getId()] = array();
	
	foreach($campain->getQueuedSendingItems() as $sendingItem)
	{
		$sendingItemsPerCampain[$campain->getId()][] = array(
				'campain' => $campain,
				'sendingItem' => $sendingItem);
	}
}
$sendingItemsFlat = call_user_func_array('array_zip', $sendingItemsPerCampain);

echo "found ".count($sendingItemsFlat)." items in ".count($campains)." campains\n";


//
// SEND MESSAGES
//
if(count($sendingItemsFlat) > 0)
{
	$sender = new MailElephantModel_SwiftMailTransport($config->getMailTransportConfig());
	
	$i = 0;
	foreach($sendingItemsFlat as $sendingItemFlat)
	{
		/* @var $campain MailElephantModel_Campain */
		$campain = $sendingItemFlat['campain'];
		/* @var $sendingItem MailElephantModel_CampainSendingItem */
		$sendingItem = $sendingItemFlat['sendingItem'];
		
		try
		{
			$sender->send(
					$campain->getNewsletter(),
					$sendingItem->getRecipientEmail(),
					$sendingItem->getRecipientName(),
					$campain->getUser()->getEmailFromSettings());
			
			$sendingItem->setSent();
		}
		catch(Exception $e)
		{
			$sendingItem->setError($e->getMessage());
		}
		
		//TODO do not update the whole campain, only the item
		$campain->save($storage);
		
		$i += 1;
	}

	echo "processed ".$i." items\n";
}

// normal exit
exit(0);