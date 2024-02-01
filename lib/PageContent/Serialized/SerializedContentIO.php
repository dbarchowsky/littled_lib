<?php

namespace Littled\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Request\RequestInput;
use Exception;

abstract class SerializedContentIO extends SerializedContentValidation
{
    /** @var bool               Flag to skip filling object values from input variables (GET or POST). */
    public bool                 $bypassCollectFromInput = false;
    protected static string     $table_name='';

    /**
     * Clears all form input values
     */
    public function clear(): void
    {
        foreach ($this as $p) {
            if ($p instanceof RequestInput) {
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
     * Execute query that commits data stored in object instance to the database.
     * @param string $query Query string to execute
     * @param string $arg_types String describing parameter types, passed to mysqli prepared statement.
     * @param mixed $args,... Variables to insert into the query
     * @throws ConfigurationUndefinedException|ConnectionException|InvalidQueryException
     */
    protected function commitSaveQuery(string $query, string $arg_types='', ...$args)
    {
        $this->connectToDatabase();
        array_unshift($args, $query, $arg_types);
        $this->query(...$args);
    }

    /**
     * Deletes the record from the database. Uses the value object's id property to look up the record.
     * @return string Message indicating result of the deletion.
     */
    abstract public function delete ( ): string;

    /**
     * Create a SQL insert statement using the values of the object's input properties & execute the insert statement.
     */
    abstract protected function executeInsertQuery();

    /**
     * Create a SQL update statement using the values of the object's input properties & execute the update statement.
     */
    abstract protected function executeUpdateQuery();

    /**
     * Returns query string, type string, and values to be inserted into the query. The query
     * will insert a new record or update an existing record depending on the value of object's id
     * property. If the id property is null, a new record is inserted. IF the property value matches
     * a record in the database, that record is updated.
     * @return array|null
     */
    public abstract function generateUpdateQuery(): ?array;

    /**
     * Returns a descriptive label of the content type suitable to insert into a sentence.
     * @param bool $make_plural Makes the label plural if TRUE.
     * @return string
     */
    public function getInlineLabel(bool $make_plural=false): string
    {
        return strtolower($make_plural ? static::makePlural($this->getContentLabel()) : $this->getContentLabel());
    }

    /**
     * Returns a descriptive label for the object, usually corresponding to the name of the database table holding
     * object records.
     * @return string
     */
    abstract function getContentLabel(): string;

    /**
     * Table name getter.
     * @return string
     * @throws NotImplementedException
     */
    public static function getTableName(): string
    {
        if (static::$table_name === '') {
            throw new NotImplementedException('Table name not set.');
        }
        return static::$table_name;
    }

    /**
     * Retrieves data from the database based on the internal properties of the class instance. Sets the values of the
     * internal properties of the class instance using the database data.
     */
    abstract public function read ();

    /**
     * Retrieves a list of records from the database using $query. Converts each row in the result to an object of
     * type $type. Stores the objects as an array in the object's property specified with $property.
     * @param string $property Name of property to use to store list.
     * @param string $type Object type to push onto the array.
     * @param string $query Query string
     * @param string $types String containing types used to bind variables to query (mysqli prepared statement)
     * @param mixed $vars,... Variables to insert into the query.
     * @throws NotImplementedException Currently only stored procedures are supported.
     * @throws InvalidTypeException $type does not represent a class derived from SerializedContent.
     * @throws Exception
     */
    public function readList( string $property, string $type, string $query, string $types='', &...$vars )
    {
        if (stripos($query, "call")===0) {
            array_unshift($vars, $query, $types);
            $data = call_user_func_array([$this, 'fetchRecords'], $vars);
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
     */
    abstract public function save ();

    /**
     * Confirm that a record with id value matching the current id value of the object currently exists in the database.
     * @return bool True/False depending on if a matching record is found.
     * @throws Exception
     */
    abstract public function recordExists(): bool;

    /**
     * Table name setter.
     * @param string $table_name
     * @return void
     */
    public static function setTableName(string $table_name)
    {
        static::$table_name = $table_name;
    }



}