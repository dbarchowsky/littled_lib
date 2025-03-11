<?php
namespace Littled\PageContent\Serialized;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidStateException;
use Littled\Exception\InvalidValueException;
use Littled\Request\ForeignKeyInput;


abstract class ManyToManySerializedRecordLink extends LinkedContent
{
    public ForeignKeyInput $primary_id;
    public ForeignKeyInput $link_id;

    /**
     * Class constructor
     * @param int|null $id
     */
    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->primary_id = new ForeignKeyInput('Primary id', LittledGlobals::ID_KEY, false);
        $this->link_id = new ForeignKeyInput('Link id', 'link_id', false);
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
            'WHERE `' . $this->primary_id->getColumnName() . '` = ? ' .
            'AND `' . $this->link_id->getColumnName() . '` = ?';
        $this->query($query, 'ii', $this->primary_id->value, $this->link_id->value);
        return 'The ' . strtolower($this->getContentLabel()) . ' record was successfully deleted.';
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws InvalidStateException
     */
    protected function formatRecordSelectPreparedStmt(): array
    {
        if ($this->id->isDatabaseField() && $this->id->value > 0) {
            return parent::formatRecordSelectPreparedStmt();
        }
        if ($this->primary_id->value > 0 && $this->link_id->value > 0) {
            $fields = $this->extractPreparedStmtArgs();
            $table = self::getTableName();
            $query = 'SELECT `'.
                join('`,`', array_map(fn($e): string => $e->key, $fields)).
                "` FROM `$table` ".
                'WHERE `'.$this->primary_id->getColumnName('parent_id').'` = ? ' .
                'AND `'.$this->link_id->getColumnName('link_id').'` = ?';
            return [$query, 'ii', $this->primary_id->value, $this->link_id->value];
        }
        throw new InvalidValueException('Unable to retrieve record. Record id value not specified.');
    }

    /**
     * @inheritdoc
     */
    public function getLinkedId(): int|null
    {
        return $this->getLinkId();
    }

    /**
     * Link key value getter.
     * @return string
     */
    public function getLinkedKey(): string
    {
        if (!isset($this->link_id)) {
            return '';
        }
        return $this->link_id->key;
    }

    /**
     * Link id value getter.
     * @return int|null
     */
    public function getLinkId(): int|null
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
     * @inheritdoc
     */
    public function getPrimaryKey(): string
    {
        return $this->primary_id->getKey();
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
        return $this->getPrimaryId() > 0 && $this->getLinkedId() > 0;
    }

    /**
     * Test if any input properties of the object have their "is required" flag value set to TRUE.
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->primary_id->isRequired() || $this->link_id->isRequired();
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
        if (!$this->primary_id->hasData() || !$this->link_id->hasData()) {
            throw new InvalidStateException('Primary or link record id values not set.');
        }
        $query = 'SELECT EXISTS(SELECT 1 FROM `' . static::getTableName() . '` '.
            'WHERE `' . $this->primary_id->getColumnName('primary_id'). '` = ? ' .
            'AND `' . $this->link_id->getColumnName('link_id') . '` = ?' .
            ') AS `record_exists`';
        $data = $this->fetchRecords($query, 'ii', $this->primary_id->value, $this->link_id->value);
        return ((int)('0' . $data[0]->record_exists) === 1);
    }

    /**
     * @inheritDoc
     */
    public function setAsNotRequired(): static
    {
        $this->primary_id->setAsOptional();
        $this->link_id->setAsOptional();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAsRequired(): static
    {
        $this->primary_id->setAsRequired();
        $this->link_id->setAsRequired();
        return $this;
    }

    /**
     * Link field name setter.
     * @param string $field
     * @return $this
     */
    public function setLinkFieldName(string $field): static
    {
        $this->link_id->setColumnName($field);
        return $this;
    }

    /**
     * @inheritdoc
     * @throws InvalidStateException
     */
    public function setLinkedId(?int $record_id): static
    {
        return $this->setLinkId($record_id);
    }

    /**
     * Foreign id setter.
     * @param ?int $record_id
     * @return $this
     * @throws InvalidStateException
     */
    public function setLinkId(?int $record_id): static
    {
        if (!isset($this->link_id)) {
            throw new InvalidStateException('Link id object is not initialized.');
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
     * @throws ConfigurationUndefinedException
     */
    public function setLinkedKey(string $key): static
    {
        $previous_key = $this->link_id->getKey();

        if (!isset($this->link_id)) {
            throw new ConfigurationUndefinedException('Link property not set.');
        }
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
     * Primary field name setter.
     * @param string $field
     * @return $this
     */
    public function setPrimaryFieldName(string $field): static
    {
        $this->primary_id->setColumnName($field);
        return $this;
    }

    /**
     * Primary id setter.
     * @param int|null $record_id
     * @return $this
     * @throws InvalidStateException
     */
    public function setPrimaryId(int|null $record_id): static
    {
        if (!isset($this->primary_id)) {
            throw new InvalidStateException('Primary id object is not initialized.');
        }
        $this->primary_id->setInputValue($record_id);
        return $this;
    }

    /**
     * Primary key setter.
     * @param string $key
     * @return $this
     */
    public function setPrimaryKey(string $key): static
    {
        $this->primary_id->setKey($key);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws InvalidStateException
     */
    public function setRecordId(?int $record_id): static
    {
        if ($this->id->isDatabaseField()) {
            $this->id->setInputValue($record_id);
            return $this;
        }
        if ($record_id === null) {
            // the record doesn't have a primary key. instead it has a unique index to two other tables and uses
            // those values to update the database. In these cases, $record_id updates are not needed.
            return $this;
        }
        return $this->setPrimaryId($record_id);
    }
}