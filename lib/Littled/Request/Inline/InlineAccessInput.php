<?php
namespace Littled\Request\Inline;


use Littled\Request\StringSelect;

/**
 * Class InlineAccessInput
 * Handles inline editing of "access" values for content records, e.g. "public", "private", etc.
 * @package Littled\Request\Inline
 */
class InlineAccessInput extends InlineInput
{
	public $access;

	function __construct()
	{
		parent::__construct();
		$this->access = new StringSelect("Access", "aid", true, "", 20);
		$this->validateProperties = array('parent_id', 'table', 'op', 'access');
	}

	/**
	 * @return string SQL query to use to retrieve access values.
	 */
	protected function formatSelectQuery()
	{
		return("SEL"."ECT `access` FROM `{$this->table->value}` WHERE id = {$this->parent_id->value}");
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
			"SET access = ".$this->access->escapeSQL($this->mysqli)." ".
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
		$this->access->value = $data[0]->access;
	}
}