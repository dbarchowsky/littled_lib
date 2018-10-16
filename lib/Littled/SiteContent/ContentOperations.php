<?php
namespace Littled\SiteContent;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\IntegerInput;
use Littled\Request\RequestInput;


/**
 * Class ContentOperations
 * @package Littled\SiteContent
 */
class ContentOperations extends MySQLConnection
{
	/** @var MySQLConnection Database connection. */
	public $connection;
	/** @var IntegerInput Record id */
	public $id;

	public static function TABLE_NAME() { return(''); }
	
	/**
	 * ContentOperations constructor.
	 * @throws \Exception Error establishing database connection.
	 * @throws ConfigurationUndefinedException Database connection properties not set.
	 */
	function __construct()
	{
		parent::__construct();
		$this->connectToDatabase();
	}

	/**
	 * Clears the data container values in the object.
	 * @return void
	 */
	public function clear ( )
	{
		foreach ($this as $key => $item) {
			/** @var object $item */
			if(is_object($item) && method_exists("clear", $item)) {
				$item->clear();
			}
		}
	}

	/**
	 * Queries the database for the latest id created.
	 * @return int Record id
	 * @throws \Exception Error running query.
	 * @throws RecordNotFoundException
	 */
	protected function fetchRecordId()
	{
		$query = "SELECT LAST_INSERT_ID()";
		$rs = $this->fetchRecords($query);
		if (count($rs) < 1) {
			throw new RecordNotFoundException("Could not retrieve record id.");
		}
		return ($rs[0]->id);
	}
	
	/**
	 * Returns list of columns based on the object's property names. The
	 * column name values are escaped for the use in queries.
	 * @return array List of columns based on the object's property names. The
	 * column name values are escaped for the use in queries.
	 */
	public function collectTableColumns()
	{
		$used_params = array();
		$fields = array();
		foreach ($this as $key => $item) {
			if ($this->isInput($key, $item, $used_params)) {
				$fields[] = "`{$key}`";
			}
		}
		return ($fields);
	}

	/**
	 * Commits object property values to database record.
	 * @throws ConfigurationUndefinedException Table name not specified.
	 */
	public function commit()
	{
		if ($this->TABLE_NAME()=="") {
			throw new ConfigurationUndefinedException("[".__METHOD__."] Table name not set. ");
		}

		if (!$this->hasData()) {
			return;
		}

		if (is_numeric($this->id->value)) {
			$this->executeUpdateQuery();
		}
		else {
			$this->executeInsertQuery();
		}
	}

	/**
	 * Copies the property values from one object into this instance.
	 * @param mixed $src Object to use to copy values over to this object.
	 */
	public function copy( $src )
	{
		if (get_class($this) != get_class($src))
		{
			return;
		}
		$keys = $src->keys();
		foreach($keys as $key)
		{
			$this->$key = $src->$key;
		}
	}

	/**
	 * Deletes the record from the database. Uses the value object's id property to look up the record.
	 * @throws ConfigurationUndefinedException
	 * @throws \Exception Error executing query.
	 */
	function delete ( )
	{
		if ($this->TABLE_NAME()=='') {
			throw new ConfigurationUndefinedException("[".__METHOD__."] Table name not set. ");
		}

		if ($this->id->value===null || $this->id->value<1) { 
			return; 
		}

		$query = "DELETE FROM `".$this->TABLE_NAME()."` WHERE `id` = {$this->id->value}";
		$this->query($query);
	}

	/**
	 * Create a SQL insert statement using the values of the object's input properties & execute the insert statement.
	 */
	protected function executeInsertQuery()
	{
		$fields = array();
		$used_params = array();

		/* pick out object properties that match columns of the record in the database */
		foreach ($this as $key => $item) {
			if ($this->isInput($key, $item, $used_params)) {
				/* format column name and value for SQL statement */
				$fields["`{$key}`"] = $item->escapeSQL();
			}
		}

		/* build sql statement */
		$query = "INSERT INTO `".$this->TABLE_NAME()."` (".
		         implode(',', array_keys($fields)).
		         ") VALUES (".
		         implode(',', array_values($fields)).
		         ")";

		/* execute sql and store id value of the new record. */
		$this->query($query);
		$this->id->value = $this->fetchRecordId();
	}

	/**
	 * Create a SQL update statement using the values of the object's input properties & execute the update statement.
	 */
	protected function executeUpdateQuery()
	{
		$used_params = array();
		$fields = array();

		/* pick out object properties that match columns of the record in the database */
		foreach ($this as $key => &$item) {
			/** @var RequestInput $item */
			if ($this->isInput($key, $item, $used_params)) {
				/* format column name and value for SQL statement */
				$fields[] = "`{$key}` = ".$item->escapeSQL($this->mysqli);
			}
		}

		/* build and execute sql statement */
		$query = "UPDATE `".$this->TABLE_NAME()."` SET ".
		         implode(',', $fields)." ".
		         "WHERE id = {$this->id->value}";
		$this->query($query);
	}

	/**
	 * Retrieves the name of the record represented by the provided id value.
	 * @param string $table Name of the table containing the records.
	 * @param int $id ID value of the record.
	 * @param string $field (optional) Column name containing the value to retrieve. Defaults to "name".
	 * @param string $id_field (optional) Column name containg the id value to retrieve. Defaults to "id".
	 * @return mixed Retrieved value.
	 * @throws \Exception Error executing query.
	 */
	public function getTypeName( $table, $id, $field="name", $id_field="id" )
	{
		if ( $id === null || $id < 1) {
			return(null);
		}
		$query = "SELECT `{$field}` from `{$table}` WHERE `{$id_field}` = {$id}";
		$rs = $this->fetchRecords($query);
		$ret_value = $rs->$field;
		return($ret_value);
	}

	/**
	 * Indicates if any form data has been entered for the current instance of the object.
	 * @return bool Returns TRUE if editing an existing record, a title has been entered, or if any gallery images have been uploaded. Most likely should be overridden in derived classes.
	 */
	function hasData()
	{
		return ($this->id->value!==null);
	}

	/**
	 * Assign values contained in array to object input properties.
	 * @param object $row Row returned by mysql query.
	 */
	protected function hydrate(&$row)
	{
		$used_params = array();
		foreach ($this as $key => &$item) {
			/** @var RequestInput $item */
			if ($this->isInput($key, $item, $used_params)) {
				/* store value retrieved from database */
				$item->setInputValue($row->$key);
			}
		}
	}

	/**
	 * Returns records from database query.
	 * @param string $query SQL query to execute
	 * @throws RecordNotFoundException
	 * @throws \Exception Error executing query.
	 */
	public function hydrateFromQuery($query)
	{
		$this->query($query);
		$got_result = false;
		do {
			$result = $this->mysqli->store_result();
			if ($result) {
				$row = $result->fetch_object();
				if($row) {
					$got_result = true;
					$this->hydrate($row);
				}
				$result->free();
			}
		} while($this->mysqli->more_results() && $this->mysqli->next_result());
		if ($got_result===false) {
			throw new RecordNotFoundException("The requested record was not found.");
		}
	}

	/**
	 * Checks if the class property is an input object and should be used for
	 * various operations such as updating or retrieving data from the database,
	 * or retrieving data from forms.
	 * @param string $key Name of the class property.
	 * @param RequestInput $item Value of the class property.
	 * @param array $used_params Array containing a list of the objects that
	 * have already been listed as input properties.
	 * @return bool True if the object is an input class and should be used to update the database. False otherwise.
	 */
	protected function isInput(&$key, &$item, &$used_params)
	{
		$is_input = (($item instanceof RequestInput) &&
		             ($key != "id") &&
		             ($key != "index") &&
		             ($item->dbField==true));
		if ($is_input) {
			/* Check if this item has already been used as in input property.
			 * This prevents references used as aliases of existing properties
			 * from being included in database queries.
			 */
			if (in_array($item->key, $used_params)) {
				$is_input = false;
			}
			else {
				/* once an input property is marked as such, track it so it
				 * can't be included again.
				 */
				$used_params[] = $item->key;
			}
		}
		return ($is_input);
	}

	/**
	 * Returns an appropriate label given the value of $count if $count requires the label to be pluralized.
	 * @param integer $count Number determining if the label is plural or not.
	 * @param string $property_name Name of the property of the object to use as the basis for the pural label.
	 * @return string Plural form of the record label if $count is not 1.
	 */
	public function pluralLabel($count, $property_name)
	{
		if (property_exists($this, $property_name)) {
			$label = strtolower($this->{$property_name}->value);
		}
		else {
			return ('');
		}

		if ($count==1) {
			return ($label);
		}
		elseif (substr($label, -1)=='y') {
			return (substr($label, 0, -1).'ies');
		}
		elseif (substr($label, -1)=='s') {
			return ($label);
		}
		else {
			return ($label.'s');
		}
	}

	/**
	 * Retrieves record from database and uses it to hydrate object properties.
	 * @throws ConfigurationUndefinedException Table name not specified.
	 * @throws RecordNotFoundException Expected record not returned.
	 * @throws \Exception Error executing query.
	 */
	public function read()
	{
		if (self::TABLE_NAME()=='') {
			throw new ConfigurationUndefinedException("[".__METHOD__."] Table name not set. ");
		}

		$fields = $this->collectTableColumns();
		$query = "SELECT ".
		         implode(',', $fields)." ".
		         "FROM `".self::TABLE_NAME()."` ".
		         "WHERE id = {$this->id->value}";
		$data = $this->fetchRecords($query);

		if (count($data) < 1) {
			throw new RecordNotFoundException("[".__METHOD__."] Record not found.");
		}

		$this->hydrate($data[0]);
	}

	/**
	 * Validates form data collected by the object.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function validateInput()
	{
		$form_errors = '';
		foreach($this as $key => &$property) {
			if ($property instanceof RequestInput) {
				try {
					$property->validate();
				}
				catch(ContentValidationException $ex) {
					$form_errors .= $ex->getMessage();
				}
			}
		}
		if ($form_errors) {
			throw new ContentValidationException($form_errors);
		}
	}
}