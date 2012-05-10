<?php

class Common_CsvFileIterator extends Common_File implements Iterator
{
	private $delimiter;
	private $enclosure;
	private $escape;
	
	private $headers;
	
	private $iteratorLine;
	private $iteratorHandle;
	private $iteratorCurrent;
	
	public function __construct($fileName, $delimiter=',', $enclosure='"', $escape='\\')
	{
		parent::__construct($fileName);
		
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
		return fgetcsv($this->iteratorHandle, 0, $this->delimiter, $this->enclosure, $this->escape);
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