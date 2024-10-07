<?php

namespace Littled\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\DuplicateRecordException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Log\Log;
use Littled\Request\ForeignKeyInput;
use Littled\Validation\Validation;


abstract class OneToManyContentLink extends SerializedContentIO
{
    public bool                 $allow_duplicates = false;
    public ForeignKeyInput      $primary_id;
    /** @var LinkedContent[] */
    protected array             $records = [];
    protected static string     $content_class;

    /**
     * Push link object onto the list.
     * @param LinkedContent $link
     * @return $this
     * @throws DuplicateRecordException
     */
    public function addLink(LinkedContent $link): OneToManyContentLink
    {
        if (!$this->allow_duplicates &&
            $link->getLinkId() > 0 &&
            ($this->lookupLinkById($link->getLinkId()) !== false)) {
            throw new DuplicateRecordException(
                'A '. strtolower($this->getContentLabel()) .' record with id ' .
                $link->getLinkId().' already exists.');
        }
        $this->records[] = $link;
        return $this;
    }

    /**
     * Adds record id to existing list of record ids.
     * @param int|int[] $value
     * @return $this
     */
    public function addLinkId(array|int $value): OneToManyContentLink
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        foreach($value as $record_id) {
            if ($this->allow_duplicates) {
                $this->records[] = (new static::$content_class())->setLinkId($record_id);
            }
            elseif ($this->lookupLinkById($record_id) === false) {
                $this->records[] = (new static::$content_class())->setLinkId($record_id);
            }
        }
        return $this;
    }

    /**
     * Clears the list of records stored by the object (without making any changes to data in the database).
     * @return $this
     */
    public function clearLinks(): OneToManyContentLink
    {
        $this->records = [];
        return $this;
    }

    /**
     * @inheritDoc
     * @throws NotInitializedException|ConfigurationUndefinedException
     */
    public function collectRequestData(?array $src = null): void
    {
        $key = $this->getLinkKey();
        $src = $src ?? Validation::getDefaultInputSource();
        if (array_key_exists($key, $src)) {
            if (!isset(static::$content_class)) {
                throw new ConfigurationUndefinedException('Content class is not defined.');
            }
            if (is_array($src[$key])) {
                // processing an array of record ids, one for each linked record
                for($i = 0; $i < count($src[$key]); $i++) {
                    $this->records[$i] = new static::$content_class();
                    $this->records[$i]->setIndex($i)
                        ->collectRequestData($src);
                }
            }
            else {
                // single link value
                $o = new static::$content_class();
                $o->collectRequestData($src);
                $this->records[] = $o;
            }
        }
        parent::collectRequestData($src);
    }

    /**
     * Returns TRUE if $link_id value is found in the instance's current link id values.
     * @param int $link_id Link record id to look up.
     * @return bool TRUE if $link_id is found in the instance's current link id values.
     */
    protected function containsLinkId(int $link_id): bool
    {
        foreach($this->records as $record) {
            if ($record->getLinkId()  === $link_id) {
                return true;
            }
        }
        return false;
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
        foreach($this->records as $record) {
            $record->delete();
        }
        $this->records = [];
        /** @var SerializedContent $class */
        $class = static::$content_class;
        return 'The '.strtolower($class::getContentLabel()).' records were deleted. ';
    }

    /**
     * Deletes any stale links between the two tables.
     * @param int[] $link_ids
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidStateException
     * @throws RecordNotFoundException
     * @throws NotInitializedException
     */
    public function deleteStaleLinks(array $link_ids): void
    {
        if (!isset(static::$content_class)) {
            throw new NotInitializedException('Content class property has not been assigned a value.');
        }

        $table = call_user_func([static::$content_class, 'getTableName']);
        if (!trim($table)) {
            $err_msg = 'A table has not been assigned within ' . Log::getClassBaseName(static::$content_class) . '.';
            throw new NotInitializedException($err_msg);
        }
        if (!$this->primary_id->hasData()) {
            throw new InvalidStateException('A primary id value is not available.');
        }
        if (count($link_ids) < 1 || count($this->records) < 1) {
            return;
        }

        $stale_link_ids = array_map(fn($e): int => $e->getLinkId(), $this->records);
        $stale_link_ids = array_filter($stale_link_ids, function($e) use ($link_ids) { return !in_array($e, $link_ids); });

        $query = "DELETE FROM `$table` ".
            'WHERE `' . $this->records[0]->primary_id->getColumnName('primary_id') . '` = ? '.
            'AND `' . $this->records[0]->link_id->getColumnName('link_id') . '` '.
            'IN (' . str_repeat('?,', count($stale_link_ids)-1) . '?)';
        $this->query($query, str_repeat('i', count($stale_link_ids)+1), $this->primary_id->value, ...$stale_link_ids);

        for ($i = count($this->records) - 1; $i >= 0; $i--) {
            if (in_array($this->records[$i]->getLinkId(), $stale_link_ids)) {
                unset($this->records[$i]);
            }
        }
        $this->records = array_values($this->records);
    }

    /**
     * @inheritDoc
     * Doesn't return anything. Committing its data means saving data stored in its $records property.
     */
    protected function formatCommitQuery(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidStateException
     * @throws NotImplementedException
     */
    protected function formatRecordSelectPreparedStmt(): array
    {
        if (!isset($this->primary_id) || !$this->primary_id->hasData()) {
            $err_msg = 'Linked records cannot be retrieved. A parent record id value is not available.';
            throw new InvalidStateException($err_msg);
        }
        $c = new static::$content_class();
        $fields = $c->extractPreparedStmtArgs();
        $table = call_user_func([static::$content_class, 'getTableName']);
        $query = 'SELECT `'.
            join('`, `', array_map(fn($e): string => $e->key, $fields)).
            "` FROM `$table` ".
            'WHERE `'.$this->primary_id->getColumnName('parent_id').'` = ? ';
        return [$query, 'i', $this->primary_id->value];
    }

    /**
     * Content class property value getter.
     * @return string
     */
    public static function getContentClass(): string
    {
        return static::$content_class ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getContentLabel(): string
    {
        /** @var LinkedContent $class */
        $class = static::$content_class;
        return $class::getContentLabel();
    }

    /**
     * Returns the key of the linked records' link id.
     * @return string
     * @throws NotInitializedException
     */
    protected function getLinkKey(): string
    {
        if (isset($this->records) && count($this->records) > 0) {
            return $this->records[0]->link_id->key;
        }
        else {
            if (!isset(static::$content_class)) {
                throw new NotInitializedException('Content class property has not been assigned a value.');
            }
            $o = new static::$content_class();
            return $o->link_id->key;
        }
    }

    /**
     * Primary id value getter.
     * @return int|null
     */
    public function getPrimaryId(): ?int
    {
        if (!isset($this->primary_id)) {
            return null;
        }
        return $this->primary_id->value;
    }

    /**
     * Record id value getter. Alias for getPrimaryId().
     * @return int|null
     */
    public function getRecordId(): ?int
    {
        return $this->getPrimaryId();
    }

    /**
     * Tests if any of the records currently loaded in the object has data to be committed to the database.
     * @return bool
     */
    public function hasRecordData(): bool
    {
        foreach($this->records as $record) {
            if ($record->hasData()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function isReadyToRead(): bool
    {
        return $this->getRecordId() > 0 && !$this->hasRecordData();
    }

    /**
     * Returns flag indicating if the "is required" flag for this property has been turned off.
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->primary_id->isRequired();
    }

    /**
     * With no argument, returns all records currently attached to the object. With an argument, returns a specific
     * record in the list, at the $index position.
     * @param int|null $index
     * @return LinkedContent|LinkedContent[]
     * @throws InvalidValueException
     */
    public function items(?int $index = null): LinkedContent|array
    {
        if ($index === null) {
            return $this->records;
        }
        if (count($this->records) < $index+1) {
            throw new InvalidValueException('Requested index is out of bounds.');
        }
        return $this->records[$index];
    }

    /**
     * Allow duplicates flag value getter.
     * @return bool
     */
    public function getAllowDuplicates(): bool
    {
        return $this->allow_duplicates;
    }

    /**
     * Returns all currently stored link record id values in an array
     * @return int[]
     */
    public function getLinkIds(): array
    {
        return array_map(fn($e): int => (int)$e->id->safeValue(), $this->records);
    }

    /**
     * Returns the index of the record whose link id value matches the supplied value.
     * @param int $link_id
     * @return false|int
     */
    public function lookupLinkById(int $link_id): bool|int
    {
        for($i = 0; $i < count($this->records); $i++) {
            if ($this->records[$i]->link_id->value === $link_id) {
                return $i;
            }
        }
        return false;
    }

    /**
     * Returns a new instance of the linked content record.
     * @return LinkedContent
     * @throws NotInitializedException
     */
    public function newContentInstance(): LinkedContent
    {
        if (!isset(static::$content_class)) {
            throw new NotInitializedException('Content class property has not been assigned a value.');
        }
        return (new static::$content_class());
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException|ConnectionException
     * @throws InvalidStateException|InvalidQueryException
     * @throws NotImplementedException
     */
    public function read(): OneToManyContentLink
    {
        $data = $this->fetchRecords(...$this->formatRecordSelectPreparedStmt());
        foreach($data as $row) {
            $o = new static::$content_class();
            $o->hydrateFromRecordsetRow($row);
            $this->records[] = $o;
        }
        return $this;
    }

    /**
     * Remove records with link id values matching values in $link_ids from the current list of linked records.
     * @param int|int[] $link_ids
     * @return $this
     */
    public function removeLink(array|int $link_ids): OneToManyContentLink
    {
        if (!is_array($link_ids)) {
            $link_ids = [$link_ids];
        }
        for($i = count($this->records)-1; $i >= 0; $i--) {
            if (in_array($this->records[$i]->getLinkId(), $link_ids)) {
                unset($this->records[$i]);
            }
        }
        // re-index
        $this->records = array_values($this->records);
        return $this;
    }


    /**
     * @inheritDoc
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function save(): void
    {
        foreach($this->records as $record) {
            $record->save();
        }
    }

    /**
     * Allow duplicates flag setter.
     * @param bool $flag
     * @return $this
     */
    public function setAllowDuplicates(bool $flag): OneToManyContentLink
    {
        $this->allow_duplicates = $flag;
        return $this;
    }

    /**
     * Primary field name setter.
     * @param string $field
     * @return $this
     */
    public function setPrimaryFieldName(string $field): OneToManyContentLink
    {
        $this->primary_id->setColumnName($field);
        return $this;
    }

    /**
     * Primary id setter.
     * @throws NotInitializedException|InvalidStateException
     */
    public function setPrimaryId(int $record_id): OneToManyContentLink
    {
        if (!isset($this->primary_id)) {
            throw new NotInitializedException('Primary id object is not initialized.');
        }
        $this->primary_id->setInputValue($record_id);
        foreach($this->records as $record) {
            $record->setPrimaryId($record_id);
        }
        return $this;
    }

    /**
     * Primary key setter.
     * @param string $key
     * @return $this
     */
    public function setPrimaryKey(string $key): OneToManyContentLink
    {
        $this->primary_id->setKey($key);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws NotInitializedException|InvalidStateException
     */
    public function setRecordId(int $record_id): OneToManyContentLink
    {
        return $this->setPrimaryId($record_id);
    }

    /**
     * @inheritDoc
     */
    public function validateInput(array $exclude_properties = []): void
    {
        try {
            parent::validateInput($exclude_properties);
        } catch (ContentValidationException) {
            /* continue */
        }

        foreach($this->records as $record) {
            try {
                $record->validateInput($exclude_properties);
            } catch (ContentValidationException) {
                $this->addValidationError($record->validationErrors());
            }
        }

        if ($this->hasValidationErrors()) {
            throw new ContentValidationException($this->validation_message);
        }
    }
}