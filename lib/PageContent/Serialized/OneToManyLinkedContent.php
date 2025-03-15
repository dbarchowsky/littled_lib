<?php
namespace Littled\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotInitializedException;
use Littled\Request\ForeignKeyInput;


/**
 * Maintains a list of records linked to a parent record.
 */
class OneToManyLinkedContent extends SerializedRecordList
{
    public ForeignKeyInput      $parent_id;
    /** @var OneToManySerializedRecordLink[] */
    protected array             $records = [];

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->parent_id = (new ForeignKeyInput())->setKey('id');
        $this->types_lut = ['records' => static::$content_class];
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
            'WHERE `' . $this->records[0]->parent_id->getColumnName('primary_id') . '` = ? '.
            'IN (' . str_repeat('?,', count($stale_link_ids)-1) . '?)';
        return [$query, str_repeat('i', count($stale_link_ids)+1), $this->parent_id->value, ...$stale_link_ids];
    }

    /**
     * @inheritdoc
     * @throws ConfigurationUndefinedException
     * @throws InvalidTypeException
     * @throws InvalidValueException
     * @throws NotInitializedException
     */
    protected function formatRecordSelectPreparedStmt(): array
    {
        $content_class = self::getContentClass();
        if ($content_class === '') {
            throw new NotInitializedException('Content class not set.');
        }
        if (!class_exists($content_class)) {
            throw new InvalidTypeException('Unavailable content class "' . $content_class . '"');
        }
        if (count($this->items()) > 0) {
            $c = $this->items(0);
        }
        else {
            $c = new $content_class();
        }
        $fields = $c->extractPreparedStmtArgs();
        $query = 'SELECT `' .
            implode('`,`', array_map(function ($e) {
                return $e->key;
            }, $fields)) .
            '` FROM `' . $content_class::getTableName(). '` '.
            'WHERE `' .$this->parent_id->getColumnName('parent_id') . '` = ? ';
        return [$query, 'i', &$this->parent_id->value];
    }

    /**
     * @inheritdoc
     * Override to indicate return type specific to the derived class.
     * @param OneToManySerializedRecordLink $e
     */
    protected function getChildRecordId(LinkedContent $e): int|null
    {
        /** @var OneToManySerializedRecordLink $e */
        return $e->getRecordId();
    }

    /**
     * @inheritdoc
     */
    protected function getLinkedKey(): string
    {
        $c = new static::$content_class();
        $key = $c->id->getKey();
        unset($c);
        return $key;
    }

    /**
     * Parent id getter
     * @return int|null
     */
    public function getParentId(): int|null
    {
        return $this->parent_id->value;
    }

    /**
     * @inheritdoc
     */
    public function getLinkedId(): int|null
    {
        return $this->getParentId();
    }

    /**
     * Returns the key of the parent id property.
     * @return string
     */
    protected function getParentKey(): string
    {
        return $this->parent_id->getKey();
    }

    /**
     * @inheritdoc
     */
    protected static function getLinkedPropertyName(): string
    {
        return 'parent_id';
    }

    /**
     * @inheritdoc
     * Override to make return type specific to this derived class.
     * @returns OneToManySerializedRecordLink
     */
    protected function instantiateChild(int $record_id = null): OneToManySerializedRecordLink
    {
        /** @var OneToManySerializedRecordLink $c */
        $c =  parent::instantiateChild($record_id);
        return $c;
    }

    /**
     * @inheritdoc
     */
    protected function isReadyToRead(): bool
    {
        return $this->parent_id->hasData();
    }

    /**
     * @inheritdoc
     * @return OneToManySerializedRecordLink|OneToManySerializedRecordLink[]
     */
    public function items(?int $index = null): OneToManySerializedRecordLink|array
    {
        /** @var OneToManySerializedRecordLink|OneToManySerializedRecordLink[] $i */
        $i = parent::items($index);
        return $i;
    }

    /**
     * @inheritdoc
     */
    public function setLinkedId(?int $record_id): static
    {
        $this->parent_id->setInputValue($record_id);
        return parent::setLinkedId($record_id);
    }

    /**
     * Parent record id value setter.
     * @param int $record_id
     * @return $this
     */
    public function setParentId(int $record_id): static
    {
        return $this->setLinkedId($record_id);
    }

    /**
     * @inheritdoc
     */
    public function setRecordId(?int $record_id): static
    {
        return $this->setParentId($record_id);
    }
}