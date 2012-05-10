<?php

class SubscribtionsController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$this->view->subscribtions = MailElephantModel_Subscribtion::fetchAll(
				$this->getInvokeArg('bootstrap')->getResource('storage'));
	}
}