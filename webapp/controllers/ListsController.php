<?php

class ListsController extends MailElephantWeb_Controller_Action_Abstract
{
	public function indexAction()
	{
		$lists = MailElephantModel_List::fetchMoreByUser(
				$this->getStorageProvider(), 
				$this->getLoggedInUser());
		
		$this->view->lists = $lists;
	}
	
	public function createAction()
	{
		$form = new MailElephantWeb_Form_List();
		$this->view->form = $form;
		
		if($this->getRequest()->isPost())
		{
			$formData = $this->getRequest()->getPost();
			
			if($form->isValid($formData))
			{
				$list = new MailElephantModel_List(null, 
					$form->getTitleInputValue(), $this->getLoggedInUser());
				
				$list->save($this->getStorageProvider());
				
				$this->addFlashMessage("List created");
				
				$this->_getRedirector()->gotoSimpleAndExit('index');
			}
		}
	}
	
	public function subscribtionsAction()
	{
		$list = $this->_getListFromRequest();
		
		$this->view->subscribtions = $list->getSubscribtions();
	}
	
	private function _getListFromRequest()
	{
		if(($listId = $this->getRequest()->getParam('list', null)) == null)
		{
			throw new Common_Exception_BadRequest("no list id provided");
		}
		
		$list = MailElephantModel_List::fetchOneById(
				$this->getStorageProvider(),
				$listId);
		
		if($list == null)
		{
			throw new Common_Exception_NotFound("list not found");
		}
		
		return $list;
	}
	
	public function importSubscribtionsAction()
	{
		$list = $this->_getListFromRequest();
		
		$uploadForm = new MailElephantWeb_Form_SubscribtionsUpload();
		$this->view->form = $uploadForm;
		
		if($this->getRequest()->isPost())
		{
			$formData = $this->getRequest()->getPost();
				
			if($uploadForm->isValid($formData))
			{
				$uploadRelDir = "tmp";
				$upload = new Zend_File_Transfer_Adapter_Http();
				$upload->setDestination(
						$this->getDataPath().DIRECTORY_SEPARATOR.$uploadRelDir,
						'subscribtionsfile');
				$upload->receive('subscribtionsfile');
				
				// detect file type and forward
				$file = new Common_File($upload->getFileName('subscribtionsfile'));
				
				if($file->getExtension() == "csv")
				{
					$relFilePath = $uploadRelDir.DIRECTORY_SEPARATOR.$file->getBasename();
					
					$this->_getRedirector()->gotoSimpleAndExit('import-subscribtions-csv', null, null, 
						array('list' => $list->getId(),
								'file' => $relFilePath,
								'encoding' => $uploadForm->getSelectedEncoding()));
				}
				else
				{
					unlink($upload->getFileName('subscribtionsfile'));
					
					$this->addFlashMessage("File type not recognized");
		
					$this->_getRedirector()->gotoSimpleAndExit('subscribtions', null, null, 
						array('list' => $list->getId()));
				}						
				
			}
		}
	}
	
	public function importSubscribtionsCsvAction()
	{
		$list = $this->_getListFromRequest();
		
		// get specified encoding
		$encoding = $this->getRequest()->getParam('encoding', null);
		if(empty($encoding))
		{
			throw new Common_Exception_BadRequest("no encoding specified");
		}
		
		// get file
		$csvFilePath = $this->getRequest()->getParam('file');
		if(empty($csvFilePath))
		{
			throw new Common_Exception_NotFound("file not found");
		}
		$csvFilePath = $this->getDataPath().DIRECTORY_SEPARATOR.$csvFilePath;
		$csvFile = new Common_CsvFileIterator($csvFilePath, $encoding);
		
		// create form	
		$neededFields = array(
				'name' => "Name",
				'email' => "Emailaddress");
		$matchFieldsForm = new MailElephantWeb_Form_CsvHeadersMatching(null,
				$neededFields, $csvFile->getHeaders());
		
		// handle input or show form
		
		$showForm = true;
		if($this->getRequest()->isPost())
		{
			$formData = $this->getRequest()->getPost();
			
			if($matchFieldsForm->isValid($formData))
			{
				$showForm = false;
				
				$nameHeader = $matchFieldsForm->getCsvHeaderByNeededFieldName('name');
				$emailHeader = $matchFieldsForm->getCsvHeaderByNeededFieldName('email');
				
				list($imported, $skipped, $duplicates) = 
					$this->_importSubscribtionsFromCsvFile($list, $csvFile, $emailHeader, $nameHeader, $encoding);
				
				$this->addFlashMessage("Succeeded: ".$imported." subscribtions imported, ".$duplicates." already existed, ".$skipped." skipped");
				
				$this->_getRedirector()->gotoSimpleAndExit('subscribtions', null, null, 
					array('list' => $list->getId()));
			}
		}		
		
		if($showForm)
		{
			$this->view->form = $matchFieldsForm;
		}
	}
	
	private function _importSubscribtionsFromCsvFile(MailElephantModel_List $list,
			Common_CsvFileIterator $csvFile, $emailHeader, $nameHeader)
	{
		$skipped = 0;
		$imported = 0;
		$duplicates = 0;
		
		foreach($csvFile as $csvLine)
		{
			$email = $csvLine[$emailHeader];
			$name = $csvLine[$nameHeader];
			
			// skip lines without email
			if(empty($email))
			{
				$skipped += 1;
				continue;
			}
			
			// name is allowed to be empty
			if(empty($name) || $name == $email)
			{
				$name = null;
			}
			
			// skip already known emails
			$duplSubscribtion = $list->getSubscribtion($email);
			if($duplSubscribtion !== null)
			{								
				$duplicates += 1;
				continue;
			}
			
			// add new subscribtion
			$subscribtion = new MailElephantModel_Subscribtion($email, $name);
			$list->addSubscribtion($subscribtion);
			$imported += 1;
		}
		
		$list->save($this->getStorageProvider());
		
		return array($imported, $skipped, $duplicates);
	}
	
	public function createSubscribtionAction()
	{
		$list = $this->_getListFromRequest();
		$form = new MailElephantWeb_Form_Subscribtion(null, $list);
		
		if($this->getRequest()->isPost())
		{
			$postData = $this->getRequest()->getPost();
			
			if($form->isValid($postData))
			{
				$this->addFlashMessage("Subscribtion added");
				
				$form->saveSubscribtion($this->getStorageProvider());
				
				$this->_getRedirector()->gotoSimpleAndExit('subscribtions', null, null,
						array('list'=>$list->getId()));
			}
		}
		
		$this->view->form = $form;
	}
}