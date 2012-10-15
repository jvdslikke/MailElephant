<?php 

class MailElephantCommon_Config extends Zend_Config_Ini
{
	public function __construct($filename, $section)
	{
		parent::__construct($filename, $section);
	}
	
	public function getMailTransportConfig()
	{
		if(empty($this->resources->mailtransportconfig))
		{
			throw new Exception("no mail sender configuration");
		}
		
		$options = $this->resources->mailtransportconfig->toArray();		
		
		return Common_MailTransportConfig::createFromOptions($options);
	}
}