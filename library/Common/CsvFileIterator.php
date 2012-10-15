<?php

/**
 * This allows you to loop over records in a csv file
 * The first line is assumed to contain the headers  
 * The field contents are returned in UTF-8 if the input encoding is specified correctly 
 */
class Common_CsvFileIterator extends Common_File implements Iterator
{
	private $encoding;
	private $delimiter;
	private $enclosure;
	private $escape;
	
	private $headers;
	
	private $iteratorLine;
	private $iteratorHandle;
	private $iteratorCurrent;
	
	public function __construct($fileName, $encoding=null, $delimiter=',', $enclosure='"', $escape='\\')
	{
		parent::__construct($fileName);
		
		$this->encoding = $encoding;
		$this->delimiter = $delimiter;
		$this->enclosure = $enclosure;
		$this->escape = $escape;
		
		$this->setupIterator();
	}
	
	private function setupIterator()
	{
		$this->iteratorHandle = $this->getHandle();
		$this->iteratorLine = 0;
		$this->headers = $this->getLineCsvValues();
		$this->iteratorCurrent = $this->getLineCsvValuesByHeaders();
	}
	
	private function getLineCsvValues()
	{
		$this->iteratorLine += 1;
		
		// store current locale, set new locale for read encoding
		$curLocale = null;
		if($this->encoding !== null)
		{
			$curLocale = setlocale(LC_ALL, "0");

			if(!setlocale(LC_ALL, "en_US.".$this->encoding))
			{
				throw new Exception("setting locale encoding failed");
			}
		}
		
		// get values, eventually convert from source encoding to UTF-8
		$values = array();
		foreach(fgetcsv($this->iteratorHandle, 0, $this->delimiter, $this->enclosure, $this->escape) as $value)
		{
			switch($this->encoding)
			{
				case Common_File::ENCODING_PHP:
					$values[] = utf8_encode($value);
					break;
					
				case Common_File::ENCODING_UTF8:
					$values[] = $value;
					break;
					
				default:
					throw new Exception("unknown encoding specified");
			}
		}
		
		// restore locale
		if($this->encoding !== null)
		{
			setlocale(LC_ALL, $curLocale);
		}
		
		return $values;
	}
	
	private function getLineCsvValuesByHeaders()
	{
		$values = $this->getLineCsvValues();
		
		$result = array();
		foreach($this->headers as $index=>$title)
		{
			$result[$title] = $values[$index];
		}
		return $result;
	}
	
	public function getHeaders()
	{
		return $this->headers;
	}
	
	/*** iterator interface */
	
	public function current()
	{
		return $this->iteratorCurrent;
	}
	
	public function key()
	{
		return $this->iteratorLine;
	}
	
	public function next()
	{
		$this->iteratorCurrent = $this->getLineCsvValuesByHeaders();
	}
	
	public function rewind()
	{
		$this->setupIterator();
	}
	
	public function valid()
	{
		return !feof($this->iteratorHandle);
	}
}