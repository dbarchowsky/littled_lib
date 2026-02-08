<?php
namespace Littled\PageContent\Serialized;

use Littled\App\LittledGlobals;

/**
 * Maintains a list of one-to-many records linked to a parent record.
 */
class OneToManyRecordList extends SerializedRecordList
{
    /**
     * @inheritDoc
     */
    protected function formatDeleteStaleLinksStmt(array $stale_link_ids): array
    {
        $query = 'DELETE FROM `' . static::getTableName() . '` '.
            'WHERE `' . $this->records[0]->id->getColumnName('id') . '` IN '.
            '(' . str_repeat('?,', count($stale_link_ids)-1) . '?)';
        return [$query, str_repeat('i', count($stale_link_ids)), ...$stale_link_ids];
    }

    /**
     * @inheritdoc
     */
    protected function getLinkedKey(): string
    {
        $c = static::getContentClass();
        if ($c === '') {
            return $c;
        }
        return (new $c())->id->getKey();
    }

    /**
     * @inheritdoc
     * Override to make the return type specific to this derived class.
     * @returns OneToManyRecordLink
     */
    protected function instantiateChild(int|null $linked_id = null): LinkedContent|OneToManyRecordLink
    {
        return parent::instantiateChild($linked_id);
    }

    /**
     * @inheritdoc
     * Override to make the return type specific to this derived class.
     * @return OneToManyRecordLink|OneToManyRecordLink[]
     */
    public function items(?int $index = null): OneToManyRecordLink|array
    {
        return parent::items($index);
    }
}