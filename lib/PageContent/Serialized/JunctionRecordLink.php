<?php
namespace Littled\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidStateException;
use Littled\Request\ForeignKeyInput;
use Littled\Request\PrimaryKeyInput;


abstract class JunctionRecordLink extends LinkedContent
{
    public ForeignKeyInput|PrimaryKeyInput $link_id;

    public function __construct()
    {
        parent::__construct();
        $this->link_id = (new ForeignKeyInput())
            ->setLabel('Link id')
            ->setKey('linkId')
            ->setAsNotRequired();
    }

    /**
     * Disable database field state for the columns that make up the index for the linked record.
     * @return array
     */
    private function bypassForeignKeyIndexFields(): array
    {
        $parent_db = $this->parent_id->isDatabaseField();
        $link_db = $this->link_id->isDatabaseField();
        $this->parent_id->setIsDatabaseField(false);
        $this->link_id->setIsDatabaseField(false);
        return [$parent_db, $link_db];
    }

    /**
     * @inheritdoc
     * @throws ConfigurationUndefinedException
     */
    public function delete(): string
    {
        try {
            if (!$this->recordExists()) {
                return '';
            }
        }
        catch (ConfigurationUndefinedException|InvalidStateException) {
            return '';
        }
        $query = 'DELETE FROM `' . self::getTableName() . '` ' .
            'WHERE `' . $this->parent_id->getColumnName('parent_id') . '` = ? ' .
            'AND `' . $this->link_id->getColumnName('link_id') . '` = ?';
        $this->query($query, 'ii', $this->parent_id->value, $this->link_id->value);
        return 'The ' . strtolower($this->getContentLabel()) . ' record was successfully deleted.';
    }

    /**
     * @inheritDoc
     */
    protected function formatRecordSelectQuery(): array
    {
        // Replace the where clause that looks up record by the primary key with
        // a where clause that looks up record by the parent id and link id.
        $state = $this->bypassForeignKeyIndexFields();
        [$query] = parent::formatRecordSelectQuery();
        $this->restoreForeignKeyIndexFields($state);

        $query = substr($query, 0, strpos($query, 'WHERE')) .
            'WHERE `'.$this->parent_id->getColumnName('parent_id').'` = ? ' .
            'AND `'.$this->link_id->getColumnName('link_id').'` = ?';
        return [$query, 'ii', $this->parent_id->value, $this->link_id->value];
    }

    /**
     * @inheritdoc
     */
    public function getLinkedId(): int|null
    {
        return $this->link_id->value;
    }

    /**
     * @inheritDoc
     */
    public function getLinkedKey(): string
    {
        return $this->link_id->key;
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
        return $this->parent_id->hasData() && $this->link_id->hasData();
    }

    /**
     * Test if any input properties of the object have their "is required" flag value set to TRUE.
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->parent_id->isRequired() || $this->link_id->isRequired();
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws InvalidStateException
     */
    public function recordExists(): bool
    {
        if (isset($this->id) && $this->id->isDatabaseField() && $this->id->hasData()) {
            return parent::recordExists();
        }
        if (!$this->parent_id->hasData() || !$this->link_id->hasData()) {
            throw new InvalidStateException('Primary or link record id values not set.');
        }
        $query = 'SELECT EXISTS(SELECT 1 FROM `' . static::getTableName() . '` '.
            'WHERE `' . $this->parent_id->getColumnName('parent_id'). '` = ? ' .
            'AND `' . $this->link_id->getColumnName('link_id') . '` = ?' .
            ') AS `record_exists`';
        $data = $this->fetchRecords($query, 'ii', $this->parent_id->value, $this->link_id->value);
        return ((int)('0' . $data[0]->record_exists) === 1);
    }

    /**
     * Restore the database field state for the columns that make up the index for the linked record.
     * @param array $state
     */
    private function restoreForeignKeyIndexFields(array $state): void
    {
        $this->parent_id->setIsDatabaseField($state[0]);
        $this->link_id->setIsDatabaseField($state[1]);
    }

    /**
     * @inheritDoc
     */
    public function setAsNotRequired(): static
    {
        parent::setAsNotRequired();
        $this->link_id->setAsNotRequired();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAsRequired(): static
    {
        parent::setAsRequired();
        $this->link_id->setAsRequired();
        return $this;
    }

    /**
     * Link field name setter.
     * @param string $name
     * @return $this
     */
    public function setLinkedColumnName(string $name): static
    {
        $this->link_id->setColumnName($name);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setLinkedId(?int $record_id): static
    {
        $this->link_id->setInputValue($record_id);
        $properties = $this->extractContentPropertiesList();
        foreach($properties as $property) {
            $this->$property->setRecordId($record_id);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLinkedKey(string $key): static
    {
        $previous_key = $this->link_id->getKey();

        $this->link_id->setKey($key);

        // update the key value of the primary key property of any content properties representing the linked record
        $p = $this->getContentPropertiesList();
        foreach($p as $property) {
            if ($this->$property->id->getKey() === $previous_key) {
                $this->$property->id->setKey($key);
                break;
            }
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected static function stripKeyFields(array $fields): array
    {
        return array_filter($fields, function($e) {
            return !$e->is_pk && !$e->is_fk;
        });
    }

    /**
     * @inheritDoc
     */
    protected static function stripPrimaryKeyFields(array $fields): array
    {
        // Overrides parent method to take no action.
        // This class uses the parent_id and link_id properties to update records. Leave the link_id property
        // in the collection in case it is of PrimaryKeyInput type
        return $fields;
    }
}