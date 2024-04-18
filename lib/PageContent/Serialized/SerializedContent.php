<?php

namespace Littled\PageContent\Serialized;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\DuplicateRecordException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Log\Log;
use Littled\Request\PrimaryKeyInput;
use Exception;
use Littled\Validation\Validation;
use mysqli;

/**
 * Routines for fetching and committing database records.
 */
abstract class SerializedContent extends SerializedContentIO
{
    /** @var PrimaryKeyInput Record id. */
    public PrimaryKeyInput $id;
    protected static string $default_id_key = LittledGlobals::ID_KEY;

    /**
     * SerializedContent constructor.
     * @param ?int $id Optional initial value to assign to the object's id property.
     */
    function __construct(?int $id = null)
    {
        parent::__construct();
        $this->id = new PrimaryKeyInput('id', static::$default_id_key, false, $id);
    }


    /**
     * Add type id to current stack.
     * @param int|int[] $link_ids
     * @return $this
     * @throws DuplicateRecordException
     * @throws InvalidStateException
     * @throws InvalidTypeException
     * @throws InvalidValueException
     */
    protected function addLink($link_ids, string $links_property): SerializedContent
    {
        if (!property_exists($this, $links_property)) {
            $err_msg = "Link property \"$links_property\" not found on ".Log::getClassBaseName(get_class($this)).'.';
            throw new InvalidValueException($err_msg);
        }
        elseif(!isset($this->$links_property)) {
            $err_msg = "Link property \"$links_property\" is not initialized on " .
                Log::getClassBaseName(get_class($this)). '.';
            throw new InvalidStateException($err_msg);
        }
        elseif(!Validation::isSubclass($this->$links_property, OneToManyContentLink::class)) {
            $err_msg = "Link property \"$links_property\" is not a one-to-many link.";
            throw new InvalidTypeException($err_msg);
        }

        $content_class = $this->$links_property->getContentClass();

        if (!is_array($link_ids)) {
            $link_ids = [$link_ids];
        }
        try {
            foreach ($link_ids as $link_id) {
                /** @var LinkedContent $link */
                $link = (new $content_class())
                    ->setMySQLi(static::getMysqli())
                    ->setLinkId($link_id);
                if ($this->id->hasData()) {
                    $link->setPrimaryId($this->getRecordId());
                }
                try {
                    $this->$links_property->addLink($link);
                } catch (DuplicateRecordException $e) {
                    /* continue */
                }
            }
        } catch(ConfigurationUndefinedException $e) {
            /* ignore */
        }
        return $this;
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
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidStateException
     * @throws RecordNotFoundException
     */
    public function delete(): string
    {
        if (null === $this->id->value || 1 > $this->id->value) {
            throw new InvalidStateException('Id not provided.');
        }

        if (!$this->recordExists()) {
            throw new RecordNotFoundException("The requested record could not be found. \n");
        }

        $query = "DEL" . "ETE FROM `" . $this::getTableName() . "` WHERE `id` = ?";
        $this->query($query, 'i', $this->id->value);
        return ("The record has been deleted. \n");
    }

    /**
     * @inheritDoc
     */
    protected function executeCommitQuery()
    {
        $this->prepareInsertIdSession();
        parent::executeCommitQuery();
        if (!$this->id->hasData()) {
            // retrieve and store the new record id after performing an insert.
            $this->updateIdAfterCommit();
        }
    }

    /**
     * @return array
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    protected function formatCommitQuery(): array
    {
        // record id value managed using SQL @insert_id session variable
        $fields = $this->extractPreparedStmtArgs();

        $keys = array_map(function ($e) {
            return $e->key;
        }, $fields);
        $query = 'INS' . 'ERT INTO `' . static::getTableName() . '` (`' .
            implode('`,`', $keys) .
            '`) VALUES (' . ($this->hasPrimaryKey() ? '@insert_id' : '?') . ',' .
            rtrim(str_repeat('?,', count($fields) - 1), ',').
            ') '.
            'ON DUPLICATE KEY UPDATE '.
            join(', ', array_map(fn($e): string => "`$e` = VALUE(`$e`)", $keys));

        // strip out primary key variables since we're using previously assigned @insert_id SQL session variable
        $fields = array_filter($fields, function($e) {
            return !$e->is_pk;
        });
        $type_str = implode('', array_map(function ($e) {
            return $e->type;
        }, $fields));

        $args = array_map(function ($e) {
            return $e->value;
        }, $fields);
        return [$query, $type_str, ...$args];
    }

    /**
     * @inheritDoc
     * @throws ConnectionException|ConfigurationUndefinedException
     */
    protected function formatRecordSelectPreparedStmt(): array
    {
        $fields = $this->extractPreparedStmtArgs();
        $query = "SELECT `" .
            implode('`,`', array_map(function ($e) {
                return $e->key;
            }, $fields)) . "` " .
            "FROM `" . $this::getTableName() . "` " .
            "WHERE id = ?";
        return [$query, 'i', $this->id->value];
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
     * Returns list of all one-to-many linked properties of the object.
     * @return OneToManyContentLink[]
     */
    protected function getOneToManyLinkedProperties(): array
    {
        $p = [];
        foreach($this as $property) {
            if (Validation::isSubclass($property, OneToManyContentLink::class)) {
                $p[] = $property;
            }
        }
        return $p;
    }

    /**
     * Record id value getter
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
     * @return string|null Retrieved value.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
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
     * @inheritDoc
     */
    public function hasData(): bool
    {
        return $this->id->hasData() || $this->hasRecordData();
    }

    /**
     * Tests if this class has a primary key. It will not if it represents one-to-many link for two other tables
     * if that link doesn't have a record id of its own.
     * @return bool
     */
    protected function hasPrimaryKey(): bool
    {
        return $this->id->isDatabaseField();
    }

    /**
     * Same as hasData() but doesn't include the object's $id property value.
     * @return bool
     */
    abstract protected function hasRecordData(): bool;

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
     * @inheritDoc
     */
    protected function isReadyToRead(): bool
    {
        return $this->getRecordId() > 0 && !$this->hasRecordData();
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
     * Retrieves data from the database based on the internal properties of the
     * class instance. Sets the values of the internal properties of the class
     * instance using the database data.
     * @return $this
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException Record id not set.
     * @throws RecordNotFoundException Requested record not available.
     * @throws NotImplementedException|InvalidQueryException
     */
    public function read(): SerializedContent
    {
        if (!$this->id->hasData()) {
            throw new ContentValidationException("Record id not set.");
        }

        try {
            $this->hydrateFromQuery(...$this->formatRecordSelectPreparedStmt());
        } catch (RecordNotFoundException $ex) {
            $error_msg = "The requested " . $this::getTableName() . " record was not found.";
            throw new RecordNotFoundException($error_msg);
        }

        $this->readLinked();
        return $this;
    }

    /**
     * Confirm that a record with id value matching the current id value of the object currently exists in the database.
     * @return bool True/False depending on if a matching record is found.
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     */
    public function recordExists(): bool
    {
        if (!$this->id->hasData()) {
            return (false);
        }

        $query = 'SELECT EXISTS(SELECT 1 FROM `' . static::getTableName() . '` WHERE `id` = ?) AS `record_exists`';
        $data = $this->fetchRecords($query, 'i', $this->id->value);
        return ((int)("0" . $data[0]->record_exists) === 1);
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws RecordNotFoundException
     */
    public function save()
    {
        if (!$this->hasData()) {
            throw new ContentValidationException("Record has no data to save.");
        }
        if ($this->id->hasData() && !$this->id->isDatabaseField() && $this->recordExists()) {
            throw new RecordNotFoundException('A matching record is not available to update.');
        }

        $this->executeCommitQuery();
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
     * @inheritDoc
     * @return SerializedContent
     */
    public function setMySQLi(mysqli $mysqli): SerializedContent
    {
        parent::setMySQLi($mysqli);
        foreach($this as $property => $value) {
            if($this->$property instanceof SerializedContent) {
                $this->$property->setMySQLi($this::getMysqli());
            }
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @throws InvalidStateException
     */
    public function setRecordId(int $record_id): SerializedContent
    {
        $this->id->setInputValue($record_id);
        $otm = $this->getOneToManyLinkedProperties();
        foreach($otm as $property) {
            try {
                $property->setPrimaryId($record_id);
            } catch (NotInitializedException $e) {
                /* ignore & continue */
            }
        }
        return $this;
    }

    /**
     * Tests for a valid parent record id. Throws ContentValidationException if the property value isn't current set.
     * @param string $msg Optional informational message to prepend to error message thrown when a valid parent id is not found.
     * @throws InvalidStateException
     */
    protected function testForParentID(string $msg = '')
    {
        if ($this->id->value === null || $this->id->value < 0) {
            $msg = ($msg) ? ("$msg ") : ('Could not perform operation. ');
            throw new InvalidStateException("{$msg}A parent record was not provided.");
        }
    }
}