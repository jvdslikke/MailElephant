<?php

interface Common_Storage_Provider_Interface
{
	/**
	 * Inserts data. Returns the specified identifying data.
	 */
	public function insert($scheme, $data, $identifyingDataSpecifiers);
	
	/**
	 * Updates matching records, or insert a new one when no records are found
	 * 
	 * @return int Returns the number of records updated/inserted
	 */
	public function upsert($scheme, $identifyingData, $data);
	
	/**
	 * Updates matching records
	 * 
	 * @return int Returns the number of records updated
	 */
	public function update($scheme, $identifyingData, $data);
	
	public function fetchAll($schema);
	
	/**
	 * @return mixed Null when not found
	 */
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
	
	/**
	 * @return int The number of records removed
	 */
	public function delete($scheme, $identifyingData);
}