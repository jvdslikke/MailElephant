<?php

class Common_File
{
	const ENCODING_UTF8 = 'UTF-8';
	const ENCODING_PHP = 'ISO-8859-1';
	
	private $path;
	private $fopenMode = 'r';
	private $handle = null;
	
	private $dataToSave = null;
	
	public function __construct($path = null, $fopenMode = null)
	{
		if($path !== null && !is_readable($path))
		{
			throw new Exception("file not readable");
		}
		
		$this->path = $path;
		
		if($fopenMode !== null)
		{
			$this->fopenMode = $fopenMode;
		}
	}
	
	public static function getEncodings()
	{
		return array(
				self::ENCODING_UTF8, 
				self::ENCODING_PHP);
	}
	
	public function hasPath()
	{
		return !empty($this->path);
	}
	
	public function getPath()
	{
		return $this->path;
	}
	
	public function setPath($path)
	{
		$this->path = $path;
	}
	
	/**
	 * filename without the extension
	 */
	public function getFilename()
	{
		return pathinfo($this->path, PATHINFO_FILENAME);
	}
	
	/**
	 * filename including extension
	 */
	public function getBasename()
	{
		return pathinfo($this->path, PATHINFO_BASENAME);
	}
	
	public function getDirectory()
	{
		return pathinfo($this->path, PATHINFO_DIRNAME);
	}
	
	public function getExtension()
	{
		return pathinfo($this->path, PATHINFO_EXTENSION);
	}
	
	protected function setFopenMode($mode)
	{
		$this->fopenMode = $mode;
	}
	
	protected function getHandle()
	{
		if($this->handle === null)
		{
			$this->handle = fopen($this->path, $this->fopenMode);
			if(!$this->handle)
			{
				throw new Exception("opening file failed");
			}
		}
		
		rewind($this->handle);
		return $this->handle;
	}
	
	public function setData($data)
	{
		$this->dataToSave = $data;
	}
	
	public function isDirty()
	{
		return $this->dataToSave !== null;
	}
	
	public function save()
	{
		if(!$this->hasPath())
		{
			throw new Exception("can't save, no path");
		}
		
		if($this->isDirty())
		{
			if(!file_exists($this->getDirectory()))
			{
				if(!mkdir($this->getDirectory(), 0777, true))
				{
					throw new Exception("directory couldn't be created");
				}
			}
			
			if(!is_writable($this->getDirectory()))
			{
				throw new Exception("location isn't writable");
			}
			
			if(!file_put_contents($this->path, $this->dataToSave))
			{
				throw new Exception("saving data failed");
			}
			$this->dataToSave = null;
		}
	}
	
	public function output()
	{
		$oldOpenMode = $this->fopenMode;
		$this->setFopenMode('rb');
		
		$handle = $this->getHandle();
		
		while(!feof($handle))
		{
			echo fread($handle, 4096);
				
			ob_flush();
			flush();
		}
		
		$this->setFopenMode($oldOpenMode);
	}
	
	public function __destruct()
	{
		if($this->handle !== null)
		{
			fclose($this->handle);
		}
	}
}