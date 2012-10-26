<?php 

abstract class MailElephantModel_StaticNewsletterAbstract implements Serializable
{
	/**
	 * Sets the newsletter subject
	 */
	abstract public function getSubject();
	
	/**
	 * Sets the newsletter recipients
	 */
	abstract public function setRecipient($email, $name=null);
	
	/**
	 * Sets the newsletter sender
	 */
	abstract public function setFrom(MailElephantModel_MailSenderDetails $senderSettings);
	
	/**
	 * Adds html for the recipient to unsubscribe from the list
	 */
	abstract protected function addUnsubscribeHtml($unsubscribeHtml);
	
	/**
	 * Adds text for the recipient to unsubscribe from the list
	 */
	abstract protected function addUnsubscribeText($unsubscribeText);
	
	public function addUnsubscribeInfo($unsubscribeHtml, $unsubscribeText, 
			MailElephantModel_List $list, $recipientEmail)
	{
		$replacements = array(
				"{listid-url}" => urlencode($list->getId()),
				"{email-url}" => urlencode($recipientEmail));
		
		$unsubscribeHtml = str_replace(array_keys($replacements), $replacements, $unsubscribeHtml);
		$unsubscribeText = str_replace(array_keys($replacements), $replacements, $unsubscribeText);
		
		$this->addUnsubscribeHtml($unsubscribeHtml);
		$this->addUnsubscribeText($unsubscribeText);
	}
}