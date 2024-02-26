<?php

namespace Littled\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\IntegerInput;
use Littled\Validation\Validation;
use Exception;

/**
 * Routines for fetching and committing database records.
 */
abstract class SerializedContent extends SerializedContentIO
{
    /** @var IntegerInput Record id. */
    public IntegerInput $id;
    protected static string $default_id_key = 'id';

    /**
     * SerializedContent constructor.
     * @param ?int $id Optional initial value to assign to the object's id property.
     */
    function __construct(?int $id = null)
    {
        parent::__construct();
        $this->id = new IntegerInput('id', static::$default_id_key, false, $id);
    }

    /**
     * Looks up any foreign key properties in the object and commits the links to the database.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws NotImplementedException|InvalidValueException
     */
    protected function commitLinkedRecords()
    {
        $lc = $this->getLinkedContentPropertyList();
        foreach ($lc as $property) {
            $property->save();
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function commitSaveQuery(string $query, string $arg_types = '', ...$args)
    {
        $this->connectToDatabase();
        $s1 = $this->mysqli->prepare('SET @insert_id = ?');
        $s1->bind_param('i', $this->id->value);
        $s1->execute();

        array_unshift($args, $query, $arg_types);
        call_user_func_array([$this, 'query'], $args);

        if (null === $this->id->value || 1 > $this->id->value) {
            $data = $this->fetchRecords('SELECT @insert_id as `insert_id`');
            if (1 > count($data)) {
                throw new Exception('New record id not found.');
            }
            $this->id->setInputValue($data[0]->insert_id);
        }
        $s1->close();
    }

    /**
     * @inheritDoc
     * @throws ContentValidationException Record id not provided.
     * @throws NotImplementedException Table name not set in inherited class.
     * @throws Exception
     */
    public function delete(): string
    {
        if (null === $this->id->value || 1 > $this->id->value) {
            throw new ContentValidationException("Id not provided.");
        }

        if (!$this->recordExists()) {
            return ("The requested record could not be found. \n");
        }

        $query = "DEL" . "ETE FROM `" . $this::getTableName() . "` WHERE `id` = ?";
        $this->query($query, 'i', $this->id->value);
        return ("The record has been deleted. \n");
    }

    /**
     * @inheritDoc
     * @throws ConnectionException On connection error.
     * @throws ConfigurationUndefinedException Database connection properties not set.
     * @throws Exception
     */
    protected function executeInsertQuery()
    {
        $fields = $this->formatDatabaseColumnList();

        /* build sql statement */
        $query = "INS" . "ERT INTO `" . $this::getTableName() . "` (`" .
            implode('`,`', array_map(function ($e) {
                return $e->key;
            }, $fields)) .
            "`) VALUES (" .
            implode(',', array_map(function () {
                return '?';
            }, $fields)) .
            ")";
        $type_str = implode('', array_map(function ($e) {
            return $e->type;
        }, $fields));
        $args = array_map(function ($e) {
            return $e->value;
        }, $fields);

        /* execute sql and store id value of the new record. */
        $this->query($query, $type_str, ...$args);
        // call_user_func_array([$this, 'query'], [$query, $type_str, $args]);
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

        /* build and execute sql statement */
        $query = "UPDATE `" . $this::getTableName() . "` SET " .
            implode(',', array_map(function ($e) {
                return "$e->key=?";
            }, $fields)) . " " .
            "WHERE id = ?;";
        $type_str = implode('', array_map(function ($e) {
                return $e->type;
            }, $fields)) . 'i';
        $args = array_map(function ($e) {
            return $e->value;
        }, $fields);
        $args[] = $this->id->value;
        $this->query($query, $type_str, ...$args);
    }

    /**
     * Default id input key getter.
     * @return string
     */
    public static function getDefaultIdKey(): string
    {
        return static::$default_id_key;
    }

    /**
     * Returns a list of all the properties of the object that represent foreign keys.
     * @return LinkedContent[]
     */
    protected function getLinkedContentPropertyList(): array
    {
        $lc = [];
        foreach ($this as $property) {
            if (is_object($property) && Validation::isSubclass($property, LinkedContent::class)) {
                $lc[] = $property;
            }
        }
        return $lc;
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
     * Retrieves the name of the record represented by the provided id value.
     * @param string $table Name of the table containing the records.
     * @param int $id ID value of the record.
     * @param string $field Optional column name containing the value to retrieve. Defaults to "name".
     * @param string $id_field Optional column name containing the id value to retrieve. Defaults to "id".
     * @throws InvalidQueryException|Exception SQL error raised running insert query.
     * @return string|null Retrieved value.
     */
    public function getTypeName(string $table, int $id, string $field = "name", string $id_field = "id"): ?string
    {
        if ($id < 1) {
            return null;
        }

        $query = "SEL" . "ECT `$field` AS `result` FROM `$table` WHERE `$id_field` = ?";
        $data = $this->fetchRecords($query, 'i', $id);
        $ret_value = $data[0]->result;
        return ($ret_value);
    }

    /**
     * Tests if query string is a procedure call.
     * @param string $query
     * @return bool
     */
    protected function isQueryProcedure(string $query): bool
    {
        return (strtolower(substr($query, 0, 5)) === 'call ');
    }

    /**
     * Create MySQL session variable to hold the value of the insert id resulting from a procedure call.
     * @return void
     * @throws ConfigurationUndefinedException|ConnectionException
     */
    protected function prepareInsertIdSession()
    {
        // insure there is a valid mysqli object
        $this->connectToDatabase();
        $stmt = $this->mysqli->prepare('SET @insert_id := ?');
        $stmt->bind_param('i', $this->id->value);
        $stmt->execute();
    }

    /**
     * Allow inherited classes to override prepared statement created in the object's read() routine.
     * @return array
     * @throws ConnectionException|ConfigurationUndefinedException|NotImplementedException
     */
    protected function formatRecordSelectPreparedStmt(): array
    {
        $fields = $this->formatDatabaseColumnList();
        $query = "SELECT `" .
            implode('`,`', array_map(function ($e) {
                return $e->key;
            }, $fields)) . "` " .
            "FROM `" . $this::getTableName() . "` " .
            "WHERE id = ?";
        return [$query, 'i', $this->id->value];
    }

    /**
     * Retrieves data from the database based on the internal properties of the
     * class instance. Sets the values of the internal properties of the class
     * instance using the database data.
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException Record id not set.
     * @throws RecordNotFoundException Requested record not available.
     * @throws NotImplementedException|InvalidQueryException|InvalidValueException
     */
    public function read(): SerializedContent
    {
        if ($this->id->value === null || $this->id->value < 1) {
            throw new ContentValidationException("Record id not set.");
        }

        try {
            $this->hydrateFromQuery(...$this->formatRecordSelectPreparedStmt());
        } catch (RecordNotFoundException $ex) {
            $error_msg = "The requested " . $this::getTableName() . " record could not be found.";
            throw new RecordNotFoundException($error_msg);
        }

        $this->readLinked();
        return $this;
    }

    /**
     * Retrieve all record data belonging to tables linked to this content type.
     * @throws InvalidValueException
     * @throws InvalidQueryException
     */
    public function readLinked()
    {
        $lc = $this->getLinkedContentPropertyList();
        foreach ($lc as $property) {
            $property->fetchLinkedListings();
        }
    }

    /**
     * Confirm that a record with id value matching the current id value of the object currently exists in the database.
     * @return bool True/False depending on if a matching record is found.
     * @throws NotImplementedException
     * @throws Exception
     */
    public function recordExists(): bool
    {
        if ($this->id->value === null || $this->id->value === '' || $this->id->value < 1) {
            return (false);
        }

        $query = "SEL" . "ECT EXISTS(SELECT 1 FROM `" . $this::getTableName() . "` WHERE `id` = ?) AS `record_exists`";
        $data = $this->fetchRecords($query, 'i', $this->id->value);
        return ((int)("0" . $data[0]->record_exists) === 1);
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws InvalidQueryException|InvalidValueException
     */
    public function save()
    {
        if (!$this->hasData()) {
            throw new ContentValidationException("Record has no data to save.");
        }

        $args = $this->generateUpdateQuery();
        if ($args) {
            if ($this->isQueryProcedure($args[0])) {
                $this->prepareInsertIdSession();
            }
            $this->query(...$args);
            $this->updateIdAfterCommit($args[0]);
        } else {
            if (is_numeric($this->id->value)) {
                $this->executeUpdateQuery();
            } else {
                $this->executeInsertQuery();
            }
        }

        $this->commitLinkedRecords();
    }

    /**
     * Chainable id input key setter.
     * @param string $key
     * @return $this
     */
    public function setIdKey(string $key): SerializedContent
    {
        $this->id->key = $key;
        return $this;
    }

    /**
     * Record id setter.
     * @param int $id
     * @return SerializedContent
     */
    public function setRecordId(int $id): SerializedContent
    {
        $this->id->setInputValue($id);
        $linked = $this->getLinkedContentPropertyList();
        foreach ($linked as $link) {
            $link->primary_id->setInputValue($id);
        }
        return $this;
    }

    /**
     * Tests for a valid parent record id. Throws ContentValidationException if the property value isn't current set.
     * @param string $msg Optional informational message to prepend to error message thrown when a valid parent id is not found.
     * @throws ContentValidationException
     */
    protected function testForParentID(string $msg = '')
    {
        if ($this->id->value === null || $this->id->value < 0) {
            $msg = ($msg) ? ("$msg ") : ('Could not perform operation. ');
            throw new ContentValidationException("{$msg}A parent record was not provided.");
        }
    }

    /**
     * Update the internal id property value after committing object property values to the database.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     */
    protected function updateIdAfterCommit(string $query)
    {
        if ($this->isQueryProcedure($query)) {
            // query was a procedure
            $data = $this->fetchRecords("SELECT @insert_id AS `id`");
            if (1 > count($data)) {
                throw new InvalidQueryException('Could not retrieve new record id.');
            }
            $this->id->setInputValue($data[0]->id);
        } elseif (!$this->id->hasData() && strtolower(substr($query, 0, 7)) === 'insert ') {
            // query was a query string
            $this->id->setInputValue($this->retrieveInsertID());
        }
    }
}