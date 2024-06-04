<?php
namespace Littled\Request\Inline;

use Littled\Request\BooleanInput;


abstract class InlineStatusInput extends InlineInput
{
	public BooleanInput $status;

	function __construct()
	{
		parent::__construct();
		$this->status = new BooleanInput('Status', 'sid', true, null);
		$this->validateProperties[] = 'status';
	}

	/**
	 * @inheritDoc
	 */
	protected function formatSelectQuery(): array
	{
        $query = "SELECT `enabled` FROM `{$this->table->value}` WHERE id = ?";
		return [$query, 'i' &$this->parent_id->value];
	}

    /**
     * @inheritDoc
     */
    public function formatCommitQuery(): array
    {
        $query = "UPDATE `{$this->table->value}` SET `enabled` = ? WHERE id = ?";
        return [$query, 'ii', &$this->status->value, &$this->parent_id->value];
    }

    /**
     * @inheritDoc
     */
    protected function hasRecordData(): bool
    {
        return $this->status->hasData();
    }

    /**
	 * @inheritDoc
	 */
	public function read(): InlineStatusInput
	{
		$data = parent::read();
		$this->status->value = $data[0]->enabled;
        return $this;
	}
}