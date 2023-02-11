<?php
namespace Littled\Request\Inline;

use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\StringSelect;


/**
 * Handles inline editing of "access" values for content records, e.g. "public", "private", etc.
 */
class InlineAccessInput extends InlineInput
{
	public StringSelect $access;

	function __construct()
	{
		parent::__construct();
		$this->access = new StringSelect("Access", "aid", true, "", 20);
		$this->validateProperties[] = 'op';
	}

	/**
	 * @inheritDoc
	 */
	protected function formatSelectQuery(): array
	{
		return array("SEL"."ECT `access` FROM `{$this->table->value}` WHERE id = ?", 'i', &$this->parent_id->value);
	}

	/**
	 * @inheritDoc
	 */
	protected function formatUpdateQuery(): array
	{
        $query = "UPD"."ATE `{$this->table->value}`  SET access = ? WHERE id = ?";
		return array ($query, 'si', &$this->access->value, &$this->parent_id->value);
	}

	/**
	 * @inheritDoc
	 */
	public function generateUpdateQuery(): ?array
	{
		$query = "UPD"."ATE `{$this->table->value}` ".
			"SET `$this->column_name` = ? ".
			"WHERE id = ?";
		return array($query, 'si', &$this->access->value, &$this->parent_id->value);
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
		$this->access->value = $data[0]->access;
	}
}