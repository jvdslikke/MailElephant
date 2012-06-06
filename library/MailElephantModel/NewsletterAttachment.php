<?php

class MailElephantModel_NewsletterAttachment extends Common_File
{
	private $mimeType;
	private $name;
	private $cid;
	
	public function __construct($mimeType, $name, $cid, $path)
	{
		parent::__construct($path);
		
		$this->mimeType = $mimeType;
		$this->name = $name;
		$this->cid = $cid;
	}
	
	public function getMimeType()
	{
		return $this->mimeType;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getCid()
	{
		return $this->cid;
	}
}