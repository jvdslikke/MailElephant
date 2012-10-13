<?php 

class CampainsController extends MailElephantWeb_Controller_Action_Abstract
{
	public function indexAction()
	{
		$this->view->campains = MailElephantModel_Campain::fetchMoreByUser(
				$this->getStorageProvider(), 
				$this->getLoggedInUser());
	}
	
	public function createAction()
	{
		// get parameters
		$newsletter = null;
		$list = null;
		
		if($this->_requestHasNewsletter())
		{
			$newsletter = $this->_getNewsletterFromRequest();
		}
		if($this->_requestHasList())
		{
			$list = $this->_getListFromRequest();
		}
		
		
		// post handling
		if($this->getRequest()->isPost())
		{
			if($newsletter === null || $list === null)
			{
				$this->addFlashMessage("No list or no newsletter chosen");
			}
			else
			{
				$this->addFlashMessage("Campain saved");
				
				$this->_createCampain($list, $newsletter);
			
				$this->_getRedirector()->gotoSimpleAndExit('index');
			}
		}
		
		
		// create form
		$this->view->newsletter = $newsletter;
		$this->view->list = $list;
		
		$this->view->newsletters = MailElephantModel_Newsletter::fetchMoreByUser(
				$this->getStorageProvider(), 
				$this->getLoggedInUser());
		
		$this->view->lists = MailElephantModel_List::fetchMoreByUser(
				$this->getStorageProvider(), 
				$this->getLoggedInUser());
	}
	
	private function _requestHasNewsletter()
	{
		return $this->getRequest()->getParam('newsletter', null) !== null;
	}
	
	private function _requestHasList()
	{
		return $this->getRequest()->getParam('list', null) !== null;
	}
	
	private function _getNewsletterFromRequest()
	{
		if(!$this->_requestHasNewsletter())
		{
			throw new Common_Exception_BadRequest("no newsletter specified");
		}
		
		$newsletter = MailElephantModel_Newsletter::fetchOneById(
				$this->getStorageProvider(), 
				$this->getRequest()->getParam('newsletter'));
		
		if($newsletter === null)
		{
			throw new Common_Exception_NotFound("newsletter not found");
		}
		
		return $newsletter;
	}
	
	private function _getListFromRequest()
	{
		if(!$this->_requestHasList())
		{
			throw new Common_Exception_BadRequest("no list specified");
		}
		
		$list = MailElephantModel_List::fetchOneById(
				$this->getStorageProvider(), 
				$this->getRequest()->getParam('list'));
		
		if($list === null)
		{
			throw new Common_Exception_NotFound("list not found");
		}
		
		return $list;
	}
	
	private function _createCampain(MailElephantModel_List $list, MailElephantModel_Newsletter $newsletter)
	{
		$campain = new MailElephantModel_Campain(
				null, 
				$this->getLoggedInUser(), 
				MailElephantModel_SwiftStaticNewsletter::createFromNewsletter($newsletter), 
				new DateTime(), 
				array());
		
		foreach($list->getSubscribtions() as $subscribtion)
		{
			$campainSendingItem = new MailElephantModel_CampainSendingItem(
					$subscribtion->getEmail(), 
					$subscribtion->getName());
			
			$campain->addSendingItem($campainSendingItem);
		}
		
		$campain->save($this->getStorageProvider());
	}
}