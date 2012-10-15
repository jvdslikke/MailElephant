<?php 

interface MailElephantModel_IStaticNewsletter extends Serializable
{	
	public function getSubject();
	
	/**
	 * @return MailElephantModel_IStaticNewsletter
	 */
	public static function createFromNewsletter(MailElephantModel_Newsletter $newsletter);
	
	public function setRecipient($email, $name=null);
	
	public function setFrom(MailElephantModel_MailSenderDetails $senderSettings);
}