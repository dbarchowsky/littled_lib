<?php
namespace Littled\PageContent\Serialized;

use http\Env\Request;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\FailedQueryException;
use Littled\Request\ForeignKeyInput;
use Littled\Request\PrimaryKeyInput;
use Littled\Validation\Validation;


/**
 * Operations for a single record in a list of records linked to a single parent record.
 */
abstract class OneToManySerializedRecordLink extends LinkedContent
{
    public PrimaryKeyInput      $id;
    public ForeignKeyInput      $parent_id;
    protected SerializedContent $link;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->id = (new PrimaryKeyInput())->setColumnName('id');
        $this->parent_id = (new ForeignKeyInput())->setKey(LittledGlobals::ID_KEY);
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
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
            'WHERE ' . $this->parent_id->getColumnName('parent_id') . ' = ? ';
        return [$query, 'i', $this->parent_id->value];
    }

    /**
     * @inheritdoc
     */
    public function getLinkedId(): int|null
    {
        return $this->parent_id->value;
    }

    /**
     * Key of linked record.
     * @return string
     */
    public function getLinkedKey(): string
    {
        return $this->parent_id->getKey();
    }

    /**
     * Parent id value getter.
     * @return ?int
     */
    public function getParentId(): ?int
    {
        if (!isset($this->parent_id)) {
            return null;
        }
        return $this->parent_id->value;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKey(): string
    {
        return $this->id->getKey();
    }

    /**
     * @inheritdoc
     * Overrides parent to return true for primary key properties. This allows the $id property to be assigned a value
     * when the object is one element of a list of records linked to a parent in a one-to-many relationship.
     * The $id property value is skipped over for top-level objects.
     */
    protected function isInput(string $property, mixed $item, array &$used_keys): bool
    {
        $result = parent::isInput($property, $item, $used_keys);
        if ($result === false &&
            $this->propertyIsPrimaryKey($property)) {
            return true;
        }
        return $result;
    }

    /**
     * Tests if property is a primary key
     * @param string $property
     * return bool
     */
    protected function propertyIsPrimaryKey(string $property): bool
    {
        if (Validation::isSubclass($this->{$property}, PrimaryKeyInput::class) &&
            !$this->hasRecordsetPrefix() &&
            $this->{$property}->getColumnName('id') === 'id') {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function isReadyToRead(): bool
    {
        return $this->getParentId() > 0;
    }

    /**
     * Test if any input properties of the object have their "is required" flag value set to TRUE.
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->parent_id->isRequired();
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function recordExists(): bool
    {
        if (!$this->id->hasData()) {
            return false;
        }
        $query = 'SELECT EXISTS(SELECT 1 FROM `' . static::getTableName() . '` '.
            'WHERE `' . $this->id->getColumnName('id'). '` = ? ' .
            ') AS `record_exists`';
        $data = $this->fetchRecords($query, 'i', $this->id->value);
        return ((int)('0' . $data[0]->record_exists) === 1);
    }

    /**
     * @inheritdoc
     */
    public function setLinkedId(int|null $record_id): static
    {
        $this->parent_id->setInputValue($record_id);
        return $this;
    }

    /**
     * Parent record id setter.
     * @param int|null $record_id
     * @return $this
     */
    public function setParentId(int|null $record_id): static
    {
        $this->parent_id->setInputValue($record_id);
        return $this;
    }

    /**
     * Primary key setter.
     * @param string $key
     * @return $this
     */
    public function setParentKey(string $key): static
    {
        $this->parent_id->setKey($key);
        return $this;
    }
}