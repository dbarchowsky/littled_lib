<?php
namespace Littled\Request\Inline;

use Littled\Request\StringSelect;


/**
 * Handles inline editing of "access" values for content records, e.g. "public", "private", etc.
 */
abstract class InlineAccessInput extends InlineInput
{
	public StringSelect $access;

	function __construct()
	{
		parent::__construct();
		$this->access = new StringSelect('Access', 'aid', true, '', 20);
		$this->validateProperties[] = 'op';
	}

	/**
	 * @inheritDoc
	 */
	protected function formatSelectQuery(): array
	{
		return ["SELECT `access` FROM `{$this->table->value}` WHERE id = ?", 'i', &$this->parent_id->value];
	}

	/**
	 * @inheritDoc
	 */
	protected function formatUpdateQuery(): array
	{
        $query = "UPDATE `{$this->table->value}`  SET access = ? WHERE id = ?";
		return [$query, 'si', &$this->access->value, &$this->parent_id->value];
	}

    /**
     * @inheritDoc
     */
    protected function formatCommitQuery(): array
    {
		$query = "UPDATE `{$this->table->value}` ".
			"SET `$this->column_name` = ? ".
            'WHERE id = ?';
		return [$query, 'si', &$this->access->value, &$this->parent_id->value];
	}

    /**
     * @inheritDoc
     */
    protected function hasRecordData(): bool
    {
        return $this->access->hasData();
    }

    /**
	 * @inheritDoc
	 */
	public function read(): InlineAccessInput
    {
		$data = parent::read();
		$this->access->value = $data[0]->access;
        return $this;
	}
}