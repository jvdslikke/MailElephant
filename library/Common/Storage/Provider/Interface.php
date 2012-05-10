<?php

interface Common_Storage_Provider_Interface
{
	/**
	 * Inserts data. Returns the specified identifying data.
	 */
	public function insert($scheme, $data, $identifyingDataSpecifiers);
	
	public function upsert($scheme, $identifyingData, $data);
	
	public function fetchAll($schema);
	
	public function fetchOneBy($scheme, $identifyingData);
	
	public function fetchMoreBy($scheme, $data);
	
	public function exists($scheme, $identifyingData);
	
	/**
	 * @param mixed $dateValue A value representing the datetime in the storage providers internal format
	 * @return DateTime
	 */
	public function createDateTimeFromInternalDateValue($dateValue);
	
	/**
	 * @param DateTime $dateTime
	 * @return mixed A value representing the datetime in the storage providers internal format
	 */
	public function createInternalDateValueFromDateTime(DateTime $dateTime);
}