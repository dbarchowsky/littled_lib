<?php
namespace Littled\Request\Inline;


use Littled\Request\IntegerInput;

/**
 * Class InlineSlotInput
 * Handles inline editing of "slot" values for content records that are a part of a series.
 * @package Littled\Request\Inline
 */
class InlineSlotInput extends InlineInput
{
	public $slot;

	function __construct()
	{
		parent::__construct();
		$this->slot = new IntegerInput("Slot", "slt", true, null);
		array_push($this->validateProperties,'slot');
	}

	/**
	 * @return string SQL query to use to retrieve access values.
	 */
	protected function formatSelectQuery()
	{
		return("SEL"."ECT `slot` FROM `{$this->table->value}` WHERE id = {$this->parent_id->value}");
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
			"SET `slot` = ".$this->slot->escapeSQL($this->mysqli)." ".
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
		$this->slot->value = $data[0]->slot;
	}
}