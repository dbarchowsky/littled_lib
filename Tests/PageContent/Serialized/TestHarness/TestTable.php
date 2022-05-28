<?php

namespace Littled\Tests\PageContent\Serialized\TestHarness;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanInput;
use Littled\Request\DateInput;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;

class TestTable extends SerializedContent
{
    /** @var int */
    public const CONTENT_TYPE_ID = 6037;
    /** @var int */
    protected static int $content_type_id = self::CONTENT_TYPE_ID;
    /** @var string */
	protected static string $table_name = 'test_table';

	/** @var StringInput */
	public StringInput $name;
	/** @var IntegerInput */
	public IntegerInput $int_col;
	/** @var BooleanInput */
	public BooleanInput $bool_col;
	/** @var DateInput */
	public DateInput $date;
	/** @var IntegerInput */
	public IntegerInput $slot;

	/**
	 * Class constructor.
	 * @param int|null $id Record id.
	 * @param string $name Test string field.
	 * @param int|null $int_col Test integer value field.
	 * @param bool|null $bool_col Test boolean value field.
	 * @param string $date Test date value field.
	 * @param int|null $slot Place of the record within listings of similar records.
	 */
	public function __construct(?int $id = null, string $name='', ?int $int_col=null, ?bool $bool_col=null, string $date='', ?int $slot=null)
	{
		parent::__construct($id);
		$this->name = new StringInput('Name', 'name', false, $name, 50);
		$this->int_col = new IntegerInput('Integer column', 'intCol', false, $int_col);
		$this->bool_col = new BooleanInput('Boolean column', 'boolCol', false, $bool_col);
		$this->date = new DateInput('Date', 'Date column', false, $date);
		$this->slot = new IntegerInput('Slot', 'slot', false, $slot);
	}

	/**
	 * @param array $used_keys
	 * @return array
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 */
    public function formatDatabaseColumnListPublic(array $used_keys = []): array
    {
        return parent::formatDatabaseColumnList($used_keys);
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