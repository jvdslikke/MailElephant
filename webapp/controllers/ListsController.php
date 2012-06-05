<?php

class ListsController extends MailElephantWeb_Controller_Action_Abstract
{
	public function indexAction()
	{
		$lists = MailElephantModel_List::fetchMoreByUser(
				$this->getStorageProvider(), 
				Zend_Auth::getInstance()->getIdentity()->getEmail());
		
		$this->view->lists = $lists;
	}
}