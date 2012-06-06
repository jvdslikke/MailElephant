<?php

class Common_Mailbox_Message_Attachment
{
	private $mimeType;
	private $name;
	private $cid;
	private $data;
	
	public function __construct($mimeType, $name, $cid, $data)
	{
		$this->mimeType = $mimeType;
		$this->name = $name;
		$this->cid = $cid;
		$this->data = $data;
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
	
	public function getData()
	{
		return $this->data;
	}
	
	public function setData($data)
	{
		$this->data = $data;
	}
}