<?php
namespace Littled\Request\Inline;


use Littled\Exception\RecordNotFoundException;
use Littled\Request\DateTextField;

/**
 * Class InlineDateInput
 * @package Littled\Request\Inline
 */
class InlineDateInput extends InlineInput
{
	/** @var DateTextField Date value */
	public $date;

	/**
	 * InlineDateInput constructor.
	 * @param array $column_names List of possible column names representing the column in the table that stores the "name" value.
	 */
	function __construct( $column_names=array() )
	{
		parent::__construct();
		$this->date = new DateTextField("Date", "d", true, date("n/j/Y"));
		array_push($this->validateProperties,'op');
		$this->columnNameOptions = array_merge(array("release_date", "post_date", "posted_date", "date"), $column_names);
	}

	/**
	 * @return string Select SQL query
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	protected function formatSelectQuery()
	{
		$this->getColumnName();
		return ("SEL"."ECT date_format(`{$this->columnName}`,'%m/%d/%Y') ".
			"FROM `{$this->table->value}` ".
			"WHERE id = {$this->parent_id->value}");
	}

	/**
	 * @return string
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 */
	protected function formatUpdateQuery()
	{
		$this->connectToDatabase();
		return("UPD"."ATE `{$this->table->value}` ".
			"SET `{$this->columnName}` = ".$this->date->escapeSQL($this->mysqli)." ".
			"WHERE id = {$this->parent_id->value}");
	}

	/**
	 * Retrieves the access value and stores it in the object properties.
	 * @return void
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function read()
	{
		$data = parent::read();
		$this->date->value = $data[0]->access;
	}
}