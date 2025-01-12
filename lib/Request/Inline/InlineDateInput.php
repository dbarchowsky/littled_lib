<?php

namespace Littled\Request\Inline;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\DateTextField;


abstract class InlineDateInput extends InlineInput
{
    public DateTextField $date;

    /**
     * @inheritdoc
     * @param string[] $column_names List of possible column names representing the column in the table that stores
     * the "name" value.
     */
    function __construct(array $column_names = [])
    {
        parent::__construct();
        $this->date = new DateTextField('Date', 'd', true, date('n/j/Y'));
        $this->validateProperties[] = 'op';
        $this->columnNameOptions = array_merge(['release_date', 'post_date', 'posted_date', 'date'], $column_names);
    }

    /**
     * @inheritDoc
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    protected function formatSelectQuery(): array
    {
        $this->getColumnName();
        $query = 'SELECT DATE_FORMAT(`$this->column_name`,\'%m/%d/%Y\') AS `date` ' .
            "FROM `{$this->table->value}` " .
            'WHERE id = ?';
        return [$query, 'i', &$this->parent_id->value];
    }

    /**
     * @inheritDoc
     */
    public function formatCommitQuery(): array
    {
        $query = "UPDATE `{$this->table->value}` " .
            "SET `$this->column_name` = " . $this->date->escapeSQL($this->mysqli) . ' ' .
            'WHERE id = ?';
        return [$query, 'i', &$this->parent_id->value];
    }

    /**
     * @inheritDoc
     */
    protected function hasRecordData(): bool
    {
        return $this->date->hasData();
    }

    /**
     * Retrieves the access value and stores it in the object properties.
     * @return InlineDateInput
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    public function read(): InlineDateInput
    {
        $data = parent::read();
        $this->date->value = $data[0]->date;
        return $this;
    }
}