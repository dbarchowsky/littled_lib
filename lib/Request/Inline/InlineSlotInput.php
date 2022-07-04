<?php
namespace Littled\Request\Inline;

use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Request\IntegerInput;


class InlineSlotInput extends InlineInput
{
	public IntegerInput $slot;

	function __construct()
	{
		parent::__construct();
		$this->slot = new IntegerInput("Slot", "slt", true, null);
		$this->validateProperties[] = 'slot';
	}

	/**
	 * @inheritDoc
	 */
	protected function formatSelectQuery(): array
	{
        $query = "SEL"."ECT `slot` FROM `{$this->table->value}` WHERE id = ?";
		return array($query, 'i', &$this->parent_id->value);
	}

	/**
	 * @inheritDoc
	 */
	protected function formatUpdateQuery(): array
	{
        return $this->generateUpdateQuery();
	}

    /**
     * @inheritDoc
     */
    public function generateUpdateQuery(): ?array
    {
        $query = "UPD"."ATE `{$this->table->value}` SET `slot` = ? WHERE id = ?";
        return array($query, 'ii', &$this->slot->value, &$this->parent_id->value);
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
		$this->slot->value = $data[0]->slot;
	}
}