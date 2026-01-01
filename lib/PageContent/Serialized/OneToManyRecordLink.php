<?php
namespace Littled\PageContent\Serialized;

use Littled\Exception\FailedQueryException;
use Littled\Request\PrimaryKeyInput;
use Littled\Validation\Validation;


/**
 * Operations for a single record in a list of records linked to a single parent record.
 */
abstract class OneToManyRecordLink extends LinkedContent
{
    public function __construct()
    {
        parent::__construct();
        $this->id = (new PrimaryKeyInput())->setColumnName('id');
    }

    /**
     * @inheritdoc
     */
    public function getLinkedId(): int|null
    {
        return $this->id->value;
    }

    /**
     * Key of linked record.
     * @return string
     */
    public function getLinkedKey(): string
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
     * @return bool
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
        return $this->id->hasData();
    }

    /**
     * Test if any input properties of the object have their "is required" flag value set to TRUE.
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->parent_id->isRequired() || $this->id->isRequired();
    }

    /**
     * @inheritDoc
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
        $this->id->setInputValue($record_id);
        return $this;
    }

    public function setLinkedKey(string $key): static
    {
        $this->id->setKey($key);
        return $this;
    }
}