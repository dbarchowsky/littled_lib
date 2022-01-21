<?php
namespace Littled\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\RequestInput;
use Littled\Request\IntegerInput;
use Exception;
use mysqli_result;

class SerializedContent extends SerializedContentValidation
{
	/** @var IntegerInput Record id. */
	public $id;
	/** @var boolean Flag to skip filling object values from input variables (GET or POST). */
	public $bypassCollectFromInput;
    /** @var string */
    protected static $table_name='';

	/**
	 * @throws NotImplementedException
	 * @deprecated Use getTableName() instead.
	 * Interface to retrieve table name associated with inherited classes.
	 */
	public static function TABLE_NAME(): string
	{
		return self::getTableName();
	}

	/**
	 * SerializedContent constructor.
	 * @param integer[optional] $id Initial value to assign to the object's id property.
	 */
	function __construct($id=null)
	{
		parent::__construct();
		$this->id = new IntegerInput('id', 'id', false, $id);
		$this->bypassCollectFromInput = false;
	}

    /**
     * Clears all form input values
     */
    public function clear()
    {
        foreach ($this as $p)
        {
            if ($p instanceof RequestInput)
            {
                $p->clearValue();
            }
        }
    }

    /**
     * Check if a column exists in a given database table in the content item's database table.
     * @param string $column_name Name of the column to check for.
     * @param string $table_name (Optional) This parameter is ignored in this class's implementation of the routine.
     * @return boolean True/false depending on if the column is found.
     * @throws Exception
     */
	public function columnExists(string $column_name, string $table_name=''): bool
	{
		if (''===$table_name) {
			$table_name = $this::getTableName();
		}
		return(parent::columnExists($column_name, $table_name));
	}

    /**
     * Execute query that will commit object properties to the database.
     * @param string $query
     * @param string $content_type (Optional) label to use to describe the content that was being saved in the case of
     * database errors.
     * @throws Exception
     */
    protected function commitSaveQuery(string $query, string $content_type)
    {
        if (!$this->mysqli->multi_query($query)) {
            /* N.B. MySQL errors thrown from SQL statements embedded in the
             * multi query won't necessarily cause mysqli->multi_query() to
             * return false. E.g. errors in the stored proc b/c it isn't the
             * first SQL statement executed.
             */
            throw new Exception((($content_type)?("Error saving $content_type: "):('')).$this->mysqli->error);
        }
        do {
            if ($result = $this->mysqli->store_result()) {
                $this->updateIdAfterCommit($result);
                $result->close();
            }
        } while ($this->mysqli->next_result());
	    if ($this->mysqli->error) {
		    throw new Exception((($content_type)?("Error saving $content_type: "):('')).$this->mysqli->error);
	    }
    }

    /**
     * Deletes the record from the database. Uses the value object's id property to look up the record.
     * @return string Message indicating result of the deletion.
     * @throws ContentValidationException Record id not provided.
     * @throws NotImplementedException Table name not set in inherited class.
     * @throws Exception
     */
	public function delete ( ): string
	{
		if (null === $this->id->value || 1 > $this->id->value) {
			throw new ContentValidationException("Id not provided.");
		}

		if (!$this->recordExists()) {
			return("The requested record could not be found. \n");
		}

		$query = "DEL"."ETE FROM `".$this::getTableName()."` WHERE `id` = ?";
		$this->query($query, 'i', $this->id->value);
		return ("The record has been deleted. \n");
	}

    /**
     * Create a SQL insert statement using the values of the object's input properties & execute the insert statement.
     * @throws ConnectionException On connection error.
     * @throws ConfigurationUndefinedException Database connection properties not set.
     * @throws Exception
     */
	protected function executeInsertQuery()
	{
		$fields = $this->formatDatabaseColumnList();

		/* build sql statement */
		$query = "INS"."ERT INTO `".$this::getTableName()."` (`".
			implode('`,`', array_keys($fields)).
			"`) VALUES (".
			implode(',', array_values($fields)).
			")";

		/* execute sql and store id value of the new record. */
		$this->query($query);
		$this->id->value = $this->retrieveInsertID();
	}

	/**
	 * Create a SQL update statement using the values of the object's input properties & execute the update statement.
	 * @throws ConnectionException On connection error.
	 * @throws ConfigurationUndefinedException Database connection properties not set.
     * @throws NotImplementedException Table name not specified in inherited class.
	 * @throws RecordNotFoundException No record exists that matches the id value.
     * @throws Exception
	 */
	protected function executeUpdateQuery()
	{
		$fields = $this->formatDatabaseColumnList();

		/* confirm that the record exists */
		if (!$this->recordExists()) {
			throw new RecordNotFoundException("Requested record not available for update.");
		}

		$fields_cb = function($key, $value) { return("`$key`=$value"); };

		/* build and execute sql statement */
		$query = "UPDATE `".$this::getTableName()."` SET ".
			implode(',', array_map($fields_cb, array_keys($fields), $fields))." ".
			"WHERE id = ?;";
		$this->query($query, 'i', $this->id->value);
	}

    /**
     * Attempts to determine which column in a table holds title or name values.
     * @return string Name of the column holding title or name values. Returns empty string if an identifier column couldn't be found.
     * @throws InvalidQueryException Error executing query.*@throws Exception
     * @throws Exception
     * @todo be implemented in inherited classes, then this routine is no longer necessary and can be removed.
     * @todo This routine exists for the benefit of the getRecordName() routine. If the switch that is in that routine
     */
    public function getNameColumnIdentifier(): string
	{
		switch(1) {
			case ($this->columnExists('name')):
				return ('name');
			case ($this->columnExists('title')):
				return('title');
			default:
				return('');
		}
	}

    /**
     * Record id getter.
     * @return int
     */
    public function getRecordId(): ?int
    {
        return $this->id->value;
    }

	/**
	 * Attempts to read the title or name from a record in the database and use
	 * its value to set the title or name property of the class instance. Uses the
	 * value of the internal TABLE_NAME() property to determine which table to search.
     * @throws RecordNotFoundException Requested data not found.
	 * @throws InvalidQueryException Error executing SQL queries.
     * @throws Exception
	 */
	function getRecordLabel()
	{
		$column = $this->getNameColumnIdentifier();

		$query = "SEL"."ECT `$column` AS `column_name` FROM `".$this::getTableName()."` WHERE `id` = ?";
		$data = $this->fetchRecords($query, 'i', $this->id->value);
		if (count($data) < 1) {
			throw new RecordNotFoundException('Column value not found');
		}

		$column_options = array('name', 'title');
		foreach($column_options as $prop) {
			if (property_exists($this, $prop)) {
				$this->$prop->value = $data[0]->column_name;
				break;
			}
		}
	}

    /**
     * Table name getter.
     * @return string
     * @throws NotImplementedException
     */
    public static function getTableName(): string
    {
        if ('' === static::$table_name) {
            throw new NotImplementedException('Table name not set.');
        }
        return static::$table_name;
    }

	/**
	 * Retrieves the name of the record represented by the provided id value.
	 * @param string $table Name of the table containing the records.
	 * @param int $id ID value of the record.
	 * @param string[optional] $field Column name containing the value to retrieve. Defaults to "name".
	 * @param string[optional] $id_field Column name containing the id value to retrieve. Defaults to "id".
	 * @throws InvalidQueryException|Exception SQL error raised running insert query.
	 * @return string|null Retrieved value.
	 */
	public function getTypeName(string $table, int $id, $field="name", $id_field="id" ): ?string
	{
		if ($id<1) {
			return null;
		}

		$query = "SEL"."ECT `$field` AS `result` FROM `$table` WHERE `$id_field` = $id";
		$data = $this->fetchRecords($query);
		$ret_value = $data[0]->result;
		return($ret_value);
	}

	/**
	 * Retrieves data from the database based on the internal properties of the
	 * class instance. Sets the values of the internal properties of the class
	 * instance using the database data.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException Record id not set.
	 * @throws InvalidTypeException Record id is not an instance of IntegerInput.
	 * @throws RecordNotFoundException Requested record not available.
	 * @throws NotImplementedException
	 */
	public function read ()
	{
		if ($this->id instanceof IntegerInput === false) {
			throw new InvalidTypeException("Record id not in expected format.");
		}
		if ($this->id->value===null || $this->id->value<0) {
			throw new ContentValidationException("Record id not set.");
		}

		$fields = $this->formatDatabaseColumnList();
		$query = "SELECT `".
			implode('`,`', array_keys($fields))."` ".
			"FROM `".$this::getTableName()."` ".
			"WHERE id = {$this->id->value}";
		try {
			$this->hydrateFromQuery($query);
		}
		catch(RecordNotFoundException $ex) {
			$error_msg = "The requested ".$this::getTableName()." record could not be found.";
			throw new RecordNotFoundException($error_msg);
		} catch (Exception $e) {
        }
    }

    /**
     * Retrieves a list of records from the database using $query. Converts each
     * row in the result to an object of type $type. Stores the objects as an
     * array in the object's property specified with $property.
     * @param string $property Name of property to use to store list.
     * @param string $type Object type to push onto the array.
     * @param string $query SQL query to execute to retrieve list.
     * @throws NotImplementedException Currently only stored procedures are supported.
     * @throws InvalidTypeException $type does not represent a class derived from SerializedContent.
     * @throws Exception
     */
	public function readList( string $property, string $type, string $query )
	{
		if (stripos($query, "call")===0) {
			$data = $this->fetchRecords($query);
		}
		else {
			throw new NotImplementedException("Unsupported query type for retrieving record list.");
		}

		$this->$property = array();
		foreach($data as $row) {
			$obj = new $type;
			if (!($obj instanceof SerializedContent)) {
				throw new InvalidTypeException("Cannot store records in object provided.");
			}
			$obj->fill($row);
			$this->$property[] = $obj;
		}
	}

	/**
	 * Commits the values stored in the class instance's properties to the database.
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException Unable to establish database connection.
	 * @throws ContentValidationException Record contains invalid data.
     * @throws NotImplementedException Table name value not set in inherited class.
	 * @throws RecordNotFoundException No record exists that matches the id value.
	 */
	public function save ()
	{
		if (!$this->hasData()) {
			throw new ContentValidationException("Record has no data to save.");
		}
		if (is_numeric($this->id->value)) {
			$this->executeUpdateQuery();
		}
		else {
			$this->executeInsertQuery();
		}
	}

    /**
     * Record id setter.
     * @param int $id
     * @return void
     */
    public function setRecordId(int $id)
    {
        $this->id->setInputValue($id);
    }

	/**
	 * Confirm that a record with id value matching the current id value of the object currently exists in the database.
	 * @return bool True/False depending on if a matching record is found.
     * @throws NotImplementedException
     * @throws Exception
	 */
	public function recordExists(): bool
	{
		if ($this->id->value===null || $this->id->value==='' || $this->id->value < 1) {
			return (false);
		}

		$query = "SEL"."ECT EXISTS(SELECT 1 FROM `".$this::getTableName()."` WHERE `id` = ?) AS `record_exists`";
		$data = $this->fetchRecords($query, 'i', $this->id->value);
		return ((int)("0".$data[0]->record_exists) === 1);
	}

    /**
     * Table name setter.
     * @param string $table_name
     * @return void
     */
    public static function setTableName(string $table_name)
    {
        static::$table_name = $table_name;
    }

	/**
	 * Tests for a valid parent record id. Throws ContentValidationException if the property value isn't current set.
     * @param string (Optional) Informational message to prepend to error message thrown when a valid parent id is not found.
	 * @throws ContentValidationException
	 */
	protected function testForParentID(string $msg='')
	{
		if ($this->id->value === null || $this->id->value < 0) {
            $msg = ($msg)?("$msg "):('Could not perform operation. ');
			throw new ContentValidationException("{$msg}A parent record was not provided.");
		}
	}

    /**
     * Update the internal id property value after committing object property
     * values to the database.
     * @param mysqli_result $result
     */
    protected function updateIdAfterCommit(mysqli_result $result)
    {
        if ($this->id->value===null) {
            $row = $result->fetch_assoc();
            if (is_array($row) && array_key_exists('_p_record_id', $row)) {
                $this->id->value = $row['_p_record_id'];
            }
        }
    }
}