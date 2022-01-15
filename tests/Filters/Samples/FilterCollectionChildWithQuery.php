<?php
namespace Littled\Tests\Filters\Samples;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;

class FilterCollectionChildWithQuery extends FilterCollectionChild
{
    /**
     * @return array
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    protected function formatListingsQuery(): array
    {
        $first = $this->calcRecordPosition();
        $query = "SEL"."ECT a.`id`, a.`name`, a.`int_col`, a.`bool_col`, a.`date`, a.`slot` FROM `test_table` a".
            $this->formatQueryClause().
            " ORDER BY a.`date` DESC, IFNULL(a.`slot`,999999), a.id DESC".
            " LIMIT $first, {$this->listings_length->value}";
        return array($query);
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