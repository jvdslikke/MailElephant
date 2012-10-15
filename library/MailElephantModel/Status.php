<?php 

class MailElephantModel_Status
{
	public static function isCampainSendingScriptRunning(Common_Storage_Provider_Interface $storage)
	{
		$row = $storage->fetchOneBy('status', array('key'=>"campain_sending_script_running"));
		
		return $row !== null && $row['value'];
	}
	
	public static function setCampainSendingScriptRunning(Common_Storage_Provider_Interface $storage, $running)
	{
		$storage->upsert('status', 
				array('key'=>"campain_sending_script_running"), 
				array('value'=> (boolean)$running));
	}
}