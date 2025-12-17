<?php
namespace Littled\PageContent\Serialized;

/**
 * Maintains a list of junction records linked to a parent record.
 */
abstract class JunctionRecordList extends SerializedRecordList
{
    /**
     * @inheritDoc
     */
    protected function formatDeleteStaleLinksStmt(array $stale_link_ids): array
    {
        $query = 'DELETE FROM `' . $this->getTableName() . '` '.
            'WHERE `' . $this->records[0]->parent_id->getColumnName('parent_id') . '` = ? '.
            'AND `' . $this->records[0]->link_id->getColumnName('link_id') . '` '.
            'IN (' . str_repeat('?,', count($stale_link_ids)-1) . '?)';
        return [$query, str_repeat('i', count($stale_link_ids)+1), $this->parent_id->value, ...$stale_link_ids];
    }

    /**
     * @inheritdoc
     * Override to make the return type specific to this derived class.
     * @returns JunctionRecordLink
     */
    protected function instantiateChild(int|null $linked_id = null): LinkedContent|JunctionRecordLink
    {
        return parent::instantiateChild($linked_id);
    }

    /**
     * @inheritdoc
     * Override to make the return type specific to this derived class.
     * @return JunctionRecordLink|JunctionRecordLink[]
     */
    public function items(?int $index = null): JunctionRecordLink|array
    {
        return parent::items($index);
    }
}