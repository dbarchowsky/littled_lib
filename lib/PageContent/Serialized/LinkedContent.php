<?php

namespace Littled\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\ForeignKeyInput;
use Littled\Validation\Validation;


abstract class LinkedContent extends SerializedContent
{
    use HydrateFieldOperations, InputOperations {
        applyInputKeyPrefix as traitApplyInputKeyPrefix;
    }

    public ForeignKeyInput      $primary_id;
    public ForeignKeyInput      $link_id;

    /**
     * @inheritDoc
     * @param string $prefix
     * @return $this
     */
    public function applyInputKeyPrefix(string $prefix): LinkedContent
    {
        $this->traitApplyInputKeyPrefix($prefix);
        return $this;
    }

    /**
     * Returns a list of the names of the object properties that represent content objects, i.e. derived from
     * SerializedContent.
     * @param string[] $exclude
     * @return array
     */
    protected function extractContentPropertiesList(array $exclude = []): array
    {
        $properties = [];
        foreach($this as $key => $property) {
            if (Validation::isSubClass($property, SerializedContent::class) &&
                !in_array($key, $exclude)) {
                $properties[] = $key;
            }
        }
        return $properties;
    }

    /**
     * Returns only the fields that map to the table managed by this class. The parent routine returns all RequestInput
     * properties of the object. This routine overrides that to subtract the fields from linked content object.
     * It also tests for any properties that may be pointers to child properties.
     * @param array $used_keys
     * @return QueryField[]
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    protected function extractPreparedStmtArgs(array &$used_keys = []): array
    {
        $fields = parent::extractPreparedStmtArgs($used_keys);

        // remove any properties that map to child content objects
        $content = $this->extractContentPropertiesList();
        for($i = count($fields)-1; $i >= 0; $i--) {
            if (in_array($fields[$i]->key, $content)) {
                unset($fields[$i]);
            }
        }

        // remove any properties that are pointers to child content object properties
        foreach($content as $property) {
            $cp = $this->$property->getInputPropertiesList();
            for($i = count($fields)-1; $i >= 0; $i--) {
                if (in_array($fields[$i]->key, $cp)) {
                    unset($fields[$i]);
                }
            }
        }

        // re-index the array before returning it
        return array_values($fields);
    }

    /**
     * @inheritDoc
     */
    protected function formatRecordSelectPreparedStmt(): array
    {
        if ($this->id->hasData() && $this->id->isDatabaseField()) {
            return parent::formatRecordSelectPreparedStmt();
        }
        $fields = $this->extractPreparedStmtArgs();
        $query = 'SELECT `' .
            implode('`,`', array_map(function ($e) {
                return $e->key;
            }, $fields)) . '` ' .
            'FROM `' . $this::getTableName() . '` ' .
            'WHERE ' . $this->primary_id->getColumnName('primary_id') . ' = ? '.
            'AND ' . $this->link_id->getColumnName('link_id') . ' = ? ';
        return [$query, 'ii', $this->primary_id->value, $this->link_id->value];
    }

    /**
     * Link id value getter.
     * @return ?int
     */
    public function getLinkId(): ?int
    {
        if (!isset($this->link_id)) {
            return null;
        }
        return $this->link_id->value;
    }

    /**
     * Primary id getter, i.e. the parent record's record id.
     * @return int|null
     */
    public function getPrimaryId(): ?int
    {
        if (!isset($this->primary_id->value)) {
            return null;
        }
        return $this->primary_id->value;
    }

    /**
     * Returns the record id property value if the database table has an explicit primary key. If the table does not
     * have a primary ky, the id of the parent record is returned (the $primary_id property value).
     * @return int|null
     */
    public function getRecordId(): ?int
    {
        if (!$this->id->isDatabaseField()) {
            return $this->getPrimaryId();
        }
        return $this->id->value;
    }

    /**
     * @inheritDoc
     */
    public function hasData(): bool
    {
        return $this->link_id->hasData() || $this->hasRecordData();
    }

    /**
     * @inheritDoc
     */
    protected function isReadyToRead(): bool
    {
        return $this->getPrimaryId() > 0 && $this->getLinkId() > 0;
    }

    /**
     * Combines two prepared statement argument lists.
     * @param array $base
     * @param ?array $args
     * @return array
     */
    protected static function mergeArgLists(array $base, ?array $args): array
    {
        $args ??= [];
        return array_merge($base, $args);
    }

    /**
     * Combines two prepared statement argument type strings.
     * @param string $base
     * @param ?string $arg_types
     * @return string
     * @todo confirm this method is needed
     */
    protected static function mergeArgTypeStrings(string $base, ?string $arg_types): string
    {
        return $base.$arg_types;
    }

    /**
     * @inheritDoc
     */
    public function read(): LinkedContent
    {
        if ($this->id->hasData() && $this->id->isDatabaseField()) {
            parent::read();
        }

        try {
            $this->hydrateFromQuery(...$this->formatRecordSelectPreparedStmt());
        } catch (RecordNotFoundException $ex) {
            $error_msg = "The requested " . $this::getTableName() . " record was not found.";
            throw new RecordNotFoundException($error_msg);
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @throws InvalidStateException
     */
    public function recordExists(): bool
    {
        if (isset($this->id) && $this->id->isDatabaseField() && $this->id->hasData()) {
            return parent::recordExists();
        }
        if (!$this->primary_id->hasData() || !$this->link_id->hasData()) {
            throw new InvalidStateException('Primary or link record id values not set.');
        }
        $query = 'SELECT EXISTS(SELECT 1 FROM `' . static::getTableName() . '` '.
            'WHERE `' . $this->primary_id->getColumnName('primary_id'). '` = ? ' .
            'AND `' . $this->link_id->getColumnName('link_id') . '` = ?' .
            ') AS `record_exists`';
        $data = $this->fetchRecords($query, 'ii', $this->primary_id->value, $this->link_id->value);
        return ((int)("0" . $data[0]->record_exists) === 1);
    }

    /**
     * Sets the index for all input properties of the object.
     * @param int $index
     * @return $this
     */
    public function setIndex(int $index): LinkedContent
    {
        $properties = $this->getInputPropertiesList();
        foreach ($properties as $property) {
            $this->$property->index = $index;
        }
        return $this;
    }

    /**
     * Link field name setter.
     * @param string $field
     * @return $this
     */
    public function setLinkFieldName(string $field): LinkedContent
    {
        $this->link_id->setColumnName($field);
        return $this;
    }

    /**
     * Foreign id setter.
     * @param int $record_id
     * @return LinkedContent
     * @throws InvalidStateException
     */
    public function setLinkId(int $record_id): LinkedContent
    {
        if (!isset($this->link_id)) {
            throw new InvalidStateException("Link id object is not initialized.");
        }
        $this->link_id->setInputValue($record_id);
        $properties = $this->extractContentPropertiesList();
        foreach($properties as $property) {
            $this->$property->setRecordId($record_id);
        }
        return $this;
    }

    /**
     * Link key setter.
     * @param string $key
     * @return $this
     */
    public function setLinkKey(string $key): LinkedContent
    {
        $this->link_id->setKey($key);
        return $this;
    }
    /**
     * Primary field name setter.
     * @param string $field
     * @return $this
     */
    public function setPrimaryFieldName(string $field): LinkedContent
    {
        $this->primary_id->setColumnName($field);
        return $this;
    }

    /**
     * Primary id setter.
     * @param int $record_id
     * @return $this
     * @throws InvalidStateException
     */
    public function setPrimaryId(int $record_id): LinkedContent
    {
        if (!isset($this->primary_id)) {
            throw new InvalidStateException("Primary id object is not initialized.");
        }
        $this->primary_id->setInputValue($record_id);
        return $this;
    }

    /**
     * Primary key setter.
     * @param string $key
     * @return $this
     */
    public function setPrimaryKey(string $key): LinkedContent
    {
        $this->primary_id->setKey($key);
        return $this;
    }
}