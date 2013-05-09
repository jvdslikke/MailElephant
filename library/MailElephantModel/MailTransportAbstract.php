<?php 

abstract class MailElephantModel_MailTransportAbstract
{
	private $config;
	
	public function __construct(Common_MailTransportConfig $config)
	{
		$this->config = $config;
	}
	
	protected function getConfig()
	{
		return $this->config;
	}
	
	public abstract function send(MailElephantModel_StaticNewsletterAbstract $newsletter, 
			$toEmail, $toName,
			MailElephantModel_MailSenderDetails $from, 
			$returnPath);
}