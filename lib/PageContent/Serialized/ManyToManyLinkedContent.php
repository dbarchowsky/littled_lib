<?php
namespace Littled\PageContent\Serialized;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotInitializedException;
use Littled\Request\ForeignKeyInput;


abstract class ManyToManyLinkedContent extends SerializedRecordList
{
    public ForeignKeyInput      $primary_id;
    public ForeignKeyInput      $link_id;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->primary_id = new ForeignKeyInput('Primary id', LittledGlobals::ID_KEY, false);
        $this->link_id = new ForeignKeyInput('Link id', 'linkId', false);
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     */
    protected function formatRecordSelectPreparedStmt(): array
    {
        if ($this->hasPrimaryKeyValue()) {
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
     * Return SQL statement to use to delete stale linked records.
     * @param array $stale_link_ids
     * @return array
     * @throws NotInitializedException
     */
    protected function formatDeleteStaleLinksStmt(array $stale_link_ids): array
    {
        $query = 'DELETE FROM `' . $this->getContentTableName() . '` '.
            'WHERE `' . $this->records[0]->primary_id->getColumnName('primary_id') . '` = ? '.
            'AND `' . $this->records[0]->link_id->getColumnName('link_id') . '` '.
            'IN (' . str_repeat('?,', count($stale_link_ids)-1) . '?)';
        return [$query, str_repeat('i', count($stale_link_ids)+1), $this->primary_id->value, ...$stale_link_ids];
    }

    /**
     * @inheritdoc
     * @param ManyToManySerializedRecordLink $e
     */
    protected function getChildRecordId(LinkedContent $e): int|null
    {
        /** @var ManyToManySerializedRecordLink $e */
        return $e->getLinkedId();
    }

    /**
     * @inheritdoc
     * @throws ConfigurationUndefinedException
     */
    public function getLinkedId(): int|null
    {
        if (!isset($this->link_id)) {
            throw new ConfigurationUndefinedException('Link id not defined.');
        }
        return $this->link_id->value;
    }

    /**
     * Returns the key of the linked records' link id.
     * @return string
     * @throws NotInitializedException
     */
    protected function getLinkedKey(): string
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
     * @inheritdoc
     */
    protected static function getLinkedPropertyName(): string
    {
        return 'link_id';
    }

    /**
     * Primary id value getter
     * @return int
     */
    public function getPrimaryId(): int|null
    {
        return $this->primary_id->value;
    }

    /**
     * @inheritdoc
     * Override to make return type specific to this derived class.
     * @returns ManyToManySerializedRecordLink
     */
    protected function instantiateChild(int $record_id = null): ManyToManySerializedRecordLink
    {
        if (!isset(static::$content_class)) {
            throw new NotInitializedException('Content class property has not been assigned a value.');
        }
        $c = (new static::$content_class());
        if ($this->getPrimaryId() > 0) {
            $c->setPrimaryId($this->getPrimaryId());
        }
        return $c->setLinkedId($record_id);
    }

    /**
     * @inheritdoc
     */
    protected function isReadyToRead(): bool
    {
        if (!isset($this->primary_id)) {
            return false;
        }
        return $this->primary_id->value > 0;
    }

    /**
     * @inheritdoc
     */
    public function lookupRecordById(int $record_id): bool|int
    {
        for($i = 0; $i < count($this->records); $i++) {
            if ($this->records[$i]->getLinkedId() === $record_id) {
                return $i;
            }
        }
        return false;
    }

    /**
     * Remove records with link id values matching values in $link_ids from the current list of linked records.
     * @param int|int[] $record_ids
     * @return $this
     */
    public function removeLink(array|int $record_ids): static
    {
        if (!is_array($record_ids)) {
            $record_ids = [$record_ids];
        }
        for($i = count($this->records)-1; $i >= 0; $i--) {
            if (in_array($this->records[$i]->getLinkedId(), $record_ids)) {
                unset($this->records[$i]);
            }
        }
        // re-index
        $this->records = array_values($this->records);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setLinkedId(?int $record_id): static
    {
        parent::setLinkedId($record_id);
        $this->link_id->setInputValue($record_id);
        return $this;
    }

    /**
     * Linked property key value settetr.
     * @param string $key
     * @return $this
     * @throws ConfigurationUndefinedException
     */
    public function setLinkedKey(string $key): static
    {
        if (!isset($this->link_id)) {
            throw new ConfigurationUndefinedException('Link id not defined.');
        }
        $this->link_id->setKey($key);
        return $this;
    }

    /**
     * Primary id setter.
     * @param int|null $record_id
     * @throws NotInitializedException
     * @return $this
     */
    public function setPrimaryId(int|null $record_id): static
    {
        if (!isset($this->primary_id)) {
            throw new NotInitializedException('Primary id object is not initialized.');
        }
        $this->primary_id->setInputValue($record_id);
        foreach($this->records as $record) {
            $record->setPrimaryId($record_id);
        }
        if (isset($this->id)) {
            $this->id->setInputValue($record_id);
        }
        return $this;
    }

    /**
     * @inheritdoc
     * @throws NotInitializedException
     */
    public function setRecordId(?int $record_id): static
    {
        return $this->setPrimaryId($record_id);
    }
}