<?php

class MailElephantModel_NewsletterAttachment extends Common_File
{
	private $mimeType;
	private $name;
	private $cid;
	
	public function __construct($mimeType, $name, $cid = null, $path = null)
	{
		parent::__construct($path);
		
		$this->mimeType = $mimeType;
		$this->name = $name;
		$this->cid = $cid;
	}
	
	public function getName()
	{
		return $this->name;
	}
}