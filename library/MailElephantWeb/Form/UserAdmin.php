<?php 

class MailElephantWeb_Form_UserAdmin extends MailElephantWeb_Form_User
{
	private $usernameElem;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->usernameElem = new Zend_Form_Element_Text('username');
		$this->usernameElem->setLabel("Email");
		$this->usernameElem->setRequired(true);
		$this->usernameElem->setOrder(0);
		$this->addElement($this->usernameElem);
	}
	
	/**
	 * @return MailElephantModel_User
	 */
	public function getNewUser()
	{
		return new MailElephantModel_User(
				$this->usernameElem->getValue(), 
				Common_Bcrypt::hash($this->getNewPassword()), 
				array(), 
				$this->getMailSenderDetails(), 
				$this->getUnsubscribeHtml(), 
				$this->getUnsubscribeText(), 
				array());
	}
}