<?php
namespace Littled\PageContent\Serialized;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\DuplicateRecordException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\ReadException;
use Littled\Exception\RecordNotFoundException;
use Littled\Log\Log;
use Littled\Request\PrimaryKeyInput;
use Littled\Validation\Validation;
use Exception;


/**
 * Routines for fetching and committing database records.
 */
abstract class SerializedContent extends SerializedContentIO
{
    /** @var PrimaryKeyInput Record id. */
    public PrimaryKeyInput $id;
    protected static string $default_id_key = LittledGlobals::ID_KEY;

    /**
     * @param ?int $id Optional initial value to assign to the object's id property.
     */
    function __construct(?int $id = null)
    {
        parent::__construct();
        $this->id = new PrimaryKeyInput('id', static::$default_id_key, false, $id);
    }

    /**
     * Add type id to the current stack.
     * @param int|int[] $link_ids
     * @return $this
     * @throws InvalidStateException
     * @throws InvalidTypeException
     * @throws InvalidValueException
     * @throws NotInitializedException
     */
    protected function addLink(array|int $link_ids, string $links_property): SerializedContent
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
        elseif(!Validation::isSubclass($this->$links_property, SerializedRecordList::class)) {
            $err_msg = "Link property \"$links_property\" is not a list.";
            throw new InvalidTypeException($err_msg);
        }

        $content_class = $this->$links_property->getContentClass();

        if (!is_array($link_ids)) {
            $link_ids = [$link_ids];
        }
        if (!$this->$links_property->getAllowDuplicates()) {
            $link_ids = array_unique($link_ids);
        }
        try {
            foreach ($link_ids as $link_id) {
                try {
                    $this->$links_property->addLink((new $content_class())
                        ->shareConnection($this)
                        ->setParentId($this->getRecordId())
                        ->setLinkedId($link_id));
                } catch (DuplicateRecordException) {
                    /* continue */
                }
            }
        } catch(ConfigurationUndefinedException) {
            /* ignore */
        }
        return $this;
    }

    /**
     * Clears the value of the record id.
     * @return $this
     */
    public function clearRecordId(): static
    {
        $this->id->value = null;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function commitSaveQuery(string $query, string $arg_types = '', ...$args): void
    {
        $this->connectToDatabase();
        $s1 = $this->mysqli->prepare('SET @insert_id = ?');
        $s1->bind_param('i', $this->id->value);
        $s1->execute();

        $this->query($query, $arg_types, ...$args);

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
     * @throws FailedQueryException
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

        try {
            $query = 'DELETE FROM `' . $this::getTableName() . '` WHERE `id` = ?';
            $this->query($query, 'i', $this->id->value);
            return ("The record has been deleted. \n");
        }
        catch(FailedQueryException $e) {
            $err_msg = 'Error deleting the record: [' . Log::getClassBaseName($e::class) . '] ' . $e->getMessage();
            throw new FailedQueryException($err_msg);
        }
    }

    /**
     * @inheritDoc
     */
    protected function executeCommitQuery(): void
    {
        $this->prepareInsertIdSession();
        parent::executeCommitQuery();
    }

    /**
     * @return array
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
            'ON DUPLICATE KEY UPDATE ';

        // strip out primary key variables since we're using the previously assigned @insert_id SQL session variable
        $fields = static::stripPrimaryKeyFields($fields);

        $update_fields = static::stripKeyFields($fields);
        $query .= join(', ', array_map(fn($e): string => "`$e->key` = VALUE(`$e->key`)", $update_fields));
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
     */
    protected function formatRecordSelectQuery(): array
    {
        $fields = $this->extractPreparedStmtArgs();
        $query = 'SELECT `' .
            implode('`,`', array_map(function ($e) {
                return $e->key;
            }, $fields)) . '` ' .
            'FROM `' . $this::getTableName() . '` ' .
            'WHERE id = ?';
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
     * Record id value getter
     * @return int|null
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
     * @throws FailedQueryException
     */
    public function getTypeName(string $table, int $id, string $field = 'name', string $id_field = 'id'): ?string
    {
        if ($id < 1) {
            return null;
        }

        $query = "SELECT `$field` AS `result` FROM `$table` WHERE `$id_field` = ?";
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
     * Tests if this class has a primary key. It will not if it represents a one-to-many link for two other tables
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
     * Tests if the query string is a procedure call.
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
     * Create the MySQL session variable to hold the value of the insert id resulting from a procedure call.
     * @return void
     * @throws FailedQueryException
     */
    protected function prepareInsertIdSession(): void
    {
        try {
            // insure there is a valid mysqli object
            $this->connectToDatabase();
            $stmt = $this->mysqli->prepare('SET @insert_id := ?');
            $stmt->bind_param('i', $this->id->value);
            $stmt->execute();
        }
        catch (ConnectionException|
        ConfigurationUndefinedException $e) {
            $msg = 'Error commiting a record. [' . Log::getClassBaseName($e::class) . '] ' . $e->getMessage();
            throw new FailedQueryException($msg);
        }
    }

    /**
     * Retrieves data from the database based on the internal properties of the
     * class instance. Sets the values of the internal properties of the class
     * instance using the database data.
     * @return $this
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     * @throws ReadException
     */
    public function read(): static
    {
        if (!$this->id->hasData()) {
            throw new ReadException('Record id not set.');
        }

        try {
            $this->hydrateFromQuery(...$this->formatRecordSelectQuery());
        }
        catch (RecordNotFoundException) {
            $error_msg = 'The requested ' . strtolower(static::getContentLabel()) . ' record was not found.';
            throw new RecordNotFoundException($error_msg);
        }

        $this->readLinked();
        return $this;
    }

    /**
     * Confirm that a record with the id value matching the current id value of the object currently exists in the database.
     * @return bool True/False depending on if a matching record is found.
     * @throws FailedQueryException
     */
    public function recordExists(): bool
    {
        if (!$this->id->hasData()) {
            return (false);
        }

        try {
            $query = 'SELECT EXISTS(SELECT 1 FROM `' . static::getTableName() . '` WHERE `id` = ?) AS `record_exists`';
            $data = $this->fetchRecords($query, 'i', $this->id->value);
            return ((int)('0' . $data[0]->record_exists) === 1);
        }
        catch (FailedQueryException $e) {
            $msg = 'Error testing for record. [' . Log::getClassBaseName($e::class) . '] ' . $e->getMessage();
            throw new FailedQueryException($msg);
        }
    }

    /**
     * @inheritDoc
     * @throws FailedQueryException
     * @throws ContentValidationException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function save(): void
    {
        if (!$this->hasData()) {
            throw new ContentValidationException('Record has no data to save.');
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
     */
    public function setRecordId(?int $record_id): static
    {
        $this->id->setInputValue($record_id);
        $lp = $this->getLinkedContent();
        foreach($lp as $property) {
            if (method_exists($property, 'setParentId')) {
                $property->setParentId($record_id);
            }
        }
        return $this;
    }

    /**
     * Strips any fields that should not be included in the ON DUPLICATE KEY UPDATE statement.
     * @param QueryField[] $fields
     * @return QueryField[]
     */
    protected static function stripKeyFields(array $fields): array
    {
        return static::stripPrimaryKeyFields($fields);
    }

    /**
     * Strips all primary key fields from a fields collection.
     * @param QueryField[] $fields
     * @return QueryField[]
     */
    protected static function stripPrimaryKeyFields(array $fields): array
    {
        return array_filter($fields, function($e) {
            return !$e->is_pk;
        });
    }

    /**
     * Tests for a valid parent record id. Throws ContentValidationException if the property value isn't current set.
     * @param string $msg Optional informational message to prepend to an error message thrown when a valid parent id is not found.
     * @throws ConfigurationUndefinedException
     */
    protected function testForParentID(string $msg = ''): void
    {
        if ($this->id->value === null || $this->id->value < 0) {
            $msg = ($msg) ? ("$msg ") : ('Could not perform operation. ');
            throw new ConfigurationUndefinedException("{$msg}A parent record was not provided.");
        }
    }
}