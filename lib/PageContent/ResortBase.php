<?php
namespace Littled\PageContent;


use Littled\Cache\ContentCache;
use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\OperationAbortedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\IntegerInput;
use Littled\Request\RequestInput;
use Littled\Request\StringInput;
use Littled\PageContent\SiteSection\ContentProperties;

/**
 * Class ResortBase
 * @package Littled\PageContent
 */
class ResortBase extends MySQLConnection
{
	/** @var StringInput Edit DOM id */
	public $editDOMID;
	/** @var array List of all record ids. */
	public $IDList;
	/** @var int ID of parent record. */
	public $parentID;
	/** @var StringInput List of all record positions. */
	public $positionList;
	/** @var IntegerInput Position of active record within the overall list of records. */
	public $positionOffset;
	/** @var ContentProperties Content properties. */
	public $contentProperties;
	/** @var StringInput Table type. */
	public $type;
	/** @var int Content type id. */
	public $typeID;
	/** @var array Validation errors list. */
	public $validationErrors;

	/**
	 * ResortBase constructor.
	 */
	function __construct()
	{
		parent::__construct();
		$this->contentProperties = new ContentProperties();
		$this->contentProperties->id->required = true;
		$this->editDOMID = new StringInput("Edit DOM ID", "rid", false, "", 100);
		$this->positionOffset = new IntegerInput("Position offset", "po", true);
		$this->positionList = new StringInput("ID array", "id", true, "", 10000);
		$this->type = new StringInput("Table type", "t", false, "", 50);
		$this->IDList = null;
		$this->parentID = null;
		$this->typeID = null;
		$this->validationErrors = array();
	}

	/**
	 * Collect script input and store it in the object's properties. Sets the object's filters property to the filter type appropriate to the section being edited.
	 * @param array|null[optional] $src Array of variables to use instead of POST data.
	 * @throws NotImplementedException
	 */
	function collectFromInput( $src=null )
	{
		if ($src===null) {
			$src = array_merge($_POST, $_GET);
		}
		$this->contentProperties->id->collectRequestData($src);
		if ($this->contentProperties->id->value>0) {
			$this->retrieveSectionProperties();
		}

		$this->editDOMID->collectFromInput($src);
		$this->positionOffset->collectRequestData($src);
		$position_str = '';
		if (array_key_exists($this->positionList->key, $src)) {
			$position_str = trim(filter_var($src[$this->positionList->key], FILTER_SANITIZE_STRING));
		}
		if ($position_str) {
			$this->positionList->value = json_decode($position_str);
		}
	}

	/**
	 * Tests if any validation errors have been added to the stack.
	 * @return bool TRUE if validation errors are detected.
	 */
	public function hasValidationErrors()
	{
		return (count($this->validationErrors) > 0);
	}

	/**
	 * Get the id's of all the records for resorting.
	 * @throws InvalidQueryException
	 * @throws RecordNotFoundException
	 */
	public function retrieveExistingIDs()
	{
		$data = array();
		switch($this->contentProperties->table->value) {
			case "ImageLink":
				/*
				 * in the case of images you can't just get all the records in the table
				 * they must be filtered by type and parent id
				 */
				$data = $this->retrieveImageIDs();
				break;

			default:

				/* implement the following line in inherited classes that have a $filters property */
				// $rs = $this->filters->retrieveListings();
				break;
		}

		$this->IDList = array();
		foreach($data as $row) {
			$this->IDList[count($this->IDList)] = $row->id;
		}
	}

	/**
	 * Retrieve record ids for image_link records.
	 * @return array Data set containing ImageLink record ids
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function retrieveImageIDs()
	{
		$pos_array = &$this->positionList->value;
		$query = "SELECT `parent_id`, `type_id` FROM `image_link` WHERE `id` = ".$pos_array[0];
		$data = $this->fetchRecords($query);
		if (count($data) > 0) {
			$this->parentID = $data[0]->parent_id;
			$this->typeID = $data[0]->type_id;
		}

		if ((!$this->parentID) || (!$this->typeID)) {
			throw new RecordNotFoundException("The image record could not be found.");
		}

		$query = <<<SQL
SELECT il.id 
FROM image_link il 
INNER JOIN `images` `full` ON il.fullres_id = `full`.id 
WHERE il.parent_id = {$this->parentID} AND il.type_id = {$this->typeID} 
ORDER BY IF(il.access='public', 0, 1), IFNULL(il.slot,999999), il.id 
SQL;
		return($this->fetchRecords($query));
	}

	/**
	 * Sets internal filters to retrieve record ids in their current order.
	 * To be defined in derived classes.
	 * @throws NotImplementedException
	 */
	public function retrieveSectionProperties()
	{
		throw new NotImplementedException(get_class($this)."retrieveSectionProperties() not defined.");
	}

	/**
	 * Commit resorted slot values to database.
	 * @return string String containing description of the results of the operation.
	 * @throws OperationAbortedException
	 */
	function save()
	{
		$i = 0;
		try {
			$status = "";
			$last = $this->positionOffset->value + count($this->positionList->value);
			for ($i = $this->positionOffset->value; $i < $last; $i++) {
				$query = "UPD"."ATE `{$this->contentProperties->table->value}` ".
					"SET slot = {$i} ".
					"WHERE id = ".$this->IDList[$i];
				$this->query($query);
			}
			$status .= "The new order of the {$this->contentProperties->label} records has been saved. \n";

			$status .= ContentCache::updateCache($this->contentProperties, $this->parentID);
		}
		catch(\Exception $ex) {
			throw new OperationAbortedException("Error updating position of record #".((is_array($this->IDList) && count($this->IDList)<$i)?($this->IDList[$i]):("unavailable")).": ".$ex->getMessage());
		}
		return ($status);
	}

	/**
	 * Resort the list of records to match the new order in the listings.
	 * @throws InvalidValueException
	 */
	public function updatePositions()
	{
		if ($this->positionOffset->value > count($this->IDList)) {
			throw new InvalidValueException("Invalid offset.");
		}

		$new_positions = &$this->positionOffset->value;

		for ($i = 0; $i < count($new_positions); $i++) {
			if (($this->positionOffset->value+$i) > count($this->IDList)) {
				break;
			}
			$this->IDList[$this->positionOffset->value+$i] = $new_positions[$i];
		}
	}

	/**
	 * Validate parameters passed to the script.
	 * @throws ContentValidationException
	 */
	public function validateInput()
	{
		try {
			$this->contentProperties->id->validate();
		}
		catch(ContentValidationException $ex) {
			array_push($this->validationErrors, $ex->getMessage());
		}
		$properties = array('editDOMID', 'positionOffset');
		foreach($properties as $key) {
			/** @var RequestInput $property */
			$property = $this->$key;
			try {
				$property->validate();
			}
			catch (ContentValidationException $ex) {
				array_push($this->validationErrors, $ex->getMessage());
			}
		}
		if (!is_array($this->positionList->value)) {
			array_push($this->validationErrors, "Record ids are required.");
		}
		if ($this->hasValidationErrors()) {
			throw new ContentValidationException("Errors were found in the resort data.");
		}
	}
}