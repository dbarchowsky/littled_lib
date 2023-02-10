<?php
namespace Littled\Request\Inline;

use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\DateTextField;


class InlineDateInput extends InlineInput
{
	public DateTextField $date;

	/**
	 * InlineDateInput constructor.
	 * @param array $column_names List of possible column names representing the column in the table that stores the "name" value.
	 */
	function __construct( $column_names=array() )
	{
		parent::__construct();
		$this->date = new DateTextField("Date", "d", true, date("n/j/Y"));
		$this->validateProperties[] = 'op';
		$this->columnNameOptions = array_merge(array("release_date", "post_date", "posted_date", "date"), $column_names);
	}

	/**
	 * @inheritDoc
     * @throws InvalidQueryException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
	protected function formatSelectQuery(): array
	{
		$this->getColumnName();
        $query = "SEL"."ECT DATE_FORMAT(`$this->column_name`,'%m/%d/%Y') AS `date` ".
            "FROM `{$this->table->value}` ".
            "WHERE id = ?";
		return array ($query, 'i', &$this->parent_id->value);
	}

    /**
     * @inheritDoc
     */
    protected function formatUpdateQuery(): array
    {
        return $this->generateUpdateQuery();
    }

    /**
     * @inheritDoc
     */
    public function generateUpdateQuery(): ?array
    {
        $query = "UPD"."ATE `{$this->table->value}` ".
            "SET `$this->column_name` = ".$this->date->escapeSQL($this->mysqli)." ".
            "WHERE id = ?";
        return array($query, 'i', &$this->parent_id->value);
    }

	/**
	 * Retrieves the access value and stores it in the object properties.
	 * @return void
	 * @throws InvalidQueryException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	public function read()
    {
        $data = parent::read();
        $this->date->value = $data[0]->date;
    }
}