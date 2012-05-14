<?php

class SubscribtionsController extends MailElephantWeb_Controller_Action_Abstract
{
	public function indexAction()
	{
		$this->view->subscribtions = MailElephantModel_Subscribtion::fetchAll(
				$this->getStorageProvider());
	}
}