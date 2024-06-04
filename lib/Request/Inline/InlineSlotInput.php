<?php
namespace Littled\Request\Inline;

use Littled\Request\IntegerInput;


abstract class InlineSlotInput extends InlineInput
{
	public IntegerInput $slot;

	function __construct()
	{
		parent::__construct();
		$this->slot = new IntegerInput('Slot', 'slt', true, null);
		$this->validateProperties[] = 'slot';
	}

	/**
	 * @inheritDoc
	 */
	protected function formatSelectQuery(): array
	{
        $query = "SELECT `slot` FROM `{$this->table->value}` WHERE id = ?";
		return array($query, 'i', &$this->parent_id->value);
	}

    /**
     * @inheritDoc
     */
    public function formatCommitQuery(): array
    {
        $query = "UPDATE `{$this->table->value}` SET `slot` = ? WHERE id = ?";
        return [$query, 'ii', &$this->slot->value, &$this->parent_id->value];
    }

    /**
     * @inheritDoc
     */
    protected function hasRecordData(): bool
    {
        return $this->slot->hasData();
    }

    /**
	 * @inheritDoc
	 */
	public function read(): InlineSlotInput
    {
		$data = parent::read();
		$this->slot->value = $data[0]->slot;
        return $this;
	}
}