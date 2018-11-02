<?php
namespace Littled\Request\Inline;


use Littled\Request\StringInput;

/**
 * Class InlineNameInput
 * @package Littled\Request\Inline
 */
class InlineNameInput extends InlineInput
{
	public $name;

	/**
	 * InlineNameInput constructor.
	 * @param array $column_names List of possible column names representing the column in the table that stores the "name" value.
	 */
	function __construct( $column_names=array() )
	{
		parent::__construct();
		$this->name = new StringInput("Name", "n", true, "", 100);
		$this->columnNameOptions = array_merge(array("name", "title", "catno", "code"), $column_names);
		$this->validateProperties = array('parent_id', 'table');
	}

	public function formatSelectQuery()
	{
		return("SEL"."ECT `{$this->columnName}` FROM `{$this->table->value}` WHERE id = {$this->parent_id->value}");
	}

	/**
	 * @return string
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 */
	public function formatUpdateQuery()
	{
		$this->connectToDatabase();
		return ("UPD"."ATE `{$this->table->value}` ".
			"SET `{$this->columnName}` = ".$this->name->escapeSQL($this->mysqli)." ".
			"WHERE id = {$this->parent_id->value}");
	}

	public function read()
	{
		$data = parent::read();
		$this->name->value = $data[0]->name;
	}
}