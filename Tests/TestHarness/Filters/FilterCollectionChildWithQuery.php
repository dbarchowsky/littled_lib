<?php
namespace LittledTests\TestHarness\Filters;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;

class FilterCollectionChildWithQuery extends FilterCollectionChild
{
    /**
     * @inheritDoc
     */
    protected function formatListingsQuery(bool $calculate_offset=true): array
    {
		parent::formatListingsQuery($calculate_offset);
        $query = "SEL"."ECT a.`id`, a.`name`, a.`int_col`, a.`bool_col`, a.`date`, a.`slot` FROM `test_table` a".
            $this->formatQueryClause().
            " ORDER BY a.`date` DESC, IFNULL(a.`slot`,999999), a.id DESC".
            " LIMIT ?, ?";
        return array($query, 'ii', &$this->listings_offset, &$this->listings_length->value);
    }

    /**
     * @return string
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    protected function formatQueryClause(): string
    {
        $this->connectToDatabase();
        return " WHERE (NULLIF(".$this->name_filter->escapeSQL($this->mysqli).", '') IS NULL OR a.`name` LIKE ".$this->name_filter->escapeSQL($this->mysqli).")";
    }
}