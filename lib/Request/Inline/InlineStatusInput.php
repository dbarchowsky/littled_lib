<?php
namespace Littled\Request\Inline;


use Littled\Request\BooleanInput;

/**
 * Class InlineStatusInput
 * Handles inline editing of "status" values for content records, e.g. "disabled" vs "enabled".
 * @package Littled\Request\Inline
 */
class InlineStatusInput extends InlineInput
{
	public $status;

	function __construct()
	{
		parent::__construct();
		$this->status = new BooleanInput("Status", "sid", true, null);
		array_push($this->validateProperties,'slot');
	}

	/**
	 * @return string SQL query to use to retrieve access values.
	 */
	protected function formatSelectQuery()
	{
		return("SEL"."ECT `enabled` FROM `{$this->table->value}` WHERE id = {$this->parent_id->value}");
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
			"SET `enabled` = ".$this->status->escapeSQL($this->mysqli)." ".
			"WHERE id = {$this->parent_id->value}");
	}

	/**
	 * Retrieves the access value and stores it in the object properties.
	 * @return void
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function read()
	{
		$data = parent::read();
		$this->status->value = $data[0]->enabled;
	}
}