<?php 

interface MailElephantModel_IStaticNewsletter extends Serializable
{	
	public function setRecipient($emailAddress, $name);
	
	public function getSubject();
	
	/**
	 * @return MailElephantModel_IStaticNewsletter;
	 */
	public static function createFromNewsletter(MailElephantModel_Newsletter $newsletter);
}