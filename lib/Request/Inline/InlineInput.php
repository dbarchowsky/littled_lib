<?php
namespace Littled\Request\Inline;


use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;


/**
 * Class InlineInput
 * @package Littled\Request\Inline
 */
class InlineInput extends SerializedContent
{
	/** @var IntegerInput Parent record id. */
	public $parent_id;
	/** @var StringInput Name of table in database that stores the value that is being updated. */
	public $table;
	/** @var StringInput Operation to be performed, e.g. "edit", "delete", etc. */
	public $op;
	/** @var array Property values to validate after changes are made in an HTML form. */
	public $validateProperties;
	/** @var array Array of validation errors. */
	public $validationErrors;
	/** @var string Possible column names. */
	public $columnNameOptions;
	/** @var string Name of column holding date value. */
	public $columnName;

	/**
	 * InlineInput constructor.
	 */
	function __construct()
	{
		parent::__construct();
		$this->parent_id = new IntegerInput("Parent id", "id", true, false);
		$this->table = new StringInput("Table", "t", true, "", 200);
		$this->op = new StringInput("Operation", "op", true, "", 20);
		$this->validateProperties = array('parent_id', 'table');
		$this->validationErrors = array();
		$this->columnNameOptions = array();
		$this->columnName = "";
	}

	/**
	 * Placeholder for method that formats SQL query string to use to retrieve specific field values from the database.
	 * @throws NotImplementedException
	 */
	protected function formatSelectQuery()
	{
		throw new NotImplementedException(get_class($this)."::formatSelectQuery() not implemented.");
	}

	/**
	 * Placeholder for method that formats SQL query string to use to update specific field values stored in the database.
	 * @throws NotImplementedException
	 */
	protected function formatUpdateQuery()
	{
		throw new NotImplementedException(get_class($this)."::formatUpdateQuery() not implemented.");
	}

	/**
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	protected function getColumnName()
	{
		foreach ($this->columnNameOptions as $column) {
			if ($this->columnExists($column, $this->table->value)) {
				$this->columnName = $column;
				return;
			}
		}
		throw new RecordNotFoundException("No matching columns were found.");
	}

	/**
	 * Retrieves data from database used to fill inline HTML forms.
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function read()
	{
		if (count($this->columnNameOptions) > 0) {
			$this->getColumnName();
		}
		$data = $this->fetchRecords($this->formatSelectQuery());
		if (count($data) < 1) {
			throw new RecordNotFoundException("Record not found.");
		}
		return($data);
	}

	/**
	 * Commits changes made to specific field values through inline HTML forms.
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function save()
	{
		if (count($this->columnNameOptions) > 0) {
			$this->getColumnName();
		}
		$this->query($this->formatUpdateQuery());
	}

	/**
	 * Validates inline HTML edit values.
	 * @throws ContentValidationException
	 */
	public function validateInlineInput()
	{
		foreach($this->validateProperties as $key) {
			try {
				/** @var \Littled\Request\RequestInput $property  */
				$property = $this->$key;
				$property->validate();
			}
			catch(ContentValidationException $ex) {
				array_push($this->validationErrors, $ex->getMessage());
			}
		}
		if (count($this->validationErrors) > 0) {
			throw new ContentValidationException("There were problems found in the information that was entered.");
		}
	}
}