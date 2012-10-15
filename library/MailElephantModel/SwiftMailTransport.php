<?php 

class MailElephantModel_SwiftMailTransport extends MailElephantModel_MailTransportAbstract
{
	private $swiftMailer;
	
	public function __construct(Common_MailTransportConfig $config)
	{
		parent::__construct($config);
		
		$transport = Swift_SmtpTransport::newInstance($config->getHost());
		
		if($config->hasPort())
		{
			$transport->setPort($config->getPort());
		}
		
		if($config->hasCredentials())
		{
			$transport->setUsername($config->getUsername());
			$transport->setPassword($config->getPassword());
		}
		
		$this->swiftMailer = Swift_Mailer::newInstance($transport);
	}
	
	public function send(MailElephantModel_IStaticNewsletter $newsletter, $toEmail=null, $toName=null,
			MailElephantModel_MailSenderDetails $from=null)
	{
		if(!is_a($newsletter, 'MailElephantModel_SwiftStaticNewsletter'))
		{
			throw new Exception("this sender only support swift newsletters");
		}
		/* @var $newsletter MailElephantModel_SwiftStaticNewsletter */
		
		if($toEmail !== null)
		{
			$newsletter->setRecipient($toEmail, $toName);
		}
		
		if($from !== null)
		{
			$newsletter->setFrom($from);
		}
		
		$this->swiftMailer->send($newsletter->getSwiftMessage(), $failedRecipients);
		
		if(count($failedRecipients) > 0)
		{
			throw new Exception("delivering to address(es) ".implode(", ", $failedRecipients)." failed");
		}
	}
}