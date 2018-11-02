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
	/** @var string Name of column holding date value. */
	public $column_name;

	function __construct()
	{
		parent::__construct();
		$this->date = new DateTextField("Date", "d", true, date("n/j/Y"));
		array_push($this->validateProperties,'date');
		$this->column_name = "";
	}

	/**
	 * @return string Select SQL query
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	protected function formatSelectQuery()
	{
		$this->getColumnName();
		return ("SEL"."ECT date_format(`{$this->column_name}`,'%m/%d/%Y') ".
			"FROM `{$this->table->value}` ".
			"WHERE id = {$this->parent_id->value}");
	}

	protected function formatUpdateQuery()
	{
		return("UPD"."ATE `{$this->table->value}` ".
			"SET `{$this->column_name}` = ".$this->date->escapeSQL($this->mysqli)." ".
			"WHERE id = {$this->parent_id->value}");
	}

	/**
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	protected function getColumnName()
	{
		$arrNames = Array("release_date", "post_date", "posted_date", "date");
		foreach ($arrNames as $col_name) {
			if ($this->columnExists($col_name, $this->table->value)) {
				$this->column_name = $col_name;
				return;
			}
		}
		throw new RecordNotFoundException("No matching columns were found.");
	}
}