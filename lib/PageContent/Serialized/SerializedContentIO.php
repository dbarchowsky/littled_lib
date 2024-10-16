<?php

namespace Littled\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Log\Log;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\Request\RequestInput;
use Littled\Validation\Validation;


abstract class SerializedContentIO extends SerializedContentValidation
{
    /** @var bool               Flag to skip filling object values from input variables (GET or POST). */
    public bool                 $bypassCollectFromInput = false;
    protected static string     $table_name;

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
     * @return bool True/false depending on if the column is found.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     */
    public function columnExists(string $column_name, string $table_name=''): bool
    {
        if (''===$table_name) {
            $table_name = $this::getTableName();
        }
        return(parent::columnExists($column_name, $table_name));
    }

    /**
     * Looks up any foreign key properties in the object and commits the links to the database.
     * @return void
     * @throws ContentValidationException
     * @throws FailedQueryException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws InvalidValueException
     */
    protected function commitLinkedRecords(): void
    {
        $lc = $this->getLinkedContentPropertyList();
        foreach ($lc as $property) {
            if (!$property instanceof SerializedContent && $property->hasRecordData()) {
                $property->save();
            }
        }
    }

    /**
     * Execute query that commits data stored in object instance to the database.
     * @param string $query Query string to execute
     * @param string $arg_types String describing parameter types, passed to mysqli prepared statement.
     * @param mixed $args,... Variables to insert into the query
     * @throws ConfigurationUndefinedException|ConnectionException|InvalidQueryException
     */
    protected function commitSaveQuery(string $query, string $arg_types='', ...$args): void
    {
        $this->connectToDatabase();
        array_unshift($args, $query, $arg_types);
        $this->query(...$args);
    }

    /**
     * Deletes the record from the database. Uses the value object's id property to look up the record.
     * @return string Message indicating result of the deletion.
     */
    abstract public function delete(): string;

    /**
     * Execute query that will commit the instance's property values to the database.
     * @return void
     * @throws FailedQueryException
     */
    protected function executeCommitQuery(): void
    {
        try {
            /* execute sql and store id value of the new record. */
            $args = $this->formatCommitQuery();
            $this->query(...$args);
            $this->testAndLoadLastInsertId($args[0]);
        }
        catch (ConnectionException|
            ConfigurationUndefinedException|
            InvalidQueryException $e) {
            $msg = 'Error commiting a record. [' . Log::getClassBaseName($e::class) . '] ' . $e->getMessage();
            throw new FailedQueryException($msg);
        }
    }

    /**
     * Return an array containing a query, a type string, and an array of arguments to be used in the MySQL
     * prepared statement that will commit the instance's property values to a record in the database.
     * @return array
     */
    abstract protected function formatCommitQuery(): array;

    /**
     * Returns the prepared statement that will retrieve matching records in the object's read() routine.
     * Allows inherited classes to override the default prepared statement created in the object's read() routine.
     * @return array
     */
    protected abstract function formatRecordSelectPreparedStmt(): array;

    /**
     * Returns a descriptive label for the object, usually corresponding to the name of the database table holding
     * object records.
     * @return string
     */
    public abstract function getContentLabel(): string;

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
     * Returns a list of all the properties of the object that represent foreign keys.
     * @return LinkedContent[]
     */
    protected function getLinkedContentPropertyList(): array
    {
        $lc = [];
        foreach ($this as $property) {
            if (is_object($property) &&
                !Validation::isSubclass($property, ContentProperties::class) &&
                (Validation::isSubclass($property, LinkedContent::class) ||
                Validation::isSubclass($property, SerializedContent::class) ||
                Validation::isSubclass($property, OneToManyContentLink::class))
            ) {
                $lc[] = $property;
            }
        }
        return $lc;
    }

    /**
     * Table name getter.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public static function getTableName(): string
    {
        if (!isset(static::$table_name)) {
            throw new ConfigurationUndefinedException('Table name not set.');
        }
        return static::$table_name;
    }

    /**
     * Returns a flag indicating that the object is in a state where record data can be retrieved for it, and it
     * does not already contain data that makes a database query unnecessary.
     * @return bool
     */
    abstract protected function isReadyToRead(): bool;

    /**
     * Retrieves data from the database based on the internal properties of the class instance. Sets the values of the
     * internal properties of the class instance using the database data.
     */
    abstract public function read ();

    /**
     * Retrieve all record data belonging to tables linked to this content type.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function readLinked(): void
    {
        $lc = $this->getLinkedContentPropertyList();
        foreach ($lc as $property) {
            if ($property->isReadyToRead()) {
                // save prefix
                $prefix = $property->getRecordsetPrefix();

                // ignore prefix in this context
                $property->setRecordsetPrefix('');

                // retrieve record
                $property->read();

                // restore prefix
                $property->setRecordsetPrefix($prefix);
            }
        }
    }

    /**
     * @deprecated Use OneToManyLinkedContent property instead
     * Retrieves a list of records from the database using $query. Converts each row in the result to an object of
     * type $type. Stores the objects as an array in the object's property specified with $property.
     * @param string $property Name of property to use to store list.
     * @param string $type Object type to push onto the array.
     * @param string $query Query string
     * @param string $types String containing types used to bind variables to query (mysqli prepared statement)
     * @param mixed $vars,... Variables to insert into the query.
     * @throws NotImplementedException Currently only stored procedures are supported.
     * @throws InvalidTypeException $type does not represent a class derived from SerializedContent.
     */
    public function readList( string $property, string $type, string $query, string $types='', &...$vars ): void
    {
        if (stripos($query, 'call')===0) {
            array_unshift($vars, $query, $types);
            $data = call_user_func_array([$this, 'fetchRecords'], $vars);
        }
        else {
            throw new NotImplementedException('Unsupported query type for retrieving record list.');
        }

        $this->$property = array();
        foreach($data as $row) {
            $obj = new $type;
            if (!($obj instanceof SerializedContent)) {
                throw new InvalidTypeException('Cannot store records in object provided.');
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
     * Record id getter.
     * @return $this
     */
    abstract public function setRecordId(int $record_id): SerializedContentIO;

    /**
     * Table name setter.
     * @param string $table_name
     * @return void
     */
    public static function setTableName(string $table_name): void
    {
        static::$table_name = $table_name;
    }

    /**
     * Tests a query string after a SQL command has been executed to determine if it was an insert statement (and not
     * a procedure) and loads the value of the new record into the MySQL @insert_id session variable so it can
     * be retrieved with a prepared statement and stored in a property of a derived class.
     * @param string $query
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     */
    protected function testAndLoadLastInsertId(string $query): void
    {
        $query = strtolower(substr(ltrim($query),  0, 7));
        if ($query == 'insert ') {
            $this->query('SELECT LAST_INSERT_ID() INTO @insert_id');
        }
    }

    /**
     * Update the internal id property value after committing object property values to the database.
     * @throws FailedQueryException
     */
    protected function updateIdAfterCommit(): void
    {
        try {
            // query was a procedure
            $data = $this->fetchRecords(query: 'SELECT @insert_id AS `id`');
            if (1 > count($data)) {
                throw new InvalidQueryException('Could not retrieve new record id.');
            }
            $id = $data[0]->id > 0 ? $data[0]->id : null;
            $this->setRecordId($id);
        }
        catch (ConnectionException|
        ConfigurationUndefinedException|
        InvalidQueryException $e) {
            $msg = 'Error retrieving new record id. [' . Log::getClassBaseName($e::class) . '] ' . $e->getMessage();
            throw new FailedQueryException($msg);
        }
    }
}