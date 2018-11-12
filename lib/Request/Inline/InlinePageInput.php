<?php
namespace Littled\Request\Inline;


use Littled\Request\IntegerInput;

/**
 * Class InlinePageInput
 * Handles inline editing of "page number" values for content records that are a part of a series.
 * @package Littled\Request\Inline
 */
class InlinePageInput extends InlineInput
{
	public $page;

	function __construct()
	{
		parent::__construct();
		$this->page = new IntegerInput("Page", "pn", true, null);
	}

	/**
	 * @return string SQL query to use to retrieve access values.
	 */
	protected function formatSelectQuery()
	{
		return("SEL"."ECT `page_number` FROM `{$this->table->value}` WHERE id = {$this->parent_id->value}");
	}

	/**
	 * @return string SQL query to use to update access values.
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 */
	protected function formatUpdateQuery()
	{
		$this->connectToDatabase();
		return("UPD"."ATE `{$this->table->value}` ".
			"SET `page_number` = ".$this->page->escapeSQL($this->mysqli)." ".
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
		$this->page->value = $data[0]->page;
	}
}