<?php

namespace Littled\Tests\PageContent\Serialized\TestHarness;


use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanInput;
use Littled\Request\DateInput;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;

class TestTable extends SerializedContent
{
	protected static $table_name = 'test_table';

	/** @var StringInput */
	public $name;
	/** @var IntegerInput */
	public $int_col;
	/** @var BooleanInput */
	public $bool_col;
	/** @var DateInput */
	public $date;
	/** @var IntegerInput */
	public $slot;

	public function __construct($id = null)
	{
		parent::__construct($id);
		$this->name = new StringInput('Name', 'name', false, '', 50);
		$this->int_col = new IntegerInput('Integer column', 'intCol');
		$this->bool_col = new BooleanInput('Boolean column', 'boolCol');
		$this->date = new DateInput('Date', 'Date column');
		$this->slot = new IntegerInput('Slot', 'slot');
	}

	/**
	 * @inheritDoc
	 */
	public function generateUpdateQuery(): ?array
	{
		return null;
	}

	/**
	 * @throws RecordNotFoundException
	 */
	public function hydrateFromQueryPublic(string $query, string $types='', &...$vars)
	{
		if ($types) {
			array_unshift($vars, $query, $types);
			call_user_func_array([$this, 'hydrateFromQuery'], $vars);
		}
		else {
			$this->hydrateFromQuery($query, $types, $vars);
		}
	}
}